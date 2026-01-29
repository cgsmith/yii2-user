<?php

/**
 * @var yii\web\View $this
 * @var cgsmith\user\models\User $user
 * @var cgsmith\user\models\AssignmentForm $model
 * @var yii\rbac\Role[] $roles
 * @var cgsmith\user\Module $module
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<?php $this->beginContent('@cgsmith/user/views/admin/update.php', ['user' => $user]) ?>

<?php if (Yii::$app->authManager === null): ?>
    <div class="alert alert-warning">
        <?= Yii::t('user', 'RBAC is not configured. Configure authManager in your application to use this feature.') ?>
    </div>
<?php elseif (empty($roles)): ?>
    <div class="alert alert-info">
        <?= Yii::t('user', 'No roles have been created yet.') ?>
        <?php if ($module->enableRbacManagement): ?>
            <?= Html::a(Yii::t('user', 'Create roles'), ['/' . $module->urlPrefix . '/rbac/roles'], ['class' => 'alert-link']) ?>
        <?php endif; ?>
    </div>
<?php else: ?>

    <?php $form = ActiveForm::begin([
        'action' => ['update-assignments', 'id' => $user->id],
    ]); ?>

    <div class="mb-3">
        <label class="form-label"><strong><?= Yii::t('user', 'Assigned Roles') ?></strong></label>
        <p class="text-muted small"><?= Yii::t('user', 'Select the roles you want to assign to this user.') ?></p>
        <div class="row">
            <?php foreach ($roles as $role): ?>
                <div class="col-md-4 mb-2">
                    <div class="form-check">
                        <?= Html::checkbox(
                            'AssignmentForm[roles][]',
                            in_array($role->name, $model->roles),
                            [
                                'value' => $role->name,
                                'id' => 'role-' . $role->name,
                                'class' => 'form-check-input',
                            ]
                        ) ?>
                        <label class="form-check-label" for="role-<?= Html::encode($role->name) ?>">
                            <strong><?= Html::encode($role->name) ?></strong>
                            <?php if ($role->description): ?>
                                <br><small class="text-muted"><?= Html::encode($role->description) ?></small>
                            <?php endif; ?>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('user', 'Update Assignments'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

<?php endif ?>

<?php $this->endContent() ?>
