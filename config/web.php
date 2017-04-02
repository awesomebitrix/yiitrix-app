<?php

function dbg($v) {
    while (@ob_end_clean()) {}
    echo '<pre>' . print_r($v, true) . '</pre>';
    die();
}

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'language' => 'ru-RU',
    'sourceLanguage' => 'ru-RU',
    'basePath' => __DIR__ . '/../',
    'bootstrap' => ['log'],
    'components' => [
        'assetManager' => [
            'basePath' => '@app/assets'
        ],

        'response' => [
            'class' => 'app\yiitrix\components\BitrixResponse',
        ],

        'request' => [
            'cookieValidationKey' => '',
            'enableCsrfValidation' => false,
        ],

        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'fileMap' => [
                        'app' => 'app.php'
                    ],
                ],
            ],
        ],

        'formatter' => [
            'sizeFormatBase' => 1000
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'suffix' => '/',
            'rules' => [

            ],
        ],

        'user' => [
            'identityClass' => 'app\models\User',
        ],

        'errorHandler' => [
            'class' => 'app\yiitrix\components\BitrixErrorHandler',
            'discardExistingOutput' => false,
            'errorAction' => 'site/error',
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                ],
            ],
        ],
        //'db' => require(__DIR__ . '/db.php'),
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
