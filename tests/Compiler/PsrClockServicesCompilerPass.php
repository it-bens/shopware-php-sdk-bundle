<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Compiler;

use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\NativeClock;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class PsrClockServicesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $nativeClock = new Definition(NativeClock::class);
        $container->setDefinition('clock', $nativeClock);

        $container->setAlias(ClockInterface::class, 'clock');
    }
}
