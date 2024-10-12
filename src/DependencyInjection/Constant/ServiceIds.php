<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\DependencyInjection\Constant;

final class ServiceIds
{
    public const TOKEN_REQUEST_FACTORY = 'itb_shopware_sdk.auth.access_token_fetcher.token_request_factory';

    public const SIMPLE_ACCESS_TOKEN_FETCHER = 'itb_shopware_sdk.auth.access_token_fetcher.simple_fetcher';

    public const CACHED_ACCESS_TOKEN_FETCHER = 'itb_shopware_sdk.auth.access_token_fetcher.cached_fetcher';

    public const WITH_CLIENT_CREDENTIALS_ACCESS_TOKEN_PROVIDER = 'itb_shopware_sdk.auth.access_token_provider.with_client_credentials';

    public const WITH_USERNAME_AND_PASSWORD_ACCESS_TOKEN_PROVIDER = 'itb_shopware_sdk.auth.access_token_provider.with_username_and_password';

    public const CONTEXT_BUILDER_FACTORY = 'itb_shopware_sdk.context.context_builder_factory';

    public const ENTITY_DEFINITION_COLLECTION_POPULATOR_WITH_SDK_MAPPING = 'itb_shopware_sdk.definition.entity_definition_collection_populator.with_sdk_mapping';

    public const ENTITY_DEFINITION_PROVIDER = 'itb_shopware_sdk.definition.entity_definition_provider';

    public const HTTP_REQUEST_FACTORY = 'itb_shopware_sdk.http.request_factory';

    public const HTTP_RESPONSE_PARSER = 'itb_shopware_sdk.http.response_parser';

    public const HTTP_CLIENT = 'itb_shopware_sdk.http.http_client';

    public const ATTRIBUTE_HYDRATOR = 'itb_shopware_sdk.hydrator.service.attribute_hydrator';

    public const EXTENSION_PARSER = 'itb_shopware_sdk.hydrator.service.extension_parser';

    public const RELATIONSHIP_PARSER = 'itb_shopware_sdk.hydrator.service.relationship_parser';

    public const ENTITY_HYDRATOR = 'itb_shopware_sdk.hydrator.entity_hydrator';

    public const REPOSITORY_PROVIDER = 'itb_shopware_sdk.repository.repository_provider';

    public const API_SERVICE = 'itb_shopware_sdk.service.api.api_service';

    public const ADMIN_SEARCH_SERVICE = 'itb_shopware_sdk.service.admin_search_service';

    public const INFO_SERVICE = 'itb_shopware_sdk.service.info_service';

    public const MAIL_SEND_SERVICE = 'itb_shopware_sdk.service.mail_send_service';

    public const MEDIA_SERVICE = 'itb_shopware_sdk.service.media_service';

    public const NOTIFICATION_SERVICE = 'itb_shopware_sdk.service.notification_service';

    public const NUMBER_RANGE_SERVICE = 'itb_shopware_sdk.service.number_range_service';

    public const STATE_MACHINE_SERVICE = 'itb_shopware_sdk.service.state_machine_service';

    public const SYNC_SERVICE = 'itb_shopware_sdk.service.sync_service';

    public const SYSTEM_CONFIG_SERVICE = 'itb_shopware_sdk.service.system_config_service';

    public const USER_CONFIG_SERVICE = 'itb_shopware_sdk.service.user_config_service';

    public const USER_SERVICE = 'itb_shopware_sdk.service.user_service';
}
