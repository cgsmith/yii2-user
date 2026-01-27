<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\User $user
 * @var cgsmith\user\models\Token|null $token
 * @var string|null $url
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

?>
<h2 style="color: #333; margin-top: 0;"><?= Yii::t('user', 'Welcome to {app}!', ['app' => Html::encode(Yii::$app->name)]) ?></h2>

<p><?= Yii::t('user', 'Thank you for registering.') ?></p>

<?php if ($url !== null): ?>
    <p><?= Yii::t('user', 'Please click the button below to confirm your email address:') ?></p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="<?= Html::encode($url) ?>"
           style="display: inline-block; padding: 12px 30px; background-color: #0d6efd; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold;">
            <?= Yii::t('user', 'Confirm Email') ?>
        </a>
    </p>

    <p style="color: #6c757d; font-size: 14px;">
        <?= Yii::t('user', 'If the button above does not work, copy and paste this URL into your browser:') ?>
        <br>
        <a href="<?= Html::encode($url) ?>" style="color: #0d6efd; word-break: break-all;"><?= Html::encode($url) ?></a>
    </p>

    <p style="color: #6c757d; font-size: 14px;">
        <?= Yii::t('user', 'This link will expire in {hours} hours.', ['hours' => round($module->confirmWithin / 3600)]) ?>
    </p>
<?php else: ?>
    <p><?= Yii::t('user', 'You can now sign in to your account.') ?></p>
<?php endif; ?>

<p style="color: #6c757d; font-size: 14px;">
    <?= Yii::t('user', 'If you did not create an account, please ignore this email.') ?>
</p>
