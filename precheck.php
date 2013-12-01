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

global $database;

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
        $blackCat = 'BlackCat OutputFilter must be installed<br />';
    }
}

// check MySQL version
$mysqlVersion =  mysqli_get_client_version();
$mainVersion = (int)($mysqlVersion/10000);
$a = $mysqlVersion - ($mainVersion*10000);
$minorVersion = (int)($a/100);
$subVersion = $a - ($minorVersion*100);
$myVersion = $mainVersion.'.'.$minorVersion.'.'.$subVersion;
$mySQL_ok = version_compare($myVersion, '5.0.3', '>=');

// check for InnoDB
$SQL = "SELECT SUPPORT FROM INFORMATION_SCHEMA.ENGINES WHERE ENGINE = 'InnoDB'";
if (null == ($inno_check = $database->get_one($SQL))) {
    trigger_error($database->get_error(), E_USER_ERROR);
}
$innoDB_available = ($inno_check != 'NO');



if (!$version_check || !$url_status || !$curl_status || !$apache_installed || !$bc_filter_installed || !$innoDB_available || !$mySQL_ok) {
  $PRECHECK['CUSTOM_CHECKS'] = array(
      'Server configuration' => array(
          'REQUIRED' => "Needed configuration:<br />".
                  "PHP version >= 5.3.3<br />Server software: APACHE<br />".
                  "cURL extension installed<br />php.ini setting: allow_url_fopen = 1<br />$blackCat".
                  "MySQL version >= 5.0.3<br />".
                  "MySQL InnoDB service must be available<br />",
          'ACTUAL' => 'Please check the server configuration!<br /><br />For further information and support please contact '.
                  'the <a href="https://support.phpmanufaktur.de" target="_blank">phpManufaktur Support Group</a>.',
          'STATUS' => false
          )
      );
}
