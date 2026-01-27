<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 */
?>

<nav class="user-admin-nav">
    <?= Html::a(Yii::t('user', 'Users'), ['/user/admin/index'], ['class' => 'user-admin-nav-item']) ?>
    <?= Html::a(Yii::t('user', 'Create User'), ['/user/admin/create'], ['class' => 'user-admin-nav-item']) ?>
</nav>
