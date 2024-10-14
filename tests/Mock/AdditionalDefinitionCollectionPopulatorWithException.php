<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Mock;

use Vin\ShopwareSdk\Definition\DefinitionCollection;
use Vin\ShopwareSdk\Definition\DefinitionCollectionPopulator;

final class AdditionalDefinitionCollectionPopulatorWithException implements DefinitionCollectionPopulator
{
    public static function priority(): int
    {
        return 1;
    }

    public function populateDefinitionCollection(DefinitionCollection $definitionCollection, string $shopwareVersion): void
    {
        throw new \RuntimeException('This exception is expected.');
    }
}
