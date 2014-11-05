<?php

namespace Raphdine;

use Silex\Application;
use Silex\Provider\MonologServiceProvider;

/**
 * Description of loggerProvider
 *
 * @author Raphiki
 */
class LoggerProvider extends MonologServiceProvider {

    public function register(Application $app) {
        parent::register($app);

        $app['monolog.logfile'] = __DIR__ . '/../temp/log/raphdine.log';
    }

}
