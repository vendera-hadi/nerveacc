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
	Route::post('complaint/options', 'ComplaintController@getOptions')->name('complaint.options');
	// contract status
	Route::get('contractstatus','ContractStatusController@index');
	Route::get('contractstatus/optContrStatus','ContractStatusController@getOptionContractStatus')->name('contractstatus.select2');
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
	Route::get('rentalperiod/optRenprd','RentalPeriodController@getOptRentalPeriod')->name('rentalperiod.select2');
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
	Route::get('tenant/optTenant', 'TenantController@getOptTenant')->name('tenant.select2');
	Route::post('tenant/options', 'TenantController@getOptions')->name('tenant.options');
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
	Route::get('unit/optUnit','UnitController@getOptUnit')->name('unit.select2');
	Route::post('unit/options', 'UnitController@getOptions')->name('unit.options');
	Route::post('unit/all', 'UnitController@getAll')->name('unit.all');
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
	Route::get('vaccount/optVA','VirtualAccountController@getOptVaccount')->name('vaccount.select2');
	Route::post('vaccount/get', 'VirtualAccountController@get')->name('vaccount.get');
	Route::post('vaccount/insert', 'VirtualAccountController@insert')->name('vaccount.insert');
	Route::post('vaccount/update', 'VirtualAccountController@update')->name('vaccount.update');
	Route::post('vaccount/delete', 'VirtualAccountController@delete')->name('vaccount.delete');

	// marketing agent
	Route::get('marketing','MarketingAgentController@getOptMarketing')->name('marketing.select2');

	// unit complaints / tr complaint
	Route::get('unitcomplaint','UnitComplaintController@index');
	Route::post('unitcomplaint/get', 'UnitComplaintController@get')->name('unitcomplaint.get');
	Route::post('unitcomplaint/insert', 'UnitComplaintController@insert')->name('unitcomplaint.insert');
	Route::post('unitcomplaint/update', 'UnitComplaintController@update')->name('unitcomplaint.update');
	Route::post('unitcomplaint/delete', 'UnitComplaintController@delete')->name('unitcomplaint.delete');

// cash bank
	Route::get('cash_bank','CashBankController@index');
	Route::post('cash_bank/get', 'CashBankController@get')->name('cash_bank.get');
	Route::post('cash_bank/options', 'CashBankController@getOptions')->name('cash_bank.options');
	Route::post('cash_bank/insert', 'CashBankController@insert')->name('cash_bank.insert');
	Route::post('cash_bank/update', 'CashBankController@update')->name('cash_bank.update');
	Route::post('cash_bank/delete', 'CashBankController@delete')->name('cash_bank.delete');

	// company
	Route::get('company','CompanyController@index');
	Route::post('company/get', 'CompanyController@get')->name('company.get');
	Route::post('company/options', 'CompanyController@getOptions')->name('company.options');
	Route::post('company/insert', 'CompanyController@insert')->name('company.insert');
	Route::post('company/update', 'CompanyController@update')->name('company.update');
	Route::post('company/delete', 'CompanyController@delete')->name('company.delete');

	// tr contract
	Route::get('contract','ContractController@index');
	Route::post('contract/get', 'ContractController@get')->name('contract.get');
	Route::post('contract/detail', 'ContractController@getdetail')->name('contract.getdetail');
	Route::post('contract/editModal', 'ContractController@editModal')->name('contract.detail');
	Route::get('contract/optParent', 'ContractController@optionParent')->name('contract.optParent');
	Route::post('contract/insert', 'ContractController@insert')->name('contract.insert');
	Route::post('contract/update', 'ContractController@update')->name('contract.update');
	Route::post('contract/delete', 'ContractController@delete')->name('contract.delete');

	// period meter

	// tr meter
	Route::get('meter','MeterController@index');
});

Route::get('logout','Auth\AuthController@logout');
