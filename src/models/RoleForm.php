<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\services\RbacService;
use Yii;
use yii\base\Model;

/**
 * Role form model.
 */
class RoleForm extends Model
{
    public ?string $name = null;
    public ?string $description = null;
    public array $permissions = [];
    public array $childRoles = [];

    public ?string $originalName = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 64],
            ['name', 'match', 'pattern' => '/^[\w\-]+$/', 'message' => Yii::t('user', 'Name can only contain letters, numbers, underscores, and hyphens.')],
            ['name', 'validateUniqueName'],
            ['description', 'string', 'max' => 255],
            [['permissions', 'childRoles'], 'each', 'rule' => ['string']],
        ];
    }

    /**
     * Validate that the name is unique.
     */
    public function validateUniqueName(string $attribute): void
    {
        if ($this->name === $this->originalName) {
            return;
        }

        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);

        if ($rbacService->getRole($this->name) !== null) {
            $this->addError($attribute, Yii::t('user', 'A role with this name already exists.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('user', 'Name'),
            'description' => Yii::t('user', 'Description'),
            'permissions' => Yii::t('user', 'Permissions'),
            'childRoles' => Yii::t('user', 'Child Roles'),
        ];
    }

    /**
     * Load from an existing role.
     */
    public function loadRole(string $name): bool
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);

        $role = $rbacService->getRole($name);
        if ($role === null) {
            return false;
        }

        $this->originalName = $name;
        $this->name = $role->name;
        $this->description = $role->description;

        $children = $rbacService->getChildren($name);
        foreach ($children as $child) {
            if ($child->type === \yii\rbac\Item::TYPE_ROLE) {
                $this->childRoles[] = $child->name;
            } else {
                $this->permissions[] = $child->name;
            }
        }

        return true;
    }

    /**
     * Save the role.
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);
        $authManager = $rbacService->getAuthManager();

        if ($authManager === null) {
            return false;
        }

        if ($this->originalName !== null) {
            if (!$rbacService->updateRole($this->originalName, $this->name, $this->description)) {
                return false;
            }
        } else {
            if ($rbacService->createRole($this->name, $this->description) === null) {
                return false;
            }
        }

        $role = $rbacService->getRole($this->name);
        if ($role === null) {
            return false;
        }

        $authManager->removeChildren($role);

        foreach ($this->permissions as $permissionName) {
            $permission = $authManager->getPermission($permissionName);
            if ($permission !== null) {
                try {
                    $authManager->addChild($role, $permission);
                } catch (\Exception $e) {
                    // Skip if already exists
                }
            }
        }

        foreach ($this->childRoles as $childRoleName) {
            $childRole = $authManager->getRole($childRoleName);
            if ($childRole !== null && $childRole->name !== $role->name) {
                try {
                    $authManager->addChild($role, $childRole);
                } catch (\Exception $e) {
                    // Skip if already exists or would cause loop
                }
            }
        }

        return true;
    }
}
