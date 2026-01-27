<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\Module;
use Yii;
use yii\base\Model;

/**
 * Account settings form.
 */
class SettingsForm extends Model
{
    public ?string $email = null;
    public ?string $username = null;
    public ?string $new_password = null;
    public ?string $new_password_confirm = null;
    public ?string $current_password = null;

    private User $_user;

    public function __construct(User $user, array $config = [])
    {
        $this->_user = $user;
        $this->email = $user->email;
        $this->username = $user->username;

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        $module = $this->getModule();

        return [
            // Email
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class, 'filter' => ['!=', 'id', $this->_user->id], 'message' => Yii::t('user', 'This email address has already been taken.')],

            // Username
            ['username', 'trim'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['username', 'match', 'pattern' => '/^[-a-zA-Z0-9_\.]+$/', 'message' => Yii::t('user', 'Username can only contain alphanumeric characters, underscores, hyphens, and dots.')],
            ['username', 'unique', 'targetClass' => User::class, 'filter' => ['!=', 'id', $this->_user->id], 'message' => Yii::t('user', 'This username has already been taken.')],

            // New password
            ['new_password', 'string', 'min' => $module->minPasswordLength, 'max' => $module->maxPasswordLength],
            ['new_password_confirm', 'compare', 'compareAttribute' => 'new_password', 'message' => Yii::t('user', 'Passwords do not match.')],

            // Current password (required when changing email or password)
            ['current_password', 'required', 'when' => function ($model) {
                return $model->email !== $this->_user->email || !empty($model->new_password);
            }, 'message' => Yii::t('user', 'Current password is required to change email or password.')],
            ['current_password', 'validateCurrentPassword'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'email' => Yii::t('user', 'Email'),
            'username' => Yii::t('user', 'Username'),
            'new_password' => Yii::t('user', 'New Password'),
            'new_password_confirm' => Yii::t('user', 'Confirm New Password'),
            'current_password' => Yii::t('user', 'Current Password'),
        ];
    }

    /**
     * Validate current password.
     */
    public function validateCurrentPassword(string $attribute): void
    {
        if ($this->hasErrors()) {
            return;
        }

        if (!empty($this->current_password) && !$this->_user->validatePassword($this->current_password)) {
            $this->addError($attribute, Yii::t('user', 'Current password is incorrect.'));
        }
    }

    /**
     * Check if email has changed.
     */
    public function isEmailChanged(): bool
    {
        return $this->email !== $this->_user->email;
    }

    /**
     * Check if password should be changed.
     */
    public function isPasswordChanged(): bool
    {
        return !empty($this->new_password);
    }

    /**
     * Get the associated user.
     */
    public function getUser(): User
    {
        return $this->_user;
    }

    /**
     * Get the user module.
     */
    protected function getModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        return $module;
    }
}
