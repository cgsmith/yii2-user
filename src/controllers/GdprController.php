<?php

declare(strict_types=1);

namespace cgsmith\user\controllers;

use cgsmith\user\models\GdprConsentForm;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use cgsmith\user\services\GdprService;
use Yii;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * GDPR controller for data export and deletion.
 */
class GdprController extends Controller
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
                    'delete' => ['post'],
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

        if ($action->id === 'consent') {
            if (!$module->enableGdprConsent) {
                throw new NotFoundHttpException();
            }
        } elseif (!$module->enableGdpr) {
            throw new NotFoundHttpException();
        }

        return parent::beforeAction($action);
    }

    /**
     * GDPR consent page.
     */
    public function actionConsent(): Response|string
    {
        /** @var Module $module */
        $module = $this->module;

        if (!$module->enableGdprConsent) {
            return $this->redirect(['/']);
        }

        /** @var User $user */
        $user = Yii::$app->user->identity;

        /** @var GdprService $gdprService */
        $gdprService = Yii::$container->get(GdprService::class);

        if ($gdprService->hasValidConsent($user)) {
            return $this->redirect(['/']);
        }

        $model = new GdprConsentForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($gdprService->recordConsent($user, $model->marketingConsent)) {
                Yii::$app->session->setFlash('success', Yii::t('user', 'Thank you for your consent.'));
                return $this->goHome();
            }
            Yii::$app->session->setFlash('danger', Yii::t('user', 'An error occurred while recording your consent.'));
        }

        return $this->render('consent', [
            'model' => $model,
            'module' => $module,
        ]);
    }

    /**
     * GDPR overview page.
     */
    public function actionIndex(): string
    {
        return $this->render('index', [
            'module' => $this->module,
        ]);
    }

    /**
     * Export user data.
     */
    public function actionExport(): Response
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $data = [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'status' => $user->status,
                'email_confirmed_at' => $user->email_confirmed_at,
                'last_login_at' => $user->last_login_at,
                'last_login_ip' => $user->last_login_ip,
                'registration_ip' => $user->registration_ip,
                'created_at' => $user->created_at,
                'gdpr_consent_at' => $user->gdpr_consent_at,
            ],
            'profile' => null,
            'exported_at' => date('Y-m-d H:i:s'),
        ];

        if ($user->profile !== null) {
            $data['profile'] = [
                'name' => $user->profile->name,
                'bio' => $user->profile->bio,
                'location' => $user->profile->location,
                'website' => $user->profile->website,
                'timezone' => $user->profile->timezone,
                'public_email' => $user->profile->public_email,
            ];
        }

        // Return as JSON download
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="user-data-' . $user->id . '.json"');

        return $this->asJson($data);
    }

    /**
     * Delete user account (GDPR right to be forgotten).
     */
    public function actionDelete(): Response|string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $model = new class extends \yii\base\Model {
            public ?string $password = null;
            public bool $confirm = false;

            public function rules(): array
            {
                return [
                    ['password', 'required'],
                    ['confirm', 'required'],
                    ['confirm', 'boolean'],
                    ['confirm', 'compare', 'compareValue' => true, 'message' => Yii::t('user', 'You must confirm that you want to delete your account.')],
                ];
            }

            public function attributeLabels(): array
            {
                return [
                    'password' => Yii::t('user', 'Current Password'),
                    'confirm' => Yii::t('user', 'I understand this action cannot be undone'),
                ];
            }
        };

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if (!$user->validatePassword($model->password)) {
                $model->addError('password', Yii::t('user', 'Password is incorrect.'));
            } else {
                // Soft delete - anonymize data but keep record for audit
                $user->email = 'deleted_' . $user->id . '@deleted.local';
                $user->username = null;
                $user->password_hash = '';
                $user->auth_key = Yii::$app->security->generateRandomString(32);
                $user->status = User::STATUS_BLOCKED;
                $user->gdpr_deleted_at = new Expression('NOW()');

                // Clear profile
                if ($user->profile !== null) {
                    $user->profile->name = null;
                    $user->profile->bio = null;
                    $user->profile->location = null;
                    $user->profile->website = null;
                    $user->profile->public_email = null;
                    $user->profile->gravatar_email = null;
                    $user->profile->avatar_path = null;
                    $user->profile->save(false);
                }

                if ($user->save(false)) {
                    Yii::$app->user->logout();
                    Yii::$app->session->setFlash('success', Yii::t('user', 'Your account has been deleted.'));
                    return $this->goHome();
                }

                Yii::$app->session->setFlash('danger', Yii::t('user', 'An error occurred while deleting your account.'));
            }
        }

        return $this->render('delete', [
            'model' => $model,
            'module' => $this->module,
        ]);
    }
}
