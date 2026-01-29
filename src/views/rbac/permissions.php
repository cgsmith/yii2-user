<?php

/**
 * @var yii\web\View $this
 * @var yii\rbac\Permission[] $permissions
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$this->title = Yii::t('user', 'Permissions');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'RBAC Management'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="rbac-permissions">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('user', 'Create Permission'), ['create-permission'], ['class' => 'btn btn-success']) ?>
    </p>

    <div class="card">
        <div class="card-body">
            <?php if (empty($permissions)): ?>
                <p class="text-muted"><?= Yii::t('user', 'No permissions have been created yet.') ?></p>
            <?php else: ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?= Yii::t('user', 'Name') ?></th>
                            <th><?= Yii::t('user', 'Description') ?></th>
                            <th><?= Yii::t('user', 'Created At') ?></th>
                            <th class="text-end"><?= Yii::t('user', 'Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($permissions as $permission): ?>
                            <tr>
                                <td><strong><?= Html::encode($permission->name) ?></strong></td>
                                <td><?= Html::encode($permission->description) ?></td>
                                <td>
                                    <?php if ($permission->createdAt): ?>
                                        <?= Yii::$app->formatter->asDatetime($permission->createdAt) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?= Html::a(Yii::t('user', 'Edit'), ['update-permission', 'name' => $permission->name], ['class' => 'btn btn-sm btn-primary']) ?>
                                    <?= Html::a(Yii::t('user', 'Delete'), ['delete-permission', 'name' => $permission->name], [
                                        'class' => 'btn btn-sm btn-danger',
                                        'data' => [
                                            'confirm' => Yii::t('user', 'Are you sure you want to delete this permission?'),
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
