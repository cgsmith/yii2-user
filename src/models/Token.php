<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\models\query\TokenQuery;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Token ActiveRecord model.
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $token
 * @property array|null $data
 * @property string $expires_at
 * @property string $created_at
 *
 * @property-read User $user
 * @property-read bool $isExpired
 */
class Token extends ActiveRecord
{
    public const TYPE_CONFIRMATION = 'confirmation';
    public const TYPE_RECOVERY = 'recovery';
    public const TYPE_EMAIL_CHANGE = 'email_change';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user_token}}';
    }

    /**
     * {@inheritdoc}
     * @return TokenQuery
     */
    public static function find(): TokenQuery
    {
        return new TokenQuery(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'type', 'token', 'expires_at'], 'required'],
            [['type'], 'in', 'range' => [self::TYPE_CONFIRMATION, self::TYPE_RECOVERY, self::TYPE_EMAIL_CHANGE]],
            [['token'], 'string', 'max' => 64],
            [['token'], 'unique'],
            [['data'], 'safe'],
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
            'type' => Yii::t('user', 'Type'),
            'token' => Yii::t('user', 'Token'),
            'data' => Yii::t('user', 'Data'),
            'expires_at' => Yii::t('user', 'Expires At'),
            'created_at' => Yii::t('user', 'Created At'),
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

        if ($insert) {
            $this->created_at = new Expression('NOW()');
        }

        // Serialize data as JSON if it's an array
        if (is_array($this->data)) {
            $this->data = json_encode($this->data);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterFind(): void
    {
        parent::afterFind();

        // Deserialize JSON data
        if (is_string($this->data)) {
            $this->data = json_decode($this->data, true);
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
     * Check if token is expired.
     */
    public function getIsExpired(): bool
    {
        return strtotime($this->expires_at) < time();
    }

    /**
     * Generate a new secure token.
     */
    public static function generateToken(): string
    {
        return Yii::$app->security->generateRandomString(64);
    }

    /**
     * Create a confirmation token for a user.
     */
    public static function createConfirmationToken(User $user): static
    {
        $module = Yii::$app->getModule('user');

        $token = new static();
        $token->user_id = $user->id;
        $token->type = self::TYPE_CONFIRMATION;
        $token->token = self::generateToken();
        $token->expires_at = date('Y-m-d H:i:s', time() + $module->confirmWithin);

        return $token;
    }

    /**
     * Create a recovery token for a user.
     */
    public static function createRecoveryToken(User $user): static
    {
        $module = Yii::$app->getModule('user');

        $token = new static();
        $token->user_id = $user->id;
        $token->type = self::TYPE_RECOVERY;
        $token->token = self::generateToken();
        $token->expires_at = date('Y-m-d H:i:s', time() + $module->recoverWithin);

        return $token;
    }

    /**
     * Create an email change token for a user.
     */
    public static function createEmailChangeToken(User $user, string $newEmail): static
    {
        $module = Yii::$app->getModule('user');

        $token = new static();
        $token->user_id = $user->id;
        $token->type = self::TYPE_EMAIL_CHANGE;
        $token->token = self::generateToken();
        $token->expires_at = date('Y-m-d H:i:s', time() + $module->confirmWithin);
        $token->data = ['new_email' => $newEmail];

        return $token;
    }

    /**
     * Find token by token string and type.
     */
    public static function findByToken(string $token, string $type): ?static
    {
        return static::find()
            ->where(['token' => $token, 'type' => $type])
            ->notExpired()
            ->one();
    }

    /**
     * Delete all tokens for a user of a specific type.
     */
    public static function deleteAllForUser(int $userId, ?string $type = null): int
    {
        $condition = ['user_id' => $userId];

        if ($type !== null) {
            $condition['type'] = $type;
        }

        return static::deleteAll($condition);
    }

    /**
     * Delete expired tokens.
     */
    public static function deleteExpired(): int
    {
        return static::deleteAll(['<', 'expires_at', new Expression('NOW()')]);
    }
}
