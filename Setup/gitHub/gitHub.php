<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/kitEvent
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\kitFramework\gitHub;

class gitHub {

	/**
	 * GET command to GitHub
	 *
	 * @param string $command API get command
	 * @param array &$result reference to the result array
	 * @param array &$info reference to the info array
	 * @return boolean
	 */
	protected function get($command, &$result=array(), &$info=array()) {
		if (strpos($command, 'https://api.github.com') !== 0)
			$command = "https://api.github.com$command";
		$ch = curl_init($command);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		if (!curl_errno($ch)) {
			$info = curl_getinfo($ch);
		}
		curl_close($ch);
		$result = json_decode($result, true);
		return (!isset($info['http_code']) || ($info['http_code'] != '200')) ? false : true;
	} // gitGet()

	/**
	 * Get the tags for the $repository and return the last one in $last_tag.
	 * This function uses version_compare() to get the last repository
	 *
	 * @param string $organization
	 * @param string $repository
	 * @param array &$last_tag reference
	 * @throws \Exception
	 * @return boolean
	 */
	public function getTags($organization, $repository, &$last_tag) {
		// API command to get a list of the repository tags
		$command = "/repos/$organization/$repository/tags";

		$result = array();
		$info = array();

		if (!$this->get($command, $result, $info)) {
			if (isset($info['http_code']) && isset($result['message']))
				$error_message = sprintf('[GitHub Error] HTTP Code: %s - %s', $info['http_code'], $result['message']);
			elseif (isset($info['http_code']))
			  $error_message = sprintf('[GitHub Error] HTTP Code: %s - no further informations.', $info['http_code']);
			else
				$error_message = '[GitHub Error] Unknown connection error, got no result!';
			throw new \Exception($error_message);
		}

		// no result?
		if (count($result) < 1) return false;

		// we only want the last release number!
		$last_tag = array();
		foreach ($result as $release) {
			if (!isset($release['name']))
				throw new \Exception('[GitHub Error] Result array has not the expected structure!');
			if (empty($last_tag)) {
				$last_tag = $release;
				continue;
			}
			// use version_compare for comparison
			if (version_compare($last_tag['name'], $release['name']) == -1)
				$last_tag = $release;
		}
		return true;
	} // getTags()

	/**
	 * Get the URL for the ZIP archive of the repository with the highest version in tag
	 *
	 * @param string $organization
	 * @param string $repository
	 * @param string &$version reference
	 * @return boolean|Ambigous <>
	 */
	public function getLastRepositoryZipUrl($organization, $repository, &$version='') {
		$last_tag = array();
		if (!$this->getTags($organization, $repository, $last_tag))
			return false;
		// get the tag name (version)
		$version = $last_tag['name'];
		return $last_tag['zipball_url'] ;
	} // getLastRepositoryZipUrl()

} // class gitHub
