<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Functional\Context;

use ITB\ShopwareSdkBundle\DependencyInjection\Configuration;
use ITB\ShopwareSdkBundle\Tests\ITBShopwareSdkBundleKernel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Vin\ShopwareSdk\Context\ContextBuilderFactory;
use Vin\ShopwareSdk\Context\ContextBuilderFactoryInterface;

/**
 * @phpstan-import-type ITBShopwareSdkConfiguration from Configuration
 */
final class ServicesTest extends TestCase
{
    public static function provider(): \Generator
    {
        $config = Yaml::parseFile(__DIR__ . '/../../Fixtures/Configuration/config_with_enabled_cache.yaml');

        yield [$config];
    }

    /**
     * @param ITBShopwareSdkConfiguration $config
     */
    #[DataProvider('provider')]
    public function test(array $config): void
    {
        $kernel = new ITBShopwareSdkBundleKernel('test', true, $config);
        $kernel->boot();

        $container = $kernel->getContainer();
        $contextBuilderFactory = $container->get(ContextBuilderFactoryInterface::class);
        $this->assertInstanceOf(ContextBuilderFactory::class, $contextBuilderFactory);
    }
}
