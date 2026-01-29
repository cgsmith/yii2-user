<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\RoleForm $model
 * @var yii\rbac\Permission[] $permissions
 * @var yii\rbac\Role[] $roles
 * @var cgsmith\user\Module $module
 * @var bool $isNew
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = $isNew ? Yii::t('user', 'Create Role') : Yii::t('user', 'Update Role');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'RBAC Management'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Roles'), 'url' => ['roles']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="rbac-role-form">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card">
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'name')->textInput(['maxlength' => 64, 'readonly' => !$isNew]) ?>

            <?= $form->field($model, 'description')->textarea(['rows' => 3, 'maxlength' => 255]) ?>

            <?php if (!empty($permissions)): ?>
                <div class="mb-3">
                    <label class="form-label"><?= Yii::t('user', 'Permissions') ?></label>
                    <div class="row">
                        <?php foreach ($permissions as $permission): ?>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <?= Html::checkbox(
                                        'RoleForm[permissions][]',
                                        in_array($permission->name, $model->permissions),
                                        [
                                            'value' => $permission->name,
                                            'id' => 'permission-' . $permission->name,
                                            'class' => 'form-check-input',
                                        ]
                                    ) ?>
                                    <label class="form-check-label" for="permission-<?= $permission->name ?>">
                                        <?= Html::encode($permission->name) ?>
                                        <?php if ($permission->description): ?>
                                            <br><small class="text-muted"><?= Html::encode($permission->description) ?></small>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($roles)): ?>
                <div class="mb-3">
                    <label class="form-label"><?= Yii::t('user', 'Child Roles') ?></label>
                    <p class="text-muted small"><?= Yii::t('user', 'This role will inherit all permissions from selected child roles.') ?></p>
                    <div class="row">
                        <?php foreach ($roles as $role): ?>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <?= Html::checkbox(
                                        'RoleForm[childRoles][]',
                                        in_array($role->name, $model->childRoles),
                                        [
                                            'value' => $role->name,
                                            'id' => 'child-role-' . $role->name,
                                            'class' => 'form-check-input',
                                        ]
                                    ) ?>
                                    <label class="form-check-label" for="child-role-<?= $role->name ?>">
                                        <?= Html::encode($role->name) ?>
                                        <?php if ($role->description): ?>
                                            <br><small class="text-muted"><?= Html::encode($role->description) ?></small>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <?= Html::submitButton(
                    $isNew ? Yii::t('user', 'Create') : Yii::t('user', 'Update'),
                    ['class' => $isNew ? 'btn btn-success' : 'btn btn-primary']
                ) ?>
                <?= Html::a(Yii::t('user', 'Cancel'), ['roles'], ['class' => 'btn btn-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
