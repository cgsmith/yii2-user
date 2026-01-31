<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use Yii;
use yii\base\Model;

/**
 * Resend confirmation email form.
 */
class ResendForm extends Model
{
    public ?string $email = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
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
}
