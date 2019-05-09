<?php

namespace App\Factories\Model;


use App\Models\Interval;
use App\Models\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use phpDocumentor\Reflection\Types\Integer;
use System\Containers\Application;

/**
 * Class ModelFactory
 * @package App\Factories\Model
 */
abstract class ModelFactory {

	protected $app;

	protected $class;
	protected $table;


	/**
	 * ModelFactory constructor.
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}


	/**
	 * Query
	 *
	 * @return QueryBuilder
	 */
	public function query () : QueryBuilder {
		return $this->app->db
			->query()
			->from($this->table);
	}


	/**
	 * Get first by ID
	 *
	 * @param int $id
	 * @return mixed
	 */
	public function first (int $id) {
		return $this->one(function($query) use ($id) {
			return $query->where ('id = '.$id);
		});
	}


	/**
	 * Make a model
	 *
	 * @param array
	 * @return model object
	 */
	public function make (array $data) {
		return new $this->class ($data);
	}


	/**
	 * One
	 *
	 * @param QueryBuilder $query
	 * @return mixed
	 */
	public function one (\Closure $closure, $columns = '*') {

		// Query
		$query = $this->query()->select($columns);

		// Fetch data from the DB
		$data = $closure($query)
			->execute()
			->fetch();

		// Create an interval instance
		return $this->make($data);
	}


	/**
	 * Get
	 *
	 * @param \Closure $closure
	 * @return array
	 */
	public function get (\Closure $closure = null, $columns = '*') {

		// Query
		$query = $this->query()->select($columns);

		// Has an extra logic for the query
		if ($closure !== null) {
			$query = $closure($query);
		}

		// Fetch all data from the DB
		$items = $query->execute()
			->fetchAll();

		// Generate list of iterval models
		$result = [];
		foreach ($items as $item) {
			$result[] = $this->make($item);
		}
		return $result;
	}


	/**
	 * Create
	 *
	 * @param array $attributes
	 */
	public function create (array $attributes) : Model {

		// Create an insert query
		$query = $this->query()
			->insert($this->table);

		// Set values
		foreach ($attributes as $key => $value) {
			$query->setValue($key, '\''.$value.'\'');
		}

		// Insert exectution
		$query->execute();

		// Get the last insert ID
		$last_insert_id = $this->app->db
			->connection()
			->lastInsertId();

		// Get a model
		return $this->first($last_insert_id);
	}


	/**
	 * Update
	 *
	 * @param int $id
	 * @param array $attributes
	 */
	public function update (int $id, array $attributes) : void {

		// Create an update query
		$query = $this->query()
			->where('id = :id')
			->setParameter(':id', $id)
			->update($this->table);


		// Set values
		foreach ($attributes as $key => $value) {
			$query->set($key, '\''.$value.'\'');
		}

		// Insert exectution
		$query->execute();
	}


	/**
	 * Delete
	 *
	 * @param int $id
	 */
	public function delete (int $id = null) : void {
		$query = $this->query()
			->delete($this->table);

		if ($id !== null) {
			$query->where('id = :id')
				->setParameter(':id', $id);
		}

		$query->execute();
	}



}