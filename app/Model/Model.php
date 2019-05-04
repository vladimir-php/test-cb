<?php

namespace App\Model;


use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Class Model
 * @package App\Model
 */
abstract class Model {


	/**
	 * Model constructor.
	 * @param array $arguments
	 */
	public function __construct(array $arguments = [])
	{

		// @todo add attrbutes property to check external attributes set
		foreach ($arguments as $key => $value) {
			$this->$key = $value;
		}
	}


	/**
	 * To array
	 *
	 * @return array
	 */
	public function toArray () {
		// @todo add logic with limited attributes list
		return get_object_vars($this);
	}


	/**
	 * To json
	 *
	 * @return json
	 */
	public function toJson () {
		return json_encode($this->toArray());
	}



}