<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\UserSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var cgsmith\user\Module $module
 */

use cgsmith\user\models\User;
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = Yii::t('user', 'Manage Users');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('/_alert', ['module' => Yii::$app->getModule('user')]) ?>

<?= $this->render('_menu') ?>

<div class="user-admin-index">
    <div class="user-admin-header">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a(Yii::t('user', 'Create User'), ['create'], ['class' => 'user-btn user-btn-primary']) ?>
    </div>

    <div class="user-card">
        <div class="user-card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'user-table'],
                'columns' => [
                    'id',
                    'email:email',
                    'username',
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'filter' => [
                            User::STATUS_PENDING => Yii::t('user', 'Pending'),
                            User::STATUS_ACTIVE => Yii::t('user', 'Active'),
                            User::STATUS_BLOCKED => Yii::t('user', 'Blocked'),
                        ],
                        'value' => function (User $model) {
                            $classes = [
                                User::STATUS_PENDING => 'user-badge user-badge-warning',
                                User::STATUS_ACTIVE => 'user-badge user-badge-success',
                                User::STATUS_BLOCKED => 'user-badge user-badge-danger',
                            ];
                            $class = $classes[$model->status] ?? 'user-badge';
                            return Html::tag('span', Html::encode($model->status), ['class' => $class]);
                        },
                    ],
                    [
                        'attribute' => 'email_confirmed_at',
                        'format' => 'raw',
                        'value' => function (User $model) {
                            if ($model->getIsConfirmed()) {
                                return '<span class="user-badge user-badge-success">' . Yii::t('user', 'Confirmed') . '</span>';
                            }
                            return '<span class="user-badge user-badge-warning">' . Yii::t('user', 'Unconfirmed') . '</span>';
                        },
                    ],
                    'created_at:datetime',
                    'last_login_at:datetime',
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{update} {block} {confirm} {impersonate} {delete}',
                        'buttons' => [
                            'block' => function ($url, User $model) use ($module) {
                                if ($model->id === Yii::$app->user->id) {
                                    return '';
                                }
                                if ($model->getIsBlocked()) {
                                    return Html::a(Yii::t('user', 'Unblock'), ['unblock', 'id' => $model->id], [
                                        'class' => 'user-btn user-btn-sm user-btn-success',
                                        'title' => Yii::t('user', 'Unblock'),
                                        'data' => ['method' => 'post', 'confirm' => Yii::t('user', 'Are you sure you want to unblock this user?')],
                                    ]);
                                }
                                return Html::a(Yii::t('user', 'Block'), ['block', 'id' => $model->id], [
                                    'class' => 'user-btn user-btn-sm user-btn-warning',
                                    'title' => Yii::t('user', 'Block'),
                                    'data' => ['method' => 'post', 'confirm' => Yii::t('user', 'Are you sure you want to block this user?')],
                                ]);
                            },
                            'confirm' => function ($url, User $model) {
                                if ($model->getIsConfirmed()) {
                                    return '';
                                }
                                return Html::a(Yii::t('user', 'Confirm'), ['confirm', 'id' => $model->id], [
                                    'class' => 'user-btn user-btn-sm user-btn-info',
                                    'title' => Yii::t('user', 'Confirm'),
                                    'data' => ['method' => 'post'],
                                ]);
                            },
                            'impersonate' => function ($url, User $model) use ($module) {
                                if (!$module->enableImpersonation || $model->id === Yii::$app->user->id) {
                                    return '';
                                }
                                return Html::a(Yii::t('user', 'Impersonate'), ['impersonate', 'id' => $model->id], [
                                    'class' => 'user-btn user-btn-sm user-btn-secondary',
                                    'title' => Yii::t('user', 'Impersonate'),
                                ]);
                            },
                            'update' => function ($url, User $model) {
                                return Html::a(Yii::t('user', 'Update'), ['update', 'id' => $model->id], [
                                    'class' => 'user-btn user-btn-sm user-btn-primary',
                                    'title' => Yii::t('user', 'Update'),
                                ]);
                            },
                            'delete' => function ($url, User $model) {
                                if ($model->id === Yii::$app->user->id) {
                                    return '';
                                }
                                return Html::a(Yii::t('user', 'Delete'), ['delete', 'id' => $model->id], [
                                    'class' => 'user-btn user-btn-sm user-btn-danger',
                                    'title' => Yii::t('user', 'Delete'),
                                    'data' => ['method' => 'post', 'confirm' => Yii::t('user', 'Are you sure you want to delete this user?')],
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
