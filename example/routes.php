<?php
return [
    [
        'uri' => '/',
        'action' => [DemoController::class, 'index'],
        'method' => 'GET',
        'middleware' => [],
        'sitemap' => true,
        'visibility' => 'live' // Sichtbarkeit auf "live"
    ],
    [
        'uri' => '/hello',
        'action' => [DemoController::class, 'hello'],
        'method' => 'GET',
        'middleware' => [],
        'sitemap' => false,
        'visibility' => 'staging' // Sichtbarkeit auf "staging"
    ],
    [
        'uri' => '/mellow',
        'action' => [DemoController::class, 'mellow'],
        'method' => 'GET',
        'middleware' => [],
        'sitemap' => true,
        'visibility' => 'live' // Sichtbarkeit auf "live"
    ],
];
