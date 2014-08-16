<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use phpManufaktur\kitFramework\Setup;

if (!defined('WB_PATH')) {
  exit('Can\'t access this file directly!');
}

// no autoloading at this point!
require_once WB_PATH.'/modules/kit_framework/Setup/Setup.php';

if (!file_exists(WB_PATH.'/modules/kit_framework/languages/'.LANGUAGE.'.php'))
    require_once (WB_PATH.'/modules/kit_framework/languages/EN.php');
else
    require_once (WB_PATH.'/modules/kit_framework/languages/'.LANGUAGE.'.php');

global $LANGUAGE;

class Tool {

    protected static $action = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        self::$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : 'NONE';
    }

    public function exec()
    {
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
                $logo_source = WB_URL.'/modules/kit_framework/images/framework.jpg';
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
                $logo_source = WB_URL.'/modules/kit_framework/images/framework.jpg';
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

            if (defined('LEPTON_VERSION')) {
                $cms_type = 'LEPTON';
                $cms_version = LEPTON_VERSION;
            }
            elseif (defined('CAT_VERSION')) {
                $cms_type = 'BlackCat';
                $cms_version = CAT_VERSION;
            }
            else {
                $cms_type = 'WebsiteBaker';
                $cms_version = WB_VERSION;
                // fix for WB 2.8.4
                if (($cms_version == '2.8.3') && file_exists(WB_PATH.'/setup.ini.php')) {
                    $cms_version = '2.8.4';
                }
            }

            $cms_info = array(
                'type' => $cms_type,
                'version' => $cms_version,
                'locale' => strtolower(LANGUAGE),
                'username' => $_SESSION['USERNAME'],
                'authentication' => $pwd,
                'target' => 'cms'
            );

            $iframe_source = WB_URL.'/kit2/welcome/cms/'.base64_encode(json_encode($cms_info));

            $cms_info['target'] = 'framework';
            $framework_url = WB_URL.'/kit2/welcome/cms/'.base64_encode(json_encode($cms_info));
            $expand_img = WB_URL.'/kit2/extension/phpmanufaktur/phpManufaktur/Basic/Template/default/framework/image/kitframework_15x14.png';

            $toggle_pagetree = '';
            if (defined('CAT_VERSION')) {
              $toggle_pagetree = '<script type="text/javascript">$(document).ready(function() { togglePageTree(); });</script>';
            }

return <<<EOD
    <div style="width:100%;margin:0;padding:5px;color:#000;background-color:#fff;">
        <div style="width:100%;height:15px;margin:5px 0;padding:0;text-align:right;">
            <a href="$framework_url" target="_blank">
                <img src="$expand_img" width="15" height="14" alt="Open in kitFramework" title="Open in kitFramework" />
            </a>
        </div>
        <iframe id="kitframework_iframe" width="100%" height="700" src="$iframe_source" frameborder="0" style="border:none;">
            <p>Sorry, but your browser does not support embedded frames!</p>
        </iframe>
        <div style="font-size:10px;text-align:right;margin:2px 0 0 0;padding:0;">
            <a href="https://kit2.phpmanufaktur.de" target="_blank">kitFramework by phpManufaktur</a>
        </div>
    </div>
    $toggle_pagetree
EOD;
        }
    }

}

try {
    $Tool = new Tool();
    echo $Tool->exec();
} catch (\Exception $e) {
    // prompt the error message
    echo sprintf('<div class="kit_error">%s<div class="kit_error_support">%s</div></div>',
            sprintf('[%s - %d] %s', basename($e->getFile()), $e->getLine(), $e->getMessage()),
            $LANGUAGE['addons_support_group']);
}


