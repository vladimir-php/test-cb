<?php

namespace System\View;

use System\Containers\Application;

/**
 * Class View
 * @package Application\Providers
 * @todo use a custom composer package for template engine (blade, twig, etc.)
 */
class View {

	protected $app;
	protected $data = [];
	protected $filepath;


	/**
	 * View constructor.
	 * @param string $filepath
	 */
	public function __construct(Application $app, string $filepath)
	{
		$this->app = $app;
		$this->filepath = $filepath;
	}

	/**
	 * @param srting $var
	 * @param $value
	 */
	public function with(string $key, $value) : self {
		$this->data[$key] = $value;
		return $this;
	}

	/**
	 * @param array $data
	 */
	public function data ($data = []) : self {
		$this->data = $data;
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getContent () {
		extract(array_merge($this->data, [
			'app' => $this->app,
		]), EXTR_SKIP);

		// Get a content from a templateq
		ob_start();
		require($this->filepath);
		return ob_get_clean();
	}


	/**
	 * To response
	 *
	 * @param Application $app
	 * @param int $status
	 * @param array $headers
	 * @return mixed
	 */
	public function toResponse ($status = 200, array $headers = []) {
		return $this->app->response->make($this->getContent(), $status, $headers);
	}


	/**
	 * @return mixed
	 */
	public function __toString () {
		return $this->getContent();
	}


	/**
	 * To js template
	 *
	 * @return mixed
	 */
	public function toJs () {
		return str_replace(["\r", "\n", "\t"], '', $this->getContent());
	}

}