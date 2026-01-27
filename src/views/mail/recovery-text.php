<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\User $user
 * @var cgsmith\user\models\Token $token
 * @var string $url
 * @var cgsmith\user\Module $module
 */

?>
<?= Yii::t('user', 'Reset Your Password') ?>

<?= Yii::t('user', 'We received a request to reset your password. Click the link below to create a new password:') ?>

<?= $url ?>

<?= Yii::t('user', 'This link will expire in {hours} hours.', ['hours' => round($module->recoverWithin / 3600)]) ?>

<?= Yii::t('user', 'If you did not request a password reset, please ignore this email. Your password will not be changed.') ?>
