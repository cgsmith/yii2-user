<?php

declare(strict_types=1);

namespace cgsmith\user\commands;

use cgsmith\user\Module;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\helpers\Console;

/**
 * Migrate users from dektrium/yii2-user to cgsmith/yii2-user.
 *
 * Usage:
 *   yii migrate-from-dektrium/preview    Preview migration changes
 *   yii migrate-from-dektrium/execute    Execute migration
 *   yii migrate-from-dektrium/rollback   Rollback migration
 */
class MigrateFromDektriumController extends Controller
{
    /**
     * @var Module
     */
    public $module;

    /**
     * @var string Database component ID
     */
    public string $db = 'db';

    /**
     * Preview migration - show what will be changed.
     */
    public function actionPreview(): int
    {
        $this->stdout("=== Dektrium to cgsmith/yii2-user Migration Preview ===\n\n", Console::FG_CYAN);

        $db = $this->getDb();

        // Check if dektrium tables exist
        $dektriumTables = $this->checkDektriumTables($db);

        if (empty($dektriumTables)) {
            $this->stdout("No dektrium tables found. Nothing to migrate.\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $this->stdout("Found dektrium tables:\n", Console::FG_GREEN);
        foreach ($dektriumTables as $table => $count) {
            $this->stdout("  - {$table}: {$count} rows\n");
        }

        $this->stdout("\nMigration will:\n", Console::FG_CYAN);
        $this->stdout("  1. Create new tables (user_new, user_profile_new, user_token_new)\n");
        $this->stdout("  2. Transform and copy data with these conversions:\n");
        $this->stdout("     - confirmed_at (int) -> email_confirmed_at (datetime)\n");
        $this->stdout("     - blocked_at (int) -> blocked_at (datetime) + status='blocked'\n");
        $this->stdout("     - created_at/updated_at (int) -> datetime\n");
        $this->stdout("     - token.type (int) -> ENUM('confirmation','recovery','email_change')\n");
        $this->stdout("     - profile table -> user_profile table\n");
        $this->stdout("  3. Backup original tables (user -> user_dektrium_backup, etc.)\n");
        $this->stdout("  4. Rename new tables to production names\n");

        $this->stdout("\nRun 'yii migrate-from-dektrium/execute' to proceed.\n", Console::FG_YELLOW);

        return ExitCode::OK;
    }

    /**
     * Execute migration.
     */
    public function actionExecute(): int
    {
        $this->stdout("=== Executing Dektrium Migration ===\n\n", Console::FG_CYAN);

        if (!$this->confirm('This will modify your database. Have you backed up your data?')) {
            $this->stdout("Migration cancelled.\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $db = $this->getDb();
        $transaction = $db->beginTransaction();

        try {
            // Step 1: Check dektrium tables exist
            $dektriumTables = $this->checkDektriumTables($db);

            if (empty($dektriumTables)) {
                $this->stdout("No dektrium tables found. Nothing to migrate.\n", Console::FG_YELLOW);
                $transaction->rollBack();
                return ExitCode::OK;
            }

            // Step 2: Create new tables
            $this->stdout("Creating new tables...\n");
            $this->createNewTables($db);

            // Step 3: Migrate users
            $this->stdout("Migrating users...\n");
            $userCount = $this->migrateUsers($db);
            $this->stdout("  Migrated {$userCount} users\n", Console::FG_GREEN);

            // Step 4: Migrate profiles
            $this->stdout("Migrating profiles...\n");
            $profileCount = $this->migrateProfiles($db);
            $this->stdout("  Migrated {$profileCount} profiles\n", Console::FG_GREEN);

            // Step 5: Migrate tokens
            $this->stdout("Migrating tokens...\n");
            $tokenCount = $this->migrateTokens($db);
            $this->stdout("  Migrated {$tokenCount} tokens\n", Console::FG_GREEN);

            // Step 6: Backup and swap tables
            $this->stdout("Backing up original tables...\n");
            $this->backupAndSwapTables($db);

            $transaction->commit();

            $this->stdout("\n=== Migration completed successfully! ===\n", Console::FG_GREEN);
            $this->stdout("Original tables backed up as: user_dektrium_backup, profile_dektrium_backup, token_dektrium_backup\n");
            $this->stdout("\nNext steps:\n");
            $this->stdout("  1. Update your config to use cgsmith\\user\\Module\n");
            $this->stdout("  2. Update model imports from dektrium\\user to cgsmith\\user\n");
            $this->stdout("  3. Test your application thoroughly\n");
            $this->stdout("  4. Once verified, you can drop the backup tables\n");

            return ExitCode::OK;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->stderr("Migration failed: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Rollback migration.
     */
    public function actionRollback(): int
    {
        $this->stdout("=== Rolling Back Migration ===\n\n", Console::FG_CYAN);

        if (!$this->confirm('This will restore the original dektrium tables and remove cgsmith tables. Continue?')) {
            $this->stdout("Rollback cancelled.\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $db = $this->getDb();
        $transaction = $db->beginTransaction();

        try {
            // Check if backup tables exist
            $schema = $db->schema;
            $hasUserBackup = $schema->getTableSchema('user_dektrium_backup') !== null;
            $hasProfileBackup = $schema->getTableSchema('profile_dektrium_backup') !== null;
            $hasTokenBackup = $schema->getTableSchema('token_dektrium_backup') !== null;

            if (!$hasUserBackup) {
                $this->stdout("No backup tables found. Cannot rollback.\n", Console::FG_YELLOW);
                $transaction->rollBack();
                return ExitCode::OK;
            }

            // Drop new tables
            $this->stdout("Dropping new tables...\n");
            $db->createCommand("DROP TABLE IF EXISTS {{%user_token}}")->execute();
            $db->createCommand("DROP TABLE IF EXISTS {{%user_profile}}")->execute();
            $db->createCommand("DROP TABLE IF EXISTS {{%user_social_account}}")->execute();
            $db->createCommand("DROP TABLE IF EXISTS {{%user}}")->execute();

            // Restore backup tables
            $this->stdout("Restoring backup tables...\n");
            $db->createCommand("RENAME TABLE {{%user_dektrium_backup}} TO {{%user}}")->execute();

            if ($hasProfileBackup) {
                $db->createCommand("RENAME TABLE {{%profile_dektrium_backup}} TO {{%profile}}")->execute();
            }

            if ($hasTokenBackup) {
                $db->createCommand("RENAME TABLE {{%token_dektrium_backup}} TO {{%token}}")->execute();
            }

            $transaction->commit();

            $this->stdout("\n=== Rollback completed successfully! ===\n", Console::FG_GREEN);

            return ExitCode::OK;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->stderr("Rollback failed: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Check if dektrium tables exist.
     */
    protected function checkDektriumTables(Connection $db): array
    {
        $tables = [];
        $schema = $db->schema;

        // Check user table with dektrium schema (has flags column)
        $userTable = $schema->getTableSchema('user');
        if ($userTable !== null && isset($userTable->columns['flags'])) {
            $count = $db->createCommand("SELECT COUNT(*) FROM {{%user}}")->queryScalar();
            $tables['user'] = (int) $count;
        }

        // Check profile table
        $profileTable = $schema->getTableSchema('profile');
        if ($profileTable !== null) {
            $count = $db->createCommand("SELECT COUNT(*) FROM {{%profile}}")->queryScalar();
            $tables['profile'] = (int) $count;
        }

        // Check token table (dektrium uses smallint type)
        $tokenTable = $schema->getTableSchema('token');
        if ($tokenTable !== null && isset($tokenTable->columns['type']) && $tokenTable->columns['type']->phpType === 'integer') {
            $count = $db->createCommand("SELECT COUNT(*) FROM {{%token}}")->queryScalar();
            $tables['token'] = (int) $count;
        }

        return $tables;
    }

    /**
     * Create new tables for cgsmith/yii2-user.
     */
    protected function createNewTables(Connection $db): void
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';

        // User table
        $db->createCommand("
            CREATE TABLE {{%user_new}} (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                username VARCHAR(255) UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                auth_key VARCHAR(32) NOT NULL,
                status ENUM('pending', 'active', 'blocked') NOT NULL DEFAULT 'pending',
                email_confirmed_at DATETIME NULL,
                blocked_at DATETIME NULL,
                last_login_at DATETIME NULL,
                last_login_ip VARCHAR(45) NULL,
                registration_ip VARCHAR(45) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                gdpr_consent_at DATETIME NULL,
                gdpr_deleted_at DATETIME NULL,
                INDEX idx_status (status),
                INDEX idx_email_confirmed (email_confirmed_at)
            ) {$tableOptions}
        ")->execute();

        // Profile table
        $db->createCommand("
            CREATE TABLE {{%user_profile_new}} (
                user_id INT UNSIGNED PRIMARY KEY,
                name VARCHAR(255) NULL,
                bio TEXT NULL,
                location VARCHAR(255) NULL,
                website VARCHAR(255) NULL,
                timezone VARCHAR(40) NULL,
                avatar_path VARCHAR(255) NULL,
                gravatar_email VARCHAR(255) NULL,
                use_gravatar TINYINT(1) DEFAULT 1,
                public_email VARCHAR(255) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) {$tableOptions}
        ")->execute();

        // Token table
        $db->createCommand("
            CREATE TABLE {{%user_token_new}} (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                type ENUM('confirmation', 'recovery', 'email_change') NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                data JSON NULL,
                expires_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_type (user_id, type),
                INDEX idx_expires (expires_at)
            ) {$tableOptions}
        ")->execute();
    }

    /**
     * Migrate users from dektrium to new schema.
     */
    protected function migrateUsers(Connection $db): int
    {
        return (int) $db->createCommand("
            INSERT INTO {{%user_new}} (
                id, email, username, password_hash, auth_key,
                status, email_confirmed_at, blocked_at,
                last_login_at, registration_ip, created_at, updated_at
            )
            SELECT
                id, email, username, password_hash, auth_key,
                CASE
                    WHEN blocked_at IS NOT NULL THEN 'blocked'
                    WHEN confirmed_at IS NOT NULL THEN 'active'
                    ELSE 'pending'
                END,
                FROM_UNIXTIME(confirmed_at),
                FROM_UNIXTIME(blocked_at),
                FROM_UNIXTIME(last_login_at),
                registration_ip,
                FROM_UNIXTIME(created_at),
                FROM_UNIXTIME(updated_at)
            FROM {{%user}}
        ")->execute();
    }

    /**
     * Migrate profiles from dektrium to new schema.
     */
    protected function migrateProfiles(Connection $db): int
    {
        $schema = $db->schema;

        if ($schema->getTableSchema('profile') === null) {
            return 0;
        }

        return (int) $db->createCommand("
            INSERT INTO {{%user_profile_new}} (
                user_id, name, bio, location, website, timezone,
                gravatar_email, public_email, use_gravatar
            )
            SELECT
                user_id, name, bio, location, website, timezone,
                gravatar_email, public_email, 1
            FROM {{%profile}}
        ")->execute();
    }

    /**
     * Migrate tokens from dektrium to new schema.
     */
    protected function migrateTokens(Connection $db): int
    {
        $schema = $db->schema;

        if ($schema->getTableSchema('token') === null) {
            return 0;
        }

        // Dektrium token types: 0 = confirmation, 1 = recovery, 2 = confirm_new_email, 3 = confirm_old_email
        // We map 2 and 3 to email_change
        return (int) $db->createCommand("
            INSERT INTO {{%user_token_new}} (
                user_id, type, token, expires_at, created_at
            )
            SELECT
                user_id,
                CASE type
                    WHEN 0 THEN 'confirmation'
                    WHEN 1 THEN 'recovery'
                    ELSE 'email_change'
                END,
                CONCAT(code, SUBSTRING(MD5(RAND()), 1, 32)),
                DATE_ADD(FROM_UNIXTIME(created_at), INTERVAL 24 HOUR),
                FROM_UNIXTIME(created_at)
            FROM {{%token}}
        ")->execute();
    }

    /**
     * Backup original tables and swap with new ones.
     */
    protected function backupAndSwapTables(Connection $db): void
    {
        $schema = $db->schema;

        // Backup user table
        $db->createCommand("RENAME TABLE {{%user}} TO {{%user_dektrium_backup}}")->execute();

        // Backup profile table if exists
        if ($schema->getTableSchema('profile') !== null) {
            $db->createCommand("RENAME TABLE {{%profile}} TO {{%profile_dektrium_backup}}")->execute();
        }

        // Backup token table if exists
        if ($schema->getTableSchema('token') !== null) {
            $db->createCommand("RENAME TABLE {{%token}} TO {{%token_dektrium_backup}}")->execute();
        }

        // Rename new tables to production names
        $db->createCommand("RENAME TABLE {{%user_new}} TO {{%user}}")->execute();
        $db->createCommand("RENAME TABLE {{%user_profile_new}} TO {{%user_profile}}")->execute();
        $db->createCommand("RENAME TABLE {{%user_token_new}} TO {{%user_token}}")->execute();

        // Add foreign keys
        $db->createCommand("
            ALTER TABLE {{%user_profile}}
            ADD CONSTRAINT fk_user_profile_user
            FOREIGN KEY (user_id) REFERENCES {{%user}}(id) ON DELETE CASCADE
        ")->execute();

        $db->createCommand("
            ALTER TABLE {{%user_token}}
            ADD CONSTRAINT fk_user_token_user
            FOREIGN KEY (user_id) REFERENCES {{%user}}(id) ON DELETE CASCADE
        ")->execute();

        // Create social account table (empty, for v2.0)
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        $db->createCommand("
            CREATE TABLE {{%user_social_account}} (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NULL,
                provider VARCHAR(50) NOT NULL,
                provider_id VARCHAR(255) NOT NULL,
                data JSON NULL,
                email VARCHAR(255) NULL,
                username VARCHAR(255) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE INDEX idx_provider_id (provider, provider_id),
                INDEX idx_user (user_id),
                CONSTRAINT fk_user_social_account_user
                FOREIGN KEY (user_id) REFERENCES {{%user}}(id) ON DELETE CASCADE
            ) {$tableOptions}
        ")->execute();
    }

    /**
     * Get database connection.
     */
    protected function getDb(): Connection
    {
        return Yii::$app->get($this->db);
    }
}
