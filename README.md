# cgsmith/yii2-user

A modern, actively maintained user management module for Yii2. Built as a spiritual successor to dektrium/yii2-user and
2amigos/yii2-usuario, designed for PHP 8.2+ with strict typing and modern practices.

## Requirements

- PHP 8.2 or higher
- Yii2 2.0.45 or higher

## Installation

### Via Composer (when published)

```bash
composer require cgsmith/yii2-user
```

### Local Development

Add to your `composer.json`:

```json
{
  "autoload": {
    "psr-4": {
      "cgsmith\\user\\": "common/modules/user/src/"
    }
  }
}
```

Then run:

```bash
composer dump-autoload
```

## Configuration

### Web Application

```php
return [
    'bootstrap' => ['log', 'cgsmith\user\Bootstrap'],
    'modules' => [
        'user' => [
            'class' => 'cgsmith\user\Module',
            'enableRegistration' => true,
            'enableConfirmation' => true,
            'enablePasswordRecovery' => true,
            'admins' => ['admin@example.com'],
            'mailer' => [
                'sender' => ['noreply@example.com' => 'My Application'],
            ],
        ],
    ],
];
```

### Console Application

```php
return [
    'bootstrap' => ['log', 'cgsmith\user\Bootstrap'],
    'modules' => [
        'user' => [
            'class' => 'cgsmith\user\Module',
        ],
    ],
];
```

## Feature Comparison

| Feature          | dektrium/yii2-user | 2amigos/yii2-usuario | cgsmith/yii2-user |
|------------------|--------------------|----------------------|-------------------|
| **Status**       | Abandoned (2018)   | Abandoned (2022)     | Active            |
| **PHP Version**  | 5.6+               | 7.1+                 | 8.2+              |
| **Strict Types** | No                 | Partial              | Yes               |
| **Yii2 Version** | 2.0.6+             | 2.0.13+              | 2.0.45+           |

### Core Features

| Feature            | dektrium | usuario | cgsmith |
|--------------------|:--------:|:-------:|:-------:|
| User Registration  |    ✅     |    ✅    |    ✅    |
| Email Confirmation |    ✅     |    ✅    |    ✅    |
| Password Recovery  |    ✅     |    ✅    |    ✅    |
| Account Settings   |    ✅     |    ✅    |    ✅    |
| Profile Management |    ✅     |    ✅    |    ✅    |
| Admin Panel        |    ✅     |    ✅    |    ✅    |
| User Blocking      |    ✅     |    ✅    |    ✅    |
| RBAC Integration   |    ✅     |    ✅    |    ✅    |
| Model Overriding   |    ✅     |    ✅    |    ✅    |
| Controller Events  |    ✅     |    ✅    |    ✅    |
| i18n Support       |    ✅     |    ✅    |    ✅    |

### Security Features

| Feature                  | dektrium | usuario | cgsmith |
|--------------------------|:--------:|:-------:|:-------:|
| Secure Password Hashing  |    ✅     |    ✅    |    ✅    |
| Configurable bcrypt Cost |    ✅     |    ✅    |    ✅    |
| Token-based Confirmation |    ✅     |    ✅    |    ✅    |
| Token Expiration         |    ✅     |    ✅    |    ✅    |
| IP Logging               |    ✅     |    ✅    |    ✅    |
| Last Login Tracking      |    ✅     |    ✅    |    ✅    |
| Email Change Strategies  |    ✅     |    ✅    |    ✅    |
| CSRF Protection          |    ✅     |    ✅    |    ✅    |

### Advanced Features

| Feature                 | dektrium | usuario | cgsmith |
|-------------------------|:--------:|:-------:|:-------:|
| Social Authentication   |    ✅     |    ✅    |  🔄 v2  |
| Two-Factor Auth (2FA)   |    ❌     |    ❌    |  🔄 v2  |
| GDPR Compliance         |    ❌     |    ✅    |  🔄 v2  |
| Data Export             |    ❌     |    ✅    |  🔄 v2  |
| Account Deletion        |    ❌     |    ✅    |  🔄 v2  |
| User Impersonation      |    ✅     |    ✅    |    ✅    |
| Gravatar Support        |    ✅     |    ✅    |    ✅    |
| Avatar Upload           |    ❌     |    ❌    |    ✅    |
| Migration from dektrium |   N/A    |    ✅    |    ✅    |
| Migration from usuario  |   N/A    |   N/A   |    ✅    |

### Architecture

| Feature              | dektrium | usuario | cgsmith |
|----------------------|:--------:|:-------:|:-------:|
| Service Layer        |    ❌     | Partial |    ✅    |
| Dependency Injection |    ❌     | Partial |    ✅    |
| Interface Contracts  |    ❌     |    ❌    |    ✅    |
| Custom Query Classes |    ❌     |    ❌    |    ✅    |
| Event-driven Design  |    ✅     |    ✅    |    ✅    |

## Configuration Options

