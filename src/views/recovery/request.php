<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\RecoveryForm $model
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$this->title = Yii::t('user', 'Forgot Password');
$this->params['breadcrumbs'][] = $this->title;

$formClass = $module->activeFormClass;
$formConfig = array_merge([
    'id' => 'recovery-form',
], $module->formFieldConfig ? ['fieldConfig' => $module->formFieldConfig] : []);
?>

<div class="user-recovery-request">
    <div class="user-form-wrapper">
        <div class="user-card">
            <div class="user-card-body">
                <h1 class="user-form-title"><?= Html::encode($this->title) ?></h1>

                <p class="user-form-description">
                    <?= Yii::t('user', 'Enter your email address and we will send you a link to reset your password.') ?>
                </p>

                <?php $form = $formClass::begin($formConfig); ?>

                <?= $form->field($model, 'email')
                    ->textInput(['autofocus' => true, 'placeholder' => Yii::t('user', 'Email')]) ?>

                <div class="user-form-actions">
                    <?= Html::submitButton(Yii::t('user', 'Send Reset Link'), ['class' => 'user-btn user-btn-primary user-btn-lg']) ?>
                </div>

                <?php $formClass::end(); ?>

                <hr>

                <div class="user-form-links">
                    <p>
                        <?= Html::a(Yii::t('user', 'Back to login'), ['/user/security/login']) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
