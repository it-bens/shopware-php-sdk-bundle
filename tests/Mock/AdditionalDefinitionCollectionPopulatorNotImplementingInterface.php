<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Mock;

use ITB\ShopwareSdkBundle\Attribute\AsEntityDefinitionCollectionPopulator;
use Vin\ShopwareSdk\Definition\DefinitionCollection;

#[AsEntityDefinitionCollectionPopulator]
final class AdditionalDefinitionCollectionPopulatorNotImplementingInterface
{
    /**
     * @return string[]
     */
    public static function getEntityNames(): array
    {
        return ['new_entity'];
    }

    public static function priority(): int
    {
        return 1;
    }

    public function populateDefinitionCollection(DefinitionCollection $definitionCollection): void
    {
        $definitionCollection->set(new AdditionalDefinition());
    }
}
