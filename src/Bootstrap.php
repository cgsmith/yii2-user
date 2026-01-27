<?php

declare(strict_types=1);

namespace cgsmith\user;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;
use yii\web\Application as WebApplication;

/**
 * Bootstrap class for the user module.
 *
 * Registers URL rules and container bindings.
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap($app): void
    {
        /** @var Module|null $module */
        $module = $app->getModule('user');

        if ($module === null) {
            return;
        }

        if ($app instanceof ConsoleApplication) {
            $this->bootstrapConsole($app, $module);
        } elseif ($app instanceof WebApplication) {
            $this->bootstrapWeb($app, $module);
        }

        $this->registerContainerBindings($module);
    }

    /**
     * Bootstrap for web application.
     */
    protected function bootstrapWeb(WebApplication $app, Module $module): void
    {
        $prefix = $module->urlPrefix;
        $moduleId = $module->id;

        $rules = [];
        foreach ($this->getUrlRules($module) as $pattern => $route) {
            $rules["{$prefix}/{$pattern}"] = "{$moduleId}/{$route}";
        }

        $app->urlManager->addRules($rules, false);
    }

    /**
     * Bootstrap for console application.
     */
    protected function bootstrapConsole(ConsoleApplication $app, Module $module): void
    {
        if (!isset($app->controllerMap['user'])) {
            $app->controllerMap['user'] = [
                'class' => 'cgsmith\user\commands\UserController',
                'module' => $module,
            ];
        }

        if (!isset($app->controllerMap['migrate-from-dektrium'])) {
            $app->controllerMap['migrate-from-dektrium'] = [
                'class' => 'cgsmith\user\commands\MigrateFromDektriumController',
                'module' => $module,
            ];
        }
    }

    /**
     * Register container bindings for dependency injection.
     */
    protected function registerContainerBindings(Module $module): void
    {
        $container = Yii::$container;

        // Bind services
        $container->setSingleton('cgsmith\user\services\UserService', function () use ($module) {
            return new \cgsmith\user\services\UserService($module);
        });

        $container->setSingleton('cgsmith\user\services\RegistrationService', function () use ($module) {
            return new \cgsmith\user\services\RegistrationService($module);
        });

        $container->setSingleton('cgsmith\user\services\RecoveryService', function () use ($module) {
            return new \cgsmith\user\services\RecoveryService($module);
        });

        $container->setSingleton('cgsmith\user\services\TokenService', function () use ($module) {
            return new \cgsmith\user\services\TokenService($module);
        });

        $container->setSingleton('cgsmith\user\services\MailerService', function () use ($module) {
            return new \cgsmith\user\services\MailerService($module);
        });

        // Bind module for injection
        $container->setSingleton(Module::class, function () use ($module) {
            return $module;
        });
    }

    /**
     * Get URL rules for the module.
     */
    protected function getUrlRules(Module $module): array
    {
        $rules = [
            // Security
            'login' => 'security/login',
            'logout' => 'security/logout',

            // Registration
            'register' => 'registration/register',
            'confirm/<id:\d+>/<token:[A-Za-z0-9_-]+>' => 'registration/confirm',
            'resend' => 'registration/resend',

            // Password Recovery
            'recovery' => 'recovery/request',
            'recovery/<id:\d+>/<token:[A-Za-z0-9_-]+>' => 'recovery/reset',

            // Settings
            'settings' => 'settings/account',
            'settings/account' => 'settings/account',
            'settings/profile' => 'settings/profile',

            // Admin
            'admin' => 'admin/index',
            'admin/index' => 'admin/index',
            'admin/create' => 'admin/create',
            'admin/update/<id:\d+>' => 'admin/update',
            'admin/delete/<id:\d+>' => 'admin/delete',
            'admin/block/<id:\d+>' => 'admin/block',
            'admin/unblock/<id:\d+>' => 'admin/unblock',
            'admin/confirm/<id:\d+>' => 'admin/confirm',
            'admin/impersonate/<id:\d+>' => 'admin/impersonate',
        ];

        // GDPR routes
        if ($module->enableGdpr) {
            $rules['gdpr'] = 'gdpr/index';
            $rules['gdpr/export'] = 'gdpr/export';
            $rules['gdpr/delete'] = 'gdpr/delete';
        }

        return $rules;
    }
}
