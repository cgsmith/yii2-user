<?php

declare(strict_types=1);

namespace cgsmith\user\widgets;

use cgsmith\user\models\LoginForm;
use cgsmith\user\Module;
use Yii;
use yii\base\Widget;

/**
 * Login widget for embedding login forms.
 */
class Login extends Widget
{
    /**
     * View file to render.
     */
    public string $view = 'login';

    /**
     * Whether to validate via AJAX.
     */
    public bool $enableAjaxValidation = false;

    /**
     * Form action URL. Defaults to login action.
     */
    public ?string $action = null;

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        /** @var LoginForm $model */
        $model = $module->createModel('LoginForm');

        return $this->render($this->view, [
            'model' => $model,
            'module' => $module,
            'enableAjaxValidation' => $this->enableAjaxValidation,
            'action' => $this->action ?? ['/user/security/login'],
        ]);
    }
}
