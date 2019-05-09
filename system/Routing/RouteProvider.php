<?php

namespace System\Routing;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use System\Config\ConfigProvider;
use System\Containers\Application;
use System\Providers\Provider;
use System\View\View;

/**
 * Class RouteProvider
 * @package System\Routing
 */
class RouteProvider extends Provider {

	protected $app;
	protected $path;
	protected $routes = [];


	/**
	 * RouteProvider constructor.
	 * @param Application $app
	 * @param ConfigProvider $config
	 */
	public function __construct(Application $app, ConfigProvider $config)
	{
		$this->app = $app;

		// Path
		$this->path = $config->get('route.path');

		// All route initialization
		$route_tables = $config->get('route.groups');
		$router = $this;
		foreach ($route_tables as $key => $info) {
			require $this->getFilepath($key);
		}
	}


	/**
	 * @param string $key
	 * @return string
	 */
	protected function getFilepath (string $key) {
		return $this->path.$key.'.php';
	}


	/**
	 * Resolve
	 *
	 * @param Request $request
	 */
	public function resolve (Request $request) : ?Response {
		foreach ($this->routes as $route) {
			if ($route->match($request) ) {
				$result = $route->execute($this->app, $request);
				if ($result instanceof View) {
					return $result->toResponse();
				}
				return $result;
			}
		}

		// 404 error
		return $this->app->view->make('errors/404')
			->toResponse(404);
	}


	/**
	 * Bind
	 *
	 * @param string $method
	 * @param string $pattern
	 * @param \Closure $closure
	 */
	public function bind (string $method, string $pattern, \Closure $closure) {
		$this->routes[] = new Route($method, $pattern, $closure);
	}


	/**
	 * Get
	 *
	 * @param string $pattern
	 * @param \Closure $closure
	 */
	public function get (string $pattern, \Closure $closure) {
		$this->bind('GET', $pattern, $closure);
	}


	/**
	 * Post
	 *
	 * @param string $pattern
	 * @param \Closure $closure
	 */
	public function post (string $pattern, \Closure $closure) {
		$this->bind('POST', $pattern, $closure);
	}


	/**
	 * Patch
	 *
	 * @param string $pattern
	 * @param \Closure $closure
	 */
	public function patch (string $pattern, \Closure $closure) {
		$this->bind('PATCH', $pattern, $closure);
	}


	/**
	 * @param string $pattern
	 * @param \Closure $closure
	 */
	public function delete (string $pattern, \Closure $closure) {
		$this->bind('DELETE', $pattern, $closure);
	}

}