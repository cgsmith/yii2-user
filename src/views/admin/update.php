<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\User $user
 * @var string $content
 */

$this->title = Yii::t('user', 'Update user account');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('/_alert', ['module' => Yii::$app->getModule('user')]) ?>

<?= $this->render('_menu') ?>

<div class="user-admin-update">
    <div class="user-admin-layout">
        <div class="user-admin-sidebar">
            <nav class="user-admin-user-nav">
                <?= Html::a(Yii::t('user', 'Account details'), ['/user/admin/update', 'id' => $user->id], ['class' => 'user-nav-item']) ?>
                <?= Html::a(Yii::t('user', 'Profile details'), ['/user/admin/update-profile', 'id' => $user->id], ['class' => 'user-nav-item']) ?>
                <?= Html::a(Yii::t('user', 'Information'), ['/user/admin/info', 'id' => $user->id], ['class' => 'user-nav-item']) ?>
                <hr>
                <?php if (!$user->isConfirmed): ?>
                    <?= Html::a(Yii::t('user', 'Confirm'), ['/user/admin/confirm', 'id' => $user->id], [
                        'class' => 'user-nav-item user-nav-item-success',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to confirm this user?'),
                    ]) ?>
                <?php endif; ?>
                <?php if (!$user->isBlocked): ?>
                    <?= Html::a(Yii::t('user', 'Block'), ['/user/admin/block', 'id' => $user->id], [
                        'class' => 'user-nav-item user-nav-item-danger',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to block this user?'),
                    ]) ?>
                <?php else: ?>
                    <?= Html::a(Yii::t('user', 'Unblock'), ['/user/admin/unblock', 'id' => $user->id], [
                        'class' => 'user-nav-item user-nav-item-success',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to unblock this user?'),
                    ]) ?>
                <?php endif; ?>
                <?= Html::a(Yii::t('user', 'Delete'), ['/user/admin/delete', 'id' => $user->id], [
                    'class' => 'user-nav-item user-nav-item-danger',
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('user', 'Are you sure you want to delete this user?'),
                ]) ?>
            </nav>
        </div>
        <div class="user-admin-content">
            <div class="user-card">
                <div class="user-card-body">
                    <?= $content ?>
                </div>
            </div>
        </div>
    </div>
</div>
