<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\services\RbacService;
use Yii;
use yii\base\Model;

/**
 * Role assignment form model.
 */
class AssignmentForm extends Model
{
    public int $userId;
    public array $roles = [];

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['userId', 'required'],
            ['userId', 'integer'],
            ['roles', 'each', 'rule' => ['string']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'userId' => Yii::t('user', 'User'),
            'roles' => Yii::t('user', 'Roles'),
        ];
    }

    /**
     * Load current assignments for a user.
     */
    public function loadAssignments(int $userId): void
    {
        $this->userId = $userId;

        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);

        $userRoles = $rbacService->getUserRoles($userId);
        $this->roles = array_keys($userRoles);
    }

    /**
     * Save the assignments.
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);

        return $rbacService->updateUserRoles($this->userId, $this->roles);
    }
}
