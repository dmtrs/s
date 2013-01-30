<?php
$config = require_once __DIR__.'/../config/main.php';
require __DIR__.'/../lib/slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->setName('Web application');

$app->get('/', function () use ($app) {
    $app->render('layout.php', array(
        'name'  => $app->getName(),
    ));
});
$app->get('/:blog', function($blog) use ($app) {
    var_dump($blog);
})->conditions(array(
    'blog' => '\w+\.tumblr\.com'
));
$app->run();
