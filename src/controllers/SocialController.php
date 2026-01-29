<?php

declare(strict_types=1);

namespace cgsmith\user\controllers;

use cgsmith\user\models\User;
use cgsmith\user\Module;
use cgsmith\user\services\SocialAuthService;
use Yii;
use yii\authclient\AuthAction;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Social authentication controller.
 */
class SocialController extends Controller
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
                    ['allow' => true, 'actions' => ['auth'], 'roles' => ['?', '@']],
                    ['allow' => true, 'actions' => ['networks', 'connect', 'disconnect'], 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'disconnect' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'auth' => [
                'class' => AuthAction::class,
                'successCallback' => [$this, 'onAuthSuccess'],
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

        if (!$module->enableSocialAuth) {
            throw new NotFoundHttpException();
        }

        return parent::beforeAction($action);
    }

    /**
     * Handle successful authentication.
     */
    public function onAuthSuccess($client): Response
    {
        /** @var Module $module */
        $module = $this->module;

        /** @var SocialAuthService $socialAuthService */
        $socialAuthService = Yii::$container->get(SocialAuthService::class);

        $user = $socialAuthService->handleCallback($client);

        if ($user !== null) {
            if ($module->enableSessionHistory) {
                /** @var \cgsmith\user\services\SessionService $sessionService */
                $sessionService = Yii::$container->get(\cgsmith\user\services\SessionService::class);
                $sessionService->trackSession($user);
            }

            return $this->goBack();
        }

        Yii::$app->session->setFlash('danger', Yii::t('user', 'Unable to complete authentication.'));
        return $this->redirect(['/' . $module->urlPrefix . '/login']);
    }

    /**
     * Display connected networks.
     */
    public function actionNetworks(): string
    {
        /** @var Module $module */
        $module = $this->module;

        if (!$module->enableSocialConnect) {
            return $this->redirect(['settings/account']);
        }

        /** @var User $user */
        $user = Yii::$app->user->identity;

        /** @var SocialAuthService $socialAuthService */
        $socialAuthService = Yii::$container->get(SocialAuthService::class);

        $connectedAccounts = $socialAuthService->getConnectedAccounts($user);
        $availableClients = $socialAuthService->getAuthClients();

        $connectedProviders = array_map(fn($account) => $account->provider, $connectedAccounts);

        return $this->render('networks', [
            'connectedAccounts' => $connectedAccounts,
            'availableClients' => $availableClients,
            'connectedProviders' => $connectedProviders,
            'module' => $module,
        ]);
    }

    /**
     * Disconnect a social account.
     */
    public function actionDisconnect(int $id): Response
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        /** @var SocialAuthService $socialAuthService */
        $socialAuthService = Yii::$container->get(SocialAuthService::class);

        if ($socialAuthService->disconnect($user, $id)) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Account disconnected successfully.'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'Unable to disconnect account.'));
        }

        return $this->redirect(['networks']);
    }
}
