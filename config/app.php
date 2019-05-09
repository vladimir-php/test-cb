<?php

return [
	'providers' => [
		'db' 		=> \System\Database\DatabaseProvider::class,
	],
	'factories' => [
		'view'		=> \System\View\ViewFactory::class,
		'response'	=> \App\Factories\ResponseFactory::class,

		// Models
		'interval_model' => \App\Factories\Model\IntervalFactory::class,
	],
];