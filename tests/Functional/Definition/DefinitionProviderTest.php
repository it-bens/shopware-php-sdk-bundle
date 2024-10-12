<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Functional\Definition;

use ITB\ShopwareSdkBundle\DependencyInjection\Configuration;
use ITB\ShopwareSdkBundle\Tests\Functional\Definition\DefinitionProviderTest\DefinitionProviderTestKernel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Vin\ShopwareSdk\Definition\DefinitionProviderInterface;

/**
 * @phpstan-import-type ITBShopwareSdkConfiguration from Configuration
 */
final class DefinitionProviderTest extends TestCase
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
    public function testWithAdditionalDefinitionCollectionPopulator(array $config): void
    {
        $kernel = new DefinitionProviderTestKernel('test', true, $config);
        $kernel->boot();

        $container = $kernel->getContainer();

        // The kernel registers a definition collection populator that throws an exception.
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This exception is expected.');
        $container->get(DefinitionProviderInterface::class);
    }
}
