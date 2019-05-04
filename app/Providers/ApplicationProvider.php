<?php

namespace App\Providers;

use System\Containers\Application;


/**
 * Class ApplicationProvider
 * @package App\Providers
 */
abstract class ApplicationProvider {

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