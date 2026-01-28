<?php

declare(strict_types=1);

namespace cgsmith\user\filters;

use cgsmith\user\models\User;
use cgsmith\user\Module;
use cgsmith\user\services\GdprService;
use Yii;
use yii\base\ActionFilter;
use yii\web\Response;

/**
 * Filter that ensures users have given GDPR consent.
 */
class GdprConsentFilter extends ActionFilter
{
    public ?string $consentRoute = null;

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action): bool
    {
        /** @var Module|null $module */
        $module = Yii::$app->getModule('user');

        if ($module === null || !$module->enableGdprConsent) {
            return true;
        }

        if (Yii::$app->user->isGuest) {
            return true;
        }

        /** @var GdprService $gdprService */
        $gdprService = Yii::$container->get(GdprService::class);

        $route = Yii::$app->requestedRoute;
        if ($gdprService->isRouteExempt($route)) {
            return true;
        }

        /** @var User $user */
        $user = Yii::$app->user->identity;

        if (!$gdprService->needsConsentUpdate($user)) {
            return true;
        }

        $consentRoute = $this->consentRoute ?? '/' . $module->urlPrefix . '/gdpr/consent';
        Yii::$app->response->redirect($consentRoute);
        Yii::$app->end();

        return false;
    }
}
