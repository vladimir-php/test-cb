<?php

use System\Containers\Application;
use Symfony\Component\HttpFoundation\Request;

// @todo add controller classes & move part of the logic to them
$router->get('/', function(Application $app, Request $request) {

	// Get an interval list (NOT need to sort here, all sort logic is executing on JS)
	$intervals = $app->interval_model->get(function($query){
		return $query; // there can be extra logic with the query (where, sorting etc.)
	});

	return $app->view->make('index')
		->with('intervals', $intervals);
});


// Create an interval
$router->put('/interval', function(Application $app, Request $request) {
	$data = json_decode($request->getContent(), true);

	$interval = $app->interval_model->create($data);

	/*
	$validator = new \App\Validators\IntervalValidator($request);
	$errors = $validator->validate([
		'id'			=> 123,
		'date_start'	=> "2010-01-01",
		'date_end'		=> '2011-02-04',
		'price'			=> 12.2
	]);
	dd ($errors);
	*/

	return $app->response->make($interval->toJson());
});

// Update an interval
$router->post('/interval', function(Application $app, Request $request) {

	$interval_id = $request->get('id');

	$data = [
		'date_start' => $request->get('date_start'),
		'date_end' => $request->get('date_end'),
		'price' => $request->get('price'),
	];

	$app->interval_model->update($interval_id, $data);

	return $app->response->make(json_encode(['success' => true]));
});

// Delete an interval
$router->delete('/interval', function(Application $app, Request $request) {
	$interval_id = $request->get('id');

	$app->interval_model->delete($interval_id);

	return $app->response->make(json_encode(['success' => true]));
});


// Delete all intervals
$router->delete('/interval/all', function(Application $app, Request $request) {
	$app->interval_model->delete();
	return $app->response->make(json_encode(['success' => true]));
});