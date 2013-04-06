<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/kitBase
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use phpManufaktur\kitFramework\Setup;

if (!defined('WB_PATH'))
  exit('Can\'t access this file directly!');

require_once WB_PATH.'/modules/kit_framework/Setup/Setup.php';

if (!file_exists(WB_PATH.'/modules/kit_framework/languages/'.LANGUAGE.'.php'))
	require_once (WB_PATH.'/modules/kit_framework/languages/EN.php');
else
	require_once (WB_PATH.'/modules/kit_framework/languages/'.LANGUAGE.'.php');

global $LANG;

class Tool {

	protected static $action = null;

	public function __construct() {
		self::$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : 'NONE';
	} // __construct()

	public function exec() {

		if (!file_exists(WB_PATH.'/kit2')) {
			// start the install procedere
			if (self::$action == 'install') {
				// execute the installation of the kitFramework
				$setup = new Setup();
				if (!$setup->checkEMailSettings()) {
					// the email settings are not valid!
					if (!file_exists(WB_PATH.'/modules/kit_framework/Setup/View/'.LANGUAGE.'.mailer.htt'))
						$html = file_get_contents(WB_PATH.'/modules/kit_framework/Setup/View/EN.mailer.htt');
					else
						$html = file_get_contents(WB_PATH.'/modules/kit_framework/Setup/View/'.LANGUAGE.'.mailer.htt');
					$img_source = WB_URL.'/modules/kit_framework/images/mail_icon_100x100.png';
					$settings_url = ADMIN_URL.'/settings/index.php?advanced=yes';
					$html = str_replace(array('{{ img_source }}', '{{ settings_url }}'), array($img_source, $settings_url), $html);
					return $html;
				}
				$setup->exec();
				// success - show the octocat as success dialog
				if (!file_exists(WB_PATH.'/modules/kit_framework/Setup/View/'.LANGUAGE.'.success.htt'))
					$html = file_get_contents(WB_PATH.'/modules/kit_framework/Setup/View/EN.success.htt');
				else
					$html = file_get_contents(WB_PATH.'/modules/kit_framework/Setup/View/'.LANGUAGE.'.success.htt');
				$logo_source = WB_URL.'/modules/kit_framework/images/goretocat_200x200.png';
				$start_url = ADMIN_URL.'/admintools/tool.php?tool=kit_framework';
				$html = str_replace(array('{{ logo_source }}', '{{ start_url }}'), array($logo_source, $start_url), $html);
				return $html;
			}
			else {
				// show info before downloading the kitFramework
				if (!file_exists(WB_PATH.'/modules/kit_framework/Setup/View/'.LANGUAGE.'.setup.htt'))
					$html = file_get_contents(WB_PATH.'/modules/kit_framework/Setup/View/EN.setup.htt');
				else
					$html = file_get_contents(WB_PATH.'/modules/kit_framework/Setup/View/'.LANGUAGE.'.setup.htt');
				$logo_source = WB_URL.'/modules/kit_framework/images/kit2-secret-200x175.png';
				$setup_url = ADMIN_URL.'/admintools/tool.php?tool=kit_framework&action=install';
				$html = str_replace(array('{{ logo_source }}', '{{ setup_url }}'), array($logo_source, $setup_url), $html);
				return $html;
			}
		}
		else {
			// nothing else to do, so we call the kitFramework
	    global $database;

        if (null === ($pwd = $database->get_one("SELECT `password` FROM `".TABLE_PREFIX."users` WHERE `username`='".$_SESSION['USERNAME']."'", MYSQL_ASSOC)))
            throw new Exception($database->get_error());

        $cms_info = array(
            'type' => defined('LEPTON_VERSION') ? 'LEPTON' : 'WebsiteBaker',
            'version' => defined('LEPTON_VERSION') ? LEPTON_VERSION : WB_VERSION,
            'locale' => strtolower(LANGUAGE),
            'username' => $_SESSION['USERNAME'],
            'authentication' => $pwd,
            'target' => 'cms'
        );

        $iframe_source = WB_URL.'/kit2/welcome/cms/'.base64_encode(json_encode($cms_info));

        $cms_info['target'] = 'framework';
        $framework_url = WB_URL.'/kit2/welcome/cms/'.base64_encode(json_encode($cms_info));
        $expand_img = WB_URL.'/kit2/template/framework/default/images/expand_10x10.png';

return <<<EOD
    <div style="width:100%;height:10px;margin:0 0 2px 0;padding:0;text-align:right;">
        <a href="$framework_url" target="_blank">
            <img src="$expand_img" width="10" height="10" alt="Open in kitFramework" title="Open in kitFramework" />
        </a>
    </div>
    <iframe id="kf_iframe" width="100%" height="700" src="$iframe_source" frameborder="0" style="border: 1px solid #ccc;">
        <p>Sorry, but your browser does not support embedded frames!</p>
    </iframe>
    <div style="font-size:10px;text-align:right;margin:2px 0 0 0;padding:0;">
        <a href="https://kit2.phpmanufaktur.de" target="_blank">kitFramework by phpManufaktur</a>
    </div>
EOD;
		}
	} // exec()

} // class Tool

try {
	$Tool = new Tool();
	echo $Tool->exec();
} catch (\Exception $e) {
	// prompt the error message
	echo sprintf('<div class="kit_error">%s<div class="kit_error_support">%s</div></div>',
			sprintf('[%s - %d] %s', basename($e->getFile()), $e->getLine(), $e->getMessage()),
			$LANG['addons_support_group']);
}


