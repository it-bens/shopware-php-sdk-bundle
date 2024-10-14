<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Mock;

use Vin\ShopwareSdk\Data\Entity\EntityDefinition;
use Vin\ShopwareSdk\Data\Schema\PropertyCollection;
use Vin\ShopwareSdk\Data\Schema\Schema;

final class AdditionalDefinition implements EntityDefinition
{
    public function getEntityName(): string
    {
        return 'new_entity';
    }

    public function getEntityClass(): string
    {
        return 'NewEntity';
    }

    public function getEntityCollection(): string
    {
        return 'NewEntityCollection';
    }

    public function getSchema(): Schema
    {
        return new Schema('new_entity', new PropertyCollection());
    }
}
