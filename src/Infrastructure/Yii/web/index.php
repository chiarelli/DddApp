<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
// Carrega o .env somente se existir; safeLoad evita InvalidPathException quando falta o arquivo.
$dotenv->safeLoad();

/*
* O padrão é sempre "prod" e debug "false": serve para mitigar vazamento de dados sensíveis 
* ou manipulação de funcionalidades de desenvolvimento, caso ".env" file não seja definido 
* (ou apresente problemas de leitura) em produção.
*/
defined('YII_DEBUG') or define('YII_DEBUG', filter_var($_ENV['YII_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
defined('YII_ENV')   or define('YII_ENV',   $_ENV['YII_ENV']   ?? 'prod');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();