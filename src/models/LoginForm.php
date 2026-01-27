<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\Module;
use Yii;
use yii\base\Model;

/**
 * Login form model.
 */
class LoginForm extends Model
{
    public ?string $login = null;
    public ?string $password = null;
    public bool $rememberMe = false;

    private ?User $_user = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['login', 'password'], 'required'],
            [['login'], 'string'],
            [['password'], 'string'],
            [['rememberMe'], 'boolean'],
            [['password'], 'validatePassword'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'login' => Yii::t('user', 'Email or Username'),
            'password' => Yii::t('user', 'Password'),
            'rememberMe' => Yii::t('user', 'Remember me'),
        ];
    }

    /**
     * Validate the password.
     */
    public function validatePassword(string $attribute): void
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->getUser();

        if ($user === null) {
            $this->addError($attribute, Yii::t('user', 'Invalid login or password.'));
            return;
        }

        if ($user->getIsBlocked()) {
            $this->addError($attribute, Yii::t('user', 'Your account has been blocked.'));
            return;
        }

        $module = $this->getModule();
        if (!$module->enableUnconfirmedLogin && !$user->getIsConfirmed()) {
            $this->addError($attribute, Yii::t('user', 'You need to confirm your email address.'));
            return;
        }

        if (!$user->validatePassword($this->password)) {
            $this->addError($attribute, Yii::t('user', 'Invalid login or password.'));
        }
    }

    /**
     * Attempt to log in the user.
     */
    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->getUser();
        $module = $this->getModule();

        $duration = $this->rememberMe ? $module->rememberFor : 0;

        if (Yii::$app->user->login($user, $duration)) {
            $user->updateLastLogin();
            return true;
        }

        return false;
    }

    /**
     * Get the user by login (email or username).
     */
    public function getUser(): ?User
    {
        if ($this->_user === null && $this->login !== null) {
            $this->_user = User::findByEmailOrUsername($this->login);
        }

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
