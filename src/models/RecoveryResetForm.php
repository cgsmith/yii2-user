<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\Module;
use Yii;
use yii\base\Model;

/**
 * Password reset form (after recovery).
 */
class RecoveryResetForm extends Model
{
    public ?string $password = null;
    public ?string $password_confirm = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        $module = $this->getModule();

        return [
            [['password', 'password_confirm'], 'required'],
            ['password', 'string', 'min' => $module->minPasswordLength, 'max' => $module->maxPasswordLength],
            ['password_confirm', 'compare', 'compareAttribute' => 'password', 'message' => Yii::t('user', 'Passwords do not match.')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'password' => Yii::t('user', 'New Password'),
            'password_confirm' => Yii::t('user', 'Confirm Password'),
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
