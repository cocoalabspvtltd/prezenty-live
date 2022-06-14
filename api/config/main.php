<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);


return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => [ 
          'v1' => [
              'basePath' => '@api/modules/v1',
              'class' => 'api\modules\v1\Module'   // here is our v1 modules
          ],
      ],
    'defaultRoute' => 'site/index',
    'components' => [
      'user' => [
          'identityClass' => 'api\modules\v1\models\Account',
          'enableAutoLogin' => true,
          'enableSession' => true,
          'identityCookie' => ['name' => '_identity-api', 'httpOnly' => true],
      ],
      'urlManager' => [
        'enablePrettyUrl' => true,
        'enableStrictParsing' => false, //if true users/ will throw error and users will work
        'showScriptName' => false,
        'rules' => [
          // [
          //   'class' => 'yii\rest\UrlRule',
          //   'controller' => 'v1/assembly-constituency',
          //   'extraPatterns' => [
          //     'GET assembly' => 'assembly-constituency/index',
          //   ]
          // ],

        

        ],
      ],
        'response' => [
            'format' => 'json'
        ],
        'request' => [
            'parsers' => [
              'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-api',
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
        'errorHandler' => [
            'errorAction' => '/v1/site/error',
        ],
		/* 'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                // ...
            ],
        ], */
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'facebook' => [
                    'class' => 'yii\authclient\clients\Facebook',
                    'clientId' => 'APP_CLIENT_ID',
                    'clientSecret' => 'APP_CLIENT_SECRET',
                    'attributeNames' => ['name', 'email', 'first_name', 'last_name'],
                ],
            ],
        ],
  ],
  'params' => $params,
];
