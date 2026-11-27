<?php

declare(strict_types=1);

return [
    'components' => [
        'user' => [
            'class' => yii\web\User::class,
            'identityClass' => cgsmith\user\models\User::class,
        ],
    ],
    'modules' => [
        'user' => [
            'class' => cgsmith\user\Module::class,
        ],
    ],
];
