<?php

namespace System\Containers;

use Psr\Container\ContainerInterface;

/**
 * Class Container
 * @package System\Containers
 */
class Container implements ContainerInterface {

	protected static $instances = [];
	protected static $aliases = [];


	/**
	 * @param $id
	 * @param $value
	 */
	public function instance(string $id, $value) {
		$class = is_object($value) ? get_class($value) : $value;
		static::$instances[$id]		= $value;
		static::$aliases[$class]	= $id;
	}


	/**
	 * Find instance
	 *
	 * @param string $id
	 * @return mixed|null
	 */
	public function findInstance (string $id) {

		// Key is a class
		if (class_exists($id) ) {
			if (!isset(static::$aliases[$id]) ) {
				return null;
			}
			$id = static::$aliases[$id];
		}

		// Try to get an instance
		if (!$this->has($id) ) {
			return null;
		}

		return static::$instances[$id];
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
	 * Make
	 *
	 * @param string $class
	 * @return mixed|object|null
	 * @throws \ReflectionException
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
	 * Has
	 * @return bool|void
	 */
	public function has ($id) {
		return isset(static::$instances[$id]);
	}


	/**
	 * @param string $id
	 * @return mixed
	 * @throws \Exception
	 */
	public function get ($id)
	{
		if (!$this->has($id) ) {
			throw new \Exception("Instance $id does not found.");
		}
		$instance = static::$instances[$id];

		// Create a new instance
		if (!is_object($instance) ) {

			// Create an instance
			$instance = $this->make($instance);

			// Save an instance
			static::$instances[$id] = $instance;
		}

		// Instance has been already created
		return $instance;
	}


	/**
	 * @param $id
	 * @return mixed
	 * @throws \Exception
	 */
	public function __get ($id)
	{
		return $this->get ($id);
	}




}