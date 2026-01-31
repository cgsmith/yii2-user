<?php

declare(strict_types=1);

namespace cgsmith\user\controllers;

use cgsmith\user\models\RegistrationForm;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use cgsmith\user\services\RegistrationService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Registration controller.
 */
class RegistrationController extends Controller
{
    public const EVENT_BEFORE_REGISTER = 'beforeRegister';
    public const EVENT_AFTER_REGISTER = 'afterRegister';
    public const EVENT_BEFORE_CONFIRM = 'beforeConfirm';
    public const EVENT_AFTER_CONFIRM = 'afterConfirm';

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['register', 'confirm', 'resend'], 'roles' => ['?']],
                ],
            ],
        ];
    }

    /**
     * Display registration form.
     */
    public function actionRegister(): Response|string
    {
        /** @var Module $module */
        $module = $this->module;

        if (!$module->enableRegistration) {
            throw new NotFoundHttpException(Yii::t('user', 'Registration is disabled.'));
        }

        /** @var RegistrationForm $model */
        $model = $module->createModel('RegistrationForm');

        // Handle AJAX validation
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return $this->asJson(\yii\widgets\ActiveForm::validate($model));
        }

        if ($model->load(Yii::$app->request->post())) {
            /** @var RegistrationService $service */
            $service = Yii::$container->get(RegistrationService::class);

            $user = $service->register($model);

            if ($user !== null) {
                if ($module->enableConfirmation) {
                    Yii::$app->session->setFlash('success', Yii::t('user', 'Your account has been created. Please check your email for confirmation instructions.'));
                } else {
                    Yii::$app->session->setFlash('success', Yii::t('user', 'Your account has been created and you can now sign in.'));
                }

                return $this->redirect(['/user/login']);
            }
        }

        return $this->render('register', [
            'model' => $model,
            'module' => $module,
        ]);
    }

    /**
     * Confirm email with token.
     */
    public function actionConfirm(int $id, string $token): Response
    {
        /** @var Module $module */
        $module = $this->module;

        $user = User::findOne($id);

        if ($user === null) {
            throw new NotFoundHttpException(Yii::t('user', 'User not found.'));
        }

        if ($user->getIsConfirmed()) {
            Yii::$app->session->setFlash('info', Yii::t('user', 'Your email has already been confirmed.'));
            return $this->redirect(['/user/login']);
        }

        /** @var RegistrationService $service */
        $service = Yii::$container->get(RegistrationService::class);

        if ($service->confirm($user, $token)) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Thank you! Your email has been confirmed.'));

            // Auto-login after confirmation
            Yii::$app->user->login($user, $module->rememberFor);

            return $this->goHome();
        }

        Yii::$app->session->setFlash('danger', Yii::t('user', 'The confirmation link is invalid or has expired.'));

        return $this->redirect(['/user/login']);
    }

    /**
     * Resend confirmation email.
     */
    public function actionResend(): Response|string
    {
        /** @var Module $module */
        $module = $this->module;

        if (!$module->enableConfirmation) {
            throw new NotFoundHttpException();
        }

        $model = $module->createModel('ResendForm');

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user = User::findByEmail($model->email);

            if ($user !== null && !$user->getIsConfirmed()) {
                /** @var RegistrationService $service */
                $service = Yii::$container->get(RegistrationService::class);
                $service->resendConfirmation($user);
            }

            // Always show success message to prevent email enumeration
            Yii::$app->session->setFlash('success', Yii::t('user', 'If the email exists and is not confirmed, we have sent a new confirmation link.'));

            return $this->redirect(['/user/login']);
        }

        return $this->render('resend', [
            'model' => $model,
            'module' => $module,
        ]);
    }
}
