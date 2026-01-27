<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\User $user
 */
?>

<?php $this->beginContent('@cgsmith/user/views/admin/update.php', ['user' => $user]) ?>

<table class="table">
    <tr>
        <td><strong><?= Yii::t('user', 'Registration time') ?>:</strong></td>
        <td><?= Yii::t('user', '{0, date, MMMM dd, YYYY HH:mm}', [strtotime($user->created_at)]) ?></td>
    </tr>
    <?php if ($user->registration_ip !== null): ?>
        <tr>
            <td><strong><?= Yii::t('user', 'Registration IP') ?>:</strong></td>
            <td><?= $user->registration_ip ?></td>
        </tr>
    <?php endif ?>
    <tr>
        <td><strong><?= Yii::t('user', 'Confirmation status') ?>:</strong></td>
        <?php if ($user->isConfirmed): ?>
            <td class="text-success"><?= Yii::t('user', 'Confirmed at {0, date, MMMM dd, YYYY HH:mm}', [strtotime($user->email_confirmed_at)]) ?></td>
        <?php else: ?>
            <td class="text-danger"><?= Yii::t('user', 'Unconfirmed') ?></td>
        <?php endif ?>
    </tr>
    <tr>
        <td><strong><?= Yii::t('user', 'Block status') ?>:</strong></td>
        <?php if ($user->isBlocked): ?>
            <td class="text-danger"><?= Yii::t('user', 'Blocked at {0, date, MMMM dd, YYYY HH:mm}', [strtotime($user->blocked_at)]) ?></td>
        <?php else: ?>
            <td class="text-success"><?= Yii::t('user', 'Not blocked') ?></td>
        <?php endif ?>
    </tr>
    <?php if ($user->last_login_at !== null): ?>
        <tr>
            <td><strong><?= Yii::t('user', 'Last login') ?>:</strong></td>
            <td><?= Yii::t('user', '{0, date, MMMM dd, YYYY HH:mm}', [strtotime($user->last_login_at)]) ?></td>
        </tr>
    <?php endif ?>
    <?php if ($user->last_login_ip !== null): ?>
        <tr>
            <td><strong><?= Yii::t('user', 'Last login IP') ?>:</strong></td>
            <td><?= $user->last_login_ip ?></td>
        </tr>
    <?php endif ?>
</table>

<?php $this->endContent() ?>
