<?php

/**
 * @var yii\web\View $this
 * @var yii\base\Model $model
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$this->title = Yii::t('user', 'Resend Confirmation');
$this->params['breadcrumbs'][] = $this->title;

$formClass = $module->activeFormClass;
$formConfig = array_merge([
    'id' => 'resend-form',
], $module->formFieldConfig ? ['fieldConfig' => $module->formFieldConfig] : []);
?>

<div class="user-resend">
    <div class="user-form-wrapper">
        <div class="user-card">
            <div class="user-card-body">
                <h1 class="user-form-title"><?= Html::encode($this->title) ?></h1>

                <p class="user-form-description">
                    <?= Yii::t('user', 'Enter your email address and we will send you a new confirmation link.') ?>
                </p>

                <?php $form = $formClass::begin($formConfig); ?>

                <?= $form->field($model, 'email')
                    ->textInput(['autofocus' => true, 'placeholder' => Yii::t('user', 'Email')]) ?>

                <div class="user-form-actions">
                    <?= Html::submitButton(Yii::t('user', 'Resend'), ['class' => 'user-btn user-btn-primary user-btn-lg']) ?>
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
