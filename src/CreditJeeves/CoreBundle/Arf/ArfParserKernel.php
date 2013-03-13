<?php
namespace CreditJeeves\CoreBundle\Arf;

$sPath =  dirname(dirname(dirname(dirname(__DIR__)))).'/vendor/CreditJeevesSf1/lib/experian/parser/ArfParserKernel.class.php';
require_once $sPath;

class ArfParserKernel extends \ArfParserKernel
{
}
