<?php

use yii\helpers\Html;

/**
 * @var cgsmith\user\Module $module
 */
?>

<?php if ($module->enableFlashMessages): ?>
    <div class="user-alerts">
        <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
            <?php if (in_array($type, ['success', 'danger', 'warning', 'info'])): ?>
                <div class="user-alert user-alert-<?= $type ?>">
                    <?= Html::encode($message) ?>
                </div>
            <?php endif ?>
        <?php endforeach ?>
    </div>
<?php endif ?>
