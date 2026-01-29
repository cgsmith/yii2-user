<?php

declare(strict_types=1);

namespace cgsmith\user\widgets;

use cgsmith\user\Module;
use cgsmith\user\services\SocialAuthService;
use Yii;
use yii\authclient\widgets\AuthChoice;
use yii\base\Widget;

/**
 * Widget for displaying social authentication buttons.
 *
 * Usage:
 * ```php
 * <?= SocialConnect::widget() ?>
 * ```
 */
class SocialConnect extends Widget
{
    public bool $popupMode = true;
    public bool $autoRender = true;
    public array $options = [];

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        if (!$module->enableSocialAuth) {
            return '';
        }

        /** @var SocialAuthService $socialAuthService */
        $socialAuthService = Yii::$container->get(SocialAuthService::class);
        $clients = $socialAuthService->getAuthClients();

        if (empty($clients)) {
            return '';
        }

        return AuthChoice::widget([
            'baseAuthUrl' => ['/' . $module->urlPrefix . '/auth'],
            'popupMode' => $this->popupMode,
            'autoRender' => $this->autoRender,
            'options' => $this->options,
        ]);
    }
}
