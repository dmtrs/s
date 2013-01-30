<?php
require __DIR__.'/../lib/slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->setName('Web application');

$app->get('/', function () use ($app) {
    $app->render('layout.php', array(
        'name'  => $app->getName(),
    ));
});
$app->run();
