<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/kitEvent
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */
namespace phpManufaktur\kitFramework;

use phpManufaktur\kitFramework\unZip\unZip;
use phpManufaktur\kitFramework\gitHub\gitHub;
use phpManufaktur\kitFramework\JSON\JSONFormat;

if (! defined('WB_PATH'))
    exit('Can\'t access this file directly!');

require_once WB_PATH . '/modules/kit_framework/Setup/unZip/unZip.php';
require_once WB_PATH . '/modules/kit_framework/Setup/gitHub/gitHub.php';
require_once WB_PATH . '/modules/kit_framework/Setup/JSON/JSONFormat.php';

class Setup
{

    protected static $download_method = null;
    protected static $kitFramework_zip_url = null;
    protected static $kitFramework_version = null;
    protected static $basicExtension_zip_url = null;
    protected static $basicExtension_version = null;
    protected static $zip_target_path = null;
    protected static $zip_target_name = null;
    protected static $cms_type = null;
    protected static $cms_version = null;

    protected static $proxy = null;
    protected static $proxy_auth = CURLAUTH_BASIC;
    protected static $proxy_port = null;
    protected static $proxy_usrpwd = null;

    protected $JSONFormat = null;

    const USERAGENT = 'kitFramework::Interface';

    /**
     * Constructor
     *
     * @throws \Exception
     */
    public function __construct ()
    {
        // increase the max. exexcution time
        ini_set('max_execution_time', 300);
        if (defined('LEPTON_VERSION')) {
            self::$cms_type = 'LEPTON';
            self::$cms_version = LEPTON_VERSION;
        }
        elseif (defined('CAT_VERSION')) {
            self::$cms_type = 'BlackCat';
            self::$cms_version = CAT_VERSION;
        }
        else {
            self::$cms_type = 'WebsiteBaker';
            self::$cms_version = WB_VERSION;
            // fix for WB 2.8.4
            if ((self::$cms_version == '2.8.3') && file_exists(WB_PATH.'/setup.ini.php')) {
                self::$cms_version = '2.8.4';
            }
        }
        // init the GitHub interface
        $gitHub = new gitHub();
        // get the kitFramework repository data
        if (false === (self::$kitFramework_zip_url = $gitHub->getLastRepositoryZipUrl('phpManufaktur', 'kitFramework', self::$kitFramework_version)))
            throw new \Exception("Can't get the last ZIP repository of the kitFramework");
        // get the kfBasic repository data
        if (false === (self::$basicExtension_zip_url = $gitHub->getLastRepositoryZipUrl('phpManufaktur', 'kfBasic', self::$basicExtension_version)))
            throw new \Exception("Can't get the last ZIP repository of the kitFramework Basic Extension");

        self::$download_method = 'UNKNOWN';
        $this->checkDownloadMethod();

        if (file_exists(WB_PATH.'/modules/kit_framework/proxy.json')) {
            $proxy = json_decode(file_get_contents(WB_PATH.'/modules/kit_framework/proxy.json'), true);
            if (isset($proxy['PROXYAUTH']) && ($proxy['PROXYAUTH'] != 'NONE')) {
                if (strtoupper($proxy['PROXYAUTH']) == 'NTLM') {
                    self::$proxy_auth = CURLAUTH_NTLM;
                }
                else {
                    self::$proxy_auth = CURLAUTH_BASIC;
                }
                self::$proxy_usrpwd = $proxy['PROXYUSERPWD'];
            }
            self::$proxy = $proxy['PROXY'];
            self::$proxy_port = $proxy['PROXYPORT'];
        }

        // init the JSON formatter
        $this->JSONFormat = new JSONFormat();
    } // __construct()

    /**
     * Get the kitFramework version (release) number
     *
     * @return string version
     */
    public function getKitFrameworkVersion ()
    {
        return self::$kitFramework_version;
    } // getKitFrameworkVersion()

    /**
     * Check the possible download methods
     *
     * @throws \Exception
     */
    protected function checkDownloadMethod ()
    {
        if (function_exists('curl_init')) {
            self::$download_method = 'CURL';
        } else {
            throw new \Exception('cURL is not enabled, can\'t download files from GitHub!');
        }
    } // checkDownloadMethod()

