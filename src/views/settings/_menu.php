<?php

/**
 * Settings menu.
 * @var yii\web\View $this
 */

use yii\helpers\Html;

$action = Yii::$app->controller->action->id;
?>

<nav class="user-settings-menu">
    <?= Html::a(
        Yii::t('user', 'Account'),
        ['/user/settings/account'],
        ['class' => 'user-menu-item' . ($action === 'account' ? ' user-menu-item-active' : '')]
    ) ?>
    <?= Html::a(
        Yii::t('user', 'Profile'),
        ['/user/settings/profile'],
        ['class' => 'user-menu-item' . ($action === 'profile' ? ' user-menu-item-active' : '')]
    ) ?>
    <?php if (Yii::$app->getModule('user')->enableGdpr): ?>
        <?= Html::a(
            Yii::t('user', 'Privacy & Data'),
            ['/user/gdpr'],
            ['class' => 'user-menu-item' . (Yii::$app->controller->id === 'gdpr' ? ' user-menu-item-active' : '')]
        ) ?>
    <?php endif; ?>
</nav>
