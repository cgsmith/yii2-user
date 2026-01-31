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

$app = new \yii\web\Application($config);

$db = $app->db;
$db->createCommand('CREATE TABLE IF NOT EXISTS {{%user}} (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL,
    username VARCHAR(255),
    password_hash VARCHAR(255) NOT NULL DEFAULT \'\',
    auth_key VARCHAR(32) NOT NULL DEFAULT \'\',
    status VARCHAR(20) NOT NULL DEFAULT \'pending\',
    email_confirmed_at DATETIME,
    blocked_at DATETIME,
    last_login_at DATETIME,
    last_login_ip VARCHAR(45),
    registration_ip VARCHAR(45),
    gdpr_consent_at DATETIME,
    gdpr_consent_version VARCHAR(20),
    gdpr_marketing_consent_at DATETIME,
    gdpr_deleted_at DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)')->execute();

$db->createCommand('CREATE TABLE IF NOT EXISTS {{%user_token}} (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    type VARCHAR(20) NOT NULL,
    token VARCHAR(64) NOT NULL,
    data TEXT,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)')->execute();

$db->createCommand('CREATE TABLE IF NOT EXISTS {{%user_profile}} (
    user_id INTEGER PRIMARY KEY,
    name VARCHAR(255),
    bio TEXT,
    location VARCHAR(255),
    website VARCHAR(255),
    timezone VARCHAR(40),
    avatar_path VARCHAR(255),
    gravatar_email VARCHAR(255),
    use_gravatar BOOLEAN NOT NULL DEFAULT 1,
    public_email VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)')->execute();

$db->createCommand('CREATE TABLE IF NOT EXISTS {{%user_session}} (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    session_id VARCHAR(128) NOT NULL,
    ip VARCHAR(45),
    user_agent TEXT,
    device_name VARCHAR(255),
    last_activity_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)')->execute();
