<?php

namespace Alamirault\ElasticsearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('alamirault_elasticsearch_form');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('alamirault_elasticsearch_form');
        }

        $this->addClientsSection($rootNode);

        return $treeBuilder;
    }

    private function addClientsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('client')
            ->children()
            ->arrayNode('clients')
            ->useAttributeAsKey('id')
            ->prototype('array')
            ->children()
            ->arrayNode('connections')
            ->prototype('array')
            ->children()
            ->scalarNode('username')->end()
            ->scalarNode('password')->end()
            ->scalarNode('host')->end()
            ->scalarNode('port')->end()
            ->scalarNode('proxy')->end()
            ->end()
            ->end()
            ->end()
            ->scalarNode('logger')->end()
            ->integerNode('max_result_window')->defaultValue(10000)->end()
            ->arrayNode("indexes")
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children()
            ->scalarNode('mapping')->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();
    }
}
