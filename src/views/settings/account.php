<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\SettingsForm $model
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$this->title = Yii::t('user', 'Account Settings');
$this->params['breadcrumbs'][] = $this->title;

$formClass = $module->activeFormClass;
$formConfig = array_merge([
    'id' => 'account-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
], $module->formFieldConfig ? ['fieldConfig' => $module->formFieldConfig] : []);
?>

<div class="user-settings-account">
    <div class="user-settings-layout">
        <div class="user-settings-sidebar">
            <?= $this->render('_menu') ?>
        </div>
        <div class="user-settings-content">
            <div class="user-card">
                <div class="user-card-header">
                    <h2 class="user-card-title"><?= Html::encode($this->title) ?></h2>
                </div>
                <div class="user-card-body">
                    <?php $form = $formClass::begin($formConfig); ?>

                    <?= $form->field($model, 'email') ?>

                    <?= $form->field($model, 'username') ?>

                    <hr>

                    <h3 class="user-section-title"><?= Yii::t('user', 'Change Password') ?></h3>

                    <?= $form->field($model, 'new_password')->passwordInput() ?>

                    <?= $form->field($model, 'new_password_confirm')->passwordInput() ?>

                    <hr>

                    <?= $form->field($model, 'current_password')
                        ->passwordInput()
                        ->hint(Yii::t('user', 'Required to change email or password.')) ?>

                    <div class="user-form-actions">
                        <?= Html::submitButton(Yii::t('user', 'Save Changes'), ['class' => 'user-btn user-btn-primary']) ?>
                    </div>

                    <?php $formClass::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
