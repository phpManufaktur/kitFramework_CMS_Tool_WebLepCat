<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use phpManufaktur\Basic\Control\kitCommand\OutputFilter;

$filter_path = WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Basic/Control/CMS/LEPTON/OutputFilter.php';
if (file_exists($filter_path)) {
    require_once $filter_path;

    if (!function_exists('kit_framework_output_filter')) {
        function kit_framework_output_filter($content) {
            $OutputFilter = new OutputFilter();
            return $OutputFilter->parse($content);
        }
    }
}
