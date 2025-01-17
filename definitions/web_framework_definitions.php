<?php

namespace WebFramework;

use Cache\Adapter\Redis\RedisCachePool;
use Composer\Autoload\ClassLoader;
use DI;
use GuzzleHttp\Client;
use Latte\Engine;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Container\ContainerInterface;
use Sentry\ClientInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Stripe\Stripe;
use Stripe\StripeClient;
use WebFramework\Core\SentryClientFactory;

return [
    RedisCachePool::class => function (ContainerInterface $c) {
        $secureConfigService = $c->get(Security\ConfigService::class);

        $cacheConfig = $secureConfigService->getAuthConfig('redis');

        $redisClient = new \Redis();

        $result = $redisClient->pconnect(
            $cacheConfig['hostname'],
            $cacheConfig['port'],
            1,
            'wf',
            0,
            0,
            ['auth' => $cacheConfig['password']]
        );

        if ($result !== true)
        {
            throw new \RuntimeException('Redis Cache connection failed');
        }

        return new RedisCachePool($redisClient);
    },
    Engine::class => DI\create(),
    SessionManagerInterface::class => function (ContainerInterface $container) {
        return $container->get(SessionInterface::class);
    },
    SessionInterface::class => function (ContainerInterface $container) {
        $options = [
            'name' => $container->get('app_name'),
            'lifetime' => $container->get('authenticator.session_timeout'),
            'path' => '/',
            'domain' => $container->get('host_name'),
            'secure' => $container->get('http_mode') === 'https',
            'httponly' => true,
        ];

        return new PhpSession($options);
    },
    ClientInterface::class => function (ContainerInterface $container) {
        $factory = $container->get(SentryClientFactory::class);
        $options = $container->get('sentry_options');

        return $factory->get($options);
    },
    ResponseFactory::class => DI\create(),
    StripeClient::class => function (ContainerInterface $c) {
        $secureConfigService = $c->get(Security\ConfigService::class);

        $stripeConfig = $secureConfigService->getAuthConfig('stripe');

        Stripe::setApiKey($stripeConfig['api_key']);

        return new StripeClient($stripeConfig['api_key']);
    },

    'app_dir' => function (ContainerInterface $c) {
        // Determine app dir
        //
        $reflection = new \ReflectionClass(ClassLoader::class);

        return dirname($reflection->getFileName() ?: '', 3);
    },
    'app_name' => 'app',
    'build_info' => function (ContainerInterface $c) {
        $buildInfoService = $c->get(Core\BuildInfoService::class);

        return $buildInfoService->getInfo();
    },
    'full_base_url' => DI\string('{http_mode}://{server_name}{base_url}'),
    'host_name' => $_SERVER['SERVER_NAME'] ?? 'app',
    'offline_mode' => false,
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'app',

    'DbStoredValues' => DI\autowire(Core\StoredValues::class)
        ->constructorParameter('module', 'db'),
    'SanityCheckStoredValues' => DI\autowire(Core\StoredValues::class)
        ->constructorParameter('module', 'sanity_check'),

    Core\Browserless::class => function (ContainerInterface $c) {
        $secureConfigService = $c->get(Security\ConfigService::class);

        $config = $secureConfigService->getAuthConfig('browserless');

        return new Core\Browserless(
            $c->get(Security\ProtectService::class),
            $config['local_server'],
            $config['pdf_endpoint'],
            $config['token'],
        );
    },
    Core\Cache::class => DI\autowire(Core\NullCache::class),
    Core\ConfigService::class => DI\autowire()
        ->constructorParameter('config', DI\get('config_tree')),
    Core\Database::class => function (ContainerInterface $c) {
        $secureConfigService = $c->get(Security\ConfigService::class);

        $dbConfig = $secureConfigService->getAuthConfig('db_config.main');

        $mysql = new \mysqli(
            $dbConfig['database_host'],
            $dbConfig['database_user'],
            $dbConfig['database_password'],
            $dbConfig['database_database']
        );

        if ($mysql->connect_error)
        {
            throw new \RuntimeException('Mysqli Database connection failed');
        }

        $database = new Core\MysqliDatabase(
            $mysql,
            $c->get(Core\Instrumentation::class),
        );

        $c->get(Core\DatabaseProvider::class)->set($database);

        return $database;
    },
    Core\DatabaseManager::class => DI\autowire()
        ->constructorParameter('storedValues', DI\get('DbStoredValues')),
    Core\Instrumentation::class => DI\autowire(Core\NullInstrumentation::class),
    Core\LatteRenderService::class => DI\autowire()
        ->constructorParameter('templateDir', DI\string('{app_dir}/templates'))
        ->constructorParameter('tmpDir', '/tmp/latte'),
    Core\MailReportFunction::class => DI\autowire()
        ->constructorParameter('assertRecipient', DI\get('sender_core.assert_recipient')),
    Core\MailService::class => DI\autowire(Core\NullMailService::class),
    Core\PostmarkClientFactory::class => DI\factory(function (ContainerInterface $c) {
        $secureConfigService = $c->get(Security\ConfigService::class);

        $apiKey = $secureConfigService->getAuthConfig('postmark');

        return new Core\PostmarkClientFactory($apiKey);
    }),
    Core\PostmarkMailService::class => DI\autowire()
        ->constructorParameter('defaultSender', DI\get('sender_core.default_sender')),
    Core\Recaptcha::class => DI\Factory(function (ContainerInterface $c) {
        return new Core\Recaptcha(
            $c->get(Client::class),
            $c->get('security.recaptcha.secret_key'),
        );
    }),
    Core\RecaptchaFactory::class => DI\autowire()
        ->constructorParameter('secretKey', DI\get('security.recaptcha.secret_key')),
    Core\ReportFunction::class => DI\autowire(Core\NullReportFunction::class),
    Core\RuntimeEnvironment::class => DI\autowire()
        ->constructorParameter('appDir', DI\get('app_dir'))
        ->constructorParameter('baseUrl', DI\get('base_url'))
        ->constructorParameter('debug', DI\get('debug'))
        ->constructorParameter('httpMode', DI\get('http_mode'))
        ->constructorParameter('offlineMode', DI\get('offline_mode'))
        ->constructorParameter('production', DI\get('production'))
        ->constructorParameter('serverName', DI\get('server_name')),
    Core\SanityCheckRunner::class => DI\autowire()
        ->constructorParameter('storedValues', DI\get('SanityCheckStoredValues'))
        ->constructorParameter('buildInfo', DI\get('build_info')),
    Core\StripeFactory::class => DI\factory(function (ContainerInterface $c) {
        $secureConfigService = $c->get(Security\ConfigService::class);

        $config = $secureConfigService->getAuthConfig('stripe');

        return new Core\StripeFactory(
            $c->get(Core\RuntimeEnvironment::class),
            $c->get(StripeClient::class),
            $config['endpoint_secret'],
        );
    }),
    Core\UserMailer::class => function (ContainerInterface $c) {
        $configService = $c->get(Core\ConfigService::class);
        $templateOverrides = $configService->get('user_mailer.template_overrides');

        return new Core\UserMailer(
            mailService: $c->get(Core\MailService::class),
            senderEmail: $c->get('sender_core.default_sender'),
            templateOverrides: $templateOverrides,
        );
    },

    Security\AuthenticationService::class => DI\autowire(Security\NullAuthenticationService::class),
    Security\BlacklistService::class => DI\autowire(Security\NullBlacklistService::class),
    Security\ConfigService::class => DI\autowire()
        ->constructorParameter('authDir', DI\get('security.auth_dir')),
    Security\DatabaseAuthenticationService::class => DI\autowire(Security\DatabaseAuthenticationService::class)
        ->constructorParameter('sessionTimeout', DI\get('authenticator.session_timeout')),
    Security\DatabaseBlacklistService::class => DI\autowire()
        ->constructorParameter('storePeriod', DI\get('security.blacklist.store_period'))
        ->constructorParameter('threshold', DI\get('security.blacklist.threshold'))
        ->constructorParameter('triggerPeriod', DI\get('security.blacklist.trigger_period')),
    Security\ProtectService::class => DI\autowire()
        ->constructorParameter('moduleConfig', [
            'hash' => DI\get('security.hash'),
            'crypt_key' => DI\get('security.crypt_key'),
            'hmac_key' => DI\get('security.hmac_key'),
        ]),
    Security\RandomProvider::class => DI\get(Security\OpensslRandomProvider::class),

    Translation\TranslationLoader::class => DI\get(Translation\FileTranslationLoader::class),
    Translation\FileTranslationLoader::class => function (ContainerInterface $c) {
        $configService = $c->get(Core\ConfigService::class);
        $directories = $configService->get('translations.directories');

        return new Translation\FileTranslationLoader(
            cache: $c->get(Core\Cache::class),
            runtimeEnvironment: $c->get(Core\RuntimeEnvironment::class),
            directories: $directories,
        );
    },
    Translation\TranslationService::class => DI\autowire()
        ->constructorParameter('language', DI\get('translations.default_language')),
];
