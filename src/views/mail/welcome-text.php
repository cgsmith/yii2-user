<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\User $user
 * @var cgsmith\user\models\Token|null $token
 * @var string|null $url
 * @var cgsmith\user\Module $module
 */

?>
<?= Yii::t('user', 'Welcome to {app}!', ['app' => Yii::$app->name]) ?>

<?= Yii::t('user', 'Thank you for registering.') ?>

<?php if ($url !== null): ?>
<?= Yii::t('user', 'Please click the link below to confirm your email address:') ?>

<?= $url ?>

<?= Yii::t('user', 'This link will expire in {hours} hours.', ['hours' => round($module->confirmWithin / 3600)]) ?>

<?php else: ?>
<?= Yii::t('user', 'You can now sign in to your account.') ?>

<?php endif; ?>
<?= Yii::t('user', 'If you did not create an account, please ignore this email.') ?>
