<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\Module $module
 * @var bool $isEnabled
 * @var int $backupCodesCount
 * @var cgsmith\user\models\TwoFactorSetupForm|null $setupForm
 * @var string|null $secret
 * @var string|null $qrCodeDataUri
 */

use yii\helpers\Html;

$formClass = $module->activeFormClass;
$this->title = Yii::t('user', 'Two-Factor Authentication');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Settings'), 'url' => ['settings/account']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-two-factor-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($isEnabled): ?>
        <div class="alert alert-success">
            <strong><?= Yii::t('user', 'Two-factor authentication is enabled.') ?></strong>
        </div>

        <p><?= Yii::t('user', 'You have {count} backup codes remaining.', ['count' => $backupCodesCount]) ?></p>

        <div class="mb-3">
            <?= Html::a(
                Yii::t('user', 'View Backup Codes'),
                ['backup-codes'],
                ['class' => 'btn btn-secondary']
            ) ?>
        </div>

        <hr>

        <h3><?= Yii::t('user', 'Disable Two-Factor Authentication') ?></h3>
        <p class="text-muted"><?= Yii::t('user', 'Disabling two-factor authentication will make your account less secure.') ?></p>

        <?= Html::beginForm(['disable'], 'post') ?>
        <?= Html::submitButton(
            Yii::t('user', 'Disable Two-Factor Authentication'),
            [
                'class' => 'btn btn-danger',
                'data-confirm' => Yii::t('user', 'Are you sure you want to disable two-factor authentication?'),
            ]
        ) ?>
        <?= Html::endForm() ?>

    <?php else: ?>
        <div class="alert alert-warning">
            <?= Yii::t('user', 'Two-factor authentication is not enabled. Enable it to add an extra layer of security to your account.') ?>
        </div>

        <h3><?= Yii::t('user', 'Set Up Two-Factor Authentication') ?></h3>

        <ol>
            <li><?= Yii::t('user', 'Install an authenticator app on your phone (e.g., Google Authenticator, Authy, 1Password).') ?></li>
            <li><?= Yii::t('user', 'Scan the QR code below with your authenticator app.') ?></li>
            <li><?= Yii::t('user', 'Enter the 6-digit code from your app to verify setup.') ?></li>
        </ol>

        <?php if ($qrCodeDataUri): ?>
            <div class="qr-code mb-3">
                <img src="<?= $qrCodeDataUri ?>" alt="QR Code" style="max-width: 200px;">
            </div>
        <?php endif; ?>

        <p class="text-muted">
            <?= Yii::t('user', "Can't scan the code? Enter this key manually:") ?>
            <code><?= Html::encode($secret) ?></code>
        </p>

        <?php $form = $formClass::begin(['action' => ['enable']] + $module->formFieldConfig) ?>

        <?= Html::activeHiddenInput($setupForm, 'secret') ?>

        <?= $form->field($setupForm, 'code')->textInput([
            'autofocus' => true,
            'autocomplete' => 'one-time-code',
            'inputmode' => 'numeric',
            'pattern' => '[0-9]*',
            'maxlength' => 6,
        ]) ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('user', 'Enable Two-Factor Authentication'), ['class' => 'btn btn-primary']) ?>
        </div>

        <?php $formClass::end() ?>
    <?php endif; ?>
</div>
