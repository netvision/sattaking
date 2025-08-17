<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'Asia/Kolkata', // Change this to your timezone
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'lByNtxYKQ12PFoYQdXUzlj0NCIqmCc3Q',
            'enableCsrfValidation' => false, // Disable CSRF for API
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false, // Disable for API
            'enableSession' => false, // Disable sessions for API
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                // Authentication routes
                'POST auth/login' => 'auth/login',
                'POST auth/logout' => 'auth/logout',
                'GET auth/me' => 'auth/me',
                'OPTIONS auth/<action>' => 'auth/options',
                
                // Slot routes
                'GET slots' => 'slot/index',
                'GET slots/today' => 'slot/today',
                'POST slots' => 'slot/create',
                'PUT slots/<id:\d+>' => 'slot/update',
                'DELETE slots/<id:\d+>' => 'slot/delete',
                'GET slots/<id:\d+>' => 'slot/view',
                'OPTIONS slots' => 'slot/options',
                'OPTIONS slots/<action>' => 'slot/options',
                
                // Result routes
                'GET results' => 'result/index',
                'GET results/today' => 'result/today',
                'GET results/archive' => 'result/archive',
                'GET results/latest' => 'result/latest',
                'POST results' => 'result/create',
                'PUT results/<id:\d+>' => 'result/update',
                'POST results/<id:\d+>/lock' => 'result/lock',
                'GET results/<id:\d+>' => 'result/view',
                'OPTIONS results' => 'result/options',
                'OPTIONS results/<action>' => 'result/options',
                
                // Info routes
                'GET info' => 'info/index',
                'GET info/status' => 'info/status',
                'OPTIONS info' => 'info/options',
                'OPTIONS info/<action>' => 'info/options',
                
                // User routes
                'GET users' => 'user/index',
                'POST users' => 'user/create',
                'PUT users/<id:\d+>' => 'user/update',
                'DELETE users/<id:\d+>' => 'user/delete',
                'POST users/<id:\d+>/toggle-status' => 'user/toggle-status',
                'POST users/<id:\d+>/reset-password' => 'user/reset-password',
                'GET users/<id:\d+>' => 'user/view',
                'OPTIONS users' => 'user/options',
                'OPTIONS users/<action>' => 'user/options',
                'OPTIONS users/<id:\d+>/toggle-status' => 'user/options',
                'OPTIONS users/<id:\d+>/reset-password' => 'user/options',
                
                // Default routes
                '' => 'site/index',
            ],
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
