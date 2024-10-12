<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\Tests\Functional\Service;

use ITB\ShopwareSdkBundle\Tests\ITBShopwareSdkBundleKernel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Vin\ShopwareSdk\Service\AdminSearchService;
use Vin\ShopwareSdk\Service\AdminSearchServiceInterface;
use Vin\ShopwareSdk\Service\InfoService;
use Vin\ShopwareSdk\Service\InfoServiceInterface;
use Vin\ShopwareSdk\Service\MailSendService;
use Vin\ShopwareSdk\Service\MailSendServiceInterface;
use Vin\ShopwareSdk\Service\MediaService;
use Vin\ShopwareSdk\Service\MediaServiceInterface;
use Vin\ShopwareSdk\Service\NotificationService;
use Vin\ShopwareSdk\Service\NotificationServiceInterface;
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

final class ServicesTest extends TestCase
{
    public static function provider(): \Generator
    {
        $config = Yaml::parseFile(__DIR__ . '/../../Fixtures/Configuration/config_with_enabled_cache.yaml');

        yield [$config];
    }

    #[DataProvider('provider')]
    public function test(array $config): void
    {
        $kernel = new ITBShopwareSdkBundleKernel('test', true, $config);
        $kernel->boot();

        $container = $kernel->getContainer();

        $adminSearchService = $container->get(AdminSearchServiceInterface::class);
        $this->assertInstanceOf(AdminSearchService::class, $adminSearchService);

        $infoService = $container->get(InfoServiceInterface::class);
        $this->assertInstanceOf(InfoService::class, $infoService);

        $mailSendService = $container->get(MailSendServiceInterface::class);
        $this->assertInstanceOf(MailSendService::class, $mailSendService);

        $mediaService = $container->get(MediaServiceInterface::class);
        $this->assertInstanceOf(MediaService::class, $mediaService);

        $notificationService = $container->get(NotificationServiceInterface::class);
        $this->assertInstanceOf(NotificationService::class, $notificationService);

        $stateMachineService = $container->get(StateMachineServiceInterface::class);
        $this->assertInstanceOf(StateMachineService::class, $stateMachineService);

        $syncService = $container->get(SyncServiceInterface::class);
        $this->assertInstanceOf(SyncService::class, $syncService);

        $systemConfigService = $container->get(SystemConfigServiceInterface::class);
        $this->assertInstanceOf(SystemConfigService::class, $systemConfigService);

        $userConfigService = $container->get(UserConfigServiceInterface::class);
        $this->assertInstanceOf(UserConfigService::class, $userConfigService);

        $userService = $container->get(UserServiceInterface::class);
        $this->assertInstanceOf(UserService::class, $userService);
    }
}
