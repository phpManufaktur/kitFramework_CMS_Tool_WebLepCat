<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/kitBase
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

//namespace phpManufaktur;

use phpManufaktur\kitFramework\Filter\outputFilter;

require_once WB_PATH.'/modules/kit_framework/Filter/outputFilter.php';

if (!function_exists('kit_framework_output_filter')) {
  function kit_framework_output_filter($content) {
      $outputFilter = new outputFilter();
      return $outputFilter->exec($content);
  }
}
