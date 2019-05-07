<?php
namespace App\Validators;

use Symfony\Component\Validator\Constraints as Assert;
use \Symfony\Component\Validator\Validation;

/**
 * Class Validator
 * @package App\Validators
 */
abstract class Validator {

	protected $validator;
	protected $constraint;


	/**
	 * Validator constructor.
	 */
	public function __construct()
	{
		$this->validator = Validation::createValidator();
	}


	/**
	 * @param array $keys
	 * @return Assert\Collection
	 */
	public function constraints (array $data = []) : array {
		return [];
	}


	/**
	 * Base validate function (@todo can be an ValidateResult or more flexible solution)
	 * @param array $data
	 * @return array
	 */
	public function validate (array $data, array $fields = null) : array {

		// Get a custom rules by fields
		$constraints = $this->constraints($data);

		// All fields
		$all_fields = array_keys($constraints);

		// Check for fields default value
		$fields = $fields ?? $all_fields;

		// Filtering data
		$filtered_data = [];
		foreach ($fields as $field) {
			$filtered_data[$field] = isset($data[$field]) ? $data[$field] : null;
		}

		// Check to unset some rules by field keys
		$unset_fields = array_diff($all_fields, $fields);
		foreach ($unset_fields as $unset_field) {
			unset ($constraints[$unset_field]);
		}

		// Create an assert collection
		$constraints = new Assert\Collection($constraints);


		// Base validation logic
		$violations = $this->validator->validate($filtered_data, $constraints);

		// Get validation errors
		$errors = [];
		foreach ($violations as $violation) {
			$errors[$violation->getPropertyPath()] = $violation->getMessage();
		}
		return $errors;
	}

}

