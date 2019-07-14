<?php

namespace Alamirault\ElasticsearchBundle\DependencyInjection;

use Alamirault\ElasticsearchBundle\Model\IndexRegistry;
use Alamirault\ElasticsearchBundle\Service\ElasticsearchClient;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AlamiraultElasticsearchFormExtension extends Extension
{

    private $clients = [];

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        if (empty($config['clients'])) {
            return;
        }

        $this->loadClients($config['clients'], $container);
        $this->loadIndexes($config['clients'], $container);
    }

    /**
     * Loads the configured clients.
     *
     * @param array $clients
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    private function loadClients(array $clients, ContainerBuilder $container): void
    {

        foreach ($clients as $name => $clientConfig) {
            $clientId = sprintf('alamirault_elasticsearch_form.client.%s', $name);

            $logger = null;
            if (isset($clientConfig["logger"])) {
                $logger = new Reference('monolog.' . $clientConfig["logger"]);
                unset($clientConfig["logger"]);
            }


            $serializer = new Reference("serializer");
            $clientDefinition = new Definition(ElasticsearchClient::class, [
                [
                    "connections" => $clientConfig["connections"],
                ],
                null,
                $logger,
                $serializer,
                new Reference("Alamirault\ElasticsearchBundle\Logger\RequestLogger"),
                $clientConfig["max_result_window"],
            ]);

            $container->setDefinition($clientId, $clientDefinition);
            $this->clients[$name] = $clientDefinition;
        }
    }

    private function loadIndexes(array $clients, ContainerBuilder $container)
    {
        foreach ($clients as $clientName => $client) {
            $indexDefinitionsData = [];
            foreach ($client["indexes"] as $index => $data) {

                $indexDefinitionsData[] = [
                    "name" => $index,
                    "mapping" => $data["mapping"],
                ];
            }

            $clientDefinitionReference = new Reference(sprintf('alamirault_elasticsearch_form.client.%s', $clientName));

            $indexRegistryName = sprintf('alamirault_elasticsearch_form.index_registry.%s', $clientName);
            $indexRegistryDefinition = new Definition(IndexRegistry::class, [$clientDefinitionReference, $indexDefinitionsData]);
            $container->setDefinition($indexRegistryName, $indexRegistryDefinition);
        }
    }
}
