<?php

declare(strict_types=1);

namespace cgsmith\user\controllers;

use cgsmith\user\filters\AccessRule;
use cgsmith\user\models\User;
use cgsmith\user\models\UserSearch;
use cgsmith\user\Module;
use cgsmith\user\services\RegistrationService;
use cgsmith\user\services\MailerService;
use cgsmith\user\services\UserService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Admin controller for user management.
 */
class AdminController extends Controller
{
    /**
     * Session key for storing original user ID during impersonation.
     */
    public const ORIGINAL_USER_SESSION_KEY = 'user.original_user_id';

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'ruleConfig' => [
                    'class' => AccessRule::class,
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['stop-impersonate'],
                        'matchCallback' => function () {
                            return Yii::$app->session->has(self::ORIGINAL_USER_SESSION_KEY);
                        },
                    ],
                    ['allow' => true, 'roles' => ['admin']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'block' => ['post'],
                    'unblock' => ['post'],
                    'confirm' => ['post'],
                    'resend-password' => ['post'],
                ],
            ],
        ];
    }

    /**
     * List all users.
     */
    public function actionIndex(): string
    {
        /** @var Module $module */
        $module = $this->module;

        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'module' => $module,
        ]);
    }

    /**
     * Create a new user.
     */
    public function actionCreate(): Response|string
    {
        /** @var Module $module */
        $module = $this->module;

        $model = new User(['scenario' => 'create']);

        if ($model->load(Yii::$app->request->post())) {
            // Handle AJAX validation
            if (Yii::$app->request->isAjax) {
                return $this->asJson(\yii\widgets\ActiveForm::validate($model));
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('user', 'User has been created.'));
                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'module' => $module,
        ]);
    }

    /**
     * Update user account details.
     */
    public function actionUpdate(int $id): Response|string
    {
        /** @var Module $module */
        $module = $this->module;

        $user = $this->findUser($id);
        $user->scenario = 'update';

        if ($user->load(Yii::$app->request->post())) {
            // Handle AJAX validation
            if (Yii::$app->request->isAjax) {
                return $this->asJson(\yii\widgets\ActiveForm::validate($user));
            }

            if ($user->save()) {
                Yii::$app->session->setFlash('success', Yii::t('user', 'User has been updated.'));
                return $this->redirect(['update', 'id' => $user->id]);
            }
        }

        return $this->render('_account', [
            'user' => $user,
            'module' => $module,
        ]);
    }

    /**
     * Update user profile.
     */
    public function actionUpdateProfile(int $id): Response|string
    {
        /** @var Module $module */
        $module = $this->module;

        $user = $this->findUser($id);
        $profile = $user->profile;

        if ($profile === null) {
            $profile = new \cgsmith\user\models\Profile(['user_id' => $user->id]);
        }

        if ($profile->load(Yii::$app->request->post())) {
            // Handle AJAX validation
            if (Yii::$app->request->isAjax) {
                return $this->asJson(\yii\widgets\ActiveForm::validate($profile));
            }

            if ($profile->save()) {
                Yii::$app->session->setFlash('success', Yii::t('user', 'Profile has been updated.'));
                return $this->redirect(['update-profile', 'id' => $user->id]);
            }
        }

        return $this->render('_profile', [
            'user' => $user,
            'profile' => $profile,
            'module' => $module,
        ]);
    }

    /**
     * Show user information.
     */
    public function actionInfo(int $id): string
    {
        $user = $this->findUser($id);

        return $this->render('_info', [
            'user' => $user,
        ]);
    }

    /**
     * Delete user.
     */
    public function actionDelete(int $id): Response
    {
        $model = $this->findUser($id);

        // Prevent self-deletion
        if ($model->id === Yii::$app->user->id) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'You cannot delete your own account.'));
            return $this->redirect(['index']);
        }

        /** @var UserService $service */
        $service = Yii::$container->get(UserService::class);

        if ($service->delete($model)) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'User has been deleted.'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'An error occurred while deleting the user.'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Block user.
     */
    public function actionBlock(int $id): Response
    {
        $model = $this->findUser($id);

        // Prevent self-blocking
        if ($model->id === Yii::$app->user->id) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'You cannot block your own account.'));
            return $this->redirect(['index']);
        }

        /** @var UserService $service */
        $service = Yii::$container->get(UserService::class);

        if ($service->block($model)) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'User has been blocked.'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'An error occurred while blocking the user.'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Unblock user.
     */
    public function actionUnblock(int $id): Response
    {
        $model = $this->findUser($id);

        /** @var UserService $service */
        $service = Yii::$container->get(UserService::class);

        if ($service->unblock($model)) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'User has been unblocked.'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'An error occurred while unblocking the user.'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Manually confirm user email.
     */
    public function actionConfirm(int $id): Response
    {
        $model = $this->findUser($id);

        /** @var UserService $service */
        $service = Yii::$container->get(UserService::class);

        if ($service->confirm($model)) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'User email has been confirmed.'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'An error occurred while confirming the user.'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Generate and send a new password to the user.
     */
    public function actionResendPassword(int $id): Response
    {
        $model = $this->findUser($id);

        /** @var UserService $service */
        $service = Yii::$container->get(UserService::class);

        /** @var MailerService $mailer */
        $mailer = Yii::$container->get(MailerService::class);

        try {
            if ($service->resendPassword($model, $mailer)) {
                Yii::$app->session->setFlash('success', Yii::t('user', 'New password has been generated and sent to user.'));
            } else {
                Yii::$app->session->setFlash('danger', Yii::t('user', 'An error occurred while generating the password.'));
            }
        } catch (\yii\base\InvalidCallException $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Impersonate user.
     */
    public function actionImpersonate(int $id): Response
    {
        /** @var Module $module */
        $module = $this->module;

        if (!$module->enableImpersonation) {
            throw new NotFoundHttpException();
        }

        $model = $this->findUser($id);

        /** @var UserService $service */
        $service = Yii::$container->get(UserService::class);

        if ($service->impersonate($model)) {
            Yii::$app->session->setFlash('warning', Yii::t('user', 'You are now impersonating {user}. Click "Stop Impersonating" to return to your account.', ['user' => $model->email]));
            return $this->goHome();
        }

        Yii::$app->session->setFlash('danger', Yii::t('user', 'You are not allowed to impersonate this user.'));

        return $this->redirect(['index']);
    }

    /**
     * Stop impersonating.
     */
    public function actionStopImpersonate(): Response
    {
        /** @var UserService $service */
        $service = Yii::$container->get(UserService::class);

        if ($service->stopImpersonation()) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'You have returned to your account.'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Find user by ID.
     */
    protected function findUser(int $id): User
    {
        $user = User::findOne($id);

        if ($user === null) {
            throw new NotFoundHttpException(Yii::t('user', 'User not found.'));
        }

        return $user;
    }
}
