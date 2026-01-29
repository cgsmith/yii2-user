<?php

declare(strict_types=1);

namespace cgsmith\user\controllers;

use cgsmith\user\models\PermissionForm;
use cgsmith\user\models\RoleForm;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use cgsmith\user\services\RbacService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * RBAC management controller.
 */
class RbacController extends Controller
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
                    'delete-role' => ['post'],
                    'delete-permission' => ['post'],
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

        if (!$module->enableRbacManagement) {
            throw new NotFoundHttpException();
        }

        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);

        /** @var User $user */
        $user = Yii::$app->user->identity;

        if (!$rbacService->canManageRbac($user)) {
            throw new ForbiddenHttpException(Yii::t('user', 'You are not allowed to manage RBAC.'));
        }

        return parent::beforeAction($action);
    }

    /**
     * RBAC overview page.
     */
    public function actionIndex(): string
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);

        $roles = $rbacService->getRoles();
        $permissions = $rbacService->getPermissions();

        return $this->render('index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'module' => $this->module,
        ]);
    }

    /**
     * List roles.
     */
    public function actionRoles(): string
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);

        return $this->render('roles', [
            'roles' => $rbacService->getRoles(),
            'module' => $this->module,
        ]);
    }

    /**
     * Create a new role.
     */
    public function actionCreateRole(): Response|string
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);

        $model = new RoleForm();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Role has been created.'));
            return $this->redirect(['roles']);
        }

        return $this->render('role-form', [
            'model' => $model,
            'permissions' => $rbacService->getPermissions(),
            'roles' => $rbacService->getRoles(),
            'module' => $this->module,
            'isNew' => true,
        ]);
    }

    /**
     * Update a role.
     */
    public function actionUpdateRole(string $name): Response|string
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);

        $model = new RoleForm();
        if (!$model->loadRole($name)) {
            throw new NotFoundHttpException(Yii::t('user', 'Role not found.'));
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Role has been updated.'));
            return $this->redirect(['roles']);
        }

        return $this->render('role-form', [
            'model' => $model,
            'permissions' => $rbacService->getPermissions(),
            'roles' => array_filter($rbacService->getRoles(), fn($r) => $r->name !== $name),
            'module' => $this->module,
            'isNew' => false,
        ]);
    }

    /**
     * Delete a role.
     */
    public function actionDeleteRole(string $name): Response
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);

        if ($rbacService->deleteRole($name)) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Role has been deleted.'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'Failed to delete role.'));
        }

        return $this->redirect(['roles']);
    }

    /**
     * List permissions.
     */
    public function actionPermissions(): string
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);

        return $this->render('permissions', [
            'permissions' => $rbacService->getPermissions(),
            'module' => $this->module,
        ]);
    }

    /**
     * Create a new permission.
     */
    public function actionCreatePermission(): Response|string
    {
        $model = new PermissionForm();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Permission has been created.'));
            return $this->redirect(['permissions']);
        }

        return $this->render('permission-form', [
            'model' => $model,
            'module' => $this->module,
            'isNew' => true,
        ]);
    }

    /**
     * Update a permission.
     */
    public function actionUpdatePermission(string $name): Response|string
    {
        $model = new PermissionForm();
        if (!$model->loadPermission($name)) {
            throw new NotFoundHttpException(Yii::t('user', 'Permission not found.'));
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Permission has been updated.'));
            return $this->redirect(['permissions']);
        }

        return $this->render('permission-form', [
            'model' => $model,
            'module' => $this->module,
            'isNew' => false,
        ]);
    }

    /**
     * Delete a permission.
     */
    public function actionDeletePermission(string $name): Response
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);

        if ($rbacService->deletePermission($name)) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Permission has been deleted.'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'Failed to delete permission.'));
        }

        return $this->redirect(['permissions']);
    }
}
