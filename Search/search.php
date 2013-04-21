<?php

/**
 * kitFrameworkSearch
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/kitBase
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (!defined('WB_PATH'))
  header("Location: ../../index.php");

if (!function_exists('getURLbyPageID')) {
    function getURLbyPageID($page_id)
    {
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
}

function kit_framework_search_search($search)
{
    global $database;

    $SQL = "SELECT `section_id`, `page_id`, `content` FROM `".TABLE_PREFIX."mod_wysiwyg` WHERE `content` LIKE '%~~%search%~~%'";
    if (null == ($query = $database->query($SQL)))
        throw new \Exception($database->get_error());

    $result = false;

    $search_words = array();
    foreach ($search['search_url_array'] as $word)
        $search_words[] = strip_tags($word);

    $param_array = array(
        'cms' => array(
            'type' => defined('LEPTON_VERSION') ? 'LEPTON' : 'WebsiteBaker',
            'version' => defined('LEPTON_VERSION') ? LEPTON_VERSION : WB_VERSION,
            'locale' => strtolower(LANGUAGE),
            'url' => WB_URL,
            'path' => WB_PATH,
            'page_id' => PAGE_ID,
            'page_url' => getURLbyPageID(PAGE_ID)
        ),
        'search' => array(
            'page' => array(
                'id' => $search['page_id'],
                'section_id' => $search['section_id'],
                'title' => $search['page_title'],
                'description' => $search['page_description'],
                'keywords' => $search['page_keywords'],
                'url' => getURLbyPageID($search['page_id']),
                'modified_when' => $search['page_modified_when'],
                'modified_by' => $search['page_modified_by']
            ),
            'words' => $search_words,
            'match' => $search['search_match'],
            'max_excerpt' => $search['default_max_excerpt'],
            'image_link' => '',
            'text' => ''
        )
    );
    $param_str = base64_encode(json_encode($param_array));

    while (false !== ($wysiwyg = $query->fetchRow(MYSQL_ASSOC))) {

        preg_match_all('/(~~ ).*( ~~)/', $wysiwyg['content'], $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $command_expression = $match[0];
            // get the expression without leading and trailing ~~
            $command_string = trim(str_replace('~~', '', $match[0]));
            if (empty($command_string)) continue;
            // explode the string into an array by spaces
            $command_array = explode(' ', $command_string);
            // the first match is the command!
            $command = strtolower(trim(strip_tags($command_array[0])));
//echo WB_URL."/kit2/kit_search/command/$command/$param_str";
            ob_start();
            $kitCommand = WB_URL."/kit2/kit_search/command/$command/$param_str";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $kitCommand);
            curl_exec($ch);
            curl_close($ch);
            $return = ob_get_clean();

            $response = json_decode(base64_decode($return), true);
//print_r($response);

            if (isset($response['search'])) {
                // continue only with search results
print_r($response);
                $item = array(
                    'page_link' => isset($response['search']['page']['url']) ? $response['search']['page']['url'] : $search['page_link'],
                    'page_title' => isset($response['search']['page']['title']) ? $response['search']['page']['title'] : $search['page_title'],
                    'page_description' => isset($response['search']['page']['description']) ? $response['search']['page']['description'] : $search['page_description'],
                    'page_modified_when' => isset($response['search']['page']['modified_when']) ? $response['search']['page']['modified_when'] : $search['page_modified_when'],
                    'page_modified_by' => isset($response['search']['page']['modified_by']) ? $response['search']['page']['modified_by'] : $search['page_modified_by'],
                    'text' => isset($response['search']['text']) ? $response['search']['text'] : '',
                    'pic_link' => isset($response['search']['image_link']) ? $response['search']['image_link'] : '',
                    'max_excerpt_num' => isset($response['max_excerpt']) ? $response['max_excerpt'] : $search['default_max_excerpt']
                );
                if (print_excerpt2($item, $search)) {
                    $result = true;
                }
            }
        }
    }
    return $result;
}