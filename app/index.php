<?php
$config = require_once __DIR__.'/../config/main.php';
require __DIR__.'/../lib/slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

require __DIR__.'/../lib/slim-extras/Views/Mustache.php';
\Slim\Extras\Views\Mustache::$mustacheDirectory = __DIR__.'/../lib/mustache/src/Mustache';

$app = new \Slim\Slim(array(
    'view' => new \Slim\Extras\Views\Mustache()
));
$app->setName('Web application');

$app->get('/', function () use ($app) {
    $app->render('layout.php', array(
        'name'  => $app->getName(),
    ));
});
$app->run();
