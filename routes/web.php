<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$router->get('profile', 'ProfileController@getProfile');

$router->get('installment', 'installmentController@getConfiguration');

$router->post('installment', 'installmentController@callCalculation');

$router->post('trx', 'PaymentController@prepareRequest');

$router->put('trx/{transactionId}', 'PaymentController@prepareRequest');
