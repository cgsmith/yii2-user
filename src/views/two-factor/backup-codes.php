<?php

/**
 * @var yii\web\View $this
 * @var array|null $backupCodes
 * @var int $backupCodesCount
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$this->title = Yii::t('user', 'Backup Codes');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Settings'), 'url' => ['settings/account']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Two-Factor Authentication'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-two-factor-backup-codes">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($backupCodes): ?>
        <div class="alert alert-warning">
            <strong><?= Yii::t('user', 'Save these backup codes!') ?></strong>
            <p><?= Yii::t('user', 'Store these codes in a safe place. You can use them to sign in if you lose access to your authenticator app. Each code can only be used once.') ?></p>
        </div>

        <div class="backup-codes-list card">
            <div class="card-body">
                <div class="row">
                    <?php foreach ($backupCodes as $code): ?>
                        <div class="col-6 col-md-4 mb-2">
                            <code class="d-block text-center py-2 bg-light"><?= Html::encode($code) ?></code>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <p class="text-muted mt-3">
            <?= Yii::t('user', 'These codes will not be shown again. Make sure to save them now.') ?>
        </p>
    <?php else: ?>
        <p><?= Yii::t('user', 'You have {count} backup codes remaining.', ['count' => $backupCodesCount]) ?></p>

        <?php if ($backupCodesCount < 3): ?>
            <div class="alert alert-warning">
                <?= Yii::t('user', 'You are running low on backup codes. Consider regenerating them.') ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <hr>

    <h3><?= Yii::t('user', 'Regenerate Backup Codes') ?></h3>
    <p class="text-muted"><?= Yii::t('user', 'Regenerating will invalidate all existing backup codes.') ?></p>

    <?= Html::beginForm(['regenerate-backup-codes'], 'post') ?>
    <?= Html::submitButton(
        Yii::t('user', 'Regenerate Backup Codes'),
        [
            'class' => 'btn btn-warning',
            'data-confirm' => Yii::t('user', 'Are you sure? This will invalidate all existing backup codes.'),
        ]
    ) ?>
    <?= Html::endForm() ?>

    <div class="mt-3">
        <?= Html::a(Yii::t('user', 'Back to Two-Factor Settings'), ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>
