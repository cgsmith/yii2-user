<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\models\query\ProfileQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Profile ActiveRecord model.
 *
 * @property int $user_id
 * @property string|null $name
 * @property string|null $bio
 * @property string|null $location
 * @property string|null $website
 * @property string|null $timezone
 * @property string|null $avatar_path
 * @property string|null $gravatar_email
 * @property bool $use_gravatar
 * @property string|null $public_email
 * @property string $created_at
 * @property string $updated_at
 *
 * @property-read User $user
 * @property-read string|null $avatarUrl
 */
class Profile extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user_profile}}';
    }

    /**
     * {@inheritdoc}
     * @return ProfileQuery
     */
    public static function find(): ProfileQuery
    {
        return new ProfileQuery(static::class);
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
    public function rules(): array
    {
        return [
            [['name', 'location', 'public_email', 'gravatar_email'], 'string', 'max' => 255],
            [['website'], 'url'],
            [['bio'], 'string'],
            [['timezone'], 'string', 'max' => 40],
            [['timezone'], 'in', 'range' => \DateTimeZone::listIdentifiers()],
            [['use_gravatar'], 'boolean'],
            [['use_gravatar'], 'default', 'value' => true],
            [['public_email', 'gravatar_email'], 'email'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'user_id' => Yii::t('user', 'User'),
            'name' => Yii::t('user', 'Name'),
            'bio' => Yii::t('user', 'Bio'),
            'location' => Yii::t('user', 'Location'),
            'website' => Yii::t('user', 'Website'),
            'timezone' => Yii::t('user', 'Timezone'),
            'avatar_path' => Yii::t('user', 'Avatar'),
            'gravatar_email' => Yii::t('user', 'Gravatar Email'),
            'use_gravatar' => Yii::t('user', 'Use Gravatar'),
            'public_email' => Yii::t('user', 'Public Email'),
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
     * Get avatar URL.
     */
    public function getAvatarUrl(int $size = 200): ?string
    {
        // Local avatar takes precedence
        if (!empty($this->avatar_path)) {
            $module = Yii::$app->getModule('user');
            return Yii::getAlias($module->avatarUrl) . '/' . $this->avatar_path;
        }

        // Gravatar fallback
        if ($this->use_gravatar) {
            $email = $this->gravatar_email ?? $this->user->email ?? '';
            return $this->getGravatarUrl($email, $size);
        }

        return null;
    }

    /**
     * Generate Gravatar URL.
     */
    public function getGravatarUrl(string $email, int $size = 200): string
    {
        $hash = md5(strtolower(trim($email)));

        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=identicon";
    }

    /**
     * Get timezone list for dropdown.
     */
    public static function getTimezoneList(): array
    {
        $timezones = [];
        $identifiers = \DateTimeZone::listIdentifiers();

        foreach ($identifiers as $identifier) {
            $timezones[$identifier] = str_replace('_', ' ', $identifier);
        }

        return $timezones;
    }
}
