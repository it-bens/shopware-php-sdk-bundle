<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Functional\Auth;

use ITB\ShopwareSdkBundle\DependencyInjection\Configuration;
use ITB\ShopwareSdkBundle\Tests\ITBShopwareSdkBundleKernel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Vin\ShopwareSdk\Auth\AccessTokenProvider;
use Vin\ShopwareSdk\Auth\AccessTokenProvider\WithClientCredentials;
use Vin\ShopwareSdk\Auth\AccessTokenProvider\WithUsernameAndPassword;

/**
 * @phpstan-import-type ITBShopwareSdkConfiguration from Configuration
 */
final class AccessTokenProviderTest extends TestCase
{
    public static function configurationWithClientCredentialsProvider(): \Generator
    {
        $config = Yaml::parseFile(__DIR__ . '/../../Fixtures/Configuration/config_with_client_credentials.yaml');

        yield [$config];
    }

    public static function configurationWithUsernameAndPasswordProvider(): \Generator
    {
        $config = Yaml::parseFile(__DIR__ . '/../../Fixtures/Configuration/config_with_username_password.yaml');

        yield [$config];
    }

    /**
     * @param ITBShopwareSdkConfiguration $config
     */
    #[DataProvider('configurationWithClientCredentialsProvider')]
    public function testConfigurationWithClientCredentials(array $config): void
    {
        $kernel = new ITBShopwareSdkBundleKernel('test', true, $config);
        $kernel->boot();

        $accessTokenProvider = $kernel->getContainer()
            ->get(AccessTokenProvider::class);
        $this->assertInstanceOf(WithClientCredentials::class, $accessTokenProvider);
    }

    /**
     * @param ITBShopwareSdkConfiguration $config
     */
    #[DataProvider('configurationWithUsernameAndPasswordProvider')]
    public function testConfigurationWithUsernameAndPassword(array $config): void
    {
        $kernel = new ITBShopwareSdkBundleKernel('test', true, $config);
        $kernel->boot();

        $accessTokenProvider = $kernel->getContainer()
            ->get(AccessTokenProvider::class);
        $this->assertInstanceOf(WithUsernameAndPassword::class, $accessTokenProvider);
    }
}
