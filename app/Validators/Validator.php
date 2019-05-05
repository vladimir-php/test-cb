<?php
namespace App\Validators;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use \Symfony\Component\Validator\Validation;

/**
 * Class Validator
 * @package App\Validators
 */
abstract class Validator {

	protected $validator;
	protected $constraint;

	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->validator = Validation::createValidator();
	}


	/**
	 * @param array $keys
	 * @return Assert\Collection
	 */
	public function constraint (array $keys) : Assert\Collection {
		$constraint = [];
		foreach ($keys as $key) {
			$constraint[] = $this->constraint[$key];
		}
		return new Assert\Collection($constraint);
	}


	/**
	 * Base validate function (@todo can be an ValidateResult or more flexible solution)
	 * @param array $data
	 * @return array
	 */
	public function validate (array $data, array $fields = []) : array {

		// Get a custom rules by fields
		$constraint = $this->constraint;
		if ($fields) {
			$constraint = $this->constraint($fields);
		}

		// Base validation logic
		$violations = $this->validator->validate($data, $constraint);

		// Get validation errors
		$errors = [];
		foreach ($violations as $violation) {
			$errors[] = $violation->getMessage();
		}
		return $errors;
	}

}

