<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use Yii;
use yii\base\Model;

/**
 * Two-factor authentication setup form.
 */
class TwoFactorSetupForm extends Model
{
    public ?string $code = null;
    public ?string $secret = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['code', 'required'],
            ['code', 'string', 'length' => 6],
            ['code', 'match', 'pattern' => '/^[0-9]+$/', 'message' => Yii::t('user', 'Code must be 6 digits.')],
            ['secret', 'required'],
            ['secret', 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'code' => Yii::t('user', 'Verification Code'),
            'secret' => Yii::t('user', 'Secret Key'),
        ];
    }
}
