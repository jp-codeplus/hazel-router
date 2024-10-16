<?php
return [
    [
        'uri' => '/',
        'action' => [DemoController::class, 'index'],
        'method' => 'GET',
        'middleware' => ['myMiddleware'],
        'sitemap' => true,
        'visibility' => 'live'
    ],
    [
        'uri' => '/hello',
        'action' => [DemoController::class, 'hello'],
        'method' => 'GET',
        'middleware' => [],
        'sitemap' => false,
        'visibility' => 'staging' 
    ],
    [
        'uri' => '/mellow',
        'action' => [DemoController::class, 'mellow'],
        'method' => 'GET',
        'middleware' => [],
        'sitemap' => true,
        'visibility' => 'live'
    ],
];
