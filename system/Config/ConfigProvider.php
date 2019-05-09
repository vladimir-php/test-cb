<?php

namespace System\Config;

use System\Providers\Provider;

/**
 * Class ConfigProvider
 * @package System\Config
 * @todo move it from providers to helpers or other?
 */
class ConfigProvider extends Provider {

	protected $config;


	/**
	 * ConfigProvider constructor.
	 * @param string $path
	 */
	public function __construct(string $path)
	{
		$files = scandir($path, SCANDIR_SORT_NONE);
		foreach ($files as $file) {
			$filepath = $path.'/'.$file;
			if (is_file($filepath) ) {
				$this->config[pathinfo($file)['filename']] = require $filepath;
			}
		}
	}


	/**
	 * Get
	 *
	 * @param string $key
	 */
	public function get (string $key, $default = null) {
		$keys = explode ('.', $key);
		$result = $this->config;
		while ($keys) {
			$current_key = array_shift($keys);
			if (!isset($result[$current_key]) ) {
				return $default;
			}
			$result = $result[$current_key];
		}
		return $result;
	}

}