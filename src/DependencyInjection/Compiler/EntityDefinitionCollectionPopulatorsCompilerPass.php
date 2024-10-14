<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\DependencyInjection\Compiler;

use ITB\ShopwareSdkBundle\DependencyInjection\Constant\ServiceIds;
use ITB\ShopwareSdkBundle\DependencyInjection\Constant\Tags;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Vin\ShopwareSdk\Definition\DefinitionCollectionPopulator;

final class EntityDefinitionCollectionPopulatorsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            $reflection = $container->getReflectionClass($definition->getClass(), false);
            if (! $reflection instanceof \ReflectionClass) {
                continue;
            }

            if ($reflection->implementsInterface(DefinitionCollectionPopulator::class)) {
                $definition->addTag(Tags::ENTITY_DEFINITION_COLLECTION_POPULATOR);
                $container->setDefinition($id, $definition);
            }
        }

        $entityDefinitionProviderDefinition = $container->getDefinition(ServiceIds::ENTITY_DEFINITION_PROVIDER);
        $entityDefinitionProviderDefinition->setArgument(
            '$definitionCollectionPopulators',
            new TaggedIteratorArgument(Tags::ENTITY_DEFINITION_COLLECTION_POPULATOR, defaultPriorityMethod: 'priority')
        );
    }
}
