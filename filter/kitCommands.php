<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use phpManufaktur\Basic\Control\CMS\BlackCat\OutputFilter;

$filter_path = WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Basic/Control/CMS/BlackCat/OutputFilter.php';
if (file_exists($filter_path)) {

    if (!function_exists('kitCommands')) {
        require_once $filter_path;

        function kitCommands(&$content) {
            $OutputFilter = new OutputFilter();
            $content = $OutputFilter->parse($content);
        }
    }
}
