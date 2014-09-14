<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (!isset($_GET['locale']) || !isset($_GET['usage']) || !isset($_GET['cms_url']) || !isset($_GET['cms_path'])) {
    exit('Missing one or more parameter!');
}

$LOCALE = $_GET['locale'];
$USAGE = $_GET['usage'];
$CMS_URL = $_GET['cms_url'];
$CMS_PATH = $_GET['cms_path'];
$ALERT_TYPE = 'alert-danger';
$ALERT = '';
$RETURN_URL = $CMS_URL.'/kit2/admin/welcome/extensions?usage='.$USAGE.'&update=executed';

$response = file_get_contents($CMS_PATH.'/modules/kit_framework/Update/kitFramework/htt/response.htt');

function getFirstSubdirectory($path)
{
    $handle = opendir($path);
    while (false !== ($directory = readdir($handle))) {
        if ('.' === $directory || '..' === $directory) {
            continue;
        }
        if (is_dir($path .'/'. $directory)) {
            return $directory;
        }
    }
    return null;
}

function rrmdir($dir) {
    foreach (glob($dir . '/*') as $file) {
        if (is_dir($file)) {
            rrmdir($file);
        }
        else {
            @unlink($file);
        }
    }
    @rmdir($dir);
}

if (!file_exists($CMS_PATH.'/kit2/temp/framework')) {
    $ALERT = 'Missing the source path for the kitFramework update!';
}

$TEMP_PATH = null;
if (empty($ALERT)) {
    if (null === ($subdirectory = getFirstSubdirectory($CMS_PATH.'/kit2/temp/framework'))) {
        $ALERT = 'Missing the repository directory in temp path!';
    }
    else {
        $TEMP_PATH = $CMS_PATH.'/kit2/temp/framework/'.$subdirectory.'/framework';
    }
}

if (empty($ALERT)) {
    // rename the existing /framework path as backup
    if (true !== @rename($CMS_PATH.'/kit2/framework', $CMS_PATH.'/kit2/framework.bak')) {
        $ALERT = 'Can not rename the existing kitFramework directory!';
    }
}

if (empty($ALERT)) {
    // move the temporary directory to /framework
    if (true !== @rename($TEMP_PATH, $CMS_PATH.'/kit2/framework')) {
        $ALERT = 'Can not move the temporary kitFramework files to the target directory!';
        // restore the backup directory
        if (true !== @rename($CMS_PATH.'/kit2/framework.bak', $CMS_PATH.'/kit2/framework')) {
            $ALERT = 'Failed to move the temporary kitFramework files to the target directory and failed to restore the kitFramework backup copy!';
        }
    }
    else {
        // very important - copy the framework roote files to the /kit2
        $search = array_merge(glob($CMS_PATH.'/kit2/*'), glob($CMS_PATH.'/kit2/.*'));
        foreach (glob($CMS_PATH.'/kit2/*') as $path) {
            if (is_file($path)) {
                $basename = basename($path);
                if (file_exists($CMS_PATH.'/kit2/temp/framework/'.$subdirectory.'/'.$basename)) {
                    @unlink($path);
                    @rename($CMS_PATH.'/kit2/temp/framework/'.$subdirectory.'/'.$basename, $CMS_PATH.'/kit2/'.$basename);
                }
            }
        }
        foreach (glob($CMS_PATH.'/kit2/temp/framework/'.$subdirectory.'/.*') as $path) {
            // delete hidden files in the /temp directory
            @unlink($path);
        }
        // remove the temporary directories
        @rrmdir($CMS_PATH.'/kit2/temp/framework/'.$subdirectory);
        @rrmdir($CMS_PATH.'/kit2/temp/framework');
    }
}

if (empty($ALERT)) {
    // success - remove the temporary files
    $ALERT = 'Successful installed the new kitFramework release. Please check if all is working fine.';
    $ALERT_TYPE = 'alert-success';
}

$response = str_replace(
    array('{{ LOCALE }}', '{{ USAGE }}', '{{ CMS_URL }}', '{{ RETURN_URL }}', '{{ ALERT_TYPE }}', '{{ ALERT_CONTENT }}'),
    array($LOCALE, $USAGE, $CMS_URL, $RETURN_URL, $ALERT_TYPE, $ALERT),
    $response);

echo $response;
