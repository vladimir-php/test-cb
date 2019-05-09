<?php

namespace System\View;



use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use System\Config\ConfigProvider;
use System\Containers\Application;


/**
 * Class ViewFactory
 * @package Application\Providers
 */
class ViewFactory {

	protected $app;
	protected $path;


	/**
	 * ViewFactory constructor.
	 * @param Application $app
	 * @param ConfigProvider $config
	 */
	public function __construct(Application $app, ConfigProvider $config)
	{
		$this->app = $app;
		$this->path = $config->get('view.path');
	}


	/**
	 * @param $file
	 * @return string
	 */
	public function getFilepath ($file) {
		return $this->path.'/'.$file.'.php';
	}


	/**
	 * @param string $file
	 * @param array $data
	 * @return View
	 */
	public function make (string $file, array $data = []) {
		return (new View($this->app, $this->getFilepath($file)))
			->data($data);
	}

}
