<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\Module;
use Yii;
use yii\base\Model;

/**
 * Registration form model.
 */
class RegistrationForm extends Model
{
    public ?string $email = null;
    public ?string $username = null;
    public ?string $password = null;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $module = $this->getModule();

        $rules = [
            // Email
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class, 'message' => Yii::t('user', 'This email address has already been taken.')],

            // Username (optional)
            ['username', 'trim'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['username', 'match', 'pattern' => '/^[-a-zA-Z0-9_\.]+$/', 'message' => Yii::t('user', 'Username can only contain alphanumeric characters, underscores, hyphens, and dots.')],
            ['username', 'unique', 'targetClass' => User::class, 'message' => Yii::t('user', 'This username has already been taken.')],
        ];

        // Password rules (unless generated)
        if (!$module->enableGeneratedPassword) {
            $rules[] = ['password', 'required'];
            $rules[] = ['password', 'string', 'min' => $module->minPasswordLength, 'max' => $module->maxPasswordLength];
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'email' => Yii::t('user', 'Email'),
            'username' => Yii::t('user', 'Username'),
            'password' => Yii::t('user', 'Password'),
        ];
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
