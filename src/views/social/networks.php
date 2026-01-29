<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\SocialAccount[] $connectedAccounts
 * @var yii\authclient\ClientInterface[] $availableClients
 * @var string[] $connectedProviders
 * @var cgsmith\user\Module $module
 */

use yii\authclient\widgets\AuthChoice;
use yii\helpers\Html;

$this->title = Yii::t('user', 'Connected Networks');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Settings'), 'url' => ['settings/account']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-social-networks">
    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted"><?= Yii::t('user', 'Connect your social accounts to enable quick sign-in.') ?></p>

    <?php if (!empty($connectedAccounts)): ?>
    <h3><?= Yii::t('user', 'Connected Accounts') ?></h3>
    <div class="connected-accounts mb-4">
        <?php foreach ($connectedAccounts as $account): ?>
        <div class="card mb-2">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <strong><?= Html::encode(ucfirst($account->provider)) ?></strong>
                    <?php if ($account->username): ?>
                        <span class="text-muted">- <?= Html::encode($account->username) ?></span>
                    <?php elseif ($account->email): ?>
                        <span class="text-muted">- <?= Html::encode($account->email) ?></span>
                    <?php endif; ?>
                    <br>
                    <small class="text-muted"><?= Yii::t('user', 'Connected {date}', [
                        'date' => Yii::$app->formatter->asRelativeTime($account->created_at)
                    ]) ?></small>
                </div>
                <?= Html::a(
                    Yii::t('user', 'Disconnect'),
                    ['disconnect', 'id' => $account->id],
                    [
                        'class' => 'btn btn-outline-danger btn-sm',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to disconnect this account?'),
                    ]
                ) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($availableClients)): ?>
        <?php
        $unconnectedClients = array_filter($availableClients, fn($client) => !in_array($client->getId(), $connectedProviders));
        ?>
        <?php if (!empty($unconnectedClients)): ?>
        <h3><?= Yii::t('user', 'Connect a New Account') ?></h3>
        <div class="available-clients">
            <?= AuthChoice::widget([
                'baseAuthUrl' => ['/' . $module->urlPrefix . '/auth'],
                'popupMode' => false,
                'clients' => $unconnectedClients,
            ]) ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <?= Yii::t('user', 'All available social networks are already connected.') ?>
        </div>
        <?php endif; ?>
    <?php else: ?>
    <div class="alert alert-info">
        <?= Yii::t('user', 'No social networks are configured.') ?>
    </div>
    <?php endif; ?>
</div>
