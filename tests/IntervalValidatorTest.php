<?php

namespace Tests;


use App\Validators\IntervalValidator;

// @todo add a browser kit testing to test js & interface
// @todo add a unit test to check REST queries

class IntervalValidatorTest extends \PHPUnit\Framework\TestCase {


	/**
	 * Test a validator
	 */
	public function testValidator () {

		$validator = new \App\Validators\IntervalValidator;


		// Check ID
		$this->checkField ($validator, 'id', [
			1, 123, 99999999, '1', '123', '999999999',
		], [
			null, false, '', 'qwerty', '$#%&*', '2010-01-01', -1, 123.123,
		]);


		// Check date_start
		$this->checkDateField ($validator, 'date_start');

		// Check date_end
		$this->checkDateField ($validator, 'date_end');


		// Check date_start & date_end
		$this->assertEmpty($validator->validate([
			'date_start' 	=> '2010-01-01',
			'date_end'		=> '2010-01-01',
		], ['date_start', 'date_end']));
		$this->assertEmpty($validator->validate([
			'date_start' 	=> '2010-01-01',
			'date_end'		=> '2010-01-02',
		], ['date_start', 'date_end']));
		$this->assertNotEmpty($validator->validate([
			'date_start' 	=> '2010-01-02',
			'date_end'		=> '2010-01-01',
		], ['date_start', 'date_end']));


		// Check price
		$this->checkField ($validator, 'price', [
			1, 123, 1.00, 123.123, '123', '123.123', 999999.99, '0.1234',
		], [
			null, false, '', 'qwerty', '$#%&*', '2010-01-01', -1, 1000000,
		]);
	}


	/**
	 * Check date field
	 *
	 * @param IntervalValidator $validator
	 * @param string $field
	 */
	protected function checkDateField (IntervalValidator $validator, string $field) {
		$this->checkField ($validator, $field, [
			'2010-01-01', '2010-10-31', '2014-09-11', '2016-06-07', '2020-12-01',
		], [
			null, false, "", 123, 123.23, "qwerty", "123.123", "$#%&*", "2013-50-01", "2010-11-80",
		]);
	}


	/**
	 * Check field
	 *
	 * @param IntervalValidator $validator
	 * @param string $field
	 * @param array $failed_values
	 */
	protected function checkField (IntervalValidator $validator, string $field, array $success_values = [], array $failed_values = []) {


		// Success values
		foreach ($success_values as $success_value) {
			$this->assertEmpty($validator->validate([$field => $success_value], [$field]));
		}

		// Check empty key
		$this->assertNotEmpty($validator->validate([], [$field]));
		foreach ($failed_values as $failed_value) {
			$this->assertNotEmpty($validator->validate([$field => $failed_value], [$field]));
		}

	}


}