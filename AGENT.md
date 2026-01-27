# yii2-user Project Guidelines

## Project Overview
- **Package**: cgsmith/yii2-user v1.0.0
- **Type**: Yii2 user management extension module
- **PHP Version**: 8.2+ (strict requirement)
- **Yii2 Version**: 2.0.45+
- **Namespace**: `cgsmith\user\*`
- **Architecture**: Service-oriented with dependency injection

## Code Style
- `declare(strict_types=1);` required on all PHP files
- PSR-4 autoloading
- Constructor property promotion for DI
- PHPDoc comments on classes and public methods
- Typed properties and return types throughout
- PHPStan for static analysis

## Directory Structure
```
src/
├── commands/        # Console commands (UserController, MigrateFromDektriumController)
├── contracts/       # Interfaces (UserInterface)
├── controllers/     # Web controllers (Security, Registration, Settings, Admin, Recovery, Gdpr)
├── events/          # Event classes (FormEvent, UserEvent, RegistrationEvent)
├── filters/         # Access control (AccessRule)
├── helpers/         # Utilities (Password)
├── messages/        # i18n translations
├── migrations/      # Database migrations
├── models/          # ActiveRecord + Form models
│   └── query/       # Custom query builders
├── services/        # Business logic (User, Registration, Recovery, Token, Mailer)
├── views/           # View templates
├── Bootstrap.php    # Module bootstrap
└── Module.php       # Main module class
```

## Key Patterns

### Service Layer
Business logic lives in services, not controllers:
- `UserService` - User CRUD, block/unblock
- `RegistrationService` - Registration workflow with transactions
- `RecoveryService` - Password recovery flow
- `TokenService` - Token generation/validation
- `MailerService` - Email sending abstraction

### Custom Query Builders
Fluent interface methods in `models/query/`:
- `active()`, `confirmed()`, `unconfirmed()`, `blocked()`, `pending()`
- `canLogin()`, `byEmail()`, `byUsername()`

### Event System
Controllers trigger events for extensibility:
- `EVENT_BEFORE_LOGIN`, `EVENT_AFTER_LOGIN`
- `EVENT_BEFORE_REGISTER`, `EVENT_AFTER_REGISTER`
- `EVENT_BEFORE_CONFIRM`, `EVENT_AFTER_CONFIRM`

### Model Customization
Override models via `modelMap` configuration in Module.

## Database Tables
- `user` - User accounts with status, IP tracking, timestamps
- `user_profile` - Extended user info (bio, avatar, gravatar)
- `user_token` - Confirmation and recovery tokens
- `user_social_account` - Reserved for future OAuth

## Console Commands
```bash
php yii user/create <email> [password]
php yii user/delete <email>
php yii user/confirm <email>
php yii user/password <email> [password]
php yii user/block <email>
php yii user/unblock <email>
php yii migrate-from-dektrium/migrate
```

## Testing
- PHPUnit 10.0+ configured
- PHPStan 1.10+ for static analysis
- Run tests: `./vendor/bin/phpunit`
- Run static analysis: `./vendor/bin/phpstan analyse`

## Important Entry Points
- `Module.php` - Configuration and model factory
- `Bootstrap.php` - DI container bindings and route registration
- `controllers/SecurityController.php` - Login/logout
- `controllers/RegistrationController.php` - User registration
- `models/User.php` - Core user model implementing IdentityInterface

## When Making Changes
1. Maintain strict typing with proper return types
2. Use service layer for business logic, keep controllers thin
3. Trigger events for extensibility
4. Use custom query methods for database queries
5. Support model map configuration for overridable models
6. Add PHPDoc for new classes/public methods