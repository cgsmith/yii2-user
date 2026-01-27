<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\LoginForm $model
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$this->title = Yii::t('user', 'Sign In');
$this->params['breadcrumbs'][] = $this->title;

$formClass = $module->activeFormClass;
$formConfig = array_merge([
    'id' => 'login-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
], $module->formFieldConfig ? ['fieldConfig' => $module->formFieldConfig] : []);
?>

<div class="user-login">
    <div class="user-form-wrapper">
        <div class="user-card">
            <div class="user-card-body">
                <h1 class="user-form-title"><?= Html::encode($this->title) ?></h1>

                <?php $form = $formClass::begin($formConfig); ?>

                <?= $form->field($model, 'login')
                    ->textInput(['autofocus' => true, 'placeholder' => Yii::t('user', 'Email or Username')]) ?>

                <?= $form->field($model, 'password')
                    ->passwordInput(['placeholder' => Yii::t('user', 'Password')]) ?>

                <?= $form->field($model, 'rememberMe')->checkbox() ?>

                <div class="user-form-actions">
                    <?= Html::submitButton(Yii::t('user', 'Sign In'), ['class' => 'user-btn user-btn-primary user-btn-lg']) ?>
                </div>

                <?php $formClass::end(); ?>

                <hr>

                <div class="user-form-links">
                    <?php if ($module->enablePasswordRecovery): ?>
                        <p>
                            <?= Html::a(Yii::t('user', 'Forgot password?'), ['/user/recovery/request']) ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($module->enableRegistration): ?>
                        <p>
                            <?= Yii::t('user', "Don't have an account?") ?>
                            <?= Html::a(Yii::t('user', 'Sign up'), ['/user/registration/register']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
