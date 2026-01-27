<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\User $model
 * @var cgsmith\user\Module $module
 */

use cgsmith\user\models\User;
use yii\helpers\Html;

$formClass = $module->activeFormClass;
$formConfig = array_merge([
    'id' => 'user-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
], $module->formFieldConfig ? ['fieldConfig' => $module->formFieldConfig] : []);
?>

<div class="user-admin-form">
    <div class="user-card">
        <div class="user-card-body">
            <?php $form = $formClass::begin($formConfig); ?>

            <div class="user-form-row">
                <div class="user-form-col">
                    <?= $form->field($model, 'email') ?>
                </div>
                <div class="user-form-col">
                    <?= $form->field($model, 'username') ?>
                </div>
            </div>

            <?= $form->field($model, 'password')
                ->passwordInput()
                ->hint($model->isNewRecord ? '' : Yii::t('user', 'Leave empty to keep current password.')) ?>

            <?php if (!$model->isNewRecord): ?>
                <?= $form->field($model, 'status')->dropDownList([
                    User::STATUS_PENDING => Yii::t('user', 'Pending'),
                    User::STATUS_ACTIVE => Yii::t('user', 'Active'),
                    User::STATUS_BLOCKED => Yii::t('user', 'Blocked'),
                ]) ?>
            <?php endif; ?>

            <div class="user-form-actions">
                <?= Html::submitButton(
                    $model->isNewRecord ? Yii::t('user', 'Create') : Yii::t('user', 'Update'),
                    ['class' => 'user-btn user-btn-primary']
                ) ?>
                <?= Html::a(Yii::t('user', 'Cancel'), ['index'], ['class' => 'user-btn user-btn-secondary']) ?>
            </div>

            <?php $formClass::end(); ?>
        </div>
    </div>
</div>
