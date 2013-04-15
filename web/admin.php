<?php
require_once(dirname(__FILE__).'/../vendor/CreditJeevesSf1/config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('admin', 'prod', false);
cjContext::createInstance($configuration)->dispatch();
