<?php
/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */

require_once(dirname(__FILE__).'/../vendor/CreditJeevesSf1/config/ProjectConfiguration.class.php');
$configuration = ProjectConfiguration::getApplicationConfiguration(
    isset($application) ? $application : 'applicant',
    isset($environment) ? $environment : 'test_cli',
    isset($debug) ? $debug : true
);
cjContext::createInstance($configuration)->dispatch();
