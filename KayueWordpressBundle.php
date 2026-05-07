<?php

namespace Kayue\WordpressBundle;

use Kayue\WordpressBundle\DependencyInjection\Compiler\ShortcodeCompilerPass;
use Kayue\WordpressBundle\DependencyInjection\Compiler\PreventDoctrineMetadataCompilerPass;
use Kayue\WordpressBundle\DependencyInjection\Security\Factory\WordpressFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class KayueWordpressBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new WordpressFactory());

        $container->addCompilerPass(new ShortcodeCompilerPass());
        $container->addCompilerPass(new PreventDoctrineMetadataCompilerPass());
    }
}
