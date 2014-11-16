<?php

use Raphdine\LoggerProvider;
use Raphdine\RouteProvider;
use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$app['debug'] = function() use ($app) {
    return $app['request_context']->getHost() === 'localhost';
};

$app['photo.dir'] = __DIR__ . '/img/mariage/';
$app['photo.password'] = '06122014';

$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../src/vues',
));

$app->register(new LoggerProvider());
$app->register(new SessionServiceProvider());
$app->register(new RouteProvider());

$app->run();

