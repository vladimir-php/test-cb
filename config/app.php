<?php

return [
	'providers' => [
		'db' 		=> \App\Providers\DatabaseProvider::class,
	],
	'factories' => [
		'view'		=> \App\Factories\ViewFactory::class,
		'response'	=> \App\Factories\ResponseFactory::class,

		// Models
		'interval_model' => \App\Factories\Model\IntervalFactory::class,
	],
];