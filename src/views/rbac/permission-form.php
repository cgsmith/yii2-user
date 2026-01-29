<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\PermissionForm $model
 * @var cgsmith\user\Module $module
 * @var bool $isNew
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = $isNew ? Yii::t('user', 'Create Permission') : Yii::t('user', 'Update Permission');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'RBAC Management'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Permissions'), 'url' => ['permissions']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="rbac-permission-form">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card">
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'name')->textInput(['maxlength' => 64]) ?>

            <p class="text-muted small">
                <?= Yii::t('user', 'Permission names can contain letters, numbers, underscores, hyphens, and dots.') ?>
            </p>

            <?= $form->field($model, 'description')->textarea(['rows' => 3, 'maxlength' => 255]) ?>

            <div class="form-group">
                <?= Html::submitButton(
                    $isNew ? Yii::t('user', 'Create') : Yii::t('user', 'Update'),
                    ['class' => $isNew ? 'btn btn-success' : 'btn btn-primary']
                ) ?>
                <?= Html::a(Yii::t('user', 'Cancel'), ['permissions'], ['class' => 'btn btn-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
