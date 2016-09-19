<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
Route::group(['middleware' => 'auth'], function () {
	
	Route::get('/', function () {
	    return redirect('home'); 
	});

	// currency
	Route::get('currency','CurrencyController@index');
	Route::post('currency/get', 'CurrencyController@get')->name('currency.get');
	Route::post('currency/insert', 'CurrencyController@insert')->name('currency.insert');
	Route::post('currency/update', 'CurrencyController@update')->name('currency.update');
	Route::post('currency/delete', 'CurrencyController@delete')->name('currency.delete');
	// complaint
	Route::get('complaint','ComplaintController@index');
	Route::post('complaint/get', 'ComplaintController@get')->name('complaint.get');
	Route::post('complaint/insert', 'ComplaintController@insert')->name('complaint.insert');
	Route::post('complaint/update', 'ComplaintController@update')->name('complaint.update');
	Route::post('complaint/delete', 'ComplaintController@delete')->name('complaint.delete');
	// contract status
	Route::get('contractstatus','ContractStatusController@index');
	Route::post('contractstatus/get', 'ContractStatusController@get')->name('contractstatus.get');
	Route::post('contractstatus/insert', 'ContractStatusController@insert')->name('contractstatus.insert');
	Route::post('contractstatus/update', 'ContractStatusController@update')->name('contractstatus.update');
	Route::post('contractstatus/delete', 'ContractStatusController@delete')->name('contractstatus.delete');

	// Contoh area
	// page contoh view
	Route::get('contoh', 'HomeController@contoh');
	// ajax get data table
	Route::post('contohget', 'HomeController@contohget')->name('contoh.get');
	// ajax insert
	Route::post('contohinsert', 'HomeController@contohinsert')->name('contoh.insert');
	// ajax update
	Route::post('contohupdate', 'HomeController@contohupdate')->name('contoh.update');
	// ajax delete
	Route::post('contohdelete', 'HomeController@contohdelete')->name('contoh.delete');

});

Route::get('logout','Auth\AuthController@logout');
