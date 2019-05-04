<?php

namespace System\Providers;


use App\Factories\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use System\Containers\Application;

/**
 * Class RouteProvider
 * @package System\Providers
 */
class RouteProvider extends SystemProvider {

	protected $app;
	protected $path;
	protected $routes = [];

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
	 * Put
	 *
	 * @param string $pattern
	 * @param \Closure $closure
	 */
	public function put (string $pattern, \Closure $closure) {
		$this->bind('PUT', $pattern, $closure);
	}


	/**
	 * @param string $pattern
	 * @param \Closure $closure
	 */
	public function delete (string $pattern, \Closure $closure) {
		$this->bind('DELETE', $pattern, $closure);
	}

}


/**
 * Class Route
 * @package System\Providers
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