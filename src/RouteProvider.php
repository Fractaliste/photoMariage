<?php

namespace Raphdine;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of RouteProvider
 *
 * @author Raphiki
 */
class RouteProvider implements ServiceProviderInterface {

    public function register(Application $app) {


        $app->get('/', function (Request $request) use ($app) {
            return 'Bienvenue, ce site sera prochainement disponible !'.$request->getClientIp();
        });
    }

    public function boot(Application $app) {
        
    }
}
