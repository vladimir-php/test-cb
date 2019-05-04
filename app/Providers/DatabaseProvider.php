<?php

namespace App\Providers;

use Doctrine\DBAL\Query\QueryBuilder;
use System\Containers\Application;
use System\Providers\ConfigProvider;

/**
 * Class DatabaseProvider
 * @package App\Providers
 */
class DatabaseProvider extends ApplicationProvider {

	protected $connection;


	/**
	 * DatabaseProvider constructor.
	 * @param Application $app
	 * @param ConfigProvider $config
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Exception
	 */
	public function __construct(Application $app, ConfigProvider $config)
	{
		parent::__construct($app);

		// Get a DB config
		$active_driver = $config->get('database.active_driver');
		$db_config = $config->get('database.drivers.'.$active_driver);

		if (!$db_config) {
			throw new \Exception('Database config does not exist or incorrect.');
		}

		// Create a connection
		$this->connection = \Doctrine\DBAL\DriverManager::getConnection($db_config);
	}


	/**
	 * Get a connection
	 *
	 * @return \Doctrine\DBAL\Connection
	 */
	public function connection () {
		return $this->connection;
	}


	/**
	 * @return QueryBuilder
	 */
	public function query () : QueryBuilder {
		return $this->connection->createQueryBuilder();
	}

}