<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\GdprConsentForm $model
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$formClass = $module->activeFormClass;
$this->title = Yii::t('user', 'Privacy Consent Required');
?>

<div class="user-gdpr-consent">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-info">
        <?= Yii::t('user', 'We have updated our privacy policy. Please review and accept the terms to continue using our service.') ?>
    </div>

    <?php if ($module->gdprConsentUrl): ?>
    <p>
        <?= Html::a(
            Yii::t('user', 'Read our Privacy Policy'),
            $module->gdprConsentUrl,
            ['target' => '_blank', 'rel' => 'noopener']
        ) ?>
    </p>
    <?php endif; ?>

    <?php $form = $formClass::begin(['id' => 'gdpr-consent-form'] + $module->formFieldConfig) ?>

    <?= $form->field($model, 'consent')->checkbox() ?>

    <?= $form->field($model, 'marketingConsent')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('user', 'Accept and Continue'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php $formClass::end() ?>

    <p class="text-muted mt-3">
        <small>
            <?= Yii::t('user', 'If you do not wish to accept these terms, you may {logout} or {delete} your account.', [
                'logout' => Html::a(Yii::t('user', 'log out'), ['/' . $module->urlPrefix . '/logout'], ['data-method' => 'post']),
                'delete' => $module->enableAccountDelete
                    ? Html::a(Yii::t('user', 'delete'), ['/' . $module->urlPrefix . '/gdpr/delete'])
                    : Yii::t('user', 'contact support to delete'),
            ]) ?>
        </small>
    </p>
</div>
