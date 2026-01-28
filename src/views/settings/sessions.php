<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\Session[] $sessions
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$this->title = Yii::t('user', 'Active Sessions');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Settings'), 'url' => ['account']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-sessions">
    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted"><?= Yii::t('user', 'These are the devices that are currently logged in to your account.') ?></p>

    <?php if (count($sessions) > 1): ?>
    <p>
        <?= Html::a(
            Yii::t('user', 'Sign out all other sessions'),
            ['terminate-all-sessions'],
            [
                'class' => 'btn btn-outline-danger btn-sm',
                'data-method' => 'post',
                'data-confirm' => Yii::t('user', 'Are you sure you want to sign out all other sessions?'),
            ]
        ) ?>
    </p>
    <?php endif; ?>

    <div class="session-list">
        <?php foreach ($sessions as $session): ?>
        <div class="card mb-3 <?= $session->isCurrent ? 'border-primary' : '' ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="card-title mb-1">
                            <?= Html::encode($session->device_name) ?>
                            <?php if ($session->isCurrent): ?>
                            <span class="badge bg-primary"><?= Yii::t('user', 'Current') ?></span>
                            <?php endif; ?>
                        </h5>
                        <p class="card-text text-muted mb-1">
                            <small>
                                <?= Yii::t('user', 'IP: {ip}', ['ip' => Html::encode($session->ip ?? 'Unknown')]) ?>
                            </small>
                        </p>
                        <p class="card-text text-muted mb-0">
                            <small>
                                <?= Yii::t('user', 'Last active: {time}', [
                                    'time' => Yii::$app->formatter->asRelativeTime($session->last_activity_at)
                                ]) ?>
                                &middot;
                                <?= Yii::t('user', 'Started: {time}', [
                                    'time' => Yii::$app->formatter->asRelativeTime($session->created_at)
                                ]) ?>
                            </small>
                        </p>
                    </div>
                    <?php if (!$session->isCurrent): ?>
                    <div>
                        <?= Html::a(
                            Yii::t('user', 'Sign out'),
                            ['terminate-session', 'id' => $session->id],
                            [
                                'class' => 'btn btn-outline-secondary btn-sm',
                                'data-method' => 'post',
                                'data-confirm' => Yii::t('user', 'Are you sure you want to sign out this session?'),
                            ]
                        ) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($sessions)): ?>
    <div class="alert alert-info">
        <?= Yii::t('user', 'No active sessions found.') ?>
    </div>
    <?php endif; ?>
</div>
