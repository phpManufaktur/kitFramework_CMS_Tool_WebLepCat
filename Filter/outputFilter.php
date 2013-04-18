<?php

/**
 * kitFramework for WebsiteBaker
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/kitBase
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\kitFramework\Filter;

class outputFilter {

    /**
     * Get the URL of the submitted PAGE_ID - check for special pages like
     * TOPICS and/or NEWS and return the URL of the TOPIC/NEW page if active
     *
     * @param integer $page_id
     * @return boolean|string
     */
    protected function getURLbyPageID($page_id) {
        global $database;

        if (defined('TOPIC_ID')) {
            // this is a TOPICS page
            $SQL = "SELECT `link` FROM `".TABLE_PREFIX."mod_topics` WHERE `topic_id`='".TOPIC_ID."'";
            $link = $database->get_one($SQL);
            if ($database->is_error()) {
                trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
                return false;
            }
            // include TOPICS settings
            global $topics_directory;
            include_once WB_PATH . '/modules/topics/module_settings.php';
            return WB_URL . $topics_directory . $link . PAGE_EXTENSION;
        }

        if (defined('POST_ID')) {
            // this is a NEWS page
            $SQL = "SELECT `link` FROM `".TABLE_PREFIX."mod_news_posts` WHERE `post_id`='".POST_ID."'";
            $link = $database->get_one($SQL);
            if ($database->is_error()) {
                trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
                return false;
            }
            return WB_URL.PAGES_DIRECTORY.$link.PAGE_EXTENSION;
        }

        $SQL = "SELECT `link` FROM `".TABLE_PREFIX."pages` WHERE `page_id`='$page_id'";
        $link = $database->get_one($SQL, MYSQL_ASSOC);
        if ($database->is_error()) {
            trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
            return false;
        }
        return WB_URL.PAGES_DIRECTORY.$link.PAGE_EXTENSION;
    }

    /**
     * Try to load a CSS file from the specified directory
     *
     * @param string reference $content
     * @param string $directory
     * @param string $css_file
     * @param string $template
     * @return boolean true on success
     */
    protected function load_css_file(&$content, $directory, $css_file, $template)
    {
        // remove leading and trailing slashes and backslashes
        $directory = trim($directory, '/\\');
        $css_file = trim($css_file, '/\\');
        $template = trim($template, '/\\');
        // we will scan the extension path for phpManufaktur and thirdParty
        $scan_paths = array(
            WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur',
            WB_PATH.'/kit2/extension/thirdparty/thirdParty'
        );
        foreach ($scan_paths as $path) {
            if (false === ($scan_files = scandir($path))) {
                return false;
            }
            foreach ($scan_files as $scan_file) {
                if (is_dir($path.'/'.$scan_file) && (strtolower($scan_file) == $directory)) {
                    $css_path = "$path/$scan_file/Template/$template/$css_file";
                    if (file_exists($css_path)) {
                        // ok - the CSS file exist, now we load it
                        $css_url = WB_URL.substr($css_path, strlen(WB_PATH));
                        if (false !== (stripos($content, '<!-- kitFramework:CSS -->'))) {
                            $replace = '<!-- kitFramework:CSS -->'."\n".'<link rel="stylesheet" type="text/css" href="'.$css_url.'" media="all" />';
                            $content = str_ireplace('<!-- kitFramework:CSS -->', $replace, $content);
                        }
                        else {
                            $replace = '<link rel="stylesheet" type="text/css" href="'.$css_url.'" media="all" />'."\n".'</head>';
                            $content = str_ireplace('</head>', $replace, $content);
                        }
                        return true;
                    }
                }
            }
        }
        // no CSS file loaded
        return false;
    }

    /**
     * Try to load a JavaScript or jQuery file from the specified directory
     *
     * @param string reference $content
     * @param string $directory
     * @param string $js_file
     * @param string $template
     * @return boolean true on success
     */
    protected function load_js_file(&$content, $directory, $js_file, $template)
    {
        // remove leading and trailing slashes and backslashes
        $directory = trim($directory, '/\\');
        $js_file = trim($js_file, '/\\');
        $template = trim($template, '/\\');
        // we will scan the extension path for phpManufaktur and thirdParty
        $scan_paths = array(
            WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur',
            WB_PATH.'/kit2/extension/thirdparty/thirdParty'
        );
        foreach ($scan_paths as $path) {
            if (false === ($scan_files = scandir($path))) {
                return false;
            }
            foreach ($scan_files as $scan_file) {
                if (is_dir($path.'/'.$scan_file) && (strtolower($scan_file) == $directory)) {
                    $js_path = "$path/$scan_file/Template/$template/$js_file";
                    if (file_exists($js_path)) {
                        // ok - the JS file exist, now we load it
                        $css_url = WB_URL.substr($js_path, strlen(WB_PATH));
                        if (false !== (stripos($content, '<!-- kitFramework:JS -->'))) {
                            $replace = '<!-- kitFramework:JS -->'."\n".'<script src="'.$css_url.'" type="text/javascript"></script>';
                            $content = str_ireplace('<!-- kitFramework:JS -->', $replace, $content);
                        }
                        else {
                            $replace = '<script src="'.$css_url.'" type="text/javascript"></script>'."\n".'</head>';
                            $content = str_ireplace('</head>', $replace, $content);
                        }
                        return true;
                    }
                }
            }
        }
        // no JS file loaded
        return false;
    }

    /**
     * Check if a CSS or JS file is to load, check the params, set defaults and
     * call the subroutines to load the files
     *
     * @param string reference $content
     * @param string $command
     * @param string $type i.e. 'css' or 'js'
     * @param string $value
     */
    protected function checkLoadFile(&$content, $command, $type, $value) {
        if ($type == 'css') {
            // we have to load an additional CSS file
            $count = substr_count($value, ',');
            if ($count == 0) {
                if (empty($value)) {
                    // assume that the directory is equal to the command
                    $this->load_css_file($content, $command, 'screen.css', 'default');
                }
                else {
                    // directory is given, all other values are default
                    $this->load_css_file($content, strtolower(trim($value)), 'screen.css', 'default');
                }
            }
            elseif ($count == 1) {
                list($directory, $css_file) = explode(',', strtolower($value));
                $this->load_css_file($content, trim($directory), trim($css_file), 'default');
            }
            elseif ($count == 2) {
                // three parameters
                list($directory, $css_file, $template) = explode(',', strtolower($value));
                $this->load_css_file($content, trim($directory), trim($css_file), trim($template));
            }
        }
        elseif ($type == 'js') {
            $count = substr_count($value, ',');
            if ($count == 1) {
                // two parameters, split into directory and JS file
                list($directory, $js_file) = explode(',', strtolower($value));
                $this->load_js_file($content, trim($directory), trim($js_file), 'default');
            }
            elseif ($count == 2) {
                // three parameters, split into directory, JS file and template
                list($directory, $js_file, $template) = explode(',', strtolower($value));
                $this->load_js_file($content, trim($directory), trim($js_file), trim($template));
            }
        }
    }

    /**
     * Execute the content filter for the kitFramework.
     * Extract CMS parameters like type, version, path, url, id of the calling
     * page and other, additional routes all parameters of a kitCommand and all
     * $_REQUESTs to the kitCommand routine of the kitFramework.
     *
     * @param string $content
     * @return mixed
     */
    public function exec($content) {
        $load_css = array();
        preg_match_all('/(~~ ).*( ~~)/', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $command_expression = $match[0];
            // get the expression without leading and trailing ~~
            $command_string = trim(str_replace('~~', '', $match[0]));
            if (empty($command_string)) continue;
            // explode the string into an array by spaces
            $command_array = explode(' ', $command_string);
            // the first match is the command!
            $command = strtolower(trim(strip_tags($command_array[0])));
            // delete the command from array
            unset($command_array[0]);
            // get the parameter string
            $parameter_string = implode(' ', $command_array);
            $params = array();
            // now we search for the parameters
            preg_match_all('/([a-z,A-Z,0-9,_]{2,32}([ ]){0,2}\[)(.*?)(])/', $parameter_string, $parameter_matches, PREG_SET_ORDER);
            // loop through the parameters
            foreach ($parameter_matches as $parameter_match) {
                // the bracket [ separate key and value
                $parameter_pair = explode('[', $parameter_match[0]);
                // no pair? continue!
                if (count($parameter_pair) != 2) continue;
                // separate the key
                $key = strtolower(trim(strip_tags($parameter_pair[0])));
                // separate the value
                $value = trim(strip_tags(substr($parameter_pair[1], 0, strrpos($parameter_pair[1], ']'))));
                // add to the params array
                $params[$key] = $value;
                if (($key == 'css') || ($key == 'js')) {
                    // we have to load an additional CSS file
                    $this->checkLoadFile($content, $command, $key, $value);
                }
            }
            $cmd_array = array(
                'cms' => array(
                    'type' => defined('LEPTON_VERSION') ? 'LEPTON' : 'WebsiteBaker',
                    'version' => defined('LEPTON_VERSION') ? LEPTON_VERSION : WB_VERSION,
                    'locale' => strtolower(LANGUAGE),
                    'url' => WB_URL,
                    'path' => WB_PATH,
                    'page_id' => PAGE_ID,
                    'page_url' => $this->getURLbyPageID(PAGE_ID)
                ),
                'GET' => $_GET,
                'POST' => $_POST,
                'SESSION' => $_SESSION,
                'params' => $params
            );
            ob_start();
            $kitCommand = WB_URL.'/kit2/kit_command/'.$command.'/'.base64_encode(json_encode($cmd_array));
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, $kitCommand);
            $result = curl_exec($ch);
            curl_close($ch);
            $response = ob_get_clean();
            $content = str_replace($command_expression, $response, $content);
        }
        return $content;
    }
}