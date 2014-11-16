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

        $app->before(function (Request $request, Application $app) {
            if ($request->getRequestUri() !== '/login' && $app['session']->get('password') !== 'ok') {
                return $app->redirect('/login');
            }
        });

        $app->get('/login', function () use ($app) {
            return $app['twig']->render('password.twig');
        });

        $app->post('/login', function (Request $request) use ($app) {
            if ($request->get('password') === '06122014') {
                $app['session']->set('password', 'ok');
                return $app->redirect('/');
            } else {
                return $app->redirect('/login');
            }
        });

        $app->get('/', function (Request $request) use ($app) {
            return $app['twig']->render('home.twig');
        });

        $app->get('/diaporama', function (Request $request) use ($app) {
            return $app['twig']->render('diaporama.twig');
        });

        $app->get('/telecharger', function (Request $request) use ($app) {
            return $app['twig']->render('telecharger.twig');
        });

        $app->get('/deposer', function (Request $request) use ($app) {
            return $app['twig']->render('deposer.twig');
        });

        $app->post('/deposer', function (Request $request) use ($app) {
            $photos = $request->files->get("photos");

            $error = array();
            foreach ($photos as $photo) {
                $dir = $app['photo.dir'] . $request->request->get('folder') . '/';
                $app['photo.save']($photo, $dir);
            }
            $filepath = print_r($dir, true);
            return $app['twig']->render('deposer.twig', ['img' => $filepath]);
        });

        $app['photo.save'] = $app->protect(function ($photo, $dir) use ($app ) {
            $result = preg_match('#^(.*)(\.(jpg|jpeg|png))$#i', $photo->getClientOriginalName(), $matches);
            if (!$result) {
                if ($result === false) {
                    $app['logger']->addDebug('Problème de regex pour gérer le fichier : "' . $photo->getClientOriginalName() . '", l\'erreur retournée est : ' . preg_last_error());
                } else {
                    $error[] = 'Le fichier "' . $photo->getClientOriginalName() . '" n\'a pas pu être téléchargé, vérifiez qu\'il comporte bien une des extensions autorisées.';
                }
            } else {
                $originalFileName = ucwords(strtolower($matches[1]));
                $fileName = $originalFileName;
                $i = 0;
                while (file_exists($dir . $fileName . strtolower($matches[2]))) {
                    $fileName = $originalFileName . '-' . ($i++);
                }
                $photo->move($dir, $fileName . strtolower($matches[2]));
            }
        });
    }

    public function boot(Application $app) {
        
    }

}
