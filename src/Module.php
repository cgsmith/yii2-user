<?php

declare(strict_types=1);

namespace cgsmith\user;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;
use yii\console\Application as ConsoleApplication;
use yii\web\Application as WebApplication;

/**
 * User module for Yii2.
 *
 * @property-read string $version
 */
class Module extends BaseModule implements BootstrapInterface
{
    public const VERSION = '1.0.0';

    /**
     * Email change strategies
     */
    public const EMAIL_CHANGE_INSECURE = 0; // Change immediately
    public const EMAIL_CHANGE_DEFAULT = 1;  // Confirm new email only
    public const EMAIL_CHANGE_SECURE = 2;   // Confirm both old and new email

    /**
     * Whether to enable user registration.
     */
    public bool $enableRegistration = true;

    /**
     * Whether to require email confirmation after registration.
     */
    public bool $enableConfirmation = true;

    /**
     * Whether to allow login without email confirmation.
     */
    public bool $enableUnconfirmedLogin = false;

    /**
     * Whether to enable password recovery.
     */
    public bool $enablePasswordRecovery = true;

    /**
     * Whether to enable GDPR features (data export, account deletion).
     */
    public bool $enableGdpr = false;

    /**
     * Whether to enable GDPR consent tracking.
     */
    public bool $enableGdprConsent = false;

    /**
     * Whether to require GDPR consent during registration.
     */
    public bool $requireGdprConsentBeforeRegistration = true;

    /**
     * Current GDPR consent version. When updated, users will be prompted to re-consent.
     */
    public ?string $gdprConsentVersion = '1.0';

    /**
     * URL to the privacy policy page.
     */
    public ?string $gdprConsentUrl = null;

    /**
     * Routes exempt from GDPR consent check. Supports wildcards (e.g., 'site/*').
     */
    public array $gdprExemptRoutes = [];

    /**
     * Whether to enable user impersonation by admins.
     */
    public bool $enableImpersonation = true;

    /**
     * Whether to generate password automatically during registration.
     */
    public bool $enableGeneratedPassword = false;

    /**
     * Whether to enable gravatar support for profile avatars.
     */
    public bool $enableGravatar = true;

    /**
     * Whether to enable local avatar uploads.
     */
    public bool $enableAvatarUpload = true;

    /**
     * Whether to show flash messages in module views.
     */
    public bool $enableFlashMessages = true;

    /**
     * Whether to enable account deletion by users.
     */
    public bool $enableAccountDelete = true;

    /**
     * Whether to enable session history tracking.
     */
    public bool $enableSessionHistory = false;

    /**
     * Maximum number of sessions to track per user.
     */
    public int $sessionHistoryLimit = 10;

    /**
     * Whether to enable separate sessions for frontend and backend.
     */
    public bool $enableSessionSeparation = false;

    /**
     * Session name for backend when session separation is enabled.
     */
    public string $backendSessionName = 'BACKENDSESSID';

    /**
     * Session name for frontend when session separation is enabled.
     */
    public string $frontendSessionName = 'PHPSESSID';

    /**
     * Whether to enable CAPTCHA on forms.
     */
    public bool $enableCaptcha = false;

    /**
     * CAPTCHA type: 'yii', 'recaptcha-v2', 'recaptcha-v3', 'hcaptcha'.
     */
    public string $captchaType = 'yii';

    /**
     * Google reCAPTCHA site key.
     */
    public ?string $reCaptchaSiteKey = null;

    /**
     * Google reCAPTCHA secret key.
     */
    public ?string $reCaptchaSecretKey = null;

    /**
     * reCAPTCHA v3 score threshold (0.0 - 1.0). Default: 0.5.
     */
    public float $reCaptchaV3Threshold = 0.5;

    /**
     * hCaptcha site key.
     */
    public ?string $hCaptchaSiteKey = null;

    /**
     * hCaptcha secret key.
     */
    public ?string $hCaptchaSecretKey = null;

    /**
     * Forms to enable CAPTCHA on: 'login', 'register', 'recovery'.
     */
    public array $captchaForms = ['register'];

    /**
     * Whether to enable two-factor authentication.
     */
    public bool $enableTwoFactor = false;

