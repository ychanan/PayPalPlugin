<?php

declare(strict_types=1);

namespace Sylius\PayPalPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('sylius_pay_pal_plugin');
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('sylius_pay_pal_plugin');
        }

        $rootNode
            ->children()
                ->booleanNode('sandbox')->defaultTrue()->end()
                ->arrayNode('logging')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('increased')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
