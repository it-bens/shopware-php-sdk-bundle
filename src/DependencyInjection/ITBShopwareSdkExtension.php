<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\DependencyInjection;

use ITB\ShopwareSdkBundle\DependencyInjection\Constant\ServiceIds;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Vin\ShopwareSdk\Auth\AccessTokenFetcher;
use Vin\ShopwareSdk\Auth\AccessTokenProvider;
use Vin\ShopwareSdk\Auth\GrantType;

final class ITBShopwareSdkExtension extends Extension
{
    public const ALIAS = 'itb_shopware_sdk';

    public function getAlias(): string
    {
        return self::ALIAS;
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        /** @var ConfigurationInterface $configuration */
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->configureShopUrl($container, $config);
        $this->configureShopwareVersion($container, $config);
        $this->configureAccessTokenFetcher($container, $config);
        $this->configureAccessTokenProvider($container, $config);
    }

    private function configureAccessTokenProvider(ContainerBuilder $container, array $config): void
    {
        $grantType = $config['credentials']['grant_type'];

        if ($grantType === GrantType::CLIENT_CREDENTIALS) {
            $accessTokenProviderDefinition = $container->getDefinition(ServiceIds::WITH_CLIENT_CREDENTIALS_ACCESS_TOKEN_PROVIDER);
            $accessTokenProviderDefinition->setArgument('$clientId', $config['credentials']['client_id']);
            $accessTokenProviderDefinition->setArgument('$clientSecret', $config['credentials']['client_secret']);

            $container->removeDefinition(ServiceIds::WITH_USERNAME_AND_PASSWORD_ACCESS_TOKEN_PROVIDER);
            $container->setAlias(AccessTokenProvider::class, ServiceIds::WITH_CLIENT_CREDENTIALS_ACCESS_TOKEN_PROVIDER)->setPublic(true);

            return;
        }

        if ($grantType === GrantType::PASSWORD) {
            $accessTokenProviderDefinition = $container->getDefinition(ServiceIds::WITH_USERNAME_AND_PASSWORD_ACCESS_TOKEN_PROVIDER);
            $accessTokenProviderDefinition->setArgument('$username', $config['credentials']['username']);
            $accessTokenProviderDefinition->setArgument('$password', $config['credentials']['password']);

            $container->removeDefinition(ServiceIds::WITH_CLIENT_CREDENTIALS_ACCESS_TOKEN_PROVIDER);
            $container->setAlias(AccessTokenProvider::class, ServiceIds::WITH_USERNAME_AND_PASSWORD_ACCESS_TOKEN_PROVIDER)->setPublic(true);

            return;
        }

        throw new \Exception(sprintf('Unsupported grant type: %s', $grantType));
    }

    private function configureAccessTokenFetcher(ContainerBuilder $container, array $config): void
    {
        if ($config['cache'] === false) {
            $container->removeDefinition(ServiceIds::CACHED_ACCESS_TOKEN_FETCHER);
            $container->setAlias(AccessTokenFetcher::class, ServiceIds::SIMPLE_ACCESS_TOKEN_FETCHER)->setPublic(true);

            return;
        }

        $cachedAccessTokenFetcherDefinition = $container->getDefinition(ServiceIds::CACHED_ACCESS_TOKEN_FETCHER);
        $cachedAccessTokenFetcherDefinition->setArgument('$cache', new Reference(CacheInterface::class));

        $container->setAlias(AccessTokenFetcher::class, ServiceIds::CACHED_ACCESS_TOKEN_FETCHER)->setPublic(true);
    }

    private function configureShopwareVersion(ContainerBuilder $container, array $config): void
    {
        $entityDefinitionProviderDefinition = $container->getDefinition(ServiceIds::ENTITY_DEFINITION_PROVIDER);
        $entityDefinitionProviderDefinition->setArgument('$shopwareVersion', $config['shopware_version']);
    }

    private function configureShopUrl(ContainerBuilder $container, array $config): void
    {
        $simpleAccessTokenFetcherDefinition = $container->getDefinition(ServiceIds::SIMPLE_ACCESS_TOKEN_FETCHER);
        $simpleAccessTokenFetcherDefinition->setArgument('$shopUrl', $config['shop_url']);

        $contextBuilderFactoryDefinition = $container->getDefinition(ServiceIds::CONTEXT_BUILDER_FACTORY);
        $contextBuilderFactoryDefinition->setArgument('$shopUrl', $config['shop_url']);
    }
}
