<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Functional\Auth;

use ITB\ShopwareSdkBundle\Tests\ITBShopwareSdkBundleKernel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Vin\ShopwareSdk\Auth\AccessTokenFetcher;
use Vin\ShopwareSdk\Auth\AccessTokenFetcher\CachedFetcher;
use Vin\ShopwareSdk\Auth\AccessTokenFetcher\SimpleFetcher;

final class AccessTokenFetcherTest extends TestCase
{
    public static function configurationWithDisabledCacheProvider(): \Generator
    {
        $config = Yaml::parseFile(__DIR__ . '/../../Fixtures/Configuration/config_with_disabled_cache.yaml');

        yield [$config];
    }

    public static function configurationWithEnabledCacheProvider(): \Generator
    {
        $config = Yaml::parseFile(__DIR__ . '/../../Fixtures/Configuration/config_with_enabled_cache.yaml');

        yield [$config];
    }

    #[DataProvider('configurationWithDisabledCacheProvider')]
    public function testConfigurationWithDisabledCache(array $config): void
    {
        $kernel = new ITBShopwareSdkBundleKernel('test', true, $config);
        $kernel->boot();

        $accessTokenFetcher = $kernel->getContainer()
            ->get(AccessTokenFetcher::class);
        $this->assertInstanceOf(SimpleFetcher::class, $accessTokenFetcher);
    }

    #[DataProvider('configurationWithEnabledCacheProvider')]
    public function testConfigurationWithEnabledCache(array $config): void
    {
        $kernel = new ITBShopwareSdkBundleKernel('test', true, $config);
        $kernel->boot();

        $accessTokenFetcher = $kernel->getContainer()
            ->get(AccessTokenFetcher::class);
        $this->assertInstanceOf(CachedFetcher::class, $accessTokenFetcher);
    }
}
