<?php

namespace Kayue\WordpressBundle\Tests\DependencyInjection;

use Kayue\WordpressBundle\DependencyInjection\KayueWordpressExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class KayueWordpressExtensionTest extends TestCase
{
    public function testImplementsPrependExtensionInterface()
    {
        $extension = new KayueWordpressExtension();

        $this->assertInstanceOf(PrependExtensionInterface::class, $extension);
    }

    public function testPrependRegistersDoctrineTypes()
    {
        $extension = new KayueWordpressExtension();
        $container = new ContainerBuilder();

        $extension->prepend($container);

        $configs = $container->getExtensionConfig('doctrine');
        $this->assertNotEmpty($configs, 'Doctrine config should be prepended');

        $merged = array_merge_recursive(...$configs);
        $types = $merged['dbal']['types'] ?? [];

        $this->assertArrayHasKey('wordpressmeta', $types);
        $this->assertArrayHasKey('wordpressid', $types);
        $this->assertEquals('Kayue\WordpressBundle\Types\WordpressMetaType', $types['wordpressmeta']);
        $this->assertEquals('Kayue\WordpressBundle\Types\WordpressIdType', $types['wordpressid']);
    }

    public function testPrependDoesNotRegisterUnrelatedConfig()
    {
        $extension = new KayueWordpressExtension();
        $container = new ContainerBuilder();

        $extension->prepend($container);

        $configs = $container->getExtensionConfig('doctrine');
        $merged = array_merge_recursive(...$configs);

        $this->assertArrayNotHasKey('orm', $merged, 'Should only configure DBAL types, not ORM');
    }
}
