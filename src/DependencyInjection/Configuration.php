<?php

namespace Krabo\SnelstartBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('krabo_snelstart');
        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('client_key')->isRequired()->end()
            ->scalarNode('primary_key')->isRequired()->end()
            ->scalarNode('secondary_key')->isRequired()->end()
            ->integerNode('kostenplaats')->defaultNull()->end()
            ->integerNode('limit')->defaultValue(25)->end()
            ->scalarNode('grootboek_omzet_hoog_nl')->isRequired()->end()
            ->scalarNode('grootboek_omzet_laag_nl')->isRequired()->end()
            ->scalarNode('grootboek_omzet_eu_standaard')->isRequired()->end()
            ->scalarNode('grootboek_omzet_eu_verlaagd_hoog')->isRequired()->end()
            ->scalarNode('grootboek_omzet_eu_verlaagd_laag')->isRequired()->end()
            ->scalarNode('grootboek_omzet_eu_super_verlaagd')->isRequired()->end()
            ->scalarNode('grootboek_omzet_eu_geen')->isRequired()->end()
            ->scalarNode('grootboek_omzet_wereld')->isRequired()->end()
            ->end();
        return $treeBuilder;
    }


}