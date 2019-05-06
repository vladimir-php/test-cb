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


	/**
	 * @param array $keys
	 * @return Assert\Collection
	 */
	public function constraints (array $data = []) : array {
		return array_merge(parent::constraints($data), [
			'id' => [
				new Assert\NotBlank([
					'message' => 'The ID {{ value }} should not be blank.',
				]),
				new Assert\GreaterThan([
					'value' => 0,
					'message' => 'The ID {{ value }} should be more than 0.',
				]),
				new Assert\Type([
					'type' => 'numeric',
					'message' => 'The ID {{ value }} is not a valid {{ type }}.',
				]),
			],
			'date_start' => [
				new Assert\NotBlank([
					'message' => 'The date start {{ value }} should not be blank.',
				]),
				new Assert\Date([
					'message' => 'The date start {{ value }} is not a valid date.',
				]),
			],
			'date_end' => [
				new Assert\NotBlank([
					'message' => 'The date end {{ value }} should not be blank.',
				]),
				new Assert\Date([
					'message' => 'The date end {{ value }} is not a valid date.',
				]),
				new Assert\GreaterThanOrEqual([ // @todo change logic to use propertyPath
					'value' => isset($data['date_start']) ? $data['date_start'] : '',
				]),
			],
			'price' => [
				new Assert\NotBlank([
					'message' => 'The price {{ value }} should not be blank.',
				]),
				new Assert\GreaterThan([
					'value' => 0,
					'message' => 'The price {{ value }} should be more than 0.',
				]),
				new Assert\LessThanOrEqual([
					'value' => 999999.99,
					'message' => 'The price {{ value }} should be less than 999999.99.',
				]),
				/*new Assert\Range([
					'min' => 0.01,
					'max' => 999999.99,
				]),*/
				new Assert\Type([
					'type' => 'numeric',
					'message' => 'The price {{ value }} is not a valid {{ type }}.',
				]),
			],
		]);
	}


}

