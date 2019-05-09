<?php

namespace System\Providers;

use System\Containers\Application;


/**
 * Class ApplicationProvider
 * @package System\Providers
 */
abstract class ApplicationProvider extends Provider {

	protected $app;


	/**
	 * ApplicationProvider constructor.
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

}