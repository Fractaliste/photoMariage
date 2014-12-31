<?php

namespace Raphdine;

use IronMQ;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Description of RouteProvider
 *
 * @author Raphiki
 */
class RouteProvider implements ServiceProviderInterface {

    public function register(Application $app) {

        $this->registerLoginAction($app);

        $app->get('/', function (Request $request) use ($app) {
            return $app['twig']->render('home.twig');
        });

        $app->get('/clear', function (Request $request) use ($app) {
            $app['session']->clear();
            return $app->redirect('/login');
        });

//        $app->get('/telecharger/{url}', function (Request $request, $url) use ($app) {
//            if ($url === 'all') {
//                $z = new File('../web/img/RaphDine.zip');
//            } else {
//                $all = false;
//                $f = new FileManager($url, $all);
//                $z = $f->getZippedPath();
//            }
//            return $app->sendFile($z)->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'MariageRaphDine.zip');
//        });

        $app->get('/telecharger/', function (Request $request) use ($app) {
            $f = new FileManager('');
            return $app['twig']->render('telecharger.twig', array(
                        'f' => $f->afficherRepertoire()
                        , 'path' => $f->getRelativePath()));
        });

        $app->get('/diaporama/run/{url}', function (Request $request, $url) use ($app) {
                    if (substr($url, -3) === 'all') {
                        $all = true;
                        $url = substr($url, null, count($url) - 4);
                    } else {
                        $all = false;
                    }
                    $o = new FileManager($url, $all);
                    return $app['twig']->render('run.diaporama.twig', array('urls' => $o->getUrls()));
                })
                ->assert('url', '.*');

        $app->get('/diaporama/{url}', function (Request $request, $url) use ($app) {
            $f = new FileManager($url);
            return $app['twig']->render('diaporama.twig', array(
                        'f' => $f->afficherRepertoire()
                        , 'path' => $f->getRelativePath()));
        })->assert('url', '.*');

        $app->get('/deposer', function (Request $request) use ($app) {
            return $app['twig']->render('deposer.twig');
        });

        $app->post('/deposer', function (Request $request) use ($app) {
            $photos = $request->files->get("photos");

            $error = array();
            foreach ($photos as $photo) {
                $dir = $app['photo.dir'] . $request->request->get('folder') . '/';
                $error[] = $app['photo.save']($photo, $dir);
            }
            $filepath = print_r($dir, true);
            return $app['twig']->render('deposer.twig', ['img' => $filepath]);
        })->after(function(Request $request, Response $response, Application $app) {
            $app['logger']->addDebug('On souhaite lancer IronMQ');
            $ironmq = new IronMQ(array(
                'token' => '3GBSTtKvdWY-keZN0y7Wh2Tnp9M',
                'project_id' => '54a134c35a03580007000055'
            ));
            $app['logger']->addDebug($request->get('folder'));
            $ironmq->postMessage("mariage_zip_photo", array('dir' => $request->get('folder')));
        });

        $app['photo.save'] = $app->protect(function ($photo, $dir) use ($app ) {
            $result = preg_match('#^(.*)(\.(jpg|jpeg|png|zip))$#i', $photo->getClientOriginalName(), $matches);
            if (!$result) {
                if ($result === false) {
                    $app['logger']->addDebug('Problème de regex pour gérer le fichier : "' . $photo->getClientOriginalName() . '", l\'erreur retournée est : ' . preg_last_error());
                } else {
                    return 'Le fichier "' . $photo->getClientOriginalName() . '" n\'a pas pu être téléchargé, vérifiez qu\'il comporte bien une des extensions autorisées.';
                }
            } elseif (strtoupper($photo->getClientOriginalExtension()) === 'ZIP') {
                $app['photo.save.zip']($photo, $dir);
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

        $app->post('/cron/zip', function (Request $request) use ($app) {
            $app['logger']->addDebug('Lancement du cron');
            $app['logger']->addDebug($request->get('dir'));

            $command = 'sh ' . __DIR__ . '/../cron.bash';
            $r = exec($command, $out);
            $app['logger']->addDebug('Fin du cron : ' . print_r($out, true));
            return $command . '<br/>' . $r . '<br/>' . print_r($out, true);
        });

        $app['photo.save.zip'] = $app->protect(function ($zip, $dir) use ($app ) {
            $app['logger']->addDebug('Fichier zip à sauvegarder');
        });
    }

    public function registerLoginAction(Application $app) {
        $app->before(function (Request $request, Application $app) {
            if ($app['session']->has('last_try')) {
                if (time() - $app['session']->get('last_try') < 15 * 60) {
                    $subRequest = Request::create('/login/erreur', 'GET');
                    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                } else {
                    $app['session']->clear();
                }
            }

            if (!in_array($request->getRequestUri(), ['/login', '/login/erreur', '/cron/zip']) && $app['session']->get('password') !== 'ok') {
                return $app->redirect('/login');
            }
        });

        $app->get('/login', function () use ($app) {
            return $app['twig']->render('password.twig');
        });

        $app->post('/login', function (Request $request) use ($app) {
            if ($request->get('password') === $app['photo.password']) {
                $app['session']->set('password', 'ok');
                return $app->redirect('/');
            } else {
                if ($app['session']->has('nombre_essai') && $app['session']->get('nombre_essai') > 3) {
                    $app['session']->set('last_try', time());
                    $subRequest = Request::create('/login/erreur', 'GET');
                    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                } else {
                    $app['session']->set('nombre_essai', $app['session']->get('nombre_essai') + 1);
                }
                return $app->redirect('/login');
            }
        });

        $app->get('/login/erreur', function () use ($app) {
            $t = ceil((15 * 60 - time() + $app['session']->get('last_try') ) / 60);
            return $app['twig']->render('to_many_try.twig', ['minutes' => $t]);
        });
    }

    public function boot(Application $app) {
        
    }

}
