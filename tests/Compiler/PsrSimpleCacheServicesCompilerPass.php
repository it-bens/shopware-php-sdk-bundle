<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Compiler;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class PsrSimpleCacheServicesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $arrayAdapter = new Definition(ArrayAdapter::class);
        $container->setDefinition('test_cache', $arrayAdapter);

        $psr16Cache = new Definition(Psr16Cache::class);
        $psr16Cache->setArguments([
            '$pool' => new Reference('test_cache'),
        ]);
        $container->setDefinition('cache.app', $psr16Cache);
    }
}
