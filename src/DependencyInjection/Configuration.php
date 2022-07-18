<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('vysoke_skoly_logging');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('app_id')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('perflog_threshold')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('doctrine_execute_time_threshold')->defaultNull()->end()
                ->arrayNode('graylog')
                    ->children()
                        ->scalarNode('facility')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('hostname')->isRequired()->cannotBeEmpty()->end()
                        ->integerNode('port')
                            ->defaultValue(12201)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
