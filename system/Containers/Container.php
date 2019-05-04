<?php

namespace System\Containers;


/**
 * Class Container
 * @package System\Containers
 */
class Container {

	protected static $instances = [];
	protected static $aliases = [];


	/**
	 * @param $key
	 * @param $value
	 */
	public function instance($key, $value) {
		$class = is_object($value) ? get_class($value) : $value;
		static::$instances[$key]	= $value;
		static::$aliases[$class]	= $key;
	}


	/**
	 * Find instance
	 *
	 * @param string $key
	 * @return mixed|null
	 */
	public function findInstance (string $key) {

		// Key is a class
		if (class_exists($key) ) {
			if (!isset(static::$aliases[$key]) ) {
				return null;
			}
			$key = static::$aliases[$key];
		}

		// Try to get an instance
		if (!isset(static::$instances[$key]) ) {
			return null;
		}

		return static::$instances[$key];
	}


	/**
	 * Find a class
	 *
	 * @param string $class
	 * @return mixed|string
	 * @throws \Exception
	 */
	public function findClass (string $class) {

		// Passed parameter is a key, NOT class - try to find it in aliases
		if (!class_exists($class) ) {
			if (!isset(static::$aliases[$class]) ) {
				return null;
			}
			$class = static::$aliases[$class];
		}

		return $class;
	}


	/**
	 * @param $key
	 * @return mixed
	 * @throws \Exception
	 */
	protected function make(string $class)
	{
		// The instance has already been initialized
		$instance = $this->findInstance($class);
		if ($instance && is_object($instance) ) {
			return $instance;
		}

		// Get an instance class
		$class = $this->findClass($class);
		if (!$class) {
			throw new \Exception("Instance $class does not exist.");
		}

		// Get a reflection class to get parameters from the contruct
		$reflector = new \ReflectionClass($class);
		if (!$reflector->isInstantiable()) {
			throw new \Exception("Target [$class] is not instantiable.");
		}

		// Get a construct
		$constructor = $reflector->getConstructor();

		// Has not construct => create a new instance without parameters
		if (!$constructor) {
			return $reflector->newInstance();
		}

		// Get instances to resolve dependencies in construct parameters
		$instances = $this->resolveDependencies(
			$constructor->getParameters()
		);

		// Create a new instance
		return $reflector->newInstanceArgs($instances);
	}


	/**
	 * Resolve dependencies
	 *
	 * @param array $params
	 */
	protected function resolveDependencies (array $dependencies)
	{
		$instances = [];
		foreach ($dependencies as $dependency) {

			// A dependency is not an object @todo need to add a resolver code here
			if ($dependency->getClass() === null) {
				// ...
			}

			// Get an instance by class
			else {
				$instances[] = $this->make($dependency->getClass()->name);
			}
		}

		return $instances;
	}


	/**
	 * @param $key
	 * @throws \Exception
	 */
	public function __get ($key)
	{
		if (!isset(static::$instances[$key]) ) {
			throw new \Exception("Instance $key does not found.");
		}
		$instance = static::$instances[$key];

		// Create a new instance
		if (!is_object($instance) ) {

			// Create an instance
			$instance = $this->make($instance);

			// Save an instance
			static::$instances[$key] = $instance;
		}

		// Instance has been already created
		return $instance;
	}




}