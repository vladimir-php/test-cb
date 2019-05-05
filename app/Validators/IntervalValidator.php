<?php
namespace App\Validators;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use \Symfony\Component\Validator\Validation;

/**
 * Class IntervalValidator
 * @package App\Validators
 */
class IntervalValidator extends Validator {

	public function __construct(Request $request)
	{
		parent::__construct($request);

		// Constraint initialization
		$this->constraint = [
			'id' => new Assert\Type([
				'type' => 'integer',
				'message' => 'The ID {{ value }} is not a valid {{ type }}.',
			]),
			'date_start' => new Assert\Date([
				'message' => 'The date_start {{ value }} is not a valid date.',
			]),
			'date_end' => new Assert\Date([
				'message' => 'The date_end {{ value }} is not a valid date.',
			]),
			'price' => new Assert\Type([
				'type' => 'float',
				'message' => 'The price {{ value }} is not a valid {{ type }}.',
			]),
		];
	}


	/**
	 * Validate
	 *
	 * @param array $data
	 * @return array
	 */
	public function validate (array $data, array $fields = []) : array {
		$errors = parent::validate($data, $fields);

		// @todo move this condition to the symfony validator (as a custom validator or other)
		if (!isset($data['date_start']) || !isset($data['date_end']) || $data['date_start'] > $data['date_end']) {
			$errors[] = 'Wrong date interval.';
		}

		return $errors;
	}


}

