<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\User $user
 * @var string $password
 * @var cgsmith\user\Module $module
 */

?>
<?= Yii::t('user', 'Your New Password') ?>

<?= Yii::t('user', 'A new password has been generated for your account on {app}.', ['app' => Yii::$app->name]) ?>

<?= Yii::t('user', 'Your new password is:') ?> <?= $password ?>

<?= Yii::t('user', 'We recommend changing your password after logging in.') ?>

<?= Yii::t('user', 'If you did not request a new password, please contact the administrator immediately.') ?>
