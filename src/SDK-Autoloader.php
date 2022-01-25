<?php

/**
 * Include all files you need to authenticate against privacyIDEA
 * All that files are placed in privacyIDEA-PHP-SDK direction
 */

spl_autoload_register('autoLoader');

function autoLoader($className)
{
    $classNameParts = explode("\\");
    $classNameOnly = $classNameParts[count($classNameParts) - 1];
    $fullPath = dirname(__FILE__) . "/" . $classNameOnly . ".php";
    if (file_exists($fullPath))
    {
        require_once $fullPath;
        class_alias($className, $classNameOnly, false);
        return true;
    } else
    {
        return false;
    }
}