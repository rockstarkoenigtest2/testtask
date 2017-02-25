<?php

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Flash\Session as FlashSession;

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

$loader = new Loader();
$loader->registerDirs(
    [
        "../app/controllers/",
        "../app/models/",
        "../app/library/",
    ]
);
$loader->register();

$di = new FactoryDefault();
$di->set(
    "view",
    function () {
        $view = new View();
        $view->setViewsDir("../app/views/");
        return $view;
    });

$di->set(
    "url",
    function () {
        $url = new UrlProvider();
        $url->setBaseUri("/testtask/");
        return $url;
    }
);

$di->set("flashSession", function () {
    $flashSession = new FlashSession();
    $flashSession->setCssClasses(array(
        'error' => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice' => 'alert alert-info'
    ));
    return $flashSession;
});

$di->set(
    "db",
    function () {
        return new DbAdapter(
            [
                "host"     => "localhost",
                "username" => "root",
                "password" => "333333",
                "dbname"   => "testtask2_db",
            ]
        );
    }
);

include APP_PATH . "/config/services.php";
$config = $di->getConfig();

$di->set('mailer', function() use ($config) {
    include BASE_PATH . '/vendor/swiftmailer/swiftmailer/lib/swift_required.php';

    $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 587)
        ->setEncryption('ssl')
        ->setUsername('rockstar.koenig.test2@gmail.com')
        ->setPassword("ZXadqr243");
    $mailer = Swift_Mailer::newInstance($transport);
    return $mailer;
});

include APP_PATH . "/config/loader.php";
$application = new \Phalcon\Mvc\Application($di);

$response = $application->handle();
$response->send();
