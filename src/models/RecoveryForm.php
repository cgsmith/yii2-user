<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use Yii;
use yii\base\Model;

/**
 * Password recovery request form.
 */
class RecoveryForm extends Model
{
    public ?string $email = null;

    private ?User $_user = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'validateEmail'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'email' => Yii::t('user', 'Email'),
        ];
    }

    /**
     * Validate that the email exists.
     */
    public function validateEmail(string $attribute): void
    {
        $user = $this->getUser();

        if ($user === null) {
            // Don't reveal that the email doesn't exist (security)
            return;
        }

        if ($user->getIsBlocked()) {
            $this->addError($attribute, Yii::t('user', 'Your account has been blocked.'));
        }
    }

    /**
     * Get the user by email.
     */
    public function getUser(): ?User
    {
        if ($this->_user === null && $this->email !== null) {
            $this->_user = User::findByEmail($this->email);
        }

        return $this->_user;
    }
}
