<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\models\query\SocialAccountQuery;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Social account ActiveRecord model.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $provider
 * @property string $provider_id
 * @property string|null $data
 * @property string|null $email
 * @property string|null $username
 * @property string $created_at
 *
 * @property-read User|null $user
 * @property-read array $decodedData
 */
class SocialAccount extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user_social_account}}';
    }

    /**
     * {@inheritdoc}
     * @return SocialAccountQuery
     */
    public static function find(): SocialAccountQuery
    {
        return new SocialAccountQuery(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['provider', 'provider_id'], 'required'],
            [['user_id'], 'integer'],
            [['provider'], 'string', 'max' => 255],
            [['provider_id'], 'string', 'max' => 255],
            [['email'], 'string', 'max' => 255],
            [['username'], 'string', 'max' => 255],
            [['data'], 'safe'],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['provider', 'provider_id'], 'unique', 'targetAttribute' => ['provider', 'provider_id']],
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
            'provider' => Yii::t('user', 'Provider'),
            'provider_id' => Yii::t('user', 'Provider ID'),
            'email' => Yii::t('user', 'Email'),
            'username' => Yii::t('user', 'Username'),
            'data' => Yii::t('user', 'Data'),
            'created_at' => Yii::t('user', 'Connected At'),
        ];
    }

    /**
     * Get user relation.
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Get decoded data.
     */
    public function getDecodedData(): array
    {
        if (empty($this->data)) {
            return [];
        }

        return is_array($this->data) ? $this->data : (json_decode($this->data, true) ?: []);
    }

    /**
     * Check if this account is connected to a user.
     */
    public function getIsConnected(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * Connect this account to a user.
     */
    public function connect(User $user): bool
    {
        $this->user_id = $user->id;
        return $this->save(false, ['user_id']);
    }

    /**
     * Find account by provider and client ID.
     */
    public static function findByProviderAndId(string $provider, string $providerId): ?self
    {
        return static::find()
            ->byProvider($provider)
            ->byProviderId($providerId)
            ->one();
    }
}
