<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Mock;

use ITB\ShopwareSdkBundle\Attribute\AsEntityDefinitionCollectionPopulator;
use Vin\ShopwareSdk\Definition\DefinitionCollection;
use Vin\ShopwareSdk\Definition\DefinitionCollectionPopulator;

#[AsEntityDefinitionCollectionPopulator]
final class AdditionalDefinitionCollectionPopulator implements DefinitionCollectionPopulator
{
    public static function getEntityNames(string $shopwareVersion): array
    {
        return ['new_entity'];
    }

    public static function priority(): int
    {
        return 1;
    }

    public function populateDefinitionCollection(DefinitionCollection $definitionCollection, string $shopwareVersion): void
    {
        $definitionCollection->set(new AdditionalDefinition());
    }
}
