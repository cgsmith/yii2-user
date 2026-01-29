<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use Yii;
use yii\base\Model;

/**
 * Two-factor authentication verification form.
 */
class TwoFactorForm extends Model
{
    public ?string $code = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['code', 'required'],
            ['code', 'string', 'min' => 6, 'max' => 10],
            ['code', 'match', 'pattern' => '/^[0-9a-zA-Z]+$/', 'message' => Yii::t('user', 'Invalid code format.')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'code' => Yii::t('user', 'Verification Code'),
        ];
    }
}
