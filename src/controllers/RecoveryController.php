<?php

declare(strict_types=1);

namespace cgsmith\user\controllers;

use cgsmith\user\models\RecoveryForm;
use cgsmith\user\models\RecoveryResetForm;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use cgsmith\user\services\RecoveryService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Password recovery controller.
 */
class RecoveryController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['request', 'reset'], 'roles' => ['?']],
                ],
            ],
        ];
    }

    /**
     * Request password recovery.
     */
    public function actionRequest(): Response|string
    {
        /** @var Module $module */
        $module = $this->module;

        if (!$module->enablePasswordRecovery) {
            throw new NotFoundHttpException(Yii::t('user', 'Password recovery is disabled.'));
        }

        /** @var RecoveryForm $model */
        $model = $module->createModel('RecoveryForm');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            /** @var RecoveryService $service */
            $service = Yii::$container->get(RecoveryService::class);
            $service->sendRecoveryMessage($model);

            Yii::$app->session->setFlash('success', Yii::t('user', 'If the email exists, we have sent password recovery instructions.'));

            return $this->redirect(['/user/login']);
        }

        return $this->render('request', [
            'model' => $model,
            'module' => $module,
        ]);
    }

    /**
     * Reset password with token.
     */
    public function actionReset(int $id, string $token): Response|string
    {
        /** @var Module $module */
        $module = $this->module;

        if (!$module->enablePasswordRecovery) {
            throw new NotFoundHttpException(Yii::t('user', 'Password recovery is disabled.'));
        }

        $user = User::findOne($id);

        if ($user === null) {
            throw new NotFoundHttpException(Yii::t('user', 'User not found.'));
        }

        /** @var RecoveryService $service */
        $service = Yii::$container->get(RecoveryService::class);

        if (!$service->validateToken($user, $token)) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'The recovery link is invalid or has expired.'));
            return $this->redirect(['/user/recovery/request']);
        }

        /** @var RecoveryResetForm $model */
        $model = $module->createModel('RecoveryResetForm');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($service->resetPassword($user, $token, $model->password)) {
                Yii::$app->session->setFlash('success', Yii::t('user', 'Your password has been reset. You can now sign in.'));
                return $this->redirect(['/user/login']);
            }

            Yii::$app->session->setFlash('danger', Yii::t('user', 'An error occurred while resetting your password.'));
        }

        return $this->render('reset', [
            'model' => $model,
            'module' => $module,
        ]);
    }
}
