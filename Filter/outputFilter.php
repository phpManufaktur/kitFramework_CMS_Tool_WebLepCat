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
     * Get the URL of the submitted PAGE_ID - check for special pages of
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
    
    protected function load_css_file($content, $directory, $css_file, $template)
    {
        return 'XXX'.$content;
        // check if the directory exists
        $scan_paths = array(
            WB_PATH.'/kit2/extension/phpmanufaktur/phpManufaktur',
            WB_PATH.'/kit2/extension/thirdparty/thirdParty'
        );
        foreach ($scan_paths as $path) {
            if (false === ($scan_files = scandir($path))) {
                return $content;
            }
            foreach ($scan_files as $scan_file) {
                
                if (is_file($path.'/'.$scan_file) && (strtolower($scan_file) == $directory)) {
                    if (file_exists("$path/$scan_file/Template/$template/css/$css_file")) {
                        // hit!
                        return $content = 'HIT:'."$scan_file/Template/$template/css/$css_file".$content;
                    }
                }
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
            $command = strtolower(trim($command_array[0]));
            // delete the command from array
            unset($command_array[0]);
            // get the parameter string
            $parameter_string = implode(' ', $command_array);
            $params = array();
            // now we search for the parameters
            preg_match_all('/([a-z,A-Z,0-9]{3,18}([ ]){0,1}\[)(.*?)(])/', $parameter_string, $parameter_matches, PREG_SET_ORDER);
            // loop through the parameters
            foreach ($parameter_matches as $parameter_match) {
                // the bracket [ separate key and value
                $parameter_pair = explode('[', $parameter_match[0]);
                // no pair? continue!
                if (count($parameter_pair) != 2) continue;
                // separate the key
                $key = strtolower(trim($parameter_pair[0]));
                // separate the value
                $value = trim(substr($parameter_pair[1], 0, strrpos($parameter_pair[1], ']')));
                // add to the params array
                $params[$key] = $value; $content = '--YY'.$content;
                if (strtolower($key) == 'load_css') {
                    
                    // we have to load an additional CSS file
                    $count = substr_count($value, ',');
                    if ($count == 0) {
                        if (empty($value)) {
                            // assume that the directory is equal to the command
                            $content = $this->load_css_file($content, $command, 'screen.css', 'default');
                        }
                        else {
                            // directory is given, all other values are default 
                            $content = $this->load_css_file($content, strtolower(trim($value)), 'screen.css', 'default');
                        } 
                    }
                    elseif ($count == 1) {
                        list($directory, $css_file) = explode(',', strtolower($value));
                        $content = $this->load_css_file($content, trim($directory), trim($css_file), 'default');
                    }
                    else {
                        // assume count == 2
                        list($directory, $css_file, $template) = explode(',', strtolower($value));
                        $content = $this->load_css_file($content, trim($directory), trim($css_file), trim($template));
                    }
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