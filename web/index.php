<?php

use Silex\Application;

require_once __DIR__.'/../vendor/autoload.php';

$app = new Application();

$app->get('/hello/{name}', function ($name) use ($app) {
    return 'Hello '.$app->escape($name);
});

$app->run();

