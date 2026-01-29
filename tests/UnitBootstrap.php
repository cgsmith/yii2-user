<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

$config = [
    'id' => 'test-app',
    'basePath' => __DIR__,
    'vendorPath' => dirname(__DIR__) . '/vendor',
    'components' => [
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => 'sqlite::memory:',
        ],
        'authManager' => [
            'class' => \yii\rbac\PhpManager::class,
        ],
        'user' => [
            'class' => \yii\web\User::class,
            'identityClass' => \cgsmith\user\models\User::class,
        ],
        'mailer' => [
            'class' => \yii\swiftmailer\Mailer::class,
            'useFileTransport' => true,
        ],
        'security' => [
            'class' => \yii\base\Security::class,
        ],
        'request' => [
            'class' => \yii\web\Request::class,
            'cookieValidationKey' => 'test-cookie-key',
            'scriptFile' => __DIR__ . '/index.php',
            'scriptUrl' => '/index.php',
        ],
        'urlManager' => [
            'class' => \yii\web\UrlManager::class,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
    ],
    'modules' => [
        'user' => [
            'class' => \cgsmith\user\Module::class,
        ],
    ],
];

new \yii\web\Application($config);