| Option                    | Type   | Default                                 | Description                         |
|---------------------------|--------|-----------------------------------------|-------------------------------------|
| `enableRegistration`      | bool   | `true`                                  | Enable/disable user registration    |
| `enableConfirmation`      | bool   | `true`                                  | Require email confirmation          |
| `enableUnconfirmedLogin`  | bool   | `false`                                 | Allow login without confirmation    |
| `enablePasswordRecovery`  | bool   | `true`                                  | Enable password recovery            |
| `enableGdpr`              | bool   | `false`                                 | Enable GDPR features (v2)           |
| `enableImpersonation`     | bool   | `true`                                  | Enable admin impersonation          |
| `enableGeneratedPassword` | bool   | `false`                                 | Auto-generate passwords             |
| `enableGravatar`          | bool   | `true`                                  | Enable Gravatar support             |
| `enableAvatarUpload`      | bool   | `true`                                  | Enable local avatar uploads         |
| `emailChangeStrategy`     | int    | `1`                                     | Email change strategy (0-2)         |
| `rememberFor`             | int    | `1209600`                               | Remember me duration (seconds)      |
| `confirmWithin`           | int    | `86400`                                 | Confirmation token expiry (seconds) |
| `recoverWithin`           | int    | `21600`                                 | Recovery token expiry (seconds)     |
| `minPasswordLength`       | int    | `8`                                     | Minimum password length             |
| `maxPasswordLength`       | int    | `72`                                    | Maximum password length             |
| `cost`                    | int    | `12`                                    | bcrypt cost parameter               |
| `admins`                  | array  | `[]`                                    | Admin email addresses               |
| `adminPermission`         | string | `null`                                  | RBAC permission for admin access    |
| `impersonatePermission`   | string | `null`                                  | RBAC permission for impersonation   |
| `urlPrefix`               | string | `'user'`                                | URL prefix for module routes        |
| `avatarPath`              | string | `'@webroot/uploads/avatars'`            | Avatar storage path                 |
| `avatarUrl`               | string | `'@web/uploads/avatars'`                | Avatar URL path                     |
| `maxAvatarSize`           | int    | `2097152`                               | Max avatar file size (bytes)        |
| `avatarExtensions`        | array  | `['jpg', 'jpeg', 'png', 'gif', 'webp']` | Allowed avatar extensions           |

## Console Commands

```bash
# Create a new user
php yii user/create admin@example.com password

# Confirm a user
php yii user/confirm admin@example.com

# Delete a user
php yii user/delete admin@example.com

# Migrate from dektrium/yii2-user
php yii migrate-from-dektrium/migrate
```

## Model Overriding

```php
'modules' => [
    'user' => [
        'class' => 'cgsmith\user\Module',
        'modelMap' => [
            'User' => 'app\models\User',
            'Profile' => 'app\models\Profile',
            'RegistrationForm' => 'app\models\RegistrationForm',
        ],
    ],
],
```

## Event Handling

```php
'modules' => [
    'user' => [
        'class' => 'cgsmith\user\Module',
        'controllerMap' => [
            'registration' => [
                'class' => 'cgsmith\user\controllers\RegistrationController',
                'on afterRegister' => ['app\handlers\UserHandler', 'onRegister'],
            ],
        ],
    ],
],
```

Available events:

- `RegistrationController::EVENT_BEFORE_REGISTER`
- `RegistrationController::EVENT_AFTER_REGISTER`
- `RegistrationController::EVENT_BEFORE_CONFIRM`
- `RegistrationController::EVENT_AFTER_CONFIRM`
- `SecurityController::EVENT_BEFORE_LOGIN`
- `SecurityController::EVENT_AFTER_LOGIN`
- `SecurityController::EVENT_BEFORE_LOGOUT`
- `SecurityController::EVENT_AFTER_LOGOUT`
- `RecoveryController::EVENT_BEFORE_REQUEST`
- `RecoveryController::EVENT_AFTER_REQUEST`
- `RecoveryController::EVENT_BEFORE_RESET`
- `RecoveryController::EVENT_AFTER_RESET`

## View Customization

Override views by setting up theme path mapping:

```php
'components' => [
    'view' => [
        'theme' => [
            'pathMap' => [
                '@cgsmith/user/views' => '@app/views/user',
            ],
        ],
    ],
],
```

## GDPR Features (Coming in v2)

GDPR compliance features are planned for v2. When complete, users will be able to:

- Export all their personal data as JSON
- Request account deletion with soft-delete support
- View what data is stored about them
- Manage consent preferences

