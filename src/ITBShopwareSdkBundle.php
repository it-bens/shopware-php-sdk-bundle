<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle;

use ITB\ShopwareSdkBundle\DependencyInjection\Compiler\EntityDefinitionCollectionPopulatorsCompilerPass;
use ITB\ShopwareSdkBundle\DependencyInjection\ITBShopwareSdkExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ITBShopwareSdkBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new EntityDefinitionCollectionPopulatorsCompilerPass());
    }

    public function getContainerExtension(): ITBShopwareSdkExtension
    {
        if ($this->extension === null) {
            $this->extension = new ITBShopwareSdkExtension();
        }

        return $this->extension;
    }
}
