<?php
return [
    'settings' => [
        // View settings
        'view' => [
            'template_path' => __DIR__ . '/templates',
            'twig' => [
                'cache' => __DIR__ . '/../cache/twig',
                'debug' => true,
                'auto_reload' => true,
            ],
        ],

        // monolog settings
        'logger' => [
            'name' => 'app',
            'path' => __DIR__ . '/../log/app.log',
        ],

        // bbs's log settings
        'log' => [
            'name' => 'log.json',
            'path' => __DIR__ . '/../dat/log.json',
            'past' => __DIR__ . '/../dat/past',
            'max'  => 100,
        ],

        // bbs's configuration settings
        'config' => [
            'path' => __DIR__ . '/../config/config.json',
        ],

        // login settings
        'auth' => [
            'id' => 'admin',
            'password' => '$2y$10$eiI5uJPCp6WBd/urzxhiNe2wcJgLtiGa0DNzJVVWZ6Q0.XWh8evhG',
        ],
    ],
];
