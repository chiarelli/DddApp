<?php
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/test_db.php';

/**
 * Application configuration shared by all test types
 */
return [
    'id' => 'basic-tests',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    // Adiciona bindings do container para que o Yii resolva os repositórios nas dependências dos UseCases
    'container' => [
        'definitions' => [
            \Chiarelli\DddApp\Domain\Repository\ProductRepositoryInterface::class => \Chiarelli\DddApp\Infrastructure\Repository\YiiProductRepository::class,
            \Chiarelli\DddApp\Domain\Repository\ProductTypeRepositoryInterface::class => \Chiarelli\DddApp\Infrastructure\Repository\YiiProductTypeRepository::class,
            \Chiarelli\DddApp\Domain\Repository\CustomerReadRepositoryInterface::class => \Chiarelli\DddApp\Infrastructure\Repository\YiiCustomerReadRepository::class,
            // Cache provider binding
            \Chiarelli\DddApp\Application\Port\CacheProviderInterface::class => \Chiarelli\DddApp\Infrastructure\Cache\YiiCacheProvider::class,
        ],
    ],
    'language' => 'en-US',
    'components' => [
        'db' => $db,
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
            'messageClass' => 'yii\symfonymailer\Message'
        ],
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'user' => [
            'identityClass' => 'app\models\User',
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'enableCsrfValidation' => false,
            // but if you absolutely need it set cookie domain to localhost
            /*
            'csrfCookie' => [
                'domain' => 'localhost',
            ],
            */
        ],
    ],
    'params' => $params,
];
