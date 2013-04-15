<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/kitBase
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (!defined('WB_PATH'))
	exit('Can\'t access this file directly!');

$url_status = (ini_get('allow_url_fopen') == 1);
$curl_status = function_exists('curl_init');
$version_check = (version_compare(PHP_VERSION, '5.3.3') < 0) ? false : true;
$apache_installed = ($_SERVER['SERVER_SOFTWARE'] === 'Apache');
$nix_system = (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN');

if (!$version_check || !$url_status || !$curl_status || !$apache_installed || !$nix_system) {
	$required =
  $PRECHECK['CUSTOM_CHECKS'] = array(
      'Server configuration' => array(
          'REQUIRED' => "Needed configuration:<br />Operating system: *nix system, (Windows is not supported)<br />".
      			"PHP version >= 5.3.3<br />Server software: APACHE<br />".
      			"cURL extension installed<br />php.ini setting: allow_url_fopen == 1<br />",
          'ACTUAL' => 'Please check the server configuration!<br /><br />For further informations and support please contact '.
      			'the <a href="https://support.phpmanufaktur.de" target="_blank">phpManufaktur Addons Support Group</a>.',
          'STATUS' => false
          )
      );
}
