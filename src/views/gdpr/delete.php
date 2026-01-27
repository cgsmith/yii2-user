<?php

/**
 * @var yii\web\View $this
 * @var yii\base\Model $model
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$this->title = Yii::t('user', 'Delete Account');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Privacy & Data'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$formClass = $module->activeFormClass;
$formConfig = array_merge([
    'id' => 'delete-account-form',
], $module->formFieldConfig ? ['fieldConfig' => $module->formFieldConfig] : []);
?>

<div class="user-gdpr-delete">
    <div class="user-form-wrapper">
        <div class="user-card user-card-danger">
            <div class="user-card-header user-card-header-danger">
                <h2 class="user-card-title"><?= Html::encode($this->title) ?></h2>
            </div>
            <div class="user-card-body">
                <div class="user-alert user-alert-warning">
                    <h3><?= Yii::t('user', 'This action is irreversible!') ?></h3>
                    <p><?= Yii::t('user', 'Deleting your account will:') ?></p>
                    <ul>
                        <li><?= Yii::t('user', 'Remove all your personal information') ?></li>
                        <li><?= Yii::t('user', 'Delete your profile and settings') ?></li>
                        <li><?= Yii::t('user', 'Log you out immediately') ?></li>
                    </ul>
                </div>

                <?php $form = $formClass::begin($formConfig); ?>

                <?= $form->field($model, 'password')->passwordInput([
                    'placeholder' => Yii::t('user', 'Enter your current password'),
                ]) ?>

                <?= $form->field($model, 'confirm')->checkbox([
                    'label' => Yii::t('user', 'I understand this action cannot be undone'),
                ]) ?>

                <div class="user-form-actions">
                    <?= Html::submitButton(
                        Yii::t('user', 'Permanently Delete My Account'),
                        ['class' => 'user-btn user-btn-danger']
                    ) ?>
                    <?= Html::a(Yii::t('user', 'Cancel'), ['index'], ['class' => 'user-btn user-btn-secondary']) ?>
                </div>

                <?php $formClass::end(); ?>
            </div>
        </div>
    </div>
</div>
