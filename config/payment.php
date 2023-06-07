<?php

return [
    'active_gateways' => ['zarinpal', 'mellat'],

    'gateways' => [
        'zarinpal' => [
            'merchant_id' => '1344b5d4-0048-11e8-94db-005056a205be',
            'request_url' => 'https://api.zarinpal.com/pg/v4/payment/request.json',
            'verify_url' => 'https://api.zarinpal.com/pg/v4/payment/verify.json',
            'pay_url' => 'https://www.zarinpal.com/pg/StartPay/' // then put authority code
        ],
        'mellat' => [
            'terminal_id' => '',
            'username' => '',
            'password' => '',
            'request_url' => 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl',
            'verify_url' => 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl',
            'pay_url' => 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat'
        ]
    ]
];
