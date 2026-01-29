<?php

declare(strict_types=1);

namespace cgsmith\user\services;

use cgsmith\user\events\SocialAuthEvent;
use cgsmith\user\models\SocialAccount;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use Yii;
use yii\authclient\ClientInterface;
use yii\db\Expression;

/**
 * Service for social authentication management.
 */
class SocialAuthService
{
    public const EVENT_BEFORE_LOGIN = 'beforeSocialLogin';
    public const EVENT_AFTER_LOGIN = 'afterSocialLogin';
    public const EVENT_BEFORE_REGISTER = 'beforeSocialRegister';
    public const EVENT_AFTER_REGISTER = 'afterSocialRegister';
    public const EVENT_BEFORE_CONNECT = 'beforeSocialConnect';
    public const EVENT_AFTER_CONNECT = 'afterSocialConnect';
    public const EVENT_BEFORE_DISCONNECT = 'beforeSocialDisconnect';
    public const EVENT_AFTER_DISCONNECT = 'afterSocialDisconnect';

    public function __construct(
        private readonly Module $module
    ) {
    }

    /**
     * Handle OAuth callback.
     */
    public function handleCallback(ClientInterface $client): ?User
    {
        $attributes = $client->getUserAttributes();
        $provider = $client->getId();
        $providerId = (string) ($attributes['id'] ?? '');

        if (empty($providerId)) {
            return null;
        }

        $account = SocialAccount::findByProviderAndId($provider, $providerId);

        if ($account !== null && $account->user !== null) {
            return $this->login($account, $client);
        }

        if (!Yii::$app->user->isGuest) {
            return $this->connect(Yii::$app->user->identity, $client, $attributes);
        }

        if (!$this->module->enableSocialRegistration) {
            return null;
        }

        return $this->register($client, $attributes);
    }

    /**
     * Login via social account.
     */
    protected function login(SocialAccount $account, ClientInterface $client): ?User
    {
        $user = $account->user;

        if ($user === null || $user->getIsBlocked()) {
            return null;
        }

        $event = new SocialAuthEvent([
            'user' => $user,
            'account' => $account,
            'client' => $client,
            'type' => SocialAuthEvent::TYPE_LOGIN,
        ]);
        $this->module->trigger(self::EVENT_BEFORE_LOGIN, $event);

        if (Yii::$app->user->login($user, $this->module->rememberFor)) {
            $user->updateLastLogin();
            $this->module->trigger(self::EVENT_AFTER_LOGIN, $event);
            return $user;
        }

        return null;
    }

    /**
     * Register new user via social account.
     */
    protected function register(ClientInterface $client, array $attributes): ?User
    {
        $provider = $client->getId();
        $providerId = (string) ($attributes['id'] ?? '');
        $email = $attributes['email'] ?? null;
        $username = $attributes['login'] ?? $attributes['username'] ?? null;

        $account = new SocialAccount([
            'provider' => $provider,
            'provider_id' => $providerId,
            'email' => $email,
            'username' => $username,
            'data' => json_encode($attributes),
        ]);

        $event = new SocialAuthEvent([
            'account' => $account,
            'client' => $client,
            'type' => SocialAuthEvent::TYPE_REGISTER,
        ]);
        $this->module->trigger(self::EVENT_BEFORE_REGISTER, $event);

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $user = new User();
            $user->email = $email ?: $this->generatePlaceholderEmail($provider, $providerId);
            $user->username = $this->generateUniqueUsername($username);
            $user->password = Yii::$app->security->generateRandomString(16);
            $user->status = User::STATUS_ACTIVE;
            $user->email_confirmed_at = $email ? new Expression('NOW()') : null;

            if (!$user->save()) {
                $transaction->rollBack();
                return null;
            }

            $account->user_id = $user->id;
            if (!$account->save()) {
                $transaction->rollBack();
                return null;
            }

            $transaction->commit();

            $event->user = $user;
            $this->module->trigger(self::EVENT_AFTER_REGISTER, $event);

            if (Yii::$app->user->login($user, $this->module->rememberFor)) {
                $user->updateLastLogin();
            }

            return $user;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Social registration failed: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * Connect social account to existing user.
     */
    public function connect(User $user, ClientInterface $client, ?array $attributes = null): ?User
    {
        $attributes = $attributes ?? $client->getUserAttributes();
        $provider = $client->getId();
        $providerId = (string) ($attributes['id'] ?? '');

        if (empty($providerId)) {
            return null;
        }

        $existingAccount = SocialAccount::findByProviderAndId($provider, $providerId);
        if ($existingAccount !== null && $existingAccount->user_id !== $user->id) {
            return null;
        }

        $account = SocialAccount::find()
            ->byUser($user->id)
            ->byProvider($provider)
            ->one();

        if ($account === null) {
            $account = new SocialAccount([
                'user_id' => $user->id,
                'provider' => $provider,
            ]);
        }

        $account->provider_id = $providerId;
        $account->email = $attributes['email'] ?? null;
        $account->username = $attributes['login'] ?? $attributes['username'] ?? null;
        $account->data = json_encode($attributes);

        $event = new SocialAuthEvent([
            'user' => $user,
            'account' => $account,
            'client' => $client,
            'type' => SocialAuthEvent::TYPE_CONNECT,
        ]);
        $this->module->trigger(self::EVENT_BEFORE_CONNECT, $event);

        if ($account->save()) {
            $this->module->trigger(self::EVENT_AFTER_CONNECT, $event);
            return $user;
        }

        return null;
    }

    /**
     * Disconnect social account from user.
     */
    public function disconnect(User $user, int $accountId): bool
    {
        $account = SocialAccount::find()
            ->byUser($user->id)
            ->andWhere(['id' => $accountId])
            ->one();

        if ($account === null) {
            return false;
        }

        $event = new SocialAuthEvent([
            'user' => $user,
            'account' => $account,
            'type' => SocialAuthEvent::TYPE_DISCONNECT,
        ]);
        $this->module->trigger(self::EVENT_BEFORE_DISCONNECT, $event);

        if ($account->delete()) {
            $this->module->trigger(self::EVENT_AFTER_DISCONNECT, $event);
            return true;
        }

        return false;
    }

    /**
     * Get connected social accounts for a user.
     *
     * @return SocialAccount[]
     */
    public function getConnectedAccounts(User $user): array
    {
        return SocialAccount::find()
            ->byUser($user->id)
            ->orderBy(['provider' => SORT_ASC])
            ->all();
    }

    /**
     * Generate a placeholder email for users without email from social provider.
     */
    protected function generatePlaceholderEmail(string $provider, string $providerId): string
    {
        return $provider . '_' . $providerId . '@social.local';
    }

    /**
     * Generate a unique username.
     */
    protected function generateUniqueUsername(?string $baseUsername): ?string
    {
        if ($baseUsername === null) {
            return null;
        }

        $username = $baseUsername;
        $counter = 1;

        while (User::findByUsername($username) !== null) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Get available auth clients.
     */
    public function getAuthClients(): array
    {
        if (!Yii::$app->has('authClientCollection')) {
            return [];
        }

        return Yii::$app->get('authClientCollection')->getClients();
    }

    /**
     * Get a specific auth client by name.
     */
    public function getAuthClient(string $name): ?ClientInterface
    {
        if (!Yii::$app->has('authClientCollection')) {
            return null;
        }

        $collection = Yii::$app->get('authClientCollection');

        if (!$collection->hasClient($name)) {
            return null;
        }

        return $collection->getClient($name);
    }
}
