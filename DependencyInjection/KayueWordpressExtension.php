<?php

namespace Kayue\WordpressBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class KayueWordpressExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine', [
            'dbal' => [
                'types' => [
                    'wordpressmeta' => 'Kayue\WordpressBundle\Types\WordpressMetaType',
                    'wordpressid' => 'Kayue\WordpressBundle\Types\WordpressIdType',
                ],
            ],
        ]);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $key => $value) {
            $container->setParameter("kayue_wordpress.".$key, $value);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setAlias('kayue_wordpress.dbal.connection', sprintf('doctrine.dbal.%s_connection', $config['connection']));
        $container->setAlias('kayue_wordpress.orm.metadata_cache.pool', $config['orm']['metadata_cache_pool']);
        $container->setAlias('kayue_wordpress.orm.result_cache.pool', $config['orm']['result_cache_pool']);
        $container->setAlias('kayue_wordpress.orm.query_cache.pool', $config['orm']['query_cache_pool']);
    }
}
