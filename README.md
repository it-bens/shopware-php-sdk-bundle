# Shopware 6 SDK Bundle for Symfony

![Static Badge](https://img.shields.io/badge/PHP-8.3-8993be?logo=php)
![Static Badge](https://img.shields.io/badge/PHP-8.4-8993be?logo=php)
![Static Badge](https://img.shields.io/badge/Symfony-6.4-000000?logo=symfony)
![Static Badge](https://img.shields.io/badge/Symfony-7.1-000000?logo=symfony)
![Static Badge](https://img.shields.io/badge/Shopware-6.5-189eff?logo=shopware)
![Static Badge](https://img.shields.io/badge/Shopware-6.6-189eff?logo=shopware)
![Packagist Version](https://img.shields.io/packagist/v/it-bens/shopware-sdk-bundle)
[![codecov](https://codecov.io/gh/it-bens/shopware-php-sdk-bundle/branch/main/graph/badge.svg?token=pbKH9OWz5t)](https://codecov.io/gh/it-bens/shopware-php-sdk-bundle)

This bundle wraps the [Shopware 6 SDK](https://github.com/it-bens/shopware-php-sdk) into a Symfony bundle.

The bundle is tested with PHP Symfony 6.4 (PHP 8.3, PHP 8.4) and with Symfony 7.1 (PHP 8.3, PHP 8.4).

## Installation

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
composer require it-bens/shopware-sdk-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require it-bens/shopware-sdk-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    ITB\ShopwareSdkBundle\ITBShopwareSdkBundle::class => ['all' => true],
];
```

## Requirements

- PHP 8.3 or higher
- Symfony 6.4 / 7.1 or higher
- a PSR-7 implementation
- a PSR-16 implementation (not required if the access token cache is disabled)
- a PSR-17 implementation
- a PSR-18 implementation
- a PSR-20 implementation

The PSR implementations can be chosen freely.

## Configuration

Add the following configuration to your `config/packages/itb_shopware_sdk.yaml`:

```yaml
itb_shopware_sdk:
  shop_url: 'https://shopware.local'
  shopware_version: '6.5.5.0'
  credentials:
    grant_type: 'client_credentials'
    client_id: 'CLIENT_ID'
    client_secret: 'CLIENT_SECRET'
  cache: 'cache.app'
```

The `shopware_version` key determines what entity schema is used for the native Shopware entities. Available versions are: `0.0.0.0`, `6.5.5.0`, `6.5.6.0`, `6.5.7.1`, `6.5.8.0`, `6.5.8.3`, `6.5.8.8`, `6.5.8.12`, `6.6.0.0`, `6.6.3.0`, `6.6.4.0`, `6.6.5.0`, `6.6.6.0` and `6.6.7.0`.
Use the next lower version in comparison to your Shopware version. The listed versions were the lowest that introduced entity schema changes.
`0.0.0.0` can be used to use the entity schemas and definitions from the [original SDK package](https://github.com/vienthuong/shopware-php-sdk).

There are two grant types available: `client_credentials` and `password`. A authentication with the `password` grant type requires this configuration:

```yaml
itb_shopware_sdk:
  shop_url: 'https://shopware.local'
  shopware_version: '6.5.5.0'
  credentials:
    grant_type: 'password'
    username: 'USERNAME'
    password: 'PASSWORD'
  cache: 'cache.app'
```

The `credentials` block will not be merged with other configuration files or environments to prevent `Environment variables ... are never used.` errors when different grant types are used in different environments. The block will be overwritten according to the hirarchy defined by Symfony: https://symfony.com/doc/current/configuration.html#configuration-environments.

The `cache` key determines if the obtained OAuth token should be cached. If set to `null` every request will request a new token from Shopware, before doing anything else.

## Usage

### CRUD Repository

Shopware provides the usual CRUD operations for entities. The bundle provides repositories to execute this operations. 

The repositories can injected directly via autowiring:

```php
use Vin\ShopwareSdk\Repository\RepositoryInterface;

final class ProductService {
    public function __construct(
        private RepositoryInterface $productEntityRepository,
        private RepositoryInterface $orderTransactionCaptureRefundPositionEntityRepository,
    ) {
    }
    
    // ...
}
```

The dependency injection container will automatically will let the `RepositoryProvider` create the requested repositories based on the argument name.
The argument name has to be the entity name in camel case with the suffix `EntityRepository`. The `RepositoryInterface` type hint is required.

Alternatively, they can be obtained via the `RepositoryProviderInterface`.

```php
use Vin\ShopwareSdk\Repository\RepositoryProviderInterface;
use Vin\ShopwareSdk\Repository\RepositoryInterface;
use Vin\ShopwareSdk\Data\Entity\v65812\Product\ProductDefinition;
use Vin\ShopwareSdk\Data\Entity\v65812\OrderTransactionCaptureRefundPosition\OrderTransactionCaptureRefundPositionDefinition

final class ProductService {
    private RepositoryInterface $productRepository;
    private RepositoryInterface $orderTransactionCaptureRefundPositionRepository;

    public function __construct(
        RepositoryProviderInterface $repositoryProvider
    ) {
        $this->productRepository = $repositoryProvider->getRepository(ProductDefinition::ENTITY_NAME);
        $this->orderTransactionCaptureRefundPositionRepository = $repositoryProvider->getRepository(OrderTransactionCaptureRefundPositionDefinition::ENTITY_NAME);
    }
    
    // ...
}
```

The provider caches the repositories, so they don't have to be recreated every time, the method is called.

> [!IMPORTANT]  
> The chosen Shopware version determines which entity classes are hydrated and returned by the repository. Pay attention to the usage of the correct entity classes in your project.

### API Service

Besides from the CRUD entity endpoints, Shopware provides endpoints that are either not entity related or perform special operations outside the CRUD scope.
The currently available API services are:
- [Admin Search API][admin-search-api-class-link] (read-equivalent to the Sync API)
- [Document API][document-api-class-link] + [Document Generator API][document-generator-api-class-link]
- [Info API][info-api-class-link]
- [Mail Send API][mail-send-api-class-link]
- [Media API][media-api-class-link]
- [Notification API][notification-api-class-link]
- [Number Range API][number-range-api-class-link]
- [State Machine API][state-machine-api-class-link]
- [Sync API][sync-api-class-link] (upserting/deleting multiple entities in a single request)
- [System Config API][system-config-api-class-link]
- [User Config API][user-config-api-class-link]
- [User API][user-api-class-link]

They can be obtained directly via their interfaces:

```php
use Vin\ShopwareSdk\Service\AdminSearchServiceInterface;
use Vin\ShopwareSdk\Service\DocumentServiceInterface;
use Vin\ShopwareSdk\Service\InfoServiceInterface;
use Vin\ShopwareSdk\Service\MailSendServiceInterface;
use Vin\ShopwareSdk\Service\MediaServiceInterface;
use Vin\ShopwareSdk\Service\NotificationServiceInterface;
use Vin\ShopwareSdk\Service\NumberRangeServiceInterface;
use Vin\ShopwareSdk\Service\StateMachineServiceInterface;
use Vin\ShopwareSdk\Service\SyncServiceInterface;
use Vin\ShopwareSdk\Service\SystemConfigServiceInterface;
use Vin\ShopwareSdk\Service\UserConfigServiceInterface;
use Vin\ShopwareSdk\Service\UserServiceInterface;

final class Services {
    public function __construct(
        private AdminSearchServiceInterface $adminSearchService,
        private DocumentServiceInterface $documentService,
        private InfoServiceInterface $infoService,
        private MailSendServiceInterface $mailSendService,
        private MediaServiceInterface $mediaService,
        private NotificationServiceInterface $notificationService,
        private NumberRangeServiceInterface $numberRangeService,
        private StateMachineServiceInterface $stateMachineService,
        private SyncServiceInterface $syncService,
        private SystemConfigServiceInterface $systemConfigService,
        private UserConfigServiceInterface $userConfigService,
        private UserServiceInterface $userService
    ) {
    }
    
    // ...
}
```

### Adding and Overriding Entity Definitions

The SDK package provides definitions, entity classes and collection classes for the native Shopware entities.
New entities can be added to Shopware via Plugins. The SDK package and this bundle provide a way to add these entities to your project.

This requires an implementation of the `DefinitionCollectionPopulator` interface.

```php
use Vin\ShopwareSdk\Definition\DefinitionCollectionPopulator;
use Vin\ShopwareSdk\Definition\DefinitionCollection;
use Vin\ShopwareSdk\Data\Entity\EntityDefinition;

final class CustomDefinitionCollectionPopulator implements DefinitionCollectionPopulator {
    public static function getEntityNames(string $shopwareVersion): array
    {
        return ['custom_entity'];
    }
    
    public static function priority(): int {
        return 0;
    }
    
    public function populateDefinitionCollection(DefinitionCollection $definitionCollection, string $shopwareVersion): void {
        /** @var EntityDefinition $customDefinition */
        $customDefinition = new CustomEntityDefinition();
        $definitionCollection->set($customDefinition); // The entity name is used as a key and allow overwriting of existing definitions
    }
}
```

If the service is autowired a compiler pass in this bundle will detect the interface usage, tag the service an add it to the `DefinitionCollectionProvider`.
Of cause the service can be tagged manually as well:

```php
use ITB\ShopwareSdkBundle\DependencyInjection\Constant\Tags;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    
    $serviceDefinition = $services->get(CustomDefinitionCollectionPopulator::class);
    $serviceDefinition->tag(Tags::ENTITY_DEFINITION_COLLECTION_POPULATOR);
    $services->set(CustomDefinitionCollectionPopulator::class);
};
```

The priority determines the order in which the populators are used. The native populator has the priority 1000 and is used first. This allows to override existing definitions by populators with a lower priority.
The entity name is used as a key in the definition collection and allows to overwrite native/existing definitions by usage of the same entity name.

> [!TIP]
> If a Shopware plugin adds relations to existing entities, these relations have to be present in the entity definition in this bundle as well. Overwriting the native definition with a populator is the way to go.
> 
> The native definition class can be copied and modified. The entity class does not have to be modified because Shopware returns tha added relations as an extension and extensions are already part of all entity classes.

### Custom Usages

This bundle provides the following additional services via dependency injection:

```php
use Vin\ShopwareSdk\Context\ContextBuilderFactoryInterface;
use Vin\ShopwareSdk\Definition\DefinitionProviderInterface;
use Vin\ShopwareSdk\Definition\SchemaProviderInterface;
use Vin\ShopwareSdk\Service\Api\ApiServiceInterface;

final class AdditionalServices {
    public function __construct(
        private ContextBuilderFactoryInterface $contextBuilderFactory,
        private DefinitionProviderInterface $definitionProvider,
        private SchemaProviderInterface $schemaProvider,
        private ApiServiceInterface $apiService,
    ) {
    }
    
    // ...
}
```

The purpose and usage of this services is explained in the [SDK repository](https://github.com/it-bens/shopware-php-sdk).

## Contributing

I am really happy that the software developer community loves Open Source, like I do! ♥

That's why I appreciate every issue that is opened (preferably constructive) and every pull request that provides other or even better code to this package.

You are all breathtaking!

[admin-search-api-class-link]: https://github.com/shopware/administration/blob/trunk/Controller/AdminSearchController.php
[document-api-class-link]: https://github.com/shopware/core/blob/trunk/Checkout/Document/Controller/DocumentController.php
[document-generator-api-class-link]: https://github.com/shopware/core/blob/trunk/Checkout/Document/DocumentGeneratorController.php
[info-api-class-link]: https://github.com/shopware/core/blob/trunk/Framework/Api/Controller/InfoController.php
[mail-send-api-class-link]: https://github.com/shopware/core/blob/trunk/Content/MailTemplate/Api/MailActionController.php
[media-api-class-link]: https://github.com/shopware/core/blob/trunk/Content/Media/Api/MediaUploadController.php
[notification-api-class-link]: https://github.com/shopware/administration/blob/trunk/Controller/NotificationController.php
[number-range-api-class-link]: https://github.com/shopware/core/blob/trunk/System/NumberRange/Api/NumberRangeController.php
[state-machine-api-class-link]: https://github.com/shopware/core/blob/trunk/System/StateMachine/Api/StateMachineActionController.php
[sync-api-class-link]: https://github.com/shopware/core/blob/trunk/Framework/Api/Controller/SyncController.php
[system-config-api-class-link]: https://github.com/shopware/core/blob/trunk/System/SystemConfig/Api/SystemConfigController.php
[user-config-api-class-link]: https://github.com/shopware/administration/blob/trunk/Controller/UserConfigController.php
[user-api-class-link]: https://github.com/shopware/core/blob/trunk/Framework/Api/Controller/UserController.php