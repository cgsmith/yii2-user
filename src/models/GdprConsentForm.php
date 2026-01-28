<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\Module;
use Yii;
use yii\base\Model;

/**
 * GDPR consent form model.
 */
class GdprConsentForm extends Model
{
    public bool $consent = false;
    public bool $marketingConsent = false;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        $rules = [
            [['consent'], 'required'],
            [['consent'], 'boolean'],
            [['consent'], 'validateConsentRequired'],
            [['marketingConsent'], 'boolean'],
        ];

        return $rules;
    }

    /**
     * Validate that consent is given.
     */
    public function validateConsentRequired(string $attribute): void
    {
        if (!$this->consent) {
            $this->addError($attribute, Yii::t('user', 'You must accept the terms to continue.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'consent' => Yii::t('user', 'I have read and accept the privacy policy'),
            'marketingConsent' => Yii::t('user', 'I agree to receive marketing communications'),
        ];
    }

    /**
     * Get the module instance.
     */
    protected function getModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        return $module;
    }
}