    /**
     * Download a file with cURL
     *
     * @param string $source_url
     * @param string $target_path
     * @throws \Exception
     */
    protected function curlDownload ($source_url, $target_path, &$info = array())
    {
        try {

            // first try to get the redirected URL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $source_url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            if (!is_null(self::$proxy)) {
                curl_setopt($ch, CURLOPT_PROXYAUTH, self::$proxy_auth);
                curl_setopt($ch, CURLOPT_PROXY, self::$proxy);
                curl_setopt($ch, CURLOPT_PROXYPORT, self::$proxy_port);
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, self::$proxy_usrpwd);
            }
            $header = curl_exec($ch);
            curl_close($ch);
            if (preg_match('#Location: (.*)#', $header, $redirect)) {
                // this is the redirected URL
                $source_url = trim($redirect[1]);
            }

            // init cURL
            $ch = curl_init();
            // set the cURL options
            curl_setopt($ch, CURLOPT_URL, $source_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
            curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            if (!is_null(self::$proxy)) {
                curl_setopt($ch, CURLOPT_PROXYAUTH, self::$proxy_auth);
                curl_setopt($ch, CURLOPT_PROXY, self::$proxy);
                curl_setopt($ch, CURLOPT_PROXYPORT, self::$proxy_port);
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, self::$proxy_usrpwd);
            }
                // exec cURL and get the file content
            if (false === ($file_content = curl_exec($ch))) {
                throw new \Exception(sprintf('cURL Error: [%d] - %s', curl_errno($ch), curl_error($ch)));
            }
            if (! curl_errno($ch)) {
                $info = curl_getinfo($ch);
            }
            // close the connection
            curl_close($ch);

            if (isset($info['http_code']) && ($info['http_code'] != '200')) {
                return false;
            }

