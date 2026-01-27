<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;

$this->title = Yii::t('user', 'Privacy & Data');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-gdpr-index">
    <div class="user-settings-layout">
        <div class="user-settings-sidebar">
            <?= $this->render('@cgsmith/user/views/settings/_menu') ?>
        </div>
        <div class="user-settings-content">
            <div class="user-card">
                <div class="user-card-header">
                    <h2 class="user-card-title"><?= Yii::t('user', 'Export Your Data') ?></h2>
                </div>
                <div class="user-card-body">
                    <p class="user-text-muted">
                        <?= Yii::t('user', 'Download a copy of your personal data in JSON format.') ?>
                    </p>
                    <?= Html::a(
                        Yii::t('user', 'Export Data'),
                        ['export'],
                        ['class' => 'user-btn user-btn-secondary']
                    ) ?>
                </div>
            </div>

            <div class="user-card user-card-danger">
                <div class="user-card-header user-card-header-danger">
                    <h2 class="user-card-title"><?= Yii::t('user', 'Delete Account') ?></h2>
                </div>
                <div class="user-card-body">
                    <div class="user-alert user-alert-warning">
                        <strong><?= Yii::t('user', 'Warning:') ?></strong>
                        <?= Yii::t('user', 'This action is permanent and cannot be undone. All your data will be deleted.') ?>
                    </div>
                    <?= Html::a(
                        Yii::t('user', 'Delete My Account'),
                        ['delete'],
                        ['class' => 'user-btn user-btn-danger']
                    ) ?>
                </div>
            </div>
        </div>
    </div>
</div>