See the [v2 Roadmap](#v2-roadmap) for more details.

## Migration from dektrium/yii2-user

1. Install cgsmith/yii2-user
2. Update your configuration to use the new module
3. Run the migration command:

```bash
php yii migrate-from-dektrium/migrate
```

This will:

- Migrate existing user data
- Convert token formats
- Preserve all user relationships
- Backup original tables as `user_dektrium_backup`, `profile_dektrium_backup`, `token_dektrium_backup`

### Custom Field Migration

**Important:** If you added custom columns to the original dektrium user table (e.g., `developer_id`, `company_id`, `department`, etc.), these fields will **not** be automatically migrated to the new user table. You must create a separate migration to:

1. Add the custom column(s) to the new `user` table
2. Copy the data from the backup table using email matching

Example migration for a custom `developer_id` field:

```php
<?php

use yii\db\Migration;

class m241215_000000_migrate_custom_user_fields extends Migration
{
    public function safeUp()
    {
        // Add custom column to new user table
        $this->addColumn('{{%user}}', 'developer_id', $this->integer()->null()->defaultValue(0));
        $this->createIndex('idx-user-developer_id', '{{%user}}', 'developer_id');

        // Copy data from backup table based on email match
        if ($this->db->schema->getTableSchema('{{%user_dektrium_backup}}') !== null) {
            $this->execute("
                UPDATE {{%user}} u
                INNER JOIN {{%user_dektrium_backup}} b ON u.email = b.email
                SET u.developer_id = b.developer_id
                WHERE b.developer_id IS NOT NULL AND b.developer_id != 0
            ");
        }

        return true;
    }

    public function safeDown()
    {
        $this->dropIndex('idx-user-developer_id', '{{%user}}');
        $this->dropColumn('{{%user}}', 'developer_id');
        return true;
    }
}
```

After migration, update your custom User model to include the new field:

```php
class User extends \cgsmith\user\models\User
{
    // Your custom field will be accessible as $user->developer_id
}
```

## v2 Roadmap

The following features are planned for version 2.0:

### Authentication & Security

- [ ] **Two-Factor Authentication (2FA)**
    - TOTP (Google Authenticator, Authy)
    - SMS verification
    - Backup codes
    - Per-user 2FA enforcement
- [ ] **Passwordless Authentication**
    - Magic link login
    - WebAuthn/FIDO2 support
- [ ] **Enhanced Session Management**
    - View active sessions
    - Remote session termination
    - Device fingerprinting

### Social Authentication

- [ ] **OAuth2 Provider Integration**
    - Google
    - GitHub
    - Facebook
    - Apple
    - Microsoft
    - Custom providers via configuration
- [ ] **Account Linking**
    - Link multiple social accounts
    - Unlink social accounts
    - Primary account selection

### Security Hardening

- [ ] **Brute Force Protection**
    - Rate limiting per IP/user
    - Progressive delays
    - CAPTCHA integration (reCAPTCHA v3, hCaptcha)
- [ ] **Password Policies**
    - Password strength meter
    - Common password blocklist
    - Password history (prevent reuse)
    - Password expiration
- [ ] **Security Audit Log**
    - Login attempts
    - Password changes
    - Security setting changes
    - Admin actions

### User Experience

- [ ] **Registration Improvements**
    - Multi-step registration wizard
    - Progressive profiling
    - Username suggestions
- [ ] **Email Templates**
    - HTML email templates
    - Template customization via admin
    - Email preview
- [ ] **Admin Dashboard**
    - User statistics
    - Registration trends
    - Login analytics
    - Security alerts

### API & Integration

- [ ] **REST API**
    - Token-based authentication
    - OAuth2 server implementation
    - API rate limiting
    - Swagger/OpenAPI documentation
- [ ] **Webhooks**
    - User registered
    - User verified
    - Password changed
    - Account deleted

### Infrastructure

- [ ] **Queue Support**
    - Asynchronous email sending
    - Background token cleanup
- [ ] **Cache Integration**
    - Session caching
    - User data caching
    - Token validation caching
- [ ] **Multi-tenancy Support**
    - Tenant-based user isolation
    - Custom domains per tenant

### Migration Tools

- [ ] **Smart Migration from dektrium/yii2-user**
    - Auto-detect custom columns added to user table
    - Interactive migration wizard for custom fields
    - Schema diff reporting before migration
    - Automatic migration script generation for custom fields
    - Support for foreign key relationship preservation
    - Rollback support with data integrity checks
  
- [ ] **Smart Migration from 2amigos/yii2-usuario**
    - Auto-detect custom columns added to user table
    - Interactive migration wizard for custom fields
    - Schema diff reporting before migration
    - Automatic migration script generation for custom fields
    - Support for foreign key relationship preservation
    - Rollback support with data integrity checks

### Compliance

- [ ] **Enhanced GDPR**
    - Right to be forgotten workflow
    - Data retention policies
    - Consent management
    - Cookie consent integration
- [ ] **Accessibility**
    - WCAG 2.1 AA compliance
    - Screen reader support
    - Keyboard navigation

## Contributing

Contributions are welcome! Please read our contributing guidelines before submitting pull requests.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## Credits

This module draws inspiration from:

- [dektrium/yii2-user](https://github.com/dektrium/yii2-user) - The original Yii2 user module
- [2amigos/yii2-usuario](https://github.com/2amigos/yii2-usuario) - A maintained fork with improvements

Special thanks to the original authors and contributors of these projects.
