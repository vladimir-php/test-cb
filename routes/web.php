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



// @todo move json encode / decode logic to overrided Response class


// --- Create an interval
$router->put('/interval', function(Application $app, Request $request) {
	$data = json_decode($request->getContent(), true);

	// Validate @todo unify error logic
	$errors = (new \App\Validators\IntervalValidator)
		->validate($data, ['date_start', 'date_end', 'price']);
	if ($errors) {
		return $app->response->make(json_encode(['success' => false, 'errors' => $errors]));
 	}

	// Create new interval
	$interval = $app->interval_model->create($data);
	return $app->response->make(json_encode(['success' => true, 'data' => $interval->toArray()]));
});



// --- Update an interval
$router->post('/interval', function(Application $app, Request $request) {

	$interval_id = $request->get('id');

	$data = [
		'date_start' => $request->get('date_start'),
		'date_end' => $request->get('date_end'),
		'price' => $request->get('price'),
	];

	// Validate @todo unify error logic
	$errors = (new \App\Validators\IntervalValidator)
		->validate(array_merge($data, ['id' => $interval_id]));
	if ($errors) {
		return $app->response->make(json_encode(['success' => false, 'errors' => $errors]));
	}

	// Update an interval
	$app->interval_model->update($interval_id, $data);
	return $app->response->make(json_encode(['success' => true]));
});



// --- Delete an interval
$router->delete('/interval', function(Application $app, Request $request) {
	$interval_id = $request->get('id');

	// Validate @todo unify error logic
	$errors = (new \App\Validators\IntervalValidator)
		->validate(['id' => $interval_id], ['id']);
	if ($errors) {
		return $app->response->make(json_encode(['success' => false, 'errors' => $errors]));
	}

	// Delete an interval
	$app->interval_model->delete($interval_id);
	return $app->response->make(json_encode(['success' => true]));
});



// --- Delete all intervals
$router->delete('/interval/all', function(Application $app, Request $request) {
	$app->interval_model->delete();
	return $app->response->make(json_encode(['success' => true]));
});