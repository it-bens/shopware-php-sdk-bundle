<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\DependencyInjection\Compiler;

use ITB\ShopwareSdkBundle\DependencyInjection\Constant\ServiceIds;
use ITB\ShopwareSdkBundle\DependencyInjection\Constant\Tags;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EntityDefinitionCollectionPopulatorsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $entityDefinitionProviderDefinition = $container->getDefinition(ServiceIds::ENTITY_DEFINITION_PROVIDER);
        $entityDefinitionProviderDefinition->setArgument(
            '$definitionCollectionPopulators',
            new TaggedIteratorArgument(Tags::ENTITY_DEFINITION_COLLECTION_POPULATOR, defaultPriorityMethod: 'priority')
        );
    }
}
