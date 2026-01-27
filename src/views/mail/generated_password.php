<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\User $user
 * @var string $password
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

?>
<h2 style="color: #333; margin-top: 0;"><?= Yii::t('user', 'Your New Password') ?></h2>

<p><?= Yii::t('user', 'A new password has been generated for your account on {app}.', ['app' => Html::encode(Yii::$app->name)]) ?></p>

<p style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 16px;">
    <?= Yii::t('user', 'Your new password is:') ?> <strong><?= Html::encode($password) ?></strong>
</p>

<p style="color: #6c757d; font-size: 14px;">
    <?= Yii::t('user', 'We recommend changing your password after logging in.') ?>
</p>

<p style="color: #6c757d; font-size: 14px;">
    <?= Yii::t('user', 'If you did not request a new password, please contact the administrator immediately.') ?>
</p>
