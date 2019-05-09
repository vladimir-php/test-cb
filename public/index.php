<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__.'/../vendor/autoload.php';

function dd ($output) {
	print_r($output);
	die ();
}


// Create a config provider
$config = new \System\Config\ConfigProvider('../config');

// Create an application
$app = new \System\Containers\Application($config);

// Create a request
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

// Resolve the request with router
$response = $app->router->resolve ($request);

// Send a response
$response->send ();