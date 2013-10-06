<?php

/**
 * kitFrameworkSearch
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (!defined('WB_PATH')) {
  header("Location: ../../index.php");
}

/**
 * Connect the CMS search function with the kitFramework search filter
 *
 * @param array $search
 * @return boolean
 */
function kit_framework_search_search($search)
{
    if (file_exists(WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Basic/Control/CMS/SearchFilter.php')) {
        require_once WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Basic/Control/CMS/SearchFilter.php';
        $SearchFilter = new \phpManufaktur\Basic\Control\CMS\SearchFilter();
        return $SearchFilter->search($search);
    }
}
