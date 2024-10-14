<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests;

use ITB\ShopwareSdkBundle\DependencyInjection\Configuration;
use ITB\ShopwareSdkBundle\DependencyInjection\ITBShopwareSdkExtension;
use ITB\ShopwareSdkBundle\ITBShopwareSdkBundle;
use ITB\ShopwareSdkBundle\Tests\Compiler\PsrClockServicesCompilerPass;
use ITB\ShopwareSdkBundle\Tests\Compiler\PsrHttpServicesCompilerPass;
use ITB\ShopwareSdkBundle\Tests\Compiler\PsrSimpleCacheServicesCompilerPass;
use ITB\ShopwareSdkBundle\Tests\Compiler\PublishServicesForTestsCompilerPass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @phpstan-import-type ITBShopwareSdkConfiguration from Configuration
 */
class ITBShopwareSdkBundleKernel extends Kernel
{
    /**
     * @var ITBShopwareSdkConfiguration|null
     */
    private ?array $shopwareSdkConfig;

    /**
     * @var array<string, Definition>
     */
    private array $additionalDefinitions;

    /**
     * @param ITBShopwareSdkConfiguration|null $shopwareSdkConfig
     * @param array<string, Definition> $additionalDefinitions
     */
    public function __construct(string $environment, bool $debug, ?array $shopwareSdkConfig = null, array $additionalDefinitions = [])
    {
        parent::__construct($environment, $debug);
        $this->shopwareSdkConfig = $shopwareSdkConfig;
        $this->additionalDefinitions = $additionalDefinitions;
    }

    public function getCacheDir(): string
    {
        return __DIR__ . '/../var/cache/' . spl_object_hash($this);
    }

    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [new ITBShopwareSdkBundle()];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            if ($this->shopwareSdkConfig !== null) {
                $container->loadFromExtension(ITBShopwareSdkExtension::ALIAS, $this->shopwareSdkConfig);
            }

            foreach ($this->additionalDefinitions as $id => $definition) {
                $container->setDefinition($id, $definition);
            }

            $container->addCompilerPass(new PsrHttpServicesCompilerPass());
            $container->addCompilerPass(new PsrClockServicesCompilerPass());
            $container->addCompilerPass(new PsrSimpleCacheServicesCompilerPass());
            // All services are made public to use them via container.
            $container->addCompilerPass(new PublishServicesForTestsCompilerPass(), priority: -1000);
        });
    }
}
