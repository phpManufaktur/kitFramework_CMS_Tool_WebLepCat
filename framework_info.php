<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use phpManufaktur\Basic\Control\CMS\kitFrameworkInfo;

$class_path = WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Basic/Control/CMS/kitFrameworkInfo.php';

if (file_exists($class_path)) {

    require_once $class_path;

    if (!class_exists('kitFrameworkInfo')) {

        /**
         * Check if the kitFramework is installed
         *
         * @return boolean
         */
        function kitFramework_isInstalled()
        {
            return true;
        }

        /**
         * Check if the given kitCommand exists
         *
         * @param string $command
         * @return boolean
         */
        function kitFramework_isCommandAvailable($command)
        {
            $kitFrameworkInfo = new kitFrameworkInfo();
            return $kitFrameworkInfo->isCommandAvailable($command);
        }

        /**
         * Check if the given kitFilter exists
         *
         * @param string $filter
         * @return boolean
         */
        function kitFramework_isFilterAvailable($filter)
        {
            $kitFrameworkInfo = new kitFrameworkInfo();
            return $kitFrameworkInfo->isFilterAvailable($filter);
        }

    }
}
else {
    // if the kitFramework is not installed the functions must also return a response!

    if (!function_exists('kitFramework_isInstalled')) {
        /**
         * Check if the kitFramework is installed
         *
         * @return boolean
         */
        function kitFramework_isInstalled()
        {
            return false;
        }
    }

    if (!function_exists('kitFramework_isCommandAvailable')) {
        /**
         * Check if the given kitCommand exists
         *
         * @return boolean
         */
        function kitFramework_isCommandAvailable()
        {
            return false;
        }
    }

    if (!function_exists('kitFramework_isFilterAvailable')) {
        /**
         * Check if the given kitFilter exists
         *
         * @return boolean
         */
        function kitFramework_isFilterAvailable()
        {
            return false;
        }
    }
}