            // create the target file
            if (false === ($downloaded_file = @fopen($target_path, 'w')))
                throw new \Exception('fopen() fails!');
            // write the content to the target file
            if (false === ($bytes = fwrite($downloaded_file, $file_content)))
                throw new \Exception('fwrite() fails!');
            // close the target file
            fclose($downloaded_file);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    } // curlDownload()

    /**
     * The kitFramework need a valid SMTP email configuration
     *
     * @throws \Exception
     * @return boolean
     */
    public function checkEMailSettings ()
    {
        if (self::$cms_type == 'BlackCat') {
            return ((CATMAILER_ROUTINE == 'smtp') && ((CATMAILER_SMTP_AUTH == 'true') || (CATMAILER_SMTP_AUTH == '1')));
        }
        else {
            // the mailer routine must be 'smtp' and authentication 'true' (LEPTON) or '1' (WB)
            return ((WBMAILER_ROUTINE == 'smtp') && ((WBMAILER_SMTP_AUTH == 'true') || (WBMAILER_SMTP_AUTH == '1')));
        }
    } // checkEMailSettings()

    /**
     * Get the E-Mail settings from the CMS and write them to the Swift configuration file.
     * If the config file already exists it will be overwritten
     *
     * @throws \Exception
     * @return boolean
     */
    protected function createEMailConfiguration ()
    {
        if (self::$cms_type == 'BlackCat') {
            $swift_config = array(
                'SERVER_EMAIL' => SERVER_EMAIL,
                'SERVER_NAME' => CATMAILER_DEFAULT_SENDERNAME,
                'SMTP_HOST' => CATMAILER_SMTP_HOST,
                'SMTP_PORT' => 25,
                'SMTP_USERNAME' => CATMAILER_SMTP_USERNAME,
                'SMTP_PASSWORD' => CATMAILER_SMTP_PASSWORD
            );
        }
        else {
            $swift_config = array(
                'SERVER_EMAIL' => SERVER_EMAIL,
                'SERVER_NAME' => WBMAILER_DEFAULT_SENDERNAME,
                'SMTP_HOST' => WBMAILER_SMTP_HOST,
                'SMTP_PORT' => 25,
                'SMTP_USERNAME' => WBMAILER_SMTP_USERNAME,
                'SMTP_PASSWORD' => WBMAILER_SMTP_PASSWORD
            );
        }
        if (! file_put_contents(WB_PATH . '/kit2/config/swift.cms.json', $this->JSONFormat->format($swift_config)))
            throw new \Exception('Can\'t write the configuration file for the SwiftMailer!');
        return true;
    } // createEMailConfiguration()

    /**
     * Generate a random password of $length
     *
     * @param integer $length
     * @return string password
     */
    public static function generatePassword ($length = 12)
    {
        $password = '';
        $salt = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz123456789';
        srand((double) microtime() * 1000000);
        for ($i = 0; $i < $length; $i ++) {
            $num = rand() % 33;
            $tmp = substr($salt, $num, 1);
            $password .= $tmp;
        }
        return $password;
    } // generatePassword()

    /**
     * Generate a random password of $length
     *
     * @param integer $length
     * @return string password
     */
    protected function createProtection ($directory_path)
    {
        $data = sprintf("# .htaccess generated by kitFramework\nAuthUserFile %s/.htpasswd\n" . "AuthName \"kitFramework protection\"\nAuthType Basic\n<Limit GET>\n" . "require valid-user\n</Limit>", $directory_path);
        if (! file_put_contents($directory_path . '/.htaccess', $data))
            throw new \Exception('Can\'t write .htaccess for directory protection!');
        $data = sprintf("# .htpasswd generated by kitFramework\nkit_protector:%s", crypt(self::generatePassword(16)));
        if (! file_put_contents($directory_path . '/.htpasswd', $data))
            throw new \Exception('Can\'t write .htpasswd for directory protection!');
        return true;
    } // createProtection

    /**
     * Get the database settings from the CMS and write them to the Doctrine config file.
     * If the config file already exists it will be overwritten
     *
     * @throws \Exception
     * @return boolean
     */
    protected function createDoctrineConfiguration ()
    {
        if ((self::$cms_type == 'WebsiteBaker') && version_compare(self::$cms_version, '2.8.4', '>=')) {
            if (file_exists(WB_PATH.'/setup.ini.php')) {
                // WebsiteBaker 2.8.4
                $settings = parse_ini_file(WB_PATH.'/setup.ini.php', true);
                // get the database settings from the CMS
                $doctrine_config = array(
                    'DB_TYPE' => $settings['DataBase']['type'],
                    'DB_HOST' => $settings['DataBase']['host'],
                    'DB_PORT' => isset($settings['DataBase']['port']) ? $settings['DataBase']['port'] : '3306',
                    'DB_NAME' => $settings['DataBase']['name'],
                    'DB_USERNAME' => $settings['DataBase']['user'],
                    'DB_PASSWORD' => $settings['DataBase']['pass'],
                    'TABLE_PREFIX' => TABLE_PREFIX
                );
            }
            else {
                throw new \Exception("Missing the constants with the DB settings!");
            }
        }
        else {
            // get the database settings from the CMS
            $doctrine_config = array(
                'DB_TYPE' => DB_TYPE,
                'DB_HOST' => DB_HOST,
                'DB_PORT' => (defined('DB_PORT')) ? DB_PORT : '3306',
                'DB_NAME' => DB_NAME,
                'DB_USERNAME' => DB_USERNAME,
                'DB_PASSWORD' => DB_PASSWORD,
                'TABLE_PREFIX' => TABLE_PREFIX
            );
        }
        if (! file_put_contents(WB_PATH . '/kit2/config/doctrine.cms.json', $this->JSONFormat->format($doctrine_config)))
            throw new \Exception('Can\'t write the configuration file for Doctrine!');
        return true;
    } // createDoctrineConfiguration()

    /**
     * Get the basic settings of the CMS and create or update the CMS configuration file of the framework
     *
     * @throws \Exception
     * @return boolean
     */
    protected function createCMSConfiguration ()
    {
        $cms_config = array();
        if (file_exists(WB_PATH . '/kit2/config/cms.json')) {
            if (null == ($cms_config = (array) json_decode(file_get_contents(WB_PATH . '/kit2/config/cms.json', true))))
                throw new \Exception("Can't read the CMS configuration file of the Framework!");
        }

        $cms_config['CMS_PATH'] = WB_PATH;
        $cms_config['CMS_URL'] = WB_URL;
        $cms_config['CMS_MEDIA_PATH'] = WB_PATH . MEDIA_DIRECTORY;
        $cms_config['CMS_MEDIA_URL'] = WB_URL . MEDIA_DIRECTORY;
        $cms_config['CMS_TEMP_PATH'] = WB_PATH . '/temp';
        $cms_config['CMS_TEMP_URL'] = WB_URL . '/temp';
        $cms_config['CMS_ADMIN_PATH'] = ADMIN_PATH;
        $cms_config['CMS_ADMIN_URL'] = ADMIN_URL;
        $cms_config['CMS_TYPE'] = self::$cms_type;
        $cms_config['CMS_VERSION'] = self::$cms_version;
        $cms_config['OUTPUT_FILTER']['METHOD'] = 'STANDARD';

        if (! file_put_contents(WB_PATH . '/kit2/config/cms.json', $this->JSONFormat->format($cms_config)))
            throw new \Exception('Can\'t write the configuration file for the CMS!');
        return true;
    } // createCMSConfiguration()

    /**
     * Update the framework configuration file with FRAMEWORK_PATH and FRAMEWORK_URL
     *
     * @throws \Exception
     * @return boolean
     */
    protected function updateFrameworkConfiguration ()
    {
        $framework_config = array(
            'DEBUG' => false,
            'CACHE' => true,
            'FRAMEWORK_TEMPLATES' => array('default')
        );
        if (file_exists(WB_PATH . '/kit2/config/framework.json')) {
            if (null == ($framework_config = (array) json_decode(file_get_contents(WB_PATH . '/kit2/config/framework.json', true))))
                throw new \Exception("Can't read the Framework configuration file!");
        }

        $framework_config['FRAMEWORK_PATH'] = WB_PATH . '/kit2';
        $framework_config['FRAMEWORK_URL'] = WB_URL . '/kit2';

        if (! file_put_contents(WB_PATH . '/kit2/config/framework.json', $this->JSONFormat->format($framework_config)))
            throw new \Exception('Can\'t write the Framework configuration file!');
        return true;
    } // updateFrameworkConfiguration()

    /**
     * Check if the WebsiteBaker output filter is already patched
     *
     * @param string $filter_path
     * @return boolean
     */
    protected function websiteBakerIsPatched ($filter_path)
    {
        if (file_exists($filter_path)) {
            $lines = file($filter_path);
            foreach ($lines as $line)
                if (strpos($line, "kitFramework") > 0)
                    return true;
        }
        return false;
    } // websiteBakerIsPatched()

    /**
     * fixes a path by removing //, /../ and other things
     *
     * @access public
     * @param string $path to fix
     * @return string
     *
     */
    public static function sanitizePath ($path)
    {
        // remove / at end of string; this will make sanitizePath fail otherwise!
        $path = preg_replace('~/{1,}$~', '', $path);

        // make all slashes forward
        $path = str_replace('\\', '/', $path);

        // bla/./bloo ==> bla/bloo
        $path = preg_replace('~/\./~', '/', $path);

        // resolve /../
        // loop through all the parts, popping whenever there's a .., pushing otherwise.
        $parts = array();
        foreach (explode('/', preg_replace('~/+~', '/', $path)) as $part) {
        if ($part === ".." || $part == '') {
        array_pop($parts);
        } elseif ($part != "") {
        $parts[] = $part;
        }
        }

        $new_path = implode("/", $parts);

        // windows
        if (! preg_match('/^[a-z]\:/i', $new_path)) {
            $new_path = '/' . $new_path;
        }

        return $new_path;
    }

    /**
     * Patch the WebsiteBaker output filter
     *
     * @param string $filter_path
     * @return boolean
     */
    protected function websiteBakerDoPatch ($filter_path)
    {
        $returnvalue = false;
        $tempfile = WB_PATH . '/modules/output_filter/new_filter.php';
        $backup = WB_PATH . '/modules/output_filter/original-kitframework-filter-routines.php';

        $addline = "\n\n\t\t// exec kitFramework filter";
        $addline .= "\n\t\tif (file_exists(WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Basic/Control/CMS/OutputFilter.php')) { ";
        $addline .= "\n\t\t\trequire_once (WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur/Basic/Control/CMS/OutputFilter.php'); ";
        $addline .= "\n\t\t\t" . '$cmsOutputFilter = new \phpManufaktur\Basic\Control\CMS\OutputFilter(); ';
        $addline .= "\n\t\t\t" . '$content = $cmsOutputFilter->parse($content); ';
        $addline .= "\n\t\t}\n\n ";

        if (file_exists($filter_path)) {
            $lines = file($filter_path);
            $handle = fopen($tempfile, 'w');
            foreach ($lines as $line) {
                fwrite($handle, $line);
                // check for both indicators of WB 2.8.1 up to wb 2.8.3
                if ((strpos($line, "define('OUTPUT_FILTER_DOT_REPLACEMENT'") > 0) || (strpos($line, 'function filter_frontend_output($content)') > 0)) {
                    $returnvalue = true;
                    fwrite($handle, $addline);
                }
            }
            fclose($handle);
            if (rename($filter_path, $backup)) {
                if (rename($tempfile, $filter_path)) {
                    return $returnvalue;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Add the output filter for the kitFramework
     *
     * @return boolean
     */
    protected function addFilter ()
    {
        global $database;

        if (defined('LEPTON_VERSION')) {
            // register the filter at LEPTON outputInterface
            if (! file_exists(WB_PATH . '/modules/output_interface/output_interface.php')) {
                throw new \Exception('Missing LEPTON outputInterface, can\'t register the kitFramework filter - installation is not complete!');
            } else {
                if (! function_exists('register_output_filter'))
                    include_once (WB_PATH . '/modules/output_interface/output_interface.php');
                register_output_filter('kit_framework', 'kitFramework');
            }
        }
        elseif (defined('CAT_VERSION')) {
            // register the filter at the blackcatFilter
            require_once CAT_PATH.'/modules/blackcatFilter/filter.php';
            // first unregister to prevent trouble at re-install
            unregister_filter('kitCommands', 'kit_framework');
            // register the filter
            register_filter('kitCommands', 'kit_framework', 'Enable the usage of kitCommands within BlackCat');
        }
        else {
            if (version_compare(WB_VERSION, '2.8.3', '>=')) {
                // WebsiteBaker 2.8.3
                $filter_path = WB_PATH . '/modules/output_filter/index.php';
            } else {
                // all other WebsiteBaker versions
                $filter_path = WB_PATH . '/modules/output_filter/filter-routines.php';
            }
            if (file_exists($filter_path)) {
                if (! $this->websiteBakerIsPatched($filter_path)) {
                    if (! $this->websiteBakerDoPatch($filter_path)) {
                        throw new \Exception('Failed to patch the WebsiteBaker output filter, please contact the support!');
                    }
                }
            } else {
                throw new \Exception('Can\'t detect the correct method to patch the output filter, please contact the support!');
            }
        }
        return true;
    } // addFilter()

    /**
     * Copy a file, or recursively copy a folder and its contents
     *
     * @param string $source Source path
     * @param string $dest Destination path
     * @param string $permissions New folder creation permissions
     * @return bool Returns true on success, false on failure
     *
     * @author <http://stackoverflow.com/a/12763962/2243419>
     */
    public static function xcopy($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            self::xcopy("$source/$entry", "$dest/$entry");
        }

        // Clean up
        $dir->close();
        return true;
    }

    /**
     * Download and configure the kitFramework
     *
     * @throws \Exception
     */
    protected function downloadAndConfigTheFramework()
    {
        self::$zip_target_name = 'kitFramework.zip';
        self::$zip_target_path = self::sanitizePath(WB_PATH . '/temp/unzip');

        if (!file_exists(self::$zip_target_path)) {
            if (!mkdir(self::$zip_target_path, 0777, true)) {
                throw new \Exception($this->app['translator']->trans("Can not create the directory %directory%!",
                    array('%directory%' => '/temp/unzip')));
            }
        }

        // clean up the temporary directory
        @array_map('unlink', glob(self::sanitizePath(WB_PATH . '/temp/unzip/*')));

        // get the kitFramework with CURL
        $info = array();
        if (! $this->curlDownload(self::$kitFramework_zip_url, self::$zip_target_path . '/' . self::$zip_target_name, $info)) {
            if (isset($info['http_code']) && ($info['http_code'] == '302') && isset($info['redirect_url']) && ! empty($info['redirect_url'])) {
                // follow the redirect URL!
                $redirect_url = $info['redirect_url'];
                $info = array();
                $this->curlDownload($redirect_url, self::$zip_target_path . '/' . self::$zip_target_name, $info);
            } elseif (isset($info['http_code']) && ($info['http_code'] != '200')) {
                throw new \Exception(sprintf('[GitHub Error] HTTP Code: %s - no further informations available!', $info['http_code']));
            }
        }

        // unzip the kitFramework to the target path
        $unzip = new unZip();

        // create target directory
        $unzip->checkDirectory(self::sanitizePath(WB_PATH . '/temp/unzip/kit2'));
        $unzip->setUnZipPath(self::sanitizePath(WB_PATH . '/temp/unzip/kit2'));

        $unzip->extract(self::$zip_target_path . '/' . self::$zip_target_name);

        // GitHub ZIP's contain a subdirectory with name we don't know ...
        $source_dir = '';
        $handle = opendir(WB_PATH . '/temp/unzip/kit2');
        // we loop through the temp dir to get the first subdirectory ...
        while (false !== ($file = readdir($handle))) {
            if ('.' == $file || '..' == $file)
                continue;
            if (is_dir(WB_PATH . '/temp/unzip/kit2/' . $file)) {
                // ... here we got it!
                $source_dir = WB_PATH . '/temp/unzip/kit2/' . $file;
                break;
            }
        }

        if ($source_dir == '')
            throw new \Exception('The unzipped archive has an unexpected structure, please contact the support!');

        // create the directory for the kitFramework
        if (!mkdir(WB_PATH . '/kit2'))
            throw new \Exception("Can't create the target directory for the KIT framework!");

        // move all files from the temporary directory to the target
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            // linux only: just rename
            if (!rename(self::sanitizePath($source_dir), self::sanitizePath(WB_PATH . '/kit2')))
                throw new \Exception("Can't move the unzipped framework to the target directory!");
        }
        else {
            // at windows copy file per file ...
            $this->xcopy(self::sanitizePath($source_dir), self::sanitizePath(WB_PATH . '/kit2'));
        }
        // delete not needed GIT files
        $delete_files = array(
            '.gitattributes',
            '.gitignore'
        );
        foreach ($delete_files as $file) {
            // we don't want any error prompt at this point
            @unlink(self::sanitizePath(WB_PATH . '/kit2/' . $file));
        }

        // clean up the temporary directory
        @array_map('unlink', glob(self::sanitizePath(WB_PATH . '/temp/unzip/*')));

        if (!file_exists(self::sanitizePath(WB_PATH . '/kit2/config'))) {
            if (!mkdir(self::sanitizePath(WB_PATH . '/kit2/config')))
                throw new \Exception("Can't create the directory for the framework configuration files!");
        }

        // create the configuration for SwiftMailer
        $this->createEMailConfiguration();

        // create the configuration for Doctrine
        $this->createDoctrineConfiguration();

        // create the CMS configuration
        $this->createCMSConfiguration();

        // update the framework configuration
        $this->updateFrameworkConfiguration();

        // create directory protection
        $this->createProtection(WB_PATH . '/kit2/config');

        // create the /media directory for the kitFramework
        $directories = array(
            WB_PATH . '/kit2/media',
            WB_PATH . '/kit2/media/public',
            WB_PATH . '/kit2/media/protected'
        );
        foreach ($directories as $directory) {
            if (! file_exists($directory)) {
                if (! mkdir($directory))
                    throw new \Exception('Can\'t create the \media directory for kitFramework!');
            }
        }

        // create directory protection
        $this->createProtection(WB_PATH . '/kit2/media/protected');
    } // downloadAndConfigTheFramework()

    /**
     * Download and install the kitFramework Basic extension
     *
     * @throws \Exception
     */
    protected function downloadAndConfigTheBasicExtension()
    {
        self::$zip_target_name = 'basicExtension.zip';
        self::$zip_target_path = self::sanitizePath(WB_PATH . '/temp/unzip');

        // clean up the temporary directory
        @array_map('unlink', glob(WB_PATH . '/temp/unzip/*'));

        // get the Basic Extension with CURL
        $info = array();
        if (! $this->curlDownload(self::$basicExtension_zip_url, self::$zip_target_path . '/' . self::$zip_target_name, $info)) {
            if (isset($info['http_code']) && ($info['http_code'] == '302') && isset($info['redirect_url']) && ! empty($info['redirect_url'])) {
                // follow the redirect URL!
                $redirect_url = $info['redirect_url'];
                $info = array();
                $this->curlDownload($redirect_url, self::$zip_target_path . '/' . self::$zip_target_name, $info);
            } elseif (isset($info['http_code']) && ($info['http_code'] != '200')) {
                throw new \Exception(sprintf('[GitHub Error] HTTP Code: %s - no further informations available!', $info['http_code']));
            }
        }

        // unzip the Basic Extension to the target path
        $unzip = new unZip();

        // create target directory
        $unzip->checkDirectory(self::sanitizePath(WB_PATH . '/temp/unzip/basic'));
        $unzip->setUnZipPath(self::sanitizePath(WB_PATH . '/temp/unzip/basic'));

        $unzip->extract(self::$zip_target_path . '/' . self::$zip_target_name);

        // GitHub ZIP's contain a subdirectory with name we don't know ...
        $source_dir = '';
        $handle = opendir(self::sanitizePath(WB_PATH . '/temp/unzip/basic'));
        // we loop through the temp dir to get the first subdirectory ...
        while (false !== ($file = readdir($handle))) {
            if ('.' == $file || '..' == $file)
                continue;
            if (is_dir(self::sanitizePath(WB_PATH . '/temp/unzip/basic/' . $file))) {
                // ... here we got it!
                $source_dir = self::sanitizePath(WB_PATH . '/temp/unzip/basic/' . $file);
                break;
            }
        }

        if ($source_dir == '')
            throw new \Exception('The unzipped archive has an unexpected structure, please contact the support!');

        // create the directory for the Basic extension
        if (!mkdir(self::sanitizePath(WB_PATH . '/kit2/extension/phpmanufaktur/phpManufaktur/Basic'))) {
            throw new \Exception("Can't create the target directory for the kitFramework Basic extension!");
        }
        // move all files from the temporary directory to the target
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            // linux only: just rename
            if (! rename($source_dir, WB_PATH . '/kit2/extension/phpmanufaktur/phpManufaktur/Basic'))
                throw new \Exception("Can't move the unzipped kitFramework extension to the target directory!");
        }
        else {
            // windows: use copy
            $this->xcopy($source_dir, self::sanitizePath(WB_PATH . '/kit2/extension/phpmanufaktur/phpManufaktur/Basic'));
        }

        // delete not needed GIT files
        $delete_files = array(
            '.gitattributes',
            '.gitignore'
        );
        foreach ($delete_files as $file) {
            // we don't want any error prompt at this point
            @unlink(self::sanitizePath(WB_PATH . '/kit2/extension/phpmanufaktur/phpManufaktur/Basic/' . $file));
        }

        // clean up the temporary directory
        @array_map('unlink', glob(self::sanitizePath(WB_PATH . '/temp/unzip/*')));

    } // downloadAndConfigTheBasicExtension()

    /**
     * Execute the Setup, download kitFramework, unzip and start the framework
     *
     * @throws \Exception
     */
    public function exec ()
    {
        // download and config the kitFramework
        $this->downloadAndConfigTheFramework();

        $relative_path = parse_url(WB_URL, PHP_URL_PATH);
        if (!empty($relative_path) && file_exists(WB_PATH.'/modules/kit_framework/Setup/htaccess.htt')) {
            $htaccess = file_get_contents(WB_PATH.'/modules/kit_framework/Setup/htaccess.htt');
            $htaccess = str_ireplace('{RELATIVE_PATH}', "$relative_path/kit2", $htaccess);
            file_put_contents(WB_PATH.'/kit2/.htaccess', $htaccess);
        }

        // download and config the Basic extension
        $this->downloadAndConfigTheBasicExtension();

        // add the output filter for the framework
        $this->addFilter();

        // copy the proxy.json
        if (file_exists(WB_PATH.'/modules/kit_framework/proxy.json')) {
            if (file_exists(WB_PATH.'/kit2/config') && !file_exists(WB_PATH.'/kit2/config/proxy.json')) {
                @copy(WB_PATH.'/modules/kit_framework/proxy.json', WB_PATH.'/kit2/config/proxy.json');
            }
        }
    }

}
