<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/2/19 5:09 PM
 */

namespace Alamirault\ElasticsearchBundle\Tests\DependencyInjection;


use Alamirault\ElasticsearchBundle\DependencyInjection\AlamiraultElasticsearchFormExtension;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Serializer\Serializer;

class AlamiraultElasticsearchFormExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    public function testLoadClientFromConfig()
    {

        $config = [
            "alamirault_elasticsearch_form" => [
                "clients" => [
                    "client_name" => [
                        "connections" => [
                            [
                                "host" => "connection1_url",
                                "port" => 9200,
                                "proxy" => null,
                            ],
                            [
                                "host" => "connection2_url",
                                "port" => 9300,
                                "proxy" => null,
                            ],
                        ],
                        "logger" => "logger.api",
                        "indexes" => [
                            "bo_log_entry" => [
                                "mapping" => "/var/www/html/src/Alamirault/LogsBundle/Resources/elasticsearch/mapping/changes.yml",
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $extension = new AlamiraultElasticsearchFormExtension();
        $extension->load($config, $this->container);

        $this->assertTrue($this->container->has("alamirault_elasticsearch_form.client.client_name"));
        $this->assertTrue($this->container->has('alamirault_elasticsearch_form.index_registry.client_name'));
    }

    protected function setUp()
    {
        $this->container = new ContainerBuilder();

        $monolog = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $serializer = $this->getMockBuilder(Serializer::class)->getMock();

        $this->container->register("monolog.logger.api", $monolog);
        $this->container->register("serializer", $serializer);

        $definition = new Definition("Alamirault\ElasticsearchBundle\Logger\RequestLogger", [$monolog]);

        $this->container->setDefinition("Alamirault\ElasticsearchBundle\Logger\RequestLogger", $definition);
    }
}