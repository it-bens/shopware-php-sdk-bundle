<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\DependencyInjection\Compiler;

use ITB\ShopwareSdkBundle\DependencyInjection\Constant\Tags;
use ITB\ShopwareSdkBundle\DependencyInjection\ITBShopwareSdkExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Vin\ShopwareSdk\Definition\DefinitionCollectionPopulator;
use Vin\ShopwareSdk\Repository\EntityRepository;
use Vin\ShopwareSdk\Repository\RepositoryInterface;
use Vin\ShopwareSdk\Repository\RepositoryProviderInterface;

final class MakeEntityRepositoriesAutowirableCompilerPass implements CompilerPassInterface
{
    private const ENTITY_REPOSITORY_ID = 'itb_shopware_sdk.repository.%s_entity_repository';

    private const ENTITY_REPOSITORY_ARGUMENT_NAME = '%s_entity_repository';

    public function __construct(
        private readonly ITBShopwareSdkExtension $extension
    ) {
    }

    public function process(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig(ITBShopwareSdkExtension::ALIAS);
        $config = $this->extension->getConfig($configs, $container);

        $shopwareVersion = $config['shopware_version'];

        $entityNameSets = [];
        foreach ($container->findTaggedServiceIds(Tags::ENTITY_DEFINITION_COLLECTION_POPULATOR) as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();
            $reflection = $container->getReflectionClass($class, false);
            if (! $reflection instanceof \ReflectionClass) {
                continue;
            }

            if ($reflection->implementsInterface(DefinitionCollectionPopulator::class) === false) {
                continue;
            }
            /** @var class-string<DefinitionCollectionPopulator> $class */

            $entityNameSets[] = [
                'entityNames' => $class::getEntityNames($shopwareVersion),
                'priority' => $class::priority(),
            ];
        }

        usort($entityNameSets, fn ($a, $b) => $b['priority'] <=> $a['priority']);
        /** @var string[] $entityNames */
        $entityNames = array_merge(...array_column($entityNameSets, 'entityNames'));

        foreach ($entityNames as $entityName) {
            $id = sprintf(self::ENTITY_REPOSITORY_ID, $entityName);
            $argumentName = sprintf(self::ENTITY_REPOSITORY_ARGUMENT_NAME, $entityName);

            $entityRepositoryDefinition = new Definition(EntityRepository::class);
            $entityRepositoryDefinition->setFactory([new Reference(RepositoryProviderInterface::class), 'getRepository']);
            $entityRepositoryDefinition->setArguments([$entityName]);
            $entityRepositoryDefinition->setPublic(false);

            $container->setDefinition($id, $entityRepositoryDefinition);
            $container->registerAliasForArgument($id, RepositoryInterface::class, $argumentName);
        }
    }
}
