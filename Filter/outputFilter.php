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

    public function exec($content) {
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
                $params[$key] = $value;
            }
            $cmd_array = array(
                'cms' => array(
                    'type' => defined('LEPTON_VERSION') ? 'LEPTON' : 'WebsiteBaker',
                    'version' => defined('LEPTON_VERSION') ? LEPTON_VERSION : WB_VERSION,
                    'locale' => strtolower(LANGUAGE),
                    'page_id' => PAGE_ID
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