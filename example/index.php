<?php
require __DIR__ . '/../src/HazelRouter.php';
require __DIR__ . '/DemoController.php';
require __DIR__ . '/DemoMiddleware.php';

// Basic Router
$router = new JayPiii\HazelRouter();
$routerPath = __DIR__ . '/routes.php'; // ==> DEMO ROUTES
$router->createSitemap('/sitemap.xml', 'http://hazel-router.test');
$router->loadRoutes($routerPath);
$router->middleware('myMiddleware', [DemoMiddleware::class, 'index']);
$router->run();

if ($router->displayErrors() !== null) {
    echo $router->displayErrors();
}
