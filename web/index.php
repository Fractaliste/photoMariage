<?php

use Raphdine\LoggerProvider;
use Raphdine\RouteProvider;
use Silex\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$app['debug'] = function() use ($app) {
    return $app['request_context']->getHost() === 'localhost';
};

$app->register(new LoggerProvider());
$app->register(new RouteProvider());

$app->run();

