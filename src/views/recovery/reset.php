<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\RecoveryResetForm $model
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$this->title = Yii::t('user', 'Reset Password');
$this->params['breadcrumbs'][] = $this->title;

$formClass = $module->activeFormClass;
$formConfig = array_merge([
    'id' => 'reset-form',
], $module->formFieldConfig ? ['fieldConfig' => $module->formFieldConfig] : []);
?>

<div class="user-recovery-reset">
    <div class="user-form-wrapper">
        <div class="user-card">
            <div class="user-card-body">
                <h1 class="user-form-title"><?= Html::encode($this->title) ?></h1>

                <p class="user-form-description">
                    <?= Yii::t('user', 'Enter your new password below.') ?>
                </p>

                <?php $form = $formClass::begin($formConfig); ?>

                <?= $form->field($model, 'password')
                    ->passwordInput(['autofocus' => true, 'placeholder' => Yii::t('user', 'New Password')]) ?>

                <?= $form->field($model, 'password_confirm')
                    ->passwordInput(['placeholder' => Yii::t('user', 'Confirm Password')]) ?>

                <div class="user-form-actions">
                    <?= Html::submitButton(Yii::t('user', 'Reset Password'), ['class' => 'user-btn user-btn-primary user-btn-lg']) ?>
                </div>

                <?php $formClass::end(); ?>
            </div>
        </div>
    </div>
</div>
