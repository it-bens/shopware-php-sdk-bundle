<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Functional\Repository;

use ITB\ShopwareSdkBundle\DependencyInjection\Configuration;
use ITB\ShopwareSdkBundle\Tests\ITBShopwareSdkBundleKernel;
use ITB\ShopwareSdkBundle\Tests\Mock\AdditionalDefinitionCollectionPopulator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Yaml;
use Vin\ShopwareSdk\Definition\EntityDefinitionCollectionPopulator\WithSdkMapping;
use Vin\ShopwareSdk\Repository\RepositoryInterface;

/**
 * @phpstan-import-type ITBShopwareSdkConfiguration from Configuration
 */
final class EntityRepositoryTest extends TestCase
{
    public static function provider(): \Generator
    {
        $config = Yaml::parseFile(__DIR__ . '/../../Fixtures/Configuration/config_with_enabled_cache.yaml');

        $additionalDefinitionCollectionPopulatorDefinition = new Definition(AdditionalDefinitionCollectionPopulator::class);
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
    #[DataProvider('provider')]
    public function testRegistration(array $config, array $dependencyInjectionDefinitions): void
    {
        $kernel = new ITBShopwareSdkBundleKernel('test', true, $config, $dependencyInjectionDefinitions);
        $kernel->boot();

        $container = $kernel->getContainer();

        $shopwareVersion = $config['shopware_version'];
        $entityNames = WithSdkMapping::getEntityNames($shopwareVersion);
        $entityNames = array_merge($entityNames, AdditionalDefinitionCollectionPopulator::getEntityNames($shopwareVersion));
        $this->assertNotEmpty($entityNames);

        foreach ($entityNames as $entityName) {
            $entityRepository = $container->get('itb_shopware_sdk.repository.' . $entityName . '_entity_repository');
            $this->assertInstanceOf(RepositoryInterface::class, $entityRepository);
            $this->assertSame($entityName, $entityRepository->getDefinition()->getEntityName());

            $entityRepository = $container->get('.' . RepositoryInterface::class . ' $' . $entityName . '_entity_repository');
            $this->assertInstanceOf(RepositoryInterface::class, $entityRepository);
            $this->assertSame($entityName, $entityRepository->getDefinition()->getEntityName());
        }
    }
}
