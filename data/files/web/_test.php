<?php
use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;

// start collect coverage from functional tests
//require_once  __DIR__ . '/../vendor/behat/mink-bundle/Behat/MinkBundle/Coverage/prepend.php';
if (function_exists('xdebug_disable')) {
    xdebug_disable();
}

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';
require_once __DIR__.'/../app/AppCjKernel.php';
require_once __DIR__.'/../app/AppCjTestKernel.php';

\Symfony\Component\Debug\Debug::enable();

$kernel = new AppCjTestKernel('test', true);
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);


//require 'PHPUnit/Extensions/SeleniumCommon/append.php';
