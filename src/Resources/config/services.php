<?php

declare(strict_types=1);

use ITB\ShopwareSdkBundle\DependencyInjection\Constant\ServiceIds;
use ITB\ShopwareSdkBundle\DependencyInjection\Constant\Tags;
use Psr\Clock\ClockInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface as PsrRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Vin\ShopwareSdk\Auth\AccessTokenFetcher;
use Vin\ShopwareSdk\Auth\AccessTokenFetcher\CachedFetcher;
use Vin\ShopwareSdk\Auth\AccessTokenFetcher\SimpleFetcher;
use Vin\ShopwareSdk\Auth\AccessTokenFetcher\TokenRequestFactory;
use Vin\ShopwareSdk\Auth\AccessTokenProvider;
use Vin\ShopwareSdk\Auth\AccessTokenProvider\WithClientCredentials;
use Vin\ShopwareSdk\Auth\AccessTokenProvider\WithUsernameAndPassword;
use Vin\ShopwareSdk\Context\ContextBuilderFactory;
use Vin\ShopwareSdk\Context\ContextBuilderFactoryInterface;
use Vin\ShopwareSdk\Definition\DefinitionProvider;
use Vin\ShopwareSdk\Definition\DefinitionProviderInterface;
use Vin\ShopwareSdk\Definition\EntityDefinitionCollectionPopulator\WithSdkMapping;
use Vin\ShopwareSdk\Definition\SchemaProvider;
use Vin\ShopwareSdk\Definition\SchemaProviderInterface;
use Vin\ShopwareSdk\Http\HttpClient;
use Vin\ShopwareSdk\Http\RequestFactory;
use Vin\ShopwareSdk\Http\ResponseParser;
use Vin\ShopwareSdk\Hydrate\EntityHydrator;
use Vin\ShopwareSdk\Hydrate\Service\AttributeHydrator;
use Vin\ShopwareSdk\Hydrate\Service\ExtensionParser;
use Vin\ShopwareSdk\Hydrate\Service\RelationshipsParser;
use Vin\ShopwareSdk\Repository\RepositoryProvider;
use Vin\ShopwareSdk\Repository\RepositoryProviderInterface;
use Vin\ShopwareSdk\Service\AdminSearchService;
use Vin\ShopwareSdk\Service\AdminSearchServiceInterface;
use Vin\ShopwareSdk\Service\Api\ApiService;
use Vin\ShopwareSdk\Service\Api\ApiServiceInterface;
use Vin\ShopwareSdk\Service\DocumentService;
use Vin\ShopwareSdk\Service\DocumentServiceInterface;
use Vin\ShopwareSdk\Service\InfoService;
use Vin\ShopwareSdk\Service\InfoServiceInterface;
use Vin\ShopwareSdk\Service\MailSendService;
use Vin\ShopwareSdk\Service\MailSendServiceInterface;
use Vin\ShopwareSdk\Service\MediaService;
use Vin\ShopwareSdk\Service\MediaServiceInterface;
use Vin\ShopwareSdk\Service\NotificationService;
use Vin\ShopwareSdk\Service\NotificationServiceInterface;
use Vin\ShopwareSdk\Service\NumberRangeService;
use Vin\ShopwareSdk\Service\NumberRangeServiceInterface;
use Vin\ShopwareSdk\Service\StateMachineService;
use Vin\ShopwareSdk\Service\StateMachineServiceInterface;
use Vin\ShopwareSdk\Service\SyncService;
use Vin\ShopwareSdk\Service\SyncServiceInterface;
use Vin\ShopwareSdk\Service\SystemConfigService;
use Vin\ShopwareSdk\Service\SystemConfigServiceInterface;
use Vin\ShopwareSdk\Service\UserConfigService;
use Vin\ShopwareSdk\Service\UserConfigServiceInterface;
use Vin\ShopwareSdk\Service\UserService;
use Vin\ShopwareSdk\Service\UserServiceInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ServiceIds::TOKEN_REQUEST_FACTORY, TokenRequestFactory::class)
        ->args([
            '$streamFactory' => service(StreamFactoryInterface::class),
            '$requestFactory' => service(PsrRequestFactoryInterface::class),
        ])
        ->private();

    $services->set(ServiceIds::SIMPLE_ACCESS_TOKEN_FETCHER, SimpleFetcher::class)
        ->args([
            '$shopUrl' => abstract_arg('Shopware API URL from config'),
            '$tokenRequestFactory' => service(ServiceIds::TOKEN_REQUEST_FACTORY),
            '$responseParser' => service(ServiceIds::HTTP_RESPONSE_PARSER),
            '$psr18HttpClient' => service(ClientInterface::class),
        ])
        ->private();

    $services->set(ServiceIds::CACHED_ACCESS_TOKEN_FETCHER, CachedFetcher::class)
        ->decorate(ServiceIds::SIMPLE_ACCESS_TOKEN_FETCHER)
        ->args([
            '$accessTokenFetcher' => service('.inner'),
            '$cache' => abstract_arg('PSR-16 Cache'),
            '$clock' => service(ClockInterface::class),
        ])
        ->private();

    $services->set(ServiceIds::WITH_CLIENT_CREDENTIALS_ACCESS_TOKEN_PROVIDER, WithClientCredentials::class)
        ->args([
            '$clientId' => abstract_arg('Shopware API Client ID from config'),
            '$clientSecret' => abstract_arg('Shopware API Client Secret from config'),
            '$accessTokenFetcher' => service(AccessTokenFetcher::class),
        ])
        ->private();

    $services->set(ServiceIds::WITH_USERNAME_AND_PASSWORD_ACCESS_TOKEN_PROVIDER, WithUsernameAndPassword::class)
        ->args([
            '$username' => abstract_arg('Shopware username from config'),
            '$password' => abstract_arg('Shopware password from config'),
            '$accessTokenFetcher' => service(AccessTokenFetcher::class),
        ])
        ->private();

    $services->set(ServiceIds::CONTEXT_BUILDER_FACTORY, ContextBuilderFactory::class)
        ->args([
            '$shopUrl' => abstract_arg('Shopware API URL from config'),
            '$accessTokenProvider' => service(AccessTokenProvider::class),
        ])
        ->private();
    $services->alias(ContextBuilderFactoryInterface::class, ServiceIds::CONTEXT_BUILDER_FACTORY)
        ->public();

    $services->set(ServiceIds::ENTITY_DEFINITION_COLLECTION_POPULATOR_WITH_SDK_MAPPING, WithSdkMapping::class)
        ->tag(Tags::ENTITY_DEFINITION_COLLECTION_POPULATOR)
        ->private();

    $services->set(ServiceIds::ENTITY_DEFINITION_PROVIDER, DefinitionProvider::class)
        ->args([
            '$definitionCollectionPopulators' => abstract_arg('Entity definition collection populators via compiler pass'),
            '$shopwareVersion' => abstract_arg('Shopware version from config'),
        ])
        ->private();
    $services->alias(DefinitionProviderInterface::class, ServiceIds::ENTITY_DEFINITION_PROVIDER)
        ->public();

    $services->set(SchemaProvider::class)
        ->args([
            '$entityDefinitionProvider' => service(DefinitionProviderInterface::class),
        ])
        ->private();
    $services->alias(SchemaProviderInterface::class, SchemaProvider::class)
        ->public();

    $services->set(ServiceIds::HTTP_REQUEST_FACTORY, RequestFactory::class)
        ->args([
            '$streamFactory' => service(StreamFactoryInterface::class),
            '$requestFactory' => service(PsrRequestFactoryInterface::class),
        ])
        ->private();

    $services->set(ServiceIds::HTTP_RESPONSE_PARSER, ResponseParser::class)
        ->private();

    $services->set(ServiceIds::HTTP_CLIENT, HttpClient::class)
        ->args([
            '$requestFactory' => service(ServiceIds::HTTP_REQUEST_FACTORY),
            '$responseParser' => service(ServiceIds::HTTP_RESPONSE_PARSER),
            '$psr18HttpClient' => service(ClientInterface::class),
        ])
        ->private();

    $services->set(ServiceIds::ATTRIBUTE_HYDRATOR, AttributeHydrator::class)
        ->args([
            '$schemaProvider' => service(SchemaProviderInterface::class),
        ])
        ->private();

    $services->set(ServiceIds::EXTENSION_PARSER, ExtensionParser::class)
        ->private();

    $services->set(ServiceIds::RELATIONSHIP_PARSER, RelationshipsParser::class)
        ->private();

    $services->set(ServiceIds::ENTITY_HYDRATOR, EntityHydrator::class)
        ->args([
            '$definitionProvider' => service(DefinitionProviderInterface::class),
            '$attributeHydrator' => service(ServiceIds::ATTRIBUTE_HYDRATOR),
            '$relationshipsParser' => service(ServiceIds::RELATIONSHIP_PARSER),
            '$extensionParser' => service(ServiceIds::EXTENSION_PARSER),
        ])
        ->private();

    $services->set(ServiceIds::REPOSITORY_PROVIDER, RepositoryProvider::class)
        ->args([
            '$definitionProvider' => service(DefinitionProviderInterface::class),
            '$contextBuilderFactory' => service(ContextBuilderFactoryInterface::class),
            '$httpClient' => service(ServiceIds::HTTP_CLIENT),
            '$entityHydrator' => service(ServiceIds::ENTITY_HYDRATOR),
        ])
        ->private();
    $services->alias(RepositoryProviderInterface::class, ServiceIds::REPOSITORY_PROVIDER)
        ->public();

    $services->set(ServiceIds::API_SERVICE, ApiService::class)
        ->args([
            '$contextBuilderFactory' => service(ContextBuilderFactoryInterface::class),
            '$httpClient' => service(ServiceIds::HTTP_CLIENT),
        ])
        ->private();
    $services->alias(ApiServiceInterface::class, ServiceIds::API_SERVICE)
        ->public();

    $services->set(ServiceIds::ADMIN_SEARCH_SERVICE, AdminSearchService::class)
        ->args([
            '$apiService' => service(ApiServiceInterface::class),
            '$hydrator' => service(ServiceIds::ENTITY_HYDRATOR),
        ])
        ->private();
    $services->alias(AdminSearchServiceInterface::class, ServiceIds::ADMIN_SEARCH_SERVICE)
        ->public();

    $services->set(ServiceIds::DOCUMENT_SERVICE, DocumentService::class)
        ->args([
            '$apiService' => service(ApiServiceInterface::class),
        ])
        ->private();
    $services->alias(DocumentServiceInterface::class, ServiceIds::DOCUMENT_SERVICE)
        ->public();

    $services->set(ServiceIds::INFO_SERVICE, InfoService::class)
        ->args([
            '$apiService' => service(ApiServiceInterface::class),
        ])
        ->private();
    $services->alias(InfoServiceInterface::class, ServiceIds::INFO_SERVICE)
        ->public();

    $services->set(ServiceIds::MAIL_SEND_SERVICE, MailSendService::class)
        ->args([
            '$apiService' => service(ApiServiceInterface::class),
        ])
        ->private();
    $services->alias(MailSendServiceInterface::class, ServiceIds::MAIL_SEND_SERVICE)
        ->public();

    $services->set(ServiceIds::MEDIA_SERVICE, MediaService::class)
        ->args([
            '$apiService' => service(ApiServiceInterface::class),
        ])
        ->private();
    $services->alias(MediaServiceInterface::class, ServiceIds::MEDIA_SERVICE)
        ->public();

    $services->set(ServiceIds::NOTIFICATION_SERVICE, NotificationService::class)
        ->args([
            '$apiService' => service(ApiServiceInterface::class),
        ])
        ->private();
    $services->alias(NotificationServiceInterface::class, ServiceIds::NOTIFICATION_SERVICE)
        ->public();

    $services->set(ServiceIds::NUMBER_RANGE_SERVICE, NumberRangeService::class)
        ->args([
            '$apiService' => service(ApiServiceInterface::class),
        ])
        ->private();
    $services->alias(NumberRangeServiceInterface::class, ServiceIds::NUMBER_RANGE_SERVICE)
        ->public();

    $services->set(ServiceIds::STATE_MACHINE_SERVICE, StateMachineService::class)
        ->args([
            '$apiService' => service(ApiServiceInterface::class),
        ])
        ->private();
    $services->alias(StateMachineServiceInterface::class, ServiceIds::STATE_MACHINE_SERVICE)
        ->public();

    $services->set(ServiceIds::SYNC_SERVICE, SyncService::class)
        ->args([
            '$apiService' => service(ApiServiceInterface::class),
        ])
        ->private();
    $services->alias(SyncServiceInterface::class, ServiceIds::SYNC_SERVICE)
        ->public();

    $services->set(ServiceIds::SYSTEM_CONFIG_SERVICE, SystemConfigService::class)
        ->args([
            '$apiService' => service(ApiServiceInterface::class),
        ])
        ->private();
    $services->alias(SystemConfigServiceInterface::class, ServiceIds::SYSTEM_CONFIG_SERVICE)
        ->public();

    $services->set(ServiceIds::USER_CONFIG_SERVICE, UserConfigService::class)
        ->args([
            '$apiService' => service(ApiServiceInterface::class),
        ])
        ->private();
    $services->alias(UserConfigServiceInterface::class, ServiceIds::USER_CONFIG_SERVICE)
        ->public();

    $services->set(ServiceIds::USER_SERVICE, UserService::class)
        ->args([
            '$apiService' => service(ApiServiceInterface::class),
            '$definitionProvider' => service(DefinitionProviderInterface::class),
        ])
        ->private();
    $services->alias(UserServiceInterface::class, ServiceIds::USER_SERVICE)
        ->public();
};
