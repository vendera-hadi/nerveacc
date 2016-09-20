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

	// master coa
	Route::get('coa','CoaController@index');
	Route::post('coa/get', 'CoaController@get')->name('coa.get');
	Route::post('coa/insert', 'CoaController@insert')->name('coa.insert');
	Route::post('coa/update', 'CoaController@update')->name('coa.update');
	Route::post('coa/delete', 'CoaController@delete')->name('coa.delete');

});

Route::get('logout','Auth\AuthController@logout');
