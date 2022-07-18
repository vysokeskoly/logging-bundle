<?php declare(strict_types=1);

namespace VysokeSkoly\LoggingBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class VysokeSkolyLoggingExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $config);

        $container->setParameter('vysokeskoly_logging.app_id', $config['app_id']);
        $container->setParameter('vysokeskoly_logging.perflog_threshold', $config['perflog_threshold']);
        $container->setParameter(
            'vysokeskoly_logging.doctrine_execute_time_threshold',
            array_key_exists('doctrine_execute_time_threshold', $config) ? $config['doctrine_execute_time_threshold'] : null
        );
        $container->setParameter('vysokeskoly_logging.graylog_hostname', $config['graylog']['hostname']);
        $container->setParameter('vysokeskoly_logging.graylog_port', $config['graylog']['port']);
        $container->setParameter('vysokeskoly_logging.graylog_facility', $config['graylog']['facility']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        if (isset($config['doctrine_execute_time_threshold'])) {
            $loader->load('services_doctrine.xml');
        }
    }
}
