<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\services\RbacService;
use Yii;
use yii\base\Model;

/**
 * Permission form model.
 */
class PermissionForm extends Model
{
    public ?string $name = null;
    public ?string $description = null;

    public ?string $originalName = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 64],
            ['name', 'match', 'pattern' => '/^[\w\-\.]+$/', 'message' => Yii::t('user', 'Name can only contain letters, numbers, underscores, hyphens, and dots.')],
            ['name', 'validateUniqueName'],
            ['description', 'string', 'max' => 255],
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

        if ($rbacService->getPermission($this->name) !== null) {
            $this->addError($attribute, Yii::t('user', 'A permission with this name already exists.'));
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
        ];
    }

    /**
     * Load from an existing permission.
     */
    public function loadPermission(string $name): bool
    {
        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);

        $permission = $rbacService->getPermission($name);
        if ($permission === null) {
            return false;
        }

        $this->originalName = $name;
        $this->name = $permission->name;
        $this->description = $permission->description;

        return true;
    }

    /**
     * Save the permission.
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var RbacService $rbacService */
        $rbacService = Yii::$container->get(RbacService::class);

        if ($this->originalName !== null) {
            return $rbacService->updatePermission($this->originalName, $this->name, $this->description);
        }

        return $rbacService->createPermission($this->name, $this->description) !== null;
    }
}
