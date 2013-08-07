<?php
require_once(dirname(__FILE__).'/../vendor/credit-jeeves/credit-jeeves/config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('dealer', 'prod', false);
cjContext::createInstance($configuration)->dispatch();
