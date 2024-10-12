<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Functional\Definition\DefinitionProviderTest;

use ITB\ShopwareSdkBundle\Tests\ITBShopwareSdkBundleKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class DefinitionProviderTestKernel extends ITBShopwareSdkBundleKernel
{
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(function (ContainerBuilder $container) {
            $additionalDefinitionCollectionPopulatorDefinition = new Definition(AdditionalDefinitionCollectionPopulator::class);
            $additionalDefinitionCollectionPopulatorDefinition->setPublic(false);
            $container->setDefinition(AdditionalDefinitionCollectionPopulator::class, $additionalDefinitionCollectionPopulatorDefinition);
        });
    }
}
