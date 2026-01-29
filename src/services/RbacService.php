<?php

declare(strict_types=1);

namespace cgsmith\user\services;

use cgsmith\user\models\User;
use cgsmith\user\Module;
use Yii;
use yii\rbac\Item;
use yii\rbac\Permission;
use yii\rbac\Role;

/**
 * Service for RBAC management.
 */
class RbacService
{
    public function __construct(
        private readonly Module $module
    ) {
    }

    /**
     * Get the auth manager.
     */
    public function getAuthManager(): ?\yii\rbac\ManagerInterface
    {
        return Yii::$app->authManager;
    }

    /**
     * Get all roles.
     *
     * @return Role[]
     */
    public function getRoles(): array
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return [];
        }

        return $authManager->getRoles();
    }

    /**
     * Get a role by name.
     */
    public function getRole(string $name): ?Role
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return null;
        }

        return $authManager->getRole($name);
    }

    /**
     * Create a new role.
     */
    public function createRole(string $name, ?string $description = null): ?Role
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return null;
        }

        $role = $authManager->createRole($name);
        $role->description = $description;

        if ($authManager->add($role)) {
            return $role;
        }

        return null;
    }

    /**
     * Update a role.
     */
    public function updateRole(string $name, string $newName, ?string $description = null): bool
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return false;
        }

        $role = $authManager->getRole($name);
        if ($role === null) {
            return false;
        }

        $role->name = $newName;
        $role->description = $description;

        return $authManager->update($name, $role);
    }

    /**
     * Delete a role.
     */
    public function deleteRole(string $name): bool
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return false;
        }

        $role = $authManager->getRole($name);
        if ($role === null) {
            return false;
        }

        return $authManager->remove($role);
    }

    /**
     * Get all permissions.
     *
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return [];
        }

        return $authManager->getPermissions();
    }

    /**
     * Get a permission by name.
     */
    public function getPermission(string $name): ?Permission
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return null;
        }

        return $authManager->getPermission($name);
    }

    /**
     * Create a new permission.
     */
    public function createPermission(string $name, ?string $description = null): ?Permission
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return null;
        }

        $permission = $authManager->createPermission($name);
        $permission->description = $description;

        if ($authManager->add($permission)) {
            return $permission;
        }

        return null;
    }

    /**
     * Update a permission.
     */
    public function updatePermission(string $name, string $newName, ?string $description = null): bool
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return false;
        }

        $permission = $authManager->getPermission($name);
        if ($permission === null) {
            return false;
        }

        $permission->name = $newName;
        $permission->description = $description;

        return $authManager->update($name, $permission);
    }

    /**
     * Delete a permission.
     */
    public function deletePermission(string $name): bool
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return false;
        }

        $permission = $authManager->getPermission($name);
        if ($permission === null) {
            return false;
        }

        return $authManager->remove($permission);
    }

    /**
     * Get roles assigned to a user.
     *
     * @return Role[]
     */
    public function getUserRoles(int $userId): array
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return [];
        }

        return $authManager->getRolesByUser($userId);
    }

    /**
     * Get permissions assigned directly to a user.
     *
     * @return Permission[]
     */
    public function getUserPermissions(int $userId): array
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return [];
        }

        return $authManager->getPermissionsByUser($userId);
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(int $userId, string $roleName): bool
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return false;
        }

        $role = $authManager->getRole($roleName);
        if ($role === null) {
            return false;
        }

        try {
            $authManager->assign($role, $userId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Revoke a role from a user.
     */
    public function revokeRole(int $userId, string $roleName): bool
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return false;
        }

        $role = $authManager->getRole($roleName);
        if ($role === null) {
            return false;
        }

        return $authManager->revoke($role, $userId);
    }

    /**
     * Update user role assignments.
     */
    public function updateUserRoles(int $userId, array $roleNames): bool
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return false;
        }

        $authManager->revokeAll($userId);

        foreach ($roleNames as $roleName) {
            $role = $authManager->getRole($roleName);
            if ($role !== null) {
                try {
                    $authManager->assign($role, $userId);
                } catch (\Exception $e) {
                    // Role already assigned, skip
                }
            }
        }

        return true;
    }

    /**
     * Add child item to a role.
     */
    public function addChild(string $parentName, string $childName): bool
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return false;
        }

        $parent = $authManager->getRole($parentName);
        if ($parent === null) {
            return false;
        }

        $child = $authManager->getRole($childName) ?? $authManager->getPermission($childName);
        if ($child === null) {
            return false;
        }

        try {
            return $authManager->addChild($parent, $child);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Remove child item from a role.
     */
    public function removeChild(string $parentName, string $childName): bool
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return false;
        }

        $parent = $authManager->getRole($parentName);
        if ($parent === null) {
            return false;
        }

        $child = $authManager->getRole($childName) ?? $authManager->getPermission($childName);
        if ($child === null) {
            return false;
        }

        return $authManager->removeChild($parent, $child);
    }

    /**
     * Get children of an item.
     *
     * @return Item[]
     */
    public function getChildren(string $name): array
    {
        $authManager = $this->getAuthManager();
        if ($authManager === null) {
            return [];
        }

        return $authManager->getChildren($name);
    }

    /**
     * Check if user can manage RBAC.
     */
    public function canManageRbac(User $user): bool
    {
        if ($this->module->rbacManagementPermission !== null && $this->getAuthManager() !== null) {
            return $this->getAuthManager()->checkAccess($user->id, $this->module->rbacManagementPermission);
        }

        return $user->getIsAdmin();
    }
}
