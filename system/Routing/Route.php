<?php

namespace System\Routing;

use Symfony\Component\HttpFoundation\Request;
use System\Containers\Application;

/**
 * Class Route
 * @package System\Routing
 */
class Route {

	protected $method;
	protected $pattern;
	protected $closure;


	/**
	 * Route constructor.
	 * @param string $method
	 * @param string $pattern
	 * @param \Closure $closure
	 */
	public function __construct(string $method, string $pattern, \Closure $closure)
	{
		$this->method = $method;
		$this->pattern = $pattern;
		$this->closure = $closure;
	}


	/**
	 * Match
	 *
	 * @param Request $request
	 * @todo expand this logic for more flexible
	 */
	public function match (Request $request) : bool {
		return (
			$this->method === $request->getMethod() &&
			preg_match('#^'.$this->pattern.'$#Usi', $request->getRequestUri() )
		);
	}


	/**
	 * Execute
	 *
	 * @param Application $app
	 * @param Request $request
	 * @return mixed
	 */
	public function execute (Application $app, Request $request) {
		return call_user_func_array($this->closure, [$app, $request]);
	}

}