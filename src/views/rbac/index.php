<?php

/**
 * @var yii\web\View $this
 * @var yii\rbac\Role[] $roles
 * @var yii\rbac\Permission[] $permissions
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$this->title = Yii::t('user', 'RBAC Management');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="rbac-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('user', 'Roles') ?></h3>
                    <div class="card-tools">
                        <?= Html::a(Yii::t('user', 'Manage Roles'), ['roles'], ['class' => 'btn btn-sm btn-primary']) ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($roles)): ?>
                        <p class="text-muted"><?= Yii::t('user', 'No roles have been created yet.') ?></p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($roles as $role): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= Html::encode($role->name) ?></strong>
                                        <?php if ($role->description): ?>
                                            <br><small class="text-muted"><?= Html::encode($role->description) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <?= Html::a(Yii::t('user', 'Edit'), ['update-role', 'name' => $role->name], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('user', 'Permissions') ?></h3>
                    <div class="card-tools">
                        <?= Html::a(Yii::t('user', 'Manage Permissions'), ['permissions'], ['class' => 'btn btn-sm btn-primary']) ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($permissions)): ?>
                        <p class="text-muted"><?= Yii::t('user', 'No permissions have been created yet.') ?></p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($permissions as $permission): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= Html::encode($permission->name) ?></strong>
                                        <?php if ($permission->description): ?>
                                            <br><small class="text-muted"><?= Html::encode($permission->description) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <?= Html::a(Yii::t('user', 'Edit'), ['update-permission', 'name' => $permission->name], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
