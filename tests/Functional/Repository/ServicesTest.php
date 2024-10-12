<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Functional\Repository;

use ITB\ShopwareSdkBundle\Tests\ITBShopwareSdkBundleKernel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Vin\ShopwareSdk\Repository\RepositoryProvider;
use Vin\ShopwareSdk\Repository\RepositoryProviderInterface;

final class ServicesTest extends TestCase
{
    public static function provider(): \Generator
    {
        $config = Yaml::parseFile(__DIR__ . '/../../Fixtures/Configuration/config_with_enabled_cache.yaml');

        yield [$config];
    }

    #[DataProvider('provider')]
    public function test(array $config): void
    {
        $kernel = new ITBShopwareSdkBundleKernel('test', true, $config);
        $kernel->boot();

        $container = $kernel->getContainer();
        $repositoryProvider = $container->get(RepositoryProviderInterface::class);
        $this->assertInstanceOf(RepositoryProvider::class, $repositoryProvider);
    }
}
