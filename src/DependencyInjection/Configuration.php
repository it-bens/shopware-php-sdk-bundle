<?php

declare(strict_types=1);

namespace ITB\ShopwareSdkBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Vin\ShopwareSdk\Auth\GrantType;

/**
 * @phpstan-type ClientCredentialsConfiguration array{grant_type: string, client_id: string, client_secret: string}
 * @phpstan-type UsernamePasswordConfiguration array{grant_type: string, username: string, password: string}
 * @phpstan-type ITBShopwareSdkConfiguration array{
 *     shop_url: string,
 *     shopware_version: string,
 *     credentials: ClientCredentialsConfiguration|UsernamePasswordConfiguration,
 *     cache: bool
 * }
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(ITBShopwareSdkExtension::ALIAS);
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('shop_url')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->enumNode('shopware_version')
                    ->info('The Shopware version of the shop. The used entity definitions are based on the given version. Use the next lowest defined version if your version is not listed.')
                    ->isRequired()
                    ->values(['0.0.0.0', '6.5.5.0', '6.5.6.0', '6.5.7.1', '6.5.8.0', '6.5.8.3', '6.5.8.8', '6.5.8.12', '6.6.0.0', '6.6.3.0', '6.6.4.0', '6.6.5.0', '6.6.6.0'])
                ->end()
                ->arrayNode('credentials')
                    ->info('The credentials are used with the given grant type to authenticate against the Shopware API.')
                    ->isRequired()
                    ->children()
                        ->enumNode('grant_type')
                            ->isRequired()
                            ->values([GrantType::CLIENT_CREDENTIALS, GrantType::PASSWORD])
                        ->end()
                        ->scalarNode('client_id')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('client_secret')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('username')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('password')
                            ->defaultNull()
                        ->end()
                    ->end()
                    ->validate()
                        ->ifTrue(static function (array $credentials) {
                            if ($credentials['grant_type'] === GrantType::CLIENT_CREDENTIALS) {
                                if (empty($credentials['client_id']) === false || empty($credentials['client_secret']) === false) {
                                    return false;
                                }
                            }
            
                            if ($credentials['grant_type'] === GrantType::PASSWORD) {
                                if (empty($credentials['username']) === false || empty($credentials['password']) === false) {
                                    return false;
                                }
                            }
            
                            return true;
                        })
                        ->thenInvalid(
                            sprintf(
                                'The credentials does not match the chosen grant type. The %s grant type required %s and %s. The %s grant type requires %s and %s.',
                                GrantType::CLIENT_CREDENTIALS,
                                'client_id',
                                'client_secret',
                                GrantType::PASSWORD,
                                'username',
                                'password'
                            )
                        )
                    ->end()
                ->end()
                ->booleanNode('cache')
                    ->info('Enable or disable the cache for the Shopware access token. A new token will be requested on every request if the cache is disabled.')
                    ->defaultTrue()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
