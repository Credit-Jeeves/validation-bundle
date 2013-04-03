<?php

$libRoot = realpath(__DIR__ . '/../vendor/hat/environment') . '/';
require_once $libRoot . 'autoload.php';

$configs = require $libRoot . 'src/Hat/Environment/Environment.config.php';
$configs['default.profile.name'] = 'data/environment/profiles.ini';

$environment = new Hat\Environment\Environment(new \Hat\Environment\Kit\Kit($configs));
$environment();
