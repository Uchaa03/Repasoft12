<?php

return [
    'default' => env('MAIL_MAILER', 'sendgrid'),

    'mailers' => [
        'sendgrid' => [
            'transport' => 'sendgrid',
            'api_key' => env('SENDGRID_API_KEY'),
            'version' => 'v3',
            'endpoint' => 'https://api.sendgrid.com/v3/mail/send'
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => ['smtp', 'log'],
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => ['ses', 'postmark'],
        ]
    ],  // <-- ¡Este cierre estaba mal ubicado!

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'tecnico.repasoft@gmail.com'),
        'name' => env('MAIL_FROM_NAME', 'Repasoft'),
    ]
];

