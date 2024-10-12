<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Compiler;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpClient\Psr18Client;

final class PsrHttpServicesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $symfonyPsrHttpClient = new Definition(Psr18Client::class);
        $container->setDefinition('http_client', $symfonyPsrHttpClient);

        $container->setAlias(RequestFactoryInterface::class, 'http_client');
        $container->setAlias(StreamFactoryInterface::class, 'http_client');
        $container->setAlias(ClientInterface::class, 'http_client');
    }
}
