<?php

namespace App\Factories\Model;

use App\Models\Interval;

/**
 * Class IntervalFactory
 * @package App\Factories\Model
 */
class IntervalFactory extends ModelFactory {

	protected $table = 'intervals';
	protected $class = Interval::class;

}