    /**
     * Issuer name for TOTP (shown in authenticator app).
     */
    public string $twoFactorIssuer = '';

    /**
     * Number of backup codes to generate.
     */
    public int $twoFactorBackupCodesCount = 10;

    /**
     * Whether to require 2FA for admin users.
     */
    public bool $twoFactorRequireForAdmins = false;

    /**
     * Whether to enable social network authentication.
     */
    public bool $enableSocialAuth = false;

    /**
     * Whether to allow registration via social networks.
     */
    public bool $enableSocialRegistration = true;

    /**
     * Whether to allow connecting social accounts in settings.
     */
    public bool $enableSocialConnect = true;

    /**
     * Whether to enable RBAC management UI.
     */
    public bool $enableRbacManagement = false;

    /**
     * RBAC permission name required to access RBAC management.
     * If null, only admins can access RBAC management.
     */
    public ?string $rbacManagementPermission = null;

    /**
     * Email change strategy.
     */
    public int $emailChangeStrategy = self::EMAIL_CHANGE_DEFAULT;

    /**
     * Duration (in seconds) for "remember me" login. Default: 2 weeks.
     */
    public int $rememberFor = 1209600;

    /**
     * Duration (in seconds) before confirmation token expires. Default: 24 hours.
     */
    public int $confirmWithin = 86400;

    /**
     * Duration (in seconds) before recovery token expires. Default: 6 hours.
     */
    public int $recoverWithin = 21600;

    /**
     * Minimum password length.
     */
    public int $minPasswordLength = 8;

    /**
     * Maximum password length.
     */
    public int $maxPasswordLength = 72;

    /**
     * Cost parameter for password hashing (bcrypt).
     */
    public int $cost = 12;

    /**
     * Admin email addresses (for fallback admin check when RBAC is not configured).
     */
    public array $admins = [];

    /**
     * RBAC permission name that grants admin access.
     */
    public ?string $adminPermission = null;

    /**
     * RBAC permission name required to impersonate users.
     */
    public ?string $impersonatePermission = null;

    /**
     * Mailer configuration.
     */
    public array $mailer = [];

    /**
     * Model class map for overriding default models.
     */
    public array $modelMap = [];

    /**
     * Identity class for user component (convenience property).
     * If set, overrides modelMap['User'] for the identity class.
     */
    public ?string $identityClass = null;

    /**
     * URL prefix for module routes.
     */
    public string $urlPrefix = 'user';

    /**
     * Default controller namespace.
     */
    public $controllerNamespace = 'cgsmith\user\controllers';

    /**
     * Path to avatar upload directory.
     */
    public string $avatarPath = '@webroot/uploads/avatars';

    /**
     * URL to avatar directory.
     */
    public string $avatarUrl = '@web/uploads/avatars';

    /**
     * Maximum avatar file size in bytes. Default: 2MB.
     */
    public int $maxAvatarSize = 2097152;

    /**
     * Allowed avatar file extensions.
     */
    public array $avatarExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * ActiveForm class to use in views.
     * Change this to match your Bootstrap version:
     * - 'yii\bootstrap\ActiveForm' for Bootstrap 3
     * - 'yii\bootstrap4\ActiveForm' for Bootstrap 4
     * - 'yii\bootstrap5\ActiveForm' for Bootstrap 5
     * - 'yii\widgets\ActiveForm' for no Bootstrap dependency
     */
    public string $activeFormClass = 'yii\widgets\ActiveForm';

    /**
     * Form field configuration for ActiveForm.
     * Override this to customize field templates for your CSS framework.
     */
    public array $formFieldConfig = [];

    /**
     * Default model classes.
     */
    private array $defaultModelMap = [
        'User' => 'cgsmith\user\models\User',
        'Profile' => 'cgsmith\user\models\Profile',
        'Token' => 'cgsmith\user\models\Token',
        'LoginForm' => 'cgsmith\user\models\LoginForm',
        'RegistrationForm' => 'cgsmith\user\models\RegistrationForm',
        'RecoveryForm' => 'cgsmith\user\models\RecoveryForm',
        'RecoveryResetForm' => 'cgsmith\user\models\RecoveryResetForm',
        'ResendForm' => 'cgsmith\user\models\ResendForm',
        'SettingsForm' => 'cgsmith\user\models\SettingsForm',
        'UserSearch' => 'cgsmith\user\models\UserSearch',
    ];

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this->registerTranslations();

        if (Yii::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'cgsmith\user\commands';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app): void
    {
        if ($app instanceof WebApplication) {
            $this->bootstrapWeb($app);
        } elseif ($app instanceof ConsoleApplication) {
            $this->bootstrapConsole($app);
        }

        $this->registerContainerBindings();
    }

    /**
     * Bootstrap for web application.
     */
    protected function bootstrapWeb(WebApplication $app): void
    {
        $prefix = $this->urlPrefix;
        $moduleId = $this->id;

        $rules = [];
        foreach ($this->getUrlRules() as $pattern => $route) {
            $rules["{$prefix}/{$pattern}"] = "{$moduleId}/{$route}";
        }

        $app->urlManager->addRules($rules, false);

        $this->configureUserComponent($app);
    }

    /**
     * Configure the user component's identityClass if not already set.
     */
    protected function configureUserComponent(WebApplication $app): void
    {
        $identityClass = $this->identityClass ?? $this->getModelClass('User');

        if ($app->has('user', true)) {
            $user = $app->get('user');
            if ($user->identityClass === null) {
                $user->identityClass = $identityClass;
            }
        } else {
            $app->set('user', [
                'class' => 'yii\web\User',
                'identityClass' => $identityClass,
                'enableAutoLogin' => true,
                'loginUrl' => ['/' . $this->urlPrefix . '/login'],
            ]);
        }
    }

    /**
     * Bootstrap for console application.
     */
    protected function bootstrapConsole(ConsoleApplication $app): void
    {
        if (!isset($app->controllerMap['user'])) {
            $app->controllerMap['user'] = [
                'class' => 'cgsmith\user\commands\UserController',
                'module' => $this,
            ];
        }

        if (!isset($app->controllerMap['migrate-from-dektrium'])) {
            $app->controllerMap['migrate-from-dektrium'] = [
                'class' => 'cgsmith\user\commands\MigrateFromDektriumController',
                'module' => $this,
            ];
        }
    }

    /**
     * Register container bindings for dependency injection.
     */
    protected function registerContainerBindings(): void
    {
        $container = Yii::$container;

        $container->setSingleton('cgsmith\user\services\UserService', function () {
            return new \cgsmith\user\services\UserService($this);
        });

        $container->setSingleton('cgsmith\user\services\RegistrationService', function () {
            return new \cgsmith\user\services\RegistrationService($this);
        });

        $container->setSingleton('cgsmith\user\services\RecoveryService', function () {
            return new \cgsmith\user\services\RecoveryService($this);
        });

        $container->setSingleton('cgsmith\user\services\TokenService', function () {
            return new \cgsmith\user\services\TokenService($this);
        });

        $container->setSingleton('cgsmith\user\services\MailerService', function () {
            return new \cgsmith\user\services\MailerService($this);
        });

        $container->setSingleton('cgsmith\user\services\SessionService', function () {
            return new \cgsmith\user\services\SessionService($this);
        });

        $container->setSingleton('cgsmith\user\services\GdprService', function () {
            return new \cgsmith\user\services\GdprService($this);
        });

        $container->setSingleton('cgsmith\user\services\CaptchaService', function () {
            return new \cgsmith\user\services\CaptchaService($this);
        });

        $container->setSingleton('cgsmith\user\services\TwoFactorService', function () {
            return new \cgsmith\user\services\TwoFactorService($this);
        });

        $container->setSingleton('cgsmith\user\services\SocialAuthService', function () {
            return new \cgsmith\user\services\SocialAuthService($this);
        });

        $container->setSingleton('cgsmith\user\services\RbacService', function () {
            return new \cgsmith\user\services\RbacService($this);
        });

        $container->setSingleton(Module::class, function () {
            return $this;
        });
    }

