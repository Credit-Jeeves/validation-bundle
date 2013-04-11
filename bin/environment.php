<?php

use Hat\Environment\Environment;

$libRoot = realpath(__DIR__ . '/../vendor/hat/environment') . '/';
require_once $libRoot . 'autoload.php';

$environment = new Environment();
$environment->getKit()->apply(
    array(
        'default.profile.name' => 'data/environment/profiles.ini'
    )
);
$environment();
