<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\models\query\TwoFactorQuery;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Two-factor authentication ActiveRecord model.
 *
 * @property int $id
 * @property int $user_id
 * @property string $secret
 * @property string|null $enabled_at
 * @property array|null $backup_codes
 * @property string $created_at
 * @property string $updated_at
 *
 * @property-read User $user
 * @property-read bool $isEnabled
 */
class TwoFactor extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user_two_factor}}';
    }

    /**
     * {@inheritdoc}
     * @return TwoFactorQuery
     */
    public static function find(): TwoFactorQuery
    {
        return new TwoFactorQuery(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'secret'], 'required'],
            [['user_id'], 'integer'],
            [['secret'], 'string', 'max' => 64],
            [['user_id'], 'unique'],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
            [['backup_codes'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('user', 'ID'),
            'user_id' => Yii::t('user', 'User'),
            'secret' => Yii::t('user', 'Secret'),
            'enabled_at' => Yii::t('user', 'Enabled At'),
            'backup_codes' => Yii::t('user', 'Backup Codes'),
            'created_at' => Yii::t('user', 'Created At'),
            'updated_at' => Yii::t('user', 'Updated At'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (is_array($this->backup_codes)) {
            $this->backup_codes = json_encode($this->backup_codes);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterFind(): void
    {
        parent::afterFind();

        if (is_string($this->backup_codes)) {
            $this->backup_codes = json_decode($this->backup_codes, true) ?: [];
        }
    }

    /**
     * Get user relation.
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Check if 2FA is enabled.
     */
    public function getIsEnabled(): bool
    {
        return $this->enabled_at !== null;
    }
}
