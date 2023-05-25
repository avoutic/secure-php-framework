<?php

namespace WebFramework;

use DI;
use Psr\Container\ContainerInterface;

return [
    \Cache\Adapter\Redis\RedisCachePool::class => function (ContainerInterface $c) {
        $secure_config_service = $c->get(Security\ConfigService::class);

        $cache_config = $secure_config_service->get_auth_config('redis');

        $redis_client = new \Redis();

        $result = $redis_client->pconnect(
            $cache_config['hostname'],
            $cache_config['port'],
            1,
            'wf',
            0,
            0,
            ['auth' => $cache_config['password']]
        );

        if ($result !== true)
        {
            throw new \RuntimeException('Redis Cache connection failed');
        }

        return new \Cache\Adapter\Redis\RedisCachePool($redis_client);
    },
    \Latte\Engine::class => DI\create(),
    \Slim\Psr7\Factory\ResponseFactory::class => DI\create(),

    'build_info' => function (ContainerInterface $c) {
        $debug_service = $c->get(Core\DebugService::class);

        return $debug_service->get_build_info();
    },
    'DbStoredValues' => DI\autowire(Core\StoredValues::class)
        ->constructor(
            module: 'db',
        ),
    'SanityCheckStoredValues' => DI\autowire(Core\StoredValues::class)
        ->constructor(
            module: 'sanity_check',
        ),

    Core\AssertService::class => DI\autowire(),
    Core\BaseFactory::class => DI\autowire(),
    Core\BootstrapService::class => DI\autowire()
        ->constructor(
            app_dir: DI\get('app_dir'),
        ),
    Core\BrowserSessionService::class => DI\autowire()
        ->method(
            'start',
            host_name: DI\get('host_name'),
            http_mode: DI\get('http_mode'),
        ),
    Core\Cache::class => function (ContainerInterface $c) {
        if ($c->get('cache_enabled'))
        {
            return $c->get(Core\RedisCache::class);
        }

        return $c->get(Core\NullCache::class);
    },
    Core\ConfigService::class => DI\autowire()
        ->constructor(
            DI\get('config_tree'),
        ),
    Core\Database::class => function (ContainerInterface $c) {
        $secure_config_service = $c->get(Security\ConfigService::class);

        $db_config = $secure_config_service->get_auth_config('db_config.main');

        $mysql = new \mysqli(
            $db_config['database_host'],
            $db_config['database_user'],
            $db_config['database_password'],
            $db_config['database_database']
        );

        if ($mysql->connect_error)
        {
            throw new \RuntimeException('Mysqli Database connection failed');
        }

        return new Core\MysqliDatabase($mysql);
    },
    Core\DatabaseManager::class => DI\autowire()
        ->constructor(
            stored_values: DI\get('DbStoredValues'),
        ),
    Core\DebugService::class => DI\autowire()
        ->constructor(
            app_dir: DI\get('app_dir'),
            server_name: DI\get('server_name'),
        ),
    Core\LatteRenderService::class => DI\autowire()
        ->constructor(
            template_dir: DI\string('{app_dir}/templates'),
            tmp_dir: '/tmp/latte',
        ),
    Core\MailService::class => DI\autowire(Core\NullMailService::class),
    Core\MessageService::class => DI\autowire(),
    Core\MysqliDatabase::class => DI\autowire(),
    Core\NullCache::class => DI\autowire(),
    Core\PostmarkClientFactory::class => DI\factory(function (ContainerInterface $c) {
        $secure_config_service = $c->get(Security\ConfigService::class);

        $api_key = $secure_config_service->get_auth_config('postmark');

        return new Core\PostmarkClientFactory($api_key);
    }),
    Core\RedisCache::class => DI\autowire(),
    Core\ReportFunction::class => DI\autowire(Core\MailReportFunction::class)
        ->constructor(
            assert_recipient: DI\get('sender_core.assert_recipient'),
        ),
    Core\ResponseEmitter::class => DI\autowire(),
    Core\SanityCheckRunner::class => DI\autowire()
        ->constructor(
            stored_values: DI\get('SanityCheckStoredValues'),
            build_info: DI\get('build_info'),
        ),
    Core\UserMailer::class => DI\autowire()
        ->constructor(
            default_sender: DI\get('sender_core.default_sender'),
        ),
    Core\ValidatorService::class => DI\autowire(),
    'WebFramework\Core\*' => DI\autowire(),

    Middleware\AuthenticationInfoMiddleware::class => DI\autowire(),
    Middleware\BlacklistMiddleware::class => DI\autowire(),
    Middleware\CsrfValidationMiddleware::class => DI\autowire(),
    Middleware\IpMiddleware::class => DI\autowire(),
    Middleware\JsonParserMiddleware::class => DI\autowire(),
    Middleware\MessageMiddleware::class => DI\autowire(),
    Middleware\SecurityHeadersMiddleware::class => DI\autowire(),

    Security\AuthenticationService::class => DI\autowire(Security\DatabaseAuthenticationService::class)
        ->constructor(
            session_timeout: DI\get('authenticator.session_timeout'),
            user_class: DI\get('authenticator.user_class'),
        ),
    Security\BlacklistService::class => DI\autowire(Security\NullBlacklistService::class),
    Security\CsrfService::class => DI\autowire(),
    Security\ProtectService::class => DI\autowire()
        ->constructor(
            [
                'hash' => DI\get('security.hash'),
                'crypt_key' => DI\get('security.crypt_key'),
                'hmac_key' => DI\get('security.hmac_key'),
            ]
        ),
    Security\ConfigService::class => DI\autowire()
        ->constructor(
            DI\get('app_dir'),
            DI\get('security.auth_dir'),
        ),
];