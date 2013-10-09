<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (!defined('WB_PATH')) {
    exit('Can\'t access this file directly!');
}

$url_status = true; (ini_get('allow_url_fopen') == 1);
$curl_status = true; function_exists('curl_init');
$version_check = (version_compare(PHP_VERSION, '5.3.3') < 0) ? false : true;
$server_software = explode('/', $_SERVER['SERVER_SOFTWARE']);
$apache_installed = (strtoupper(substr($_SERVER['SERVER_SOFTWARE'], 0, 6)) == 'APACHE');

$blackCat = '';
$bc_filter_installed = true;
if (defined('CAT_VERSION')) {
    if (!file_exists(CAT_PATH.'/modules/blackcatFilter/filter.php')) {
        $bc_filter_installed = false;
        $blackCat = 'blackcatFilter must be installed<br />';
    }
}

if (!$version_check || !$url_status || !$curl_status || !$apache_installed || !$bc_filter_installed) {
    $required =
  $PRECHECK['CUSTOM_CHECKS'] = array(
      'Server configuration' => array(
          'REQUIRED' => "Needed configuration:<br />".
                  "PHP version >= 5.3.3<br />Server software: APACHE<br />".
                  "cURL extension installed<br />php.ini setting: allow_url_fopen == 1<br />.$blackCat",
          'ACTUAL' => 'Please check the server configuration!<br /><br />For further informations and support please contact '.
                  'the <a href="https://support.phpmanufaktur.de" target="_blank">phpManufaktur Addons Support Group</a>.',
          'STATUS' => false
          )
      );
}
