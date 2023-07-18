<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [
    'smsPanel' => [
        'username' => env('SMS_PANEL_USERNAME', 'username'),
        'pass' => env('SMS_PANEL_PASSWORD', 'pass'),
        'number' => env('SMS_PANEL_NUMBER', '100001'),
    ]
];
