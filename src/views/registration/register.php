<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\RegistrationForm $model
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$this->title = Yii::t('user', 'Sign Up');
$this->params['breadcrumbs'][] = $this->title;

$formClass = $module->activeFormClass;
$formConfig = array_merge([
    'id' => 'registration-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
], $module->formFieldConfig ? ['fieldConfig' => $module->formFieldConfig] : []);
?>

<div class="user-register">
    <div class="user-form-wrapper">
        <div class="user-card">
            <div class="user-card-body">
                <h1 class="user-form-title"><?= Html::encode($this->title) ?></h1>

                <?php $form = $formClass::begin($formConfig); ?>

                <?= $form->field($model, 'email')
                    ->textInput(['autofocus' => true, 'placeholder' => Yii::t('user', 'Email')]) ?>

                <?= $form->field($model, 'username')
                    ->textInput(['placeholder' => Yii::t('user', 'Username (optional)')]) ?>

                <?php if (!$module->enableGeneratedPassword): ?>
                    <?= $form->field($model, 'password')
                        ->passwordInput(['placeholder' => Yii::t('user', 'Password')]) ?>
                <?php endif; ?>

                <div class="user-form-actions">
                    <?= Html::submitButton(Yii::t('user', 'Sign Up'), ['class' => 'user-btn user-btn-primary user-btn-lg']) ?>
                </div>

                <?php $formClass::end(); ?>

                <hr>

                <div class="user-form-links">
                    <p>
                        <?= Yii::t('user', 'Already have an account?') ?>
                        <?= Html::a(Yii::t('user', 'Sign in'), ['/user/security/login']) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
