<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@uploads' => '@common/uploads',
    ],
    'timeZone' => 'Asia/Kolkata',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'notification' => [
            'class'=>'backend\components\NotificationComponent'
        ],
        'email' => [
            'class'=>'backend\components\EmailComponent'
        ],
        'mailer' => [
          'class'=>'common\components\Email'
        ],
        'sendGrid' => [
            'class' => 'wadeshuler\sendgrid\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => false,
            'apiKey' => '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',
        ],
        // 'sendGrid' => [
        //     'class' => 'bryglen\sendgrid\Mailer',
        //     'username' => 'apikey',
        //     'password' => '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',
        //     //'viewPath' => '@app/views/mail', // your view path here
        // ],
        'onesignal' => [
			'class' => '\rocketfirm\onesignal\OneSignal',
			'appId' => '146c8ca5-723e-4616-aaca-765389534403',
			'apiKey' => 'MzBkM2U5MzUtYWE1ZC00NGQwLWIyMDgtNDljMDliNzU4MmE0',
            // 'appIdWeb' => '28633676-3bf9-460d-bb1c-3de2aedec358',
			// 'apiKeyWeb' => 'NjkzMmE1MjMtNGE0Yy00OWJmLWJlNjEtNGRkMDEyMGI4MmI1',
		],
        'onesignalWeb' => [
			'class' => '\rocketfirm\onesignal\OneSignal',
			'appId' => 'e2849475-f8f6-4f05-988d-c42184dd5293',
			'apiKey' => 'ODJkMDFlNjMtZDY4YS00ZTgxLWE0ZmUtNTkyYWMzMWEzNDAx',
            // 'appIdWeb' => '28633676-3bf9-460d-bb1c-3de2aedec358',
			// 'apiKeyWeb' => 'NjkzMmE1MjMtNGE0Yy00OWJmLWJlNjEtNGRkMDEyMGI4MmI1',
		],
    ],
];