<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\DependencyInjection;

use ITB\ShopwareSdkBundle\Attribute\AsEntityDefinitionCollectionPopulator;
use ITB\ShopwareSdkBundle\DependencyInjection\Constant\ServiceIds;
use ITB\ShopwareSdkBundle\DependencyInjection\Constant\Tags;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Vin\ShopwareSdk\Auth\AccessTokenFetcher;
use Vin\ShopwareSdk\Auth\AccessTokenProvider;
use Vin\ShopwareSdk\Auth\GrantType;
use Vin\ShopwareSdk\Definition\DefinitionCollectionPopulator;

/**
 * @phpstan-import-type ITBShopwareSdkConfiguration from Configuration
 * @phpstan-import-type ClientCredentialsConfiguration from Configuration
 * @phpstan-import-type UsernamePasswordConfiguration from Configuration
 */
final class ITBShopwareSdkExtension extends Extension
{
    public const ALIAS = 'itb_shopware_sdk';

    public function getAlias(): string
    {
        return self::ALIAS;
    }

    /**
     * @param array<array<mixed>> $configs
     * @return ITBShopwareSdkConfiguration
     */
    public function getConfig(array $configs, ContainerBuilder $container): array
    {
        /** @var ConfigurationInterface $configuration */
        $configuration = $this->getConfiguration($configs, $container);
        /** @var ITBShopwareSdkConfiguration $config */
        $config = $this->processConfiguration($configuration, $configs);

        return $config;
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        /** @var ITBShopwareSdkConfiguration $config */
        $config = $this->getConfig($configs, $container);

        $this->configureShopUrl($container, $config);
        $this->configureShopwareVersion($container, $config);
        $this->configureAccessTokenFetcher($container, $config);
        $this->configureAccessTokenProvider($container, $config);
        $this->configureEntityDefinitionCollectionPopulators($container);
    }

    /**
     * @param ITBShopwareSdkConfiguration $config
     */
    private function configureAccessTokenProvider(ContainerBuilder $container, array $config): void
    {
        $grantType = $config['credentials']['grant_type'];

        if ($grantType === GrantType::CLIENT_CREDENTIALS) {
            /** @var ClientCredentialsConfiguration $credentials */
            $credentials = $config['credentials'];

            $accessTokenProviderDefinition = $container->getDefinition(ServiceIds::WITH_CLIENT_CREDENTIALS_ACCESS_TOKEN_PROVIDER);
            $accessTokenProviderDefinition->setArgument('$clientId', $credentials['client_id']);
            $accessTokenProviderDefinition->setArgument('$clientSecret', $credentials['client_secret']);

            $container->removeDefinition(ServiceIds::WITH_USERNAME_AND_PASSWORD_ACCESS_TOKEN_PROVIDER);
            $container->setAlias(AccessTokenProvider::class, ServiceIds::WITH_CLIENT_CREDENTIALS_ACCESS_TOKEN_PROVIDER)->setPublic(true);

            return;
        }

        if ($grantType === GrantType::PASSWORD) {
            /** @var UsernamePasswordConfiguration $credentials */
            $credentials = $config['credentials'];

            $accessTokenProviderDefinition = $container->getDefinition(ServiceIds::WITH_USERNAME_AND_PASSWORD_ACCESS_TOKEN_PROVIDER);
            $accessTokenProviderDefinition->setArgument('$username', $credentials['username']);
            $accessTokenProviderDefinition->setArgument('$password', $credentials['password']);

            $container->removeDefinition(ServiceIds::WITH_CLIENT_CREDENTIALS_ACCESS_TOKEN_PROVIDER);
            $container->setAlias(AccessTokenProvider::class, ServiceIds::WITH_USERNAME_AND_PASSWORD_ACCESS_TOKEN_PROVIDER)->setPublic(true);

            return;
        }

        throw new \Exception(sprintf('Unsupported grant type: %s', $grantType));
    }

    /**
     * @param ITBShopwareSdkConfiguration $config
     */
    private function configureAccessTokenFetcher(ContainerBuilder $container, array $config): void
    {
        if ($config['cache'] === null) {
            $container->removeDefinition(ServiceIds::CACHED_ACCESS_TOKEN_FETCHER);
            $container->setAlias(AccessTokenFetcher::class, ServiceIds::SIMPLE_ACCESS_TOKEN_FETCHER)->setPublic(true);

            return;
        }

        $cachedAccessTokenFetcherDefinition = $container->getDefinition(ServiceIds::CACHED_ACCESS_TOKEN_FETCHER);
        $cachedAccessTokenFetcherDefinition->setArgument('$cache', new Reference($config['cache']));

        $container->setAlias(AccessTokenFetcher::class, ServiceIds::CACHED_ACCESS_TOKEN_FETCHER)->setPublic(true);
    }

    /**
     * @param ITBShopwareSdkConfiguration $config
     */
    private function configureShopwareVersion(ContainerBuilder $container, array $config): void
    {
        $entityDefinitionProviderDefinition = $container->getDefinition(ServiceIds::ENTITY_DEFINITION_PROVIDER);
        $entityDefinitionProviderDefinition->setArgument('$shopwareVersion', $config['shopware_version']);
    }

    /**
     * @param ITBShopwareSdkConfiguration $config
     */
    private function configureShopUrl(ContainerBuilder $container, array $config): void
    {
        $simpleAccessTokenFetcherDefinition = $container->getDefinition(ServiceIds::SIMPLE_ACCESS_TOKEN_FETCHER);
        $simpleAccessTokenFetcherDefinition->setArgument('$shopUrl', $config['shop_url']);

        $contextBuilderFactoryDefinition = $container->getDefinition(ServiceIds::CONTEXT_BUILDER_FACTORY);
        $contextBuilderFactoryDefinition->setArgument('$shopUrl', $config['shop_url']);
    }

    private function configureEntityDefinitionCollectionPopulators(ContainerBuilder $container): void
    {
        $container->registerAttributeForAutoconfiguration(
            AsEntityDefinitionCollectionPopulator::class,
            static function (
                ChildDefinition $definition,
                AsEntityDefinitionCollectionPopulator $attribute,
                \ReflectionClass $reflector
            ): void {
                if (! $reflector->implementsInterface(DefinitionCollectionPopulator::class)) {
                    throw new \RuntimeException(sprintf(
                        'The class %s must implement %s to be used as an entity definition collection populator. The `AsEntityDefinitionCollectionPopulator` attribute cannot be used here.',
                        $reflector->getName(),
                        DefinitionCollectionPopulator::class
                    ));
                }

                $definition->addTag(Tags::ENTITY_DEFINITION_COLLECTION_POPULATOR);
            }
        );
    }
}
