<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\Profile $model
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$this->title = Yii::t('user', 'Profile Settings');
$this->params['breadcrumbs'][] = $this->title;

$formClass = $module->activeFormClass;
$formConfig = array_merge([
    'id' => 'profile-form',
    'options' => ['enctype' => 'multipart/form-data'],
], $module->formFieldConfig ? ['fieldConfig' => $module->formFieldConfig] : []);
?>

<div class="user-settings-profile">
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

                    <div class="user-profile-avatar-section">
                        <div class="user-avatar-container">
                            <?php if ($model->getAvatarUrl()): ?>
                                <img src="<?= Html::encode($model->getAvatarUrl(150)) ?>"
                                     class="user-avatar"
                                     alt="Avatar"
                                     width="150"
                                     height="150">
                            <?php else: ?>
                                <div class="user-avatar-placeholder">
                                    <span><?= strtoupper(substr($model->user->email, 0, 1)) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($module->enableAvatarUpload): ?>
                                <?= $form->field($model, 'avatar_path')->fileInput(['accept' => 'image/*'])->label(Yii::t('user', 'Upload Avatar')) ?>

                                <?php if (!empty($model->avatar_path)): ?>
                                    <?= Html::a(Yii::t('user', 'Delete Avatar'), ['delete-avatar'], [
                                        'class' => 'user-btn user-btn-danger user-btn-sm',
                                        'data' => ['method' => 'post', 'confirm' => Yii::t('user', 'Are you sure you want to delete your avatar?')],
                                    ]) ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="user-profile-fields">
                            <?= $form->field($model, 'name') ?>

                            <?= $form->field($model, 'public_email') ?>

                            <?= $form->field($model, 'location') ?>

                            <?= $form->field($model, 'website') ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'bio')->textarea(['rows' => 4]) ?>

                    <?= $form->field($model, 'timezone')->dropDownList(
                        cgsmith\user\models\Profile::getTimezoneList(),
                        ['prompt' => Yii::t('user', 'Select timezone...')]
                    ) ?>

                    <?php if ($module->enableGravatar): ?>
                        <?= $form->field($model, 'use_gravatar')->checkbox() ?>
                        <?= $form->field($model, 'gravatar_email')
                            ->textInput()
                            ->hint(Yii::t('user', 'Leave empty to use your account email for Gravatar.')) ?>
                    <?php endif; ?>

                    <div class="user-form-actions">
                        <?= Html::submitButton(Yii::t('user', 'Save Changes'), ['class' => 'user-btn user-btn-primary']) ?>
                    </div>

                    <?php $formClass::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
