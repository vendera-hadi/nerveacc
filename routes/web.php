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
	// department
	Route::get('department','DepartmentController@index');
	Route::post('department/get', 'DepartmentController@get')->name('department.get');
	Route::post('department/insert', 'DepartmentController@insert')->name('department.insert');
	Route::post('department/update', 'DepartmentController@update')->name('department.update');
	Route::post('department/delete', 'DepartmentController@delete')->name('department.delete');
	// invoice type
	Route::get('invtype','InvoiceTypeController@index');
	Route::post('invtype/get', 'InvoiceTypeController@get')->name('invtype.get');
	Route::post('invtype/insert', 'InvoiceTypeController@insert')->name('invtype.insert');
	Route::post('invtype/update', 'InvoiceTypeController@update')->name('invtype.update');
	Route::post('invtype/delete', 'InvoiceTypeController@delete')->name('invtype.delete');
	// group account
	Route::get('groupaccount','GroupAccountController@index');
	Route::post('groupaccount/get', 'GroupAccountController@get')->name('groupaccount.get');
	Route::post('groupaccount/options', 'GroupAccountController@getOptions')->name('groupaccount.options');
	Route::post('groupaccount/insert', 'GroupAccountController@insert')->name('groupaccount.insert');
	Route::post('groupaccount/update', 'GroupAccountController@update')->name('groupaccount.update');
	Route::post('groupaccount/delete', 'GroupAccountController@delete')->name('groupaccount.delete');
	// group account detail
	Route::get('groupaccdetail','GroupAccountDetailController@index');
	Route::post('groupaccdetail/get', 'GroupAccountDetailController@get')->name('groupaccdetail.get');
	Route::post('groupaccdetail/insert', 'GroupAccountDetailController@insert')->name('groupaccdetail.insert');
	Route::post('groupaccdetail/update', 'GroupAccountDetailController@update')->name('groupaccdetail.update');
	Route::post('groupaccdetail/delete', 'GroupAccountDetailController@delete')->name('groupaccdetail.delete');


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
	Route::post('coa/years', 'CoaController@getCoaYear')->name('coa.year');
	Route::post('coa/code', 'CoaController@getCoaCode')->name('coa.code');
	Route::post('coa/insert', 'CoaController@insert')->name('coa.insert');
	Route::post('coa/update', 'CoaController@update')->name('coa.update');
	Route::post('coa/delete', 'CoaController@delete')->name('coa.delete');
	// Rental period
	Route::get('rentalperiod','RentalPeriodController@index');
	Route::post('rentalperiod/get', 'RentalPeriodController@get')->name('rentalperiod.get');
	Route::post('rentalperiod/insert', 'RentalPeriodController@insert')->name('rentalperiod.insert');
	Route::post('rentalperiod/update', 'RentalPeriodController@update')->name('rentalperiod.update');
	Route::post('rentalperiod/delete', 'RentalPeriodController@delete')->name('rentalperiod.delete');
	// master supplier
	Route::get('supplier','SupplierController@index');
	Route::post('supplier/get', 'SupplierController@get')->name('supplier.get');
	Route::post('supplier/insert', 'SupplierController@insert')->name('supplier.insert');
	Route::post('supplier/update', 'SupplierController@update')->name('supplier.update');
	Route::post('supplier/delete', 'SupplierController@delete')->name('supplier.delete');
	// master tenant
	Route::get('tenant','TenantController@index');
	Route::post('tenant/get', 'TenantController@get')->name('tenant.get');
	Route::post('tenant/insert', 'TenantController@insert')->name('tenant.insert');
	Route::post('tenant/update', 'TenantController@update')->name('tenant.update');
	Route::post('tenant/delete', 'TenantController@delete')->name('tenant.delete');
	// master tenant type
	Route::get('typetenant','TenantTypeController@index');
	Route::post('typetenant/get', 'TenantTypeController@get')->name('typetenant.get');
	Route::post('typetenant/insert', 'TenantTypeController@insert')->name('typetenant.insert');
	Route::post('typetenant/update', 'TenantTypeController@update')->name('typetenant.update');
	Route::post('typetenant/delete', 'TenantTypeController@delete')->name('typetenant.delete');
	// master unit
	Route::get('unit','UnitController@index');
	Route::post('unit/get', 'UnitController@get')->name('unit.get');
	Route::post('unit/insert', 'UnitController@insert')->name('unit.insert');
	Route::post('unit/update', 'UnitController@update')->name('unit.update');
	Route::post('unit/delete', 'UnitController@delete')->name('unit.delete');
	// master unit type
	Route::get('unittype','UnitTypeController@index');
	Route::post('unittype/get', 'UnitTypeController@get')->name('unittype.get');
	Route::post('unittype/insert', 'UnitTypeController@insert')->name('unittype.insert');
	Route::post('unittype/update', 'UnitTypeController@update')->name('unittype.update');
	Route::post('unittype/delete', 'UnitTypeController@delete')->name('unittype.delete');
	// master virtual account
	Route::get('vaccount','VirtualAccountController@index');
	Route::post('vaccount/get', 'VirtualAccountController@get')->name('vaccount.get');
	Route::post('vaccount/insert', 'VirtualAccountController@insert')->name('vaccount.insert');
	Route::post('vaccount/update', 'VirtualAccountController@update')->name('vaccount.update');
	Route::post('vaccount/delete', 'VirtualAccountController@delete')->name('vaccount.delete');
});

Route::get('logout','Auth\AuthController@logout');
