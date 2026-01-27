<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\contracts\UserInterface;
use cgsmith\user\helpers\Password;
use cgsmith\user\models\query\UserQuery;
use cgsmith\user\Module;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * User ActiveRecord model.
 *
 * @property int $id
 * @property string $email
 * @property string|null $username
 * @property string $password_hash
 * @property string $auth_key
 * @property string $status
 * @property string|null $email_confirmed_at
 * @property string|null $blocked_at
 * @property string|null $last_login_at
 * @property string|null $last_login_ip
 * @property string|null $registration_ip
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $gdpr_consent_at
 * @property string|null $gdpr_deleted_at
 *
 * @property-read bool $isAdmin
 * @property-read bool $isBlocked
 * @property-read bool $isConfirmed
 * @property-read Profile $profile
 * @property-read Token[] $tokens
 */
class User extends ActiveRecord implements IdentityInterface, UserInterface
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_BLOCKED = 'blocked';

    /**
     * Plain password for validation and hashing.
     */
    public ?string $password = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     * @return UserQuery
     */
    public static function find(): UserQuery
    {
        return new UserQuery(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Email
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'message' => Yii::t('user', 'This email address has already been taken.')],

            // Username
            ['username', 'trim'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['username', 'match', 'pattern' => '/^[-a-zA-Z0-9_\.]+$/', 'message' => Yii::t('user', 'Username can only contain alphanumeric characters, underscores, hyphens, and dots.')],
            ['username', 'unique', 'message' => Yii::t('user', 'This username has already been taken.')],

            // Password
            ['password', 'string', 'min' => $this->getModule()->minPasswordLength, 'max' => $this->getModule()->maxPasswordLength],

            // Status
            ['status', 'in', 'range' => [self::STATUS_PENDING, self::STATUS_ACTIVE, self::STATUS_BLOCKED]],
            ['status', 'default', 'value' => self::STATUS_PENDING],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('user', 'ID'),
            'email' => Yii::t('user', 'Email'),
            'username' => Yii::t('user', 'Username'),
            'password' => Yii::t('user', 'Password'),
            'status' => Yii::t('user', 'Status'),
            'email_confirmed_at' => Yii::t('user', 'Email Confirmed'),
            'blocked_at' => Yii::t('user', 'Blocked At'),
            'last_login_at' => Yii::t('user', 'Last Login'),
            'registration_ip' => Yii::t('user', 'Registration IP'),
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

        if ($insert) {
            $this->auth_key = Yii::$app->security->generateRandomString(32);
            if (Yii::$app->request instanceof \yii\web\Request) {
                $this->registration_ip = Yii::$app->request->userIP;
            }
        }

        if (!empty($this->password)) {
            $this->password_hash = Password::hash($this->password, $this->getModule()->cost);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            $profile = new Profile(['user_id' => $this->id]);
            $profile->save(false);
        }
    }

    // IdentityInterface implementation

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id): ?static
    {
        return static::find()->active()->andWhere(['id' => $id])->one();
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null): ?static
    {
        throw new NotSupportedException('findIdentityByAccessToken is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    // UserInterface implementation

    /**
     * {@inheritdoc}
     */
    public function getIsAdmin(): bool
    {
        $module = $this->getModule();

        // Check RBAC permission first
        if ($module->adminPermission !== null && Yii::$app->authManager !== null) {
            if (Yii::$app->authManager->checkAccess($this->id, $module->adminPermission)) {
                return true;
            }
        }

        // Fallback to admins array (check by email)
        return in_array($this->email, $module->admins, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED || $this->blocked_at !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsConfirmed(): bool
    {
        return $this->email_confirmed_at !== null;
    }

    // Relations

    /**
     * Get user profile relation.
     */
    public function getProfile(): ActiveQuery
    {
        return $this->hasOne(Profile::class, ['user_id' => 'id']);
    }

    /**
     * Get user tokens relation.
     */
    public function getTokens(): ActiveQuery
    {
        return $this->hasMany(Token::class, ['user_id' => 'id']);
    }

    // Helper methods

    /**
     * Validate password against stored hash.
     */
    public function validatePassword(string $password): bool
    {
        return Password::validate($password, $this->password_hash);
    }

    /**
     * Find user by email.
     */
    public static function findByEmail(string $email): ?static
    {
        return static::find()->where(['email' => $email])->one();
    }

    /**
     * Find user by username.
     */
    public static function findByUsername(string $username): ?static
    {
        return static::find()->where(['username' => $username])->one();
    }

    /**
     * Find user by email or username.
     */
    public static function findByEmailOrUsername(string $login): ?static
    {
        return static::find()
            ->where(['or', ['email' => $login], ['username' => $login]])
            ->one();
    }

    /**
     * Confirm user email.
     */
    public function confirm(): bool
    {
        $this->status = self::STATUS_ACTIVE;
        $this->email_confirmed_at = new Expression('NOW()');

        return $this->save(false, ['status', 'email_confirmed_at']);
    }

    /**
     * Block user.
     */
    public function block(): bool
    {
        $this->status = self::STATUS_BLOCKED;
        $this->blocked_at = new Expression('NOW()');
        $this->auth_key = Yii::$app->security->generateRandomString(32);

        return $this->save(false, ['status', 'blocked_at', 'auth_key']);
    }

    /**
     * Unblock user.
     */
    public function unblock(): bool
    {
        $this->status = $this->email_confirmed_at !== null ? self::STATUS_ACTIVE : self::STATUS_PENDING;
        $this->blocked_at = null;

        return $this->save(false, ['status', 'blocked_at']);
    }

    /**
     * Update last login information.
     */
    public function updateLastLogin(): bool
    {
        $this->last_login_at = new Expression('NOW()');
        if (Yii::$app->request instanceof \yii\web\Request) {
            $this->last_login_ip = Yii::$app->request->userIP;
        }

        return $this->save(false, ['last_login_at', 'last_login_ip']);
    }

    /**
     * Reset password.
     */
    public function resetPassword(string $password): bool
    {
        $this->password_hash = Password::hash($password, $this->getModule()->cost);

        return $this->save(false, ['password_hash']);
    }

    /**
     * Get the user module instance.
     */
    protected function getModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        return $module;
    }
}
