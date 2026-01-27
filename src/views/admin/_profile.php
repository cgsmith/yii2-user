<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\User $user
 * @var cgsmith\user\models\Profile $profile
 * @var cgsmith\user\Module $module
 */
?>

<?php $this->beginContent('@cgsmith/user/views/admin/update.php', ['user' => $user]) ?>

<?php 
$formClass = $module->activeFormClass;
$form = $formClass::begin([
    'id' => 'profile-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
] + ($module->formFieldConfig ? ['fieldConfig' => $module->formFieldConfig] : []));
?>

<?= $form->field($profile, 'name') ?>
<?= $form->field($profile, 'public_email') ?>
<?= $form->field($profile, 'website') ?>
<?= $form->field($profile, 'location') ?>
<?= $form->field($profile, 'bio')->textarea() ?>

<div class="user-form-actions">
    <?= Html::submitButton(Yii::t('user', 'Update'), ['class' => 'user-btn user-btn-primary']) ?>
</div>

<?php $form::end(); ?>

<?php $this->endContent() ?>
