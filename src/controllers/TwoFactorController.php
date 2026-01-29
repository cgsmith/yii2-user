<?php

declare(strict_types=1);

namespace cgsmith\user\controllers;

use cgsmith\user\models\TwoFactorForm;
use cgsmith\user\models\TwoFactorSetupForm;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use cgsmith\user\services\TwoFactorService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Two-factor authentication controller.
 */
class TwoFactorController extends Controller
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
                    ['allow' => true, 'actions' => ['verify'], 'roles' => ['?', '@']],
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'enable' => ['post'],
                    'disable' => ['post'],
                    'regenerate-backup-codes' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action): bool
    {
        /** @var Module $module */
        $module = $this->module;

        if (!$module->enableTwoFactor) {
            throw new NotFoundHttpException();
        }

        return parent::beforeAction($action);
    }

    /**
     * 2FA settings page.
     */
    public function actionIndex(): string
    {
        /** @var Module $module */
        $module = $this->module;

        /** @var User $user */
        $user = Yii::$app->user->identity;

        /** @var TwoFactorService $twoFactorService */
        $twoFactorService = Yii::$container->get(TwoFactorService::class);

        $isEnabled = $twoFactorService->isEnabled($user);
        $backupCodesCount = $twoFactorService->getBackupCodesCount($user);

        $setupForm = null;
        $secret = null;
        $qrCodeDataUri = null;

        if (!$isEnabled) {
            $secret = $twoFactorService->generateSecret();
            $qrCodeDataUri = $twoFactorService->getQrCodeDataUri($user, $secret);
            $setupForm = new TwoFactorSetupForm(['secret' => $secret]);
        }

        return $this->render('index', [
            'module' => $module,
            'isEnabled' => $isEnabled,
            'backupCodesCount' => $backupCodesCount,
            'setupForm' => $setupForm,
            'secret' => $secret,
            'qrCodeDataUri' => $qrCodeDataUri,
        ]);
    }

    /**
     * Enable 2FA.
     */
    public function actionEnable(): Response
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        /** @var TwoFactorService $twoFactorService */
        $twoFactorService = Yii::$container->get(TwoFactorService::class);

        $model = new TwoFactorSetupForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($twoFactorService->enable($user, $model->secret, $model->code)) {
                $twoFactor = $twoFactorService->getTwoFactor($user);
                Yii::$app->session->setFlash('success', Yii::t('user', 'Two-factor authentication has been enabled.'));
                Yii::$app->session->setFlash('backup_codes', $twoFactor->backup_codes);
                return $this->redirect(['backup-codes']);
            }

            Yii::$app->session->setFlash('danger', Yii::t('user', 'Invalid verification code. Please try again.'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Disable 2FA.
     */
    public function actionDisable(): Response
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        /** @var TwoFactorService $twoFactorService */
        $twoFactorService = Yii::$container->get(TwoFactorService::class);

        if ($twoFactorService->disable($user)) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Two-factor authentication has been disabled.'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'Failed to disable two-factor authentication.'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Display backup codes.
     */
    public function actionBackupCodes(): Response|string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        /** @var TwoFactorService $twoFactorService */
        $twoFactorService = Yii::$container->get(TwoFactorService::class);

        if (!$twoFactorService->isEnabled($user)) {
            return $this->redirect(['index']);
        }

        $backupCodes = Yii::$app->session->getFlash('backup_codes');
        $twoFactor = $twoFactorService->getTwoFactor($user);

        return $this->render('backup-codes', [
            'backupCodes' => $backupCodes,
            'backupCodesCount' => count($twoFactor->backup_codes ?? []),
            'module' => $this->module,
        ]);
    }

    /**
     * Regenerate backup codes.
     */
    public function actionRegenerateBackupCodes(): Response
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        /** @var TwoFactorService $twoFactorService */
        $twoFactorService = Yii::$container->get(TwoFactorService::class);

        $codes = $twoFactorService->regenerateBackupCodes($user);

        if ($codes !== null) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Backup codes have been regenerated.'));
            Yii::$app->session->setFlash('backup_codes', $codes);
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'Failed to regenerate backup codes.'));
        }

        return $this->redirect(['backup-codes']);
    }

    /**
     * Verify 2FA during login.
     */
    public function actionVerify(): Response|string
    {
        /** @var Module $module */
        $module = $this->module;

        /** @var TwoFactorService $twoFactorService */
        $twoFactorService = Yii::$container->get(TwoFactorService::class);

        $userId = $twoFactorService->getPending2FAUserId();

        if ($userId === null) {
            return $this->redirect(['/' . $module->urlPrefix . '/login']);
        }

        $user = User::findOne($userId);
        if ($user === null) {
            $twoFactorService->clearPending2FA();
            return $this->redirect(['/' . $module->urlPrefix . '/login']);
        }

        $model = new TwoFactorForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($twoFactorService->verify($user, $model->code)) {
                $twoFactorService->clearPending2FA();

                $rememberMe = $twoFactorService->getPending2FARememberMe();
                $duration = $rememberMe ? $module->rememberFor : 0;

                if (Yii::$app->user->login($user, $duration)) {
                    $user->updateLastLogin();

                    if ($module->enableSessionHistory) {
                        /** @var \cgsmith\user\services\SessionService $sessionService */
                        $sessionService = Yii::$container->get(\cgsmith\user\services\SessionService::class);
                        $sessionService->trackSession($user);
                    }

                    return $this->goBack();
                }
            }

            $model->addError('code', Yii::t('user', 'Invalid verification code.'));
        }

        return $this->render('verify', [
            'model' => $model,
            'module' => $module,
        ]);
    }
}
