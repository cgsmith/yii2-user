<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\TwoFactorForm $model
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$formClass = $module->activeFormClass;
$this->title = Yii::t('user', 'Two-Factor Verification');
?>

<div class="user-two-factor-verify">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Yii::t('user', 'Enter the 6-digit code from your authenticator app, or use one of your backup codes.') ?></p>

    <?php $form = $formClass::begin(['id' => 'two-factor-form'] + $module->formFieldConfig) ?>

    <?= $form->field($model, 'code')->textInput([
        'autofocus' => true,
        'autocomplete' => 'one-time-code',
        'inputmode' => 'numeric',
        'placeholder' => Yii::t('user', 'Enter code'),
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('user', 'Verify'), ['class' => 'btn btn-primary btn-block']) ?>
    </div>

    <?php $formClass::end() ?>

    <p class="text-muted mt-3">
        <small>
            <?= Yii::t('user', "Lost your phone? Use one of your backup codes to sign in.") ?>
        </small>
    </p>
</div>
