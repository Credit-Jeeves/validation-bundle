<?php

$libRoot = realpath(__DIR__ . '/../vendor/hat/environment') . '/';
require_once $libRoot . 'autoload.php';

use Hat\Environment\Request\CliRequest;
use Hat\Environment\Environment;

$env = new Environment();

$env->handle(
    new CliRequest(
        array(
            'profile' => 'data/environment/profiles.ini'
        )
    )
);
