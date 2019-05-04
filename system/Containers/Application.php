<?php

namespace System\Containers;

use System\Providers\ConfigProvider;
use System\Providers\RouteProvider;


/**
 * Class Application
 * @package App\Containers
 */
class Application extends Container {


	/**
	 * Application constructor.
	 * @param ConfigProvider $config
	 */
	public function __construct(ConfigProvider $config)
	{
		$this->instance('app', $this);
		$this->instance('config', $config);
		$this->instance('router', new RouteProvider($this, $config));

		foreach ($config->get('app.providers', []) as $key => $class) {
			$this->instance($key, $class);
		}
		foreach ($config->get('app.factories', []) as $key => $class) {
			$this->instance($key, $class);
		}
	}



}