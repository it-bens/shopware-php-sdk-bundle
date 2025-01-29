<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Functional\Definition;

use ITB\ShopwareSdkBundle\DependencyInjection\Configuration;
use ITB\ShopwareSdkBundle\Tests\ITBShopwareSdkBundleKernel;
use ITB\ShopwareSdkBundle\Tests\Mock\AdditionalDefinitionCollectionPopulator;
use ITB\ShopwareSdkBundle\Tests\Mock\AdditionalDefinitionCollectionPopulatorWithException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Yaml;
use Vin\ShopwareSdk\Definition\DefinitionCollection;
use Vin\ShopwareSdk\Definition\DefinitionProvider;
use Vin\ShopwareSdk\Definition\DefinitionProviderInterface;

/**
 * @phpstan-import-type ITBShopwareSdkConfiguration from Configuration
 */
final class DefinitionProviderTest extends TestCase
{
    public static function withAdditionalDefinitionCollectionPopulatorWithExceptionProvider(): \Generator
    {
        $config = Yaml::parseFile(__DIR__ . '/../../Fixtures/Configuration/config_with_enabled_cache.yaml');

        $additionalDefinitionCollectionPopulatorDefinition = new Definition(AdditionalDefinitionCollectionPopulatorWithException::class);
        $additionalDefinitionCollectionPopulatorDefinition->setAutoconfigured(true);
        $additionalDefinitionCollectionPopulatorDefinition->setPublic(false);

        yield [
            $config, [
                AdditionalDefinitionCollectionPopulatorWithException::class => $additionalDefinitionCollectionPopulatorDefinition,
            ]];
    }

    public static function withAdditionalDefinitionCollectionPopulatorProvider(): \Generator
    {
        $config = Yaml::parseFile(__DIR__ . '/../../Fixtures/Configuration/config_with_enabled_cache.yaml');

        $additionalDefinitionCollectionPopulatorDefinition = new Definition(AdditionalDefinitionCollectionPopulator::class);
        $additionalDefinitionCollectionPopulatorDefinition->setAutoconfigured(true);
        $additionalDefinitionCollectionPopulatorDefinition->setPublic(false);

        yield [
            $config, [
                AdditionalDefinitionCollectionPopulator::class => $additionalDefinitionCollectionPopulatorDefinition,
            ]];
    }

    /**
     * @param ITBShopwareSdkConfiguration $config
     * @param array<string, Definition> $dependencyInjectionDefinitions
     */
    #[DataProvider('withAdditionalDefinitionCollectionPopulatorWithExceptionProvider')]
    public function testWithAdditionalDefinitionCollectionPopulator(array $config, array $dependencyInjectionDefinitions): void
    {
        $kernel = new ITBShopwareSdkBundleKernel('test', true, $config, $dependencyInjectionDefinitions);
        $kernel->boot();

        $container = $kernel->getContainer();

        // The kernel registers a definition collection populator that throws an exception.
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This exception is expected.');
        $container->get(DefinitionProviderInterface::class);
    }

    /**
     * @param ITBShopwareSdkConfiguration $config
     * @param array<string, Definition> $dependencyInjectionDefinitions
     */
    #[DataProvider('withAdditionalDefinitionCollectionPopulatorProvider')]
    public function testPriorityWithAdditionalCollectionPopulator(array $config, array $dependencyInjectionDefinitions): void
    {
        $kernel = new ITBShopwareSdkBundleKernel('test', true, $config, $dependencyInjectionDefinitions);
        $kernel->boot();

        $container = $kernel->getContainer();
        $definitionProvider = $container->get(DefinitionProviderInterface::class);
        /** @var DefinitionProvider $definitionProvider */

        $definitionProviderReflection = new \ReflectionClass($definitionProvider);
        $definitionCollectionProperty = $definitionProviderReflection->getProperty('definitionCollection');
        /** @var DefinitionCollection $definitionCollection */
        $definitionCollection = $definitionCollectionProperty->getValue($definitionProvider);

        $definitionCollectionReflection = new \ReflectionClass($definitionCollection);
        $definitionsProperty = $definitionCollectionReflection->getProperty('definitions');
        /** @var array<string, Definition> $definitions */
        $definitions = $definitionsProperty->getValue($definitionCollection);

        $entityNames = array_keys($definitions);
        $lastEntityName = $entityNames[count($entityNames) - 1];

        $this->assertSame('new_entity', $lastEntityName);
    }
}
