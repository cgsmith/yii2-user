<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\User $user
 */
?>

<?php $this->beginContent('@cgsmith/user/views/admin/update.php', ['user' => $user]) ?>

<div class="user-alert user-alert-info">
    <?= Yii::t('user', 'You can assign multiple roles or permissions to user by using the form below') ?>
</div>

<?php if (Yii::$app->authManager !== null): ?>
    <p><?= Yii::t('user', 'RBAC assignment widget would go here. Configure your RBAC module to enable this feature.') ?></p>
<?php else: ?>
    <p class="text-muted"><?= Yii::t('user', 'RBAC is not configured. Configure authManager in your application to use this feature.') ?></p>
<?php endif ?>

<?php $this->endContent() ?>
