<?php

define('XDEBUG', "xdebug");
define('ZEND_DEBUGGER', "Zend Debugger");

function createXmlHeader()
{
    return "<?xml version=\"1.0\"?>";
}

function createXmlElement($tagName, $attributes, $content = null)
{
    $result = "";
    $result .= "<{$tagName}";
    foreach ($attributes as $attributeName => $attributeValue) {
        $result .= " {$attributeName}=\"$attributeValue\"";
    }
    if (!empty($content)) {
        $result .= ">";
        $result .= $content;
        $result .= "</{$tagName}>";
    } else {
        $result .= "/>";
    }
    return $result;
}

function collectConfigurationFiles() {
    $files = array(php_ini_loaded_file());
    $scannedFiles = php_ini_scanned_files();
    if ($scannedFiles) {
        foreach (explode(',', $scannedFiles) as $file) {
            array_push($files, trim($file));
        }
    }
    return $files;
}

function validateXdebug() {
    $element = array();
    $element["name"] = XDEBUG;
    $element["zend_extension"] = isLoadByZendExtension($element);
    $element["version"] = htmlspecialchars(phpversion(XDEBUG));
    $element["enable"] = htmlspecialchars(ini_get("xdebug.remote_enable"));
    $element["host"] = htmlspecialchars(ini_get("xdebug.remote_host"));
    $element["port"] = htmlspecialchars(ini_get("xdebug.remote_port"));
    $element["mode"] = htmlspecialchars(ini_get("xdebug.remote_mode"));
    return $element;
}

function isLoadByZendExtension() {
    $warning = error_get_last();
    if (isset($warning) && is_array($warning) &&
        strcasecmp($warning["message"], "Xdebug MUST be loaded as a Zend extension") == 0) {
        return "0";
    }
    return "1";
}

function validateZendDebugger() {
    $element = array();
    $element["name"] = ZEND_DEBUGGER;
    $element["enable"] = htmlspecialchars(ini_get("zend_debugger.expose_remotely"));
    $element["host"] = htmlspecialchars(ini_get("zend_debugger.allow_hosts"));
    return $element;
}

$result = createXmlHeader();
$content = "";
$file = php_ini_loaded_file();
$content .= createXmlElement(
    "path_to_ini",
    array(
        "path" => htmlspecialchars($file)
    ));

$xdebug = extension_loaded(XDEBUG);
if ($xdebug) {
    $config = validateXdebug();
    $content .= createXmlElement("debugger", $config);
}

$zend_debug = extension_loaded(ZEND_DEBUGGER);
if ($zend_debug) {
    $config = validateZendDebugger();
    $content .= createXmlElement("debugger", $config);
}

echo $result . createXmlElement("validation", array(), $content);
