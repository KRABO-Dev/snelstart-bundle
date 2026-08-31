<?php

namespace Krabo\SnelstartBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SnelstartExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('krabo.snelstart.client_key', $config['client_key']);
        $container->setParameter('krabo.snelstart.primary_key', $config['primary_key']);
        $container->setParameter('krabo.snelstart.secondary_key', $config['secondary_key']);
        $container->setParameter('krabo.snelstart.kostenplaats', $config['kostenplaats']);
        $container->setParameter('krabo.snelstart.limit', $config['limit']);
        $container->setParameter('krabo.snelstart.grootboek_omzet_hoog_nl', $config['grootboek_omzet_hoog_nl']);
        $container->setParameter('krabo.snelstart.grootboek_omzet_laag_nl', $config['grootboek_omzet_laag_nl']);
        $container->setParameter('krabo.snelstart.grootboek_omzet_eu_standaard', $config['grootboek_omzet_eu_standaard']);
        $container->setParameter('krabo.snelstart.grootboek_omzet_eu_verlaagd_hoog', $config['grootboek_omzet_eu_verlaagd_hoog']);
        $container->setParameter('krabo.snelstart.grootboek_omzet_eu_verlaagd_laag', $config['grootboek_omzet_eu_verlaagd_laag']);
        $container->setParameter('krabo.snelstart.grootboek_omzet_eu_super_verlaagd', $config['grootboek_omzet_eu_super_verlaagd']);
        $container->setParameter('krabo.snelstart.grootboek_omzet_eu_geen', $config['grootboek_omzet_eu_geen']);
        $container->setParameter('krabo.snelstart.grootboek_omzet_wereld', $config['grootboek_omzet_wereld']);
    }
}
