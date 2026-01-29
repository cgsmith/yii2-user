<?php

/**
 * @var yii\web\View $this
 * @var yii\rbac\Role[] $roles
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$this->title = Yii::t('user', 'Roles');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'RBAC Management'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="rbac-roles">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('user', 'Create Role'), ['create-role'], ['class' => 'btn btn-success']) ?>
    </p>

    <div class="card">
        <div class="card-body">
            <?php if (empty($roles)): ?>
                <p class="text-muted"><?= Yii::t('user', 'No roles have been created yet.') ?></p>
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
                        <?php foreach ($roles as $role): ?>
                            <tr>
                                <td><strong><?= Html::encode($role->name) ?></strong></td>
                                <td><?= Html::encode($role->description) ?></td>
                                <td>
                                    <?php if ($role->createdAt): ?>
                                        <?= Yii::$app->formatter->asDatetime($role->createdAt) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?= Html::a(Yii::t('user', 'Edit'), ['update-role', 'name' => $role->name], ['class' => 'btn btn-sm btn-primary']) ?>
                                    <?= Html::a(Yii::t('user', 'Delete'), ['delete-role', 'name' => $role->name], [
                                        'class' => 'btn btn-sm btn-danger',
                                        'data' => [
                                            'confirm' => Yii::t('user', 'Are you sure you want to delete this role?'),
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