    /**
     * Get URL rules for the module.
     */
    protected function getUrlRules(): array
    {
        $rules = [
            'login' => 'security/login',
            'logout' => 'security/logout',
            'register' => 'registration/register',
            'confirm/<id:\d+>/<token:[A-Za-z0-9_-]+>' => 'registration/confirm',
            'resend' => 'registration/resend',
            'recovery' => 'recovery/request',
            'recovery/<id:\d+>/<token:[A-Za-z0-9_-]+>' => 'recovery/reset',
            'settings' => 'settings/account',
            'settings/account' => 'settings/account',
            'settings/profile' => 'settings/profile',
            'settings/sessions' => 'settings/sessions',
            'settings/sessions/terminate/<id:\d+>' => 'settings/terminate-session',
            'settings/sessions/terminate-all' => 'settings/terminate-all-sessions',
            'admin' => 'admin/index',
            'admin/index' => 'admin/index',
            'admin/create' => 'admin/create',
            'admin/update/<id:\d+>' => 'admin/update',
            'admin/delete/<id:\d+>' => 'admin/delete',
            'admin/block/<id:\d+>' => 'admin/block',
            'admin/unblock/<id:\d+>' => 'admin/unblock',
            'admin/confirm/<id:\d+>' => 'admin/confirm',
            'admin/impersonate/<id:\d+>' => 'admin/impersonate',
            'admin/assignments/<id:\d+>' => 'admin/assignments',
        ];

        if ($this->enableGdpr) {
            $rules['gdpr'] = 'gdpr/index';
            $rules['gdpr/export'] = 'gdpr/export';
            $rules['gdpr/delete'] = 'gdpr/delete';
        }

        if ($this->enableGdprConsent) {
            $rules['gdpr/consent'] = 'gdpr/consent';
        }

        if ($this->enableTwoFactor) {
            $rules['two-factor'] = 'two-factor/verify';
            $rules['settings/two-factor'] = 'two-factor/index';
            $rules['settings/two-factor/enable'] = 'two-factor/enable';
            $rules['settings/two-factor/disable'] = 'two-factor/disable';
            $rules['settings/two-factor/backup-codes'] = 'two-factor/backup-codes';
            $rules['settings/two-factor/regenerate-backup-codes'] = 'two-factor/regenerate-backup-codes';
        }

        if ($this->enableSocialAuth) {
            $rules['auth/<authclient:[\w\-]+>'] = 'social/auth';
            $rules['settings/networks'] = 'social/networks';
            $rules['settings/networks/disconnect/<id:\d+>'] = 'social/disconnect';
        }

        if ($this->enableRbacManagement) {
            $rules['rbac'] = 'rbac/index';
            $rules['rbac/roles'] = 'rbac/roles';
            $rules['rbac/roles/create'] = 'rbac/create-role';
            $rules['rbac/roles/update/<name:[\w\-]+>'] = 'rbac/update-role';
            $rules['rbac/roles/delete/<name:[\w\-]+>'] = 'rbac/delete-role';
            $rules['rbac/permissions'] = 'rbac/permissions';
            $rules['rbac/permissions/create'] = 'rbac/create-permission';
            $rules['rbac/permissions/update/<name:[\w\-\.]+>'] = 'rbac/update-permission';
            $rules['rbac/permissions/delete/<name:[\w\-\.]+>'] = 'rbac/delete-permission';
        }

        return $rules;
    }

    /**
     * Get version string.
     */
    public function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * Get model class from the model map.
     */
    public function getModelClass(string $name): string
    {
        $map = array_merge($this->defaultModelMap, $this->modelMap);

        if (!isset($map[$name])) {
            throw new \InvalidArgumentException("Unknown model: {$name}");
        }

        return $map[$name];
    }

    /**
     * Create a model instance.
     *
     * @template T of object
     * @param string $name Model name from the model map
     * @param array $config Configuration array
     * @return T
     */
    public function createModel(string $name, array $config = []): object
    {
        $class = $this->getModelClass($name);
        $config['class'] = $class;

        return Yii::createObject($config);
    }

    /**
     * Get the mailer sender configuration.
     */
    public function getMailerSender(): array
    {
        return $this->mailer['sender'] ?? [Yii::$app->params['adminEmail'] ?? 'noreply@example.com' => Yii::$app->name];
    }

    /**
     * Register translation messages.
     */
    protected function registerTranslations(): void
    {
        if (!isset(Yii::$app->i18n->translations['user'])) {
            Yii::$app->i18n->translations['user'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => __DIR__ . '/messages',
            ];
        }
    }
}
