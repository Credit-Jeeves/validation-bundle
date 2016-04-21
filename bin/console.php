<?php

// if you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

set_time_limit(0);
if (function_exists('xdebug_disable')) {
    xdebug_disable();
}
require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/AppKernel.php';
require_once __DIR__.'/../app/AppCjKernel.php';
require_once __DIR__.'/../app/AppRjKernel.php';

// use Symfony\Bundle\FrameworkBundle\Console\Application;
use JMS\JobQueueBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;

$input = new ArgvInput();
$env = $input->getParameterOption(array('--env', '-e'), getenv('SYMFONY_ENV') ?: 'cli');
$debug = getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(array('--no-debug', '')) && $env !== 'prod';

if ($debug) {
    \Symfony\Component\Debug\Debug::enable();
}

$app = $input->getParameterOption(array('--app', '-a'), 'cj' ?: 'rj');
switch ($app) {
    case 'rj':
        $kernel = new AppRjKernel($env, $debug);
        break;
    default:
        $kernel = new AppCjKernel($env, $debug);
        break;
}
$application = new Application($kernel);
$application->getDefinition()->addOption(new InputOption('--app', '-a', InputOption::VALUE_REQUIRED, 'Application'));
$application->run($input);
