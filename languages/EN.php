<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/kitEvent
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (!defined('WB_PATH'))
  exit('Can\'t access this file directly!');

if ('á' != "\xc3\xa1") {
  // important: language files must be saved as UTF-8 (without BOM)
  trigger_error('The language file <b>' . basename(__FILE__) . '</b> is damaged, it must be saved <b>UTF-8</b> encoded!', E_USER_ERROR);
}

// module description for the info file
$module_description = 'Additional Applications and Extensions for your Content Management System, install them with just a click and use them in every WYSIWYG section.';

$LANGUAGE = array(
        'addons_support_group'
            => 'Please help to improve Open Source Software and report this problem to the <a href="https://support.phpmanufaktur.de" target="_blank">phpManufaktur Addons Support</a> Group.'
        );
