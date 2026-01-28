<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use cgsmith\user\models\query\SessionQuery;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * User session ActiveRecord model.
 *
 * @property int $id
 * @property int $user_id
 * @property string $session_id
 * @property string|null $ip
 * @property string|null $user_agent
 * @property string|null $device_name
 * @property string $last_activity_at
 * @property string $created_at
 *
 * @property-read User $user
 * @property-read bool $isCurrent
 */
class Session extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user_session}}';
    }

    /**
     * {@inheritdoc}
     * @return SessionQuery
     */
    public static function find(): SessionQuery
    {
        return new SessionQuery(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'session_id', 'last_activity_at'], 'required'],
            [['user_id'], 'integer'],
            [['session_id'], 'string', 'max' => 128],
            [['ip'], 'string', 'max' => 45],
            [['device_name'], 'string', 'max' => 255],
            [['user_agent'], 'string'],
            [['session_id'], 'unique'],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
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
            'session_id' => Yii::t('user', 'Session ID'),
            'ip' => Yii::t('user', 'IP Address'),
            'user_agent' => Yii::t('user', 'User Agent'),
            'device_name' => Yii::t('user', 'Device'),
            'last_activity_at' => Yii::t('user', 'Last Activity'),
            'created_at' => Yii::t('user', 'Created At'),
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
     * Check if this is the current session.
     */
    public function getIsCurrent(): bool
    {
        return $this->session_id === Yii::$app->session->id;
    }

    /**
     * Parse user agent to determine device name.
     */
    public static function parseDeviceName(?string $userAgent): string
    {
        if ($userAgent === null) {
            return Yii::t('user', 'Unknown Device');
        }

        $userAgent = strtolower($userAgent);

        $os = 'Unknown OS';
        if (str_contains($userAgent, 'windows')) {
            $os = 'Windows';
        } elseif (str_contains($userAgent, 'macintosh') || str_contains($userAgent, 'mac os')) {
            $os = 'macOS';
        } elseif (str_contains($userAgent, 'linux')) {
            $os = 'Linux';
        } elseif (str_contains($userAgent, 'android')) {
            $os = 'Android';
        } elseif (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad')) {
            $os = 'iOS';
        }

        $browser = 'Unknown Browser';
        if (str_contains($userAgent, 'edg/') || str_contains($userAgent, 'edge/')) {
            $browser = 'Edge';
        } elseif (str_contains($userAgent, 'chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'safari') && !str_contains($userAgent, 'chrome')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'opera') || str_contains($userAgent, 'opr/')) {
            $browser = 'Opera';
        }

        return "{$browser} on {$os}";
    }
}
