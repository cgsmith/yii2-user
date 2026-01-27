<?php

declare(strict_types=1);

namespace cgsmith\user\commands;

use cgsmith\user\helpers\Password;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * User management console commands.
 *
 * Usage:
 *   yii user/create <email> [password]    Create a new user
 *   yii user/delete <email>               Delete a user
 *   yii user/password <email> [password]  Change user password
 *   yii user/confirm <email>              Confirm user email
 *   yii user/block <email>                Block a user
 *   yii user/unblock <email>              Unblock a user
 */
class UserController extends Controller
{
    /**
     * @var Module
     */
    public $module;

    /**
     * Create a new user.
     *
     * @param string $email User email
     * @param string|null $password Password (auto-generated if not provided)
     */
    public function actionCreate(string $email, ?string $password = null): int
    {
        if ($password === null) {
            $password = Password::generate(12);
            $this->stdout("Generated password: {$password}\n", Console::FG_YELLOW);
        }

        $user = new User();
        $user->email = $email;
        $user->password = $password;
        $user->status = User::STATUS_ACTIVE;
        $user->email_confirmed_at = date('Y-m-d H:i:s');

        if (!$user->save()) {
            $this->stderr("Failed to create user:\n", Console::FG_RED);
            foreach ($user->errors as $attribute => $errors) {
                foreach ($errors as $error) {
                    $this->stderr("  - {$attribute}: {$error}\n");
                }
            }
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("User created successfully!\n", Console::FG_GREEN);
        $this->stdout("  ID: {$user->id}\n");
        $this->stdout("  Email: {$user->email}\n");

        return ExitCode::OK;
    }

    /**
     * Delete a user.
     *
     * @param string $email User email
     */
    public function actionDelete(string $email): int
    {
        $user = User::findByEmail($email);

        if ($user === null) {
            $this->stderr("User not found: {$email}\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (!$this->confirm("Are you sure you want to delete user {$email}?")) {
            return ExitCode::OK;
        }

        if ($user->delete()) {
            $this->stdout("User deleted successfully.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->stderr("Failed to delete user.\n", Console::FG_RED);
        return ExitCode::UNSPECIFIED_ERROR;
    }

    /**
     * Change user password.
     *
     * @param string $email User email
     * @param string|null $password New password (auto-generated if not provided)
     */
    public function actionPassword(string $email, ?string $password = null): int
    {
        $user = User::findByEmail($email);

        if ($user === null) {
            $this->stderr("User not found: {$email}\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if ($password === null) {
            $password = Password::generate(12);
            $this->stdout("Generated password: {$password}\n", Console::FG_YELLOW);
        }

        if ($user->resetPassword($password)) {
            $this->stdout("Password changed successfully.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->stderr("Failed to change password.\n", Console::FG_RED);
        return ExitCode::UNSPECIFIED_ERROR;
    }

    /**
     * Confirm user email.
     *
     * @param string $email User email
     */
    public function actionConfirm(string $email): int
    {
        $user = User::findByEmail($email);

        if ($user === null) {
            $this->stderr("User not found: {$email}\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if ($user->getIsConfirmed()) {
            $this->stdout("User is already confirmed.\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        if ($user->confirm()) {
            $this->stdout("User confirmed successfully.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->stderr("Failed to confirm user.\n", Console::FG_RED);
        return ExitCode::UNSPECIFIED_ERROR;
    }

    /**
     * Block a user.
     *
     * @param string $email User email
     */
    public function actionBlock(string $email): int
    {
        $user = User::findByEmail($email);

        if ($user === null) {
            $this->stderr("User not found: {$email}\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if ($user->getIsBlocked()) {
            $this->stdout("User is already blocked.\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        if ($user->block()) {
            $this->stdout("User blocked successfully.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->stderr("Failed to block user.\n", Console::FG_RED);
        return ExitCode::UNSPECIFIED_ERROR;
    }

    /**
     * Unblock a user.
     *
     * @param string $email User email
     */
    public function actionUnblock(string $email): int
    {
        $user = User::findByEmail($email);

        if ($user === null) {
            $this->stderr("User not found: {$email}\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (!$user->getIsBlocked()) {
            $this->stdout("User is not blocked.\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        if ($user->unblock()) {
            $this->stdout("User unblocked successfully.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->stderr("Failed to unblock user.\n", Console::FG_RED);
        return ExitCode::UNSPECIFIED_ERROR;
    }
}
