<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests;

use ITB\ShopwareSdkBundle\DependencyInjection\ITBShopwareSdkExtension;
use ITB\ShopwareSdkBundle\ITBShopwareSdkBundle;
use ITB\ShopwareSdkBundle\Tests\Compiler\PsrClockServicesCompilerPass;
use ITB\ShopwareSdkBundle\Tests\Compiler\PsrHttpServicesCompilerPass;
use ITB\ShopwareSdkBundle\Tests\Compiler\PsrSimpleCacheServicesCompilerPass;
use ITB\ShopwareSdkBundle\Tests\Compiler\PublishServicesForTestsCompilerPass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

class ITBShopwareSdkBundleKernel extends Kernel
{
    private ?array $shopwareSdkConfig;

    public function __construct(string $environment, bool $debug, ?array $shopwareSdkConfig = null)
    {
        parent::__construct($environment, $debug);
        $this->shopwareSdkConfig = $shopwareSdkConfig;
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

            $container->addCompilerPass(new PsrHttpServicesCompilerPass());
            $container->addCompilerPass(new PsrClockServicesCompilerPass());
            $container->addCompilerPass(new PsrSimpleCacheServicesCompilerPass());
            // All services are made public to use them via container.
            $container->addCompilerPass(new PublishServicesForTestsCompilerPass());
        });
    }
}
