<?php
require __DIR__.'/../src/HazelRouter.php';


class DemoController{
    public function index(){
        echo 'Hello Mellow!';
    }
    public function hello(){
        echo 'Whats GIT up you 🚀!';
    }

    public function mellow(){
        echo 'We love ❤️ Hazel & PHP';
    }
}

// Erstelle eine neue Instanz des Routers
$router = new HazelRouter();
$router->loadRoutes(__DIR__.'/routes.php');
$router->run();

if($router->displayErrors() !== null){
    echo $router->displayErrors();
}