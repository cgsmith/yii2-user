<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\User $user
 * @var cgsmith\user\models\Token $token
 * @var string $url
 * @var cgsmith\user\Module $module
 */

?>
<?= Yii::t('user', 'Confirm Your Email') ?>

<?= Yii::t('user', 'Please click the link below to confirm your email address:') ?>

<?= $url ?>

<?= Yii::t('user', 'This link will expire in {hours} hours.', ['hours' => round($module->confirmWithin / 3600)]) ?>

<?= Yii::t('user', 'If you did not request this email, please ignore it.') ?>
