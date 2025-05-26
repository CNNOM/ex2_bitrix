<?php
return [
    'config_options' => [
        'value' => [
            'config_options' => 3600.0,
        ],
        'readonly' => false,
    ],
    'cookies' => [
        'value' => [
            'secure' => false,
            'http_only' => true,
        ],
        'readonly' => false,
    ],
    'exception_handling' => [
        'value' => [
            'debug' => true,
            'handled_errors_types' => 4437,
            'exception_errors_types' => 4437,
            'ignore_silence' => false,
            'assertion_throws_exception' => true,
            'assertion_error_type' => E_USER_ERROR,
            'log' => null, 
        ],
        'readonly' => false,
    ],
    'connections' => [
        'value' => [
            'default' => [
                'host' => 'localhost',
                'database' => 'test1',
                'login' => 'test1',
                'password' => 'tV4aJ3zW7anX3eL3',
                'options' => 2.0,
                'className' => '\\Bitrix\\Main\\DB\\MysqliConnection',
            ],
        ],
        'readonly' => true,
    ],
    'crypto' => [
        'value' => [
            'crypto_key' => 'd83e74431e6c1977e6a4f3a386c04afc',
        ],
        'readonly' => true,
    ],
    'messenger' => [
        'value' => [
            'run_mode' => null,
            'brokers' => [
                'default' => [
                    'type' => 'db',
                    'params' => [
                        'table' => 'Bitrix\\Main\\Messenger\\Internals\\Storage\\Db\\Model\\MessengerMessageTable',
                    ],
                ],
            ],
            'queues' => [],
        ],
        'readonly' => true,
    ],
];