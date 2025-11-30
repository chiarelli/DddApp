<?php
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    // Mapeamentos do container para injeção de dependências (UseCases -> Repositories)
    'container' => [
        'definitions' => [
            \Chiarelli\DddApp\Domain\Repository\ProductRepositoryInterface::class => \Chiarelli\DddApp\Infrastructure\Repository\YiiProductRepository::class,
            \Chiarelli\DddApp\Domain\Repository\ProductTypeRepositoryInterface::class => \Chiarelli\DddApp\Infrastructure\Repository\YiiProductTypeRepository::class,
            \Chiarelli\DddApp\Domain\Repository\CustomerReadRepositoryInterface::class => \Chiarelli\DddApp\Infrastructure\Repository\YiiCustomerReadRepository::class,
            // Cache provider binding
            \Chiarelli\DddApp\Application\Port\CacheProviderInterface::class => \Chiarelli\DddApp\Infrastructure\Cache\YiiCacheProvider::class,
        ],
    ],

    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'D6wppHwZDaucwFXkxVJEVXZKjkBQtOoR',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            // Agora aponta para o AR app\models\User (DB)
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            // identityCookie reforçado em produção mais abaixo
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,

        // Habilita Pretty URLs
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                // rota amigável para acessar a listagem de customers sem "/index"
                'customer' => 'customer/index',
                // outras regras custom podem ser adicionadas aqui se necessário
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];
} else {
    // Endurece cookies quando não for ambiente de desenvolvimento
    $config['components']['request']['csrfCookie'] = [
        'httpOnly' => true,
        'secure' => true,     // exigir HTTPS em produção
        'sameSite' => 'Lax',
    ];
    $config['components']['user']['identityCookie'] = [
        'name' => '_identity',
        'httpOnly' => true,
        'secure' => true,     // exigir HTTPS em produção
        'sameSite' => 'Lax',
    ];
    $config['components']['session'] = [
        'cookieParams' => [
            'httpOnly' => true,
            'secure' => true, // exigir HTTPS em produção
            'sameSite' => 'Lax',
        ],
    ];
}

return $config;
