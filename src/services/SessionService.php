<?php

declare(strict_types=1);

namespace cgsmith\user\services;

use cgsmith\user\models\Session;
use cgsmith\user\models\User;
use cgsmith\user\Module;
use Yii;
use yii\db\Expression;

/**
 * Service for managing user sessions.
 */
class SessionService
{
    public function __construct(
        private readonly Module $module
    ) {
    }

    /**
     * Create or update a session record for the user.
     */
    public function trackSession(User $user): ?Session
    {
        if (!$this->module->enableSessionHistory) {
            return null;
        }

        $sessionId = Yii::$app->session->id;
        if (empty($sessionId)) {
            return null;
        }

        $session = Session::find()
            ->bySessionId($sessionId)
            ->one();

        if ($session === null) {
            $session = new Session();
            $session->user_id = $user->id;
            $session->session_id = $sessionId;
            $session->created_at = new Expression('NOW()');
        }

        $request = Yii::$app->request;
        $session->ip = $request->userIP;
        $session->user_agent = $request->userAgent;
        $session->device_name = Session::parseDeviceName($request->userAgent);
        $session->last_activity_at = new Expression('NOW()');

        if ($session->save()) {
            $this->enforceSessionLimit($user);
            return $session;
        }

        Yii::error('Failed to track session: ' . json_encode($session->errors), __METHOD__);
        return null;
    }

    /**
     * Update last activity for current session.
     */
    public function updateActivity(User $user): bool
    {
        if (!$this->module->enableSessionHistory) {
            return true;
        }

        $sessionId = Yii::$app->session->id;
        if (empty($sessionId)) {
            return false;
        }

        return Session::updateAll(
            ['last_activity_at' => new Expression('NOW()')],
            ['session_id' => $sessionId, 'user_id' => $user->id]
        ) > 0;
    }

    /**
     * Get all sessions for a user.
     *
     * @return Session[]
     */
    public function getUserSessions(User $user): array
    {
        return Session::find()
            ->byUser($user->id)
            ->latestFirst()
            ->limit($this->module->sessionHistoryLimit)
            ->all();
    }

    /**
     * Terminate a specific session.
     */
    public function terminateSession(int $sessionId, User $user): bool
    {
        $session = Session::find()
            ->byUser($user->id)
            ->andWhere(['id' => $sessionId])
            ->one();

        if ($session === null) {
            return false;
        }

        if ($session->getIsCurrent()) {
            Yii::$app->user->logout();
        }

        return $session->delete() > 0;
    }

    /**
     * Terminate all sessions except the current one.
     */
    public function terminateOtherSessions(User $user): int
    {
        $currentSessionId = Yii::$app->session->id;

        return Session::deleteAll([
            'and',
            ['user_id' => $user->id],
            ['!=', 'session_id', $currentSessionId],
        ]);
    }

    /**
     * Terminate all sessions for a user.
     */
    public function terminateAllSessions(User $user): int
    {
        return Session::deleteAll(['user_id' => $user->id]);
    }

    /**
     * Remove session on logout.
     */
    public function removeCurrentSession(User $user): bool
    {
        if (!$this->module->enableSessionHistory) {
            return true;
        }

        $sessionId = Yii::$app->session->id;
        if (empty($sessionId)) {
            return false;
        }

        return Session::deleteAll([
            'session_id' => $sessionId,
            'user_id' => $user->id,
        ]) > 0;
    }

    /**
     * Enforce session limit by removing oldest sessions.
     */
    private function enforceSessionLimit(User $user): void
    {
        $limit = $this->module->sessionHistoryLimit;
        if ($limit <= 0) {
            return;
        }

        $sessions = Session::find()
            ->byUser($user->id)
            ->orderBy(['last_activity_at' => SORT_ASC])
            ->all();

        $toDelete = count($sessions) - $limit;
        if ($toDelete <= 0) {
            return;
        }

        $idsToDelete = [];
        for ($i = 0; $i < $toDelete; $i++) {
            if (!$sessions[$i]->getIsCurrent()) {
                $idsToDelete[] = $sessions[$i]->id;
            }
        }

        if (!empty($idsToDelete)) {
            Session::deleteAll(['id' => $idsToDelete]);
        }
    }

    /**
     * Clean up expired sessions (older than rememberFor duration).
     */
    public function cleanupExpiredSessions(): int
    {
        $expireTime = date('Y-m-d H:i:s', time() - $this->module->rememberFor);

        return Session::deleteAll(['<', 'last_activity_at', $expireTime]);
    }
}
