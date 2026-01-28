<?php

declare(strict_types=1);

namespace cgsmith\user\controllers;

use cgsmith\user\models\Profile;
use cgsmith\user\models\SettingsForm;
use cgsmith\user\models\Token;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use cgsmith\user\services\MailerService;
use cgsmith\user\services\SessionService;
use cgsmith\user\services\TokenService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Settings controller for account and profile.
 */
class SettingsController extends Controller
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
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete-avatar' => ['post'],
                    'terminate-session' => ['post'],
                    'terminate-all-sessions' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Account settings (email, password).
     */
    public function actionAccount(): Response|string
    {
        /** @var Module $module */
        $module = $this->module;

        /** @var User $user */
        $user = Yii::$app->user->identity;

        $model = new SettingsForm($user);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // Handle email change
                if ($model->isEmailChanged()) {
                    if ($module->emailChangeStrategy === Module::EMAIL_CHANGE_INSECURE) {
                        $user->email = $model->email;
                    } else {
                        /** @var TokenService $tokenService */
                        $tokenService = Yii::$container->get(TokenService::class);
                        $token = $tokenService->createEmailChangeToken($user, $model->email);

                        /** @var MailerService $mailer */
                        $mailer = Yii::$container->get(MailerService::class);
                        $mailer->sendEmailChangeMessage($user, $token, $model->email);

                        Yii::$app->session->setFlash('info', Yii::t('user', 'A confirmation email has been sent to your new email address.'));
                    }
                }

                // Handle username change
                if ($model->username !== $user->username) {
                    $user->username = $model->username;
                }

                // Handle password change
                if ($model->isPasswordChanged()) {
                    $user->password = $model->new_password;
                }

                if (!$user->save()) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('danger', Yii::t('user', 'An error occurred while saving your settings.'));
                } else {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', Yii::t('user', 'Your settings have been updated.'));
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Settings update failed: ' . $e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash('danger', Yii::t('user', 'An error occurred while saving your settings.'));
            }

            return $this->refresh();
        }

        return $this->render('account', [
            'model' => $model,
            'module' => $module,
        ]);
    }

    /**
     * Profile settings.
     */
    public function actionProfile(): Response|string
    {
        /** @var Module $module */
        $module = $this->module;

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $profile = $user->profile;

        if ($profile->load(Yii::$app->request->post())) {
            // Handle avatar upload
            if ($module->enableAvatarUpload) {
                $avatarFile = UploadedFile::getInstance($profile, 'avatar_path');

                if ($avatarFile !== null) {
                    $uploadPath = Yii::getAlias($module->avatarPath);

                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    // Delete old avatar
                    if (!empty($profile->avatar_path)) {
                        $oldPath = $uploadPath . '/' . $profile->avatar_path;
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    // Save new avatar
                    $filename = $user->id . '_' . time() . '.' . $avatarFile->extension;
                    $avatarFile->saveAs($uploadPath . '/' . $filename);
                    $profile->avatar_path = $filename;
                }
            }

            if ($profile->save()) {
                Yii::$app->session->setFlash('success', Yii::t('user', 'Your profile has been updated.'));
                return $this->refresh();
            }
        }

        return $this->render('profile', [
            'model' => $profile,
            'module' => $module,
        ]);
    }

    /**
     * Delete avatar.
     */
    public function actionDeleteAvatar(): Response
    {
        /** @var Module $module */
        $module = $this->module;

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $profile = $user->profile;

        if (!empty($profile->avatar_path)) {
            $uploadPath = Yii::getAlias($module->avatarPath);
            $path = $uploadPath . '/' . $profile->avatar_path;

            if (file_exists($path)) {
                unlink($path);
            }

            $profile->avatar_path = null;
            $profile->save(false, ['avatar_path']);

            Yii::$app->session->setFlash('success', Yii::t('user', 'Your avatar has been deleted.'));
        }

        return $this->redirect(['profile']);
    }

    /**
     * Confirm email change.
     */
    public function actionConfirmEmail(int $id, string $token): Response
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        if ($user->id !== $id) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'Invalid request.'));
            return $this->redirect(['account']);
        }

        /** @var TokenService $tokenService */
        $tokenService = Yii::$container->get(TokenService::class);
        $tokenModel = $tokenService->findEmailChangeToken($token);

        if ($tokenModel === null || $tokenModel->user_id !== $user->id) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'The confirmation link is invalid or has expired.'));
            return $this->redirect(['account']);
        }

        $newEmail = $tokenModel->data['new_email'] ?? null;

        if ($newEmail === null) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'Invalid email change request.'));
            return $this->redirect(['account']);
        }

        $user->email = $newEmail;

        if ($user->save(false, ['email'])) {
            $tokenService->deleteToken($tokenModel);
            Yii::$app->session->setFlash('success', Yii::t('user', 'Your email has been changed.'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'An error occurred while changing your email.'));
        }

        return $this->redirect(['account']);
    }

    /**
     * Display active sessions.
     */
    public function actionSessions(): Response|string
    {
        /** @var Module $module */
        $module = $this->module;

        if (!$module->enableSessionHistory) {
            return $this->redirect(['account']);
        }

        /** @var User $user */
        $user = Yii::$app->user->identity;

        /** @var SessionService $sessionService */
        $sessionService = Yii::$container->get(SessionService::class);
        $sessions = $sessionService->getUserSessions($user);

        return $this->render('sessions', [
            'sessions' => $sessions,
            'module' => $module,
        ]);
    }

    /**
     * Terminate a specific session.
     */
    public function actionTerminateSession(int $id): Response
    {
        /** @var Module $module */
        $module = $this->module;

        if (!$module->enableSessionHistory) {
            return $this->redirect(['account']);
        }

        /** @var User $user */
        $user = Yii::$app->user->identity;

        /** @var SessionService $sessionService */
        $sessionService = Yii::$container->get(SessionService::class);

        if ($sessionService->terminateSession($id, $user)) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Session has been terminated.'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'Session not found.'));
        }

        return $this->redirect(['sessions']);
    }

    /**
     * Terminate all sessions except the current one.
     */
    public function actionTerminateAllSessions(): Response
    {
        /** @var Module $module */
        $module = $this->module;

        if (!$module->enableSessionHistory) {
            return $this->redirect(['account']);
        }

        /** @var User $user */
        $user = Yii::$app->user->identity;

        /** @var SessionService $sessionService */
        $sessionService = Yii::$container->get(SessionService::class);
        $count = $sessionService->terminateOtherSessions($user);

        Yii::$app->session->setFlash('success', Yii::t('user', '{count, plural, =0{No sessions} =1{1 session} other{# sessions}} terminated.', ['count' => $count]));

        return $this->redirect(['sessions']);
    }
}
