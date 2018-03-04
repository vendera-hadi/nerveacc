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
	    return redirect('/home');
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
	Route::get('groupaccount/getdetail', 'GroupAccountController@getDetail')->name('groupaccount.getdetail');
	Route::post('groupaccount/updatedetail', 'GroupAccountController@updateDetail')->name('groupaccount.updatedetail');
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
	Route::get('coa','CoaController@index')->name('coa.index');
	Route::post('coa/get', 'CoaController@get')->name('coa.get');
	Route::post('coa/years', 'CoaController@getCoaYear')->name('coa.year');
	Route::post('coa/code', 'CoaController@getCoaCode')->name('coa.code');
	Route::post('coa/insert', 'CoaController@insert')->name('coa.insert');
	Route::post('coa/update', 'CoaController@update')->name('coa.update');
	Route::post('coa/delete', 'CoaController@delete')->name('coa.delete');
	Route::get('coa/downloadCoaExcel', 'CoaController@downloadCoaExcel');
	Route::get('coa/tplUploadCoaExcel', 'CoaController@dlTemplateUploadCoaExcel');
	Route::post('coa/upload', 'CoaController@upload')->name('coa.upload');
	Route::get('coa/printCoaExcel','CoaController@printCoa')->name('coa.printCoa');
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
	Route::post('supplier/ajaxget', 'SupplierController@ajaxdtl')->name('supplier.ajaxget');
	// master tenant
	Route::get('tenant','TenantController@index');
	Route::post('tenant/get', 'TenantController@get')->name('tenant.get');
	Route::post('tenant/modaldt', 'TenantController@modaldt')->name('tenant.modaldt');
	Route::get('tenant/optTenant', 'TenantController@getOptTenant')->name('tenant.select2');
	Route::post('tenant/popup', 'TenantController@getPopupOptions')->name('tenant.popup');
	Route::post('tenant/options', 'TenantController@getOptions')->name('tenant.options');
	Route::post('tenant/edit', 'TenantController@edit')->name('tenant.edit');
	Route::post('tenant/insert', 'TenantController@insert')->name('tenant.insert');
	Route::post('tenant/update', 'TenantController@update')->name('tenant.update');
	Route::post('tenant/delete', 'TenantController@delete')->name('tenant.delete');
	Route::post('tenant/deleteunit', 'TenantController@deleteunit')->name('tenant.deleteunit');
	Route::post('tenant/addunit', 'TenantController@addunit')->name('tenant.addunit');
	Route::post('tenant/outstanding', 'TenantController@outstanding')->name('tenant.outstanding');

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
	Route::post('unit/popup', 'UnitController@getPopupOptions')->name('unit.popup');
	Route::post('unit/fopt', 'UnitController@fopt')->name('unit.fopt');
	Route::post('unit/all', 'UnitController@getAll')->name('unit.all');
	Route::post('unit/insert', 'UnitController@insert')->name('unit.insert');
	Route::post('unit/update', 'UnitController@update')->name('unit.update');
	Route::post('unit/delete', 'UnitController@delete')->name('unit.delete');
	Route::post('unit/option2', 'UnitController@getOptions_account')->name('unit.option2');
	Route::post('unit/ajaxdetail','UnitController@newAjaxUnitDetail')->name('unit.ajaxdetail');
	Route::post('unit/modaldetail', 'UnitController@getdetail')->name('unit.modaldetail');

	// master unit type
	Route::get('unittype','UnitTypeController@index');
	Route::post('unittype/get', 'UnitTypeController@get')->name('unittype.get');
	Route::post('unittype/insert', 'UnitTypeController@insert')->name('unittype.insert');
	Route::post('unittype/update', 'UnitTypeController@update')->name('unittype.update');
	Route::post('unittype/delete', 'UnitTypeController@delete')->name('unittype.delete');
	// master virtual account
	Route::get('vaccount','VirtualAccountController@index')->name('vaccount.index');
	Route::get('vaccount/optVA','VirtualAccountController@getOptVaccount')->name('vaccount.select2');
	Route::post('vaccount/get', 'VirtualAccountController@get')->name('vaccount.get');
	Route::post('vaccount/insert', 'VirtualAccountController@insert')->name('vaccount.insert');
	Route::post('vaccount/update', 'VirtualAccountController@update')->name('vaccount.update');
	Route::post('vaccount/delete', 'VirtualAccountController@delete')->name('vaccount.delete');

	// marketing agent
	Route::get('marketing','MarketingAgentController@index')->name('marketing.index');
	Route::post('marketing/get', 'MarketingAgentController@get')->name('marketing.get');
	Route::post('marketing/insert', 'MarketingAgentController@insert')->name('marketing.insert');
	Route::post('marketing/update', 'MarketingAgentController@update')->name('marketing.update');
	Route::post('marketing/delete', 'MarketingAgentController@delete')->name('marketing.delete');
	Route::get('marketing/optmarketing','MarketingAgentController@getOptMarketing')->name('marketing.select2');

	// unit complaints / tr complaint
	Route::get('unitcomplaint','UnitComplaintController@index')->name('unitcomplaint.index');
	Route::post('unitcomplaint/get', 'UnitComplaintController@get')->name('unitcomplaint.get');
	Route::post('unitcomplaint/insert', 'UnitComplaintController@insert')->name('unitcomplaint.insert');
	Route::post('unitcomplaint/update', 'UnitComplaintController@update')->name('unitcomplaint.update');
	Route::post('unitcomplaint/delete', 'UnitComplaintController@delete')->name('unitcomplaint.delete');

	// cash bank
	Route::get('cash_bank','CashBankController@index')->name('cash_bank.index');
	Route::post('cash_bank/get', 'CashBankController@get')->name('cash_bank.get');
	Route::post('cash_bank/options', 'CashBankController@getOptions')->name('cash_bank.options');
	Route::post('cash_bank/insert', 'CashBankController@insert')->name('cash_bank.insert');
	Route::post('cash_bank/update', 'CashBankController@update')->name('cash_bank.update');
	Route::post('cash_bank/delete', 'CashBankController@delete')->name('cash_bank.delete');

	// company
	Route::get('company2','CompanyController@index');
	Route::get('company','CompanyController@index2');
	Route::post('company/get', 'CompanyController@get')->name('company.get');
	Route::post('company/options', 'CompanyController@getOptions')->name('company.options');
	Route::post('company/insert', 'CompanyController@insert')->name('company.insert');
	Route::post('company/update', 'CompanyController@update')->name('company.update');
	Route::post('company/delete', 'CompanyController@delete')->name('company.delete');
	Route::get('config','CompanyController@config');
	Route::post('config', 'CompanyController@configUpdate')->name('config.update');

	// tr contract
	Route::get('contract','ContractController@index')->name('contract.index');
	Route::post('contract/get', 'ContractController@get')->name('contract.get');
	Route::post('contract/get2', 'ContractController@getOwner')->name('contract.get2');
	Route::post('contract/detail', 'ContractController@getdetail')->name('contract.getdetail');
	Route::post('contract/ctrDetail', 'ContractController@ctrDetail')->name('contract.ctrdetail');
	Route::post('contract/citmDetail', 'ContractController@citmDetail')->name('contract.citmdetail');
	Route::get('contract/optParent', 'ContractController@optionParent')->name('contract.optParent');
	Route::post('contract/cdtupdate','ContractController@costdetailUpdate')->name('contract.cdtupdate');
	Route::post('contract/insert', 'ContractController@insert')->name('contract.insert');
	Route::post('contract/update', 'ContractController@update')->name('contract.update');
	Route::post('contract/delete', 'ContractController@delete')->name('contract.delete');
	Route::get('contract/confirmation','ContractController@confirmation')->name('contract.confirmation');
	Route::get('contract/addendum','ContractController@addendum')->name('contract.addendum');
	Route::get('contract/renewal','ContractController@renewal')->name('contract.renewal');
	Route::get('contract/termination','ContractController@termination')->name('contract.termination');
	Route::post('contract/getother/{page}','ContractController@getOther')->name('contract.getother');
	Route::post('contract/confirm','ContractController@confirm')->name('contract.confirm');
	Route::post('contract/inputed','ContractController@inputed')->name('contract.inputed');
	Route::post('contract/terminate','ContractController@terminate')->name('contract.terminate');
	Route::post('contract/renew','ContractController@renew')->name('contract.renew');
	Route::post('contract/popup','ContractController@getPopupOptions')->name('contract.popup');

	Route::get('contract/unclosed','ContractController@unclosed')->name('contract.unclosed');
	Route::post('contract/getUnclosed', 'ContractController@unclosedList')->name('contract.getunclosed');
	Route::post('contract/closeCtrModal', 'ContractController@closeCtrModal')->name('contract.closeCtrModal');
	Route::post('contract/closeCtr', 'ContractController@closeCtrProcess')->name('contract.closectr');
	// period meter

	// tr meter
	Route::get('meter','MeterController@index');

	//cost item
	Route::get('cost_item','CostItemController@index');
	Route::post('cost_item/get', 'CostItemController@get')->name('cost_item.get');
	Route::post('cost_item/insert', 'CostItemController@insert')->name('cost_item.insert');
	Route::post('cost_item/update', 'CostItemController@update')->name('cost_item.update');
	Route::post('cost_item/delete', 'CostItemController@delete')->name('cost_item.delete');
	Route::post('cost_item/getDetail', 'CostItemController@getDetail')->name('cost_item.getDetail');
	Route::post('cost_item/cost_detail','CostItemController@cost_detail')->name('cost_item.cost_detail');
	Route::post('cost_item/getOptionsCoa', 'CostItemController@getOptionsCoa')->name('cost_item.getOptionsCoa');

	//cost detail
	Route::get('cost_detail','CostDetailController@index');
	Route::post('cost_detail/get', 'CostDetailController@get')->name('cost_detail.get');
	Route::post('cost_detail/options', 'CostDetailController@getOptions')->name('cost_detail.options');
	Route::post('cost_detail/insert', 'CostDetailController@insert')->name('cost_detail.insert');
	Route::post('cost_detail/update', 'CostDetailController@update')->name('cost_detail.update');
	Route::post('cost_detail/delete', 'CostDetailController@delete')->name('cost_detail.delete');

	//floor
	Route::get('floor','FloorController@index')->name('floor.index');
	Route::post('floor/get', 'FloorController@get')->name('floor.get');
	Route::post('floor/insert', 'FloorController@insert')->name('floor.insert');
	Route::post('floor/update', 'FloorController@update')->name('floor.update');
	Route::post('floor/delete', 'FloorController@delete')->name('floor.delete');

	//journal type
	Route::get('journal_type','JournalTypeController@index');
	Route::post('journal_type/get', 'JournalTypeController@get')->name('journal_type.get');
	Route::post('journal_type/insert', 'JournalTypeController@insert')->name('journal_type.insert');
	Route::post('journal_type/update', 'JournalTypeController@update')->name('journal_type.update');
	Route::post('journal_type/delete', 'JournalTypeController@delete')->name('journal_type.delete');

	//payment type
	Route::get('payment_type','PaymentTypeController@index');
	Route::post('payment_type/get', 'PaymentTypeController@get')->name('payment_type.get');
	Route::post('payment_type/insert', 'PaymentTypeController@insert')->name('payment_type.insert');
	Route::post('payment_type/update', 'PaymentTypeController@update')->name('payment_type.update');
	Route::post('payment_type/delete', 'PaymentTypeController@delete')->name('payment_type.delete');

	//unit owner
	Route::get('unit_owner','UnitOwnerController@index');
	Route::post('unit_owner/get', 'UnitOwnerController@get')->name('unit_owner.get');
	Route::post('unit_owner/tenanopt', 'UnitOwnerController@tenanopt')->name('unit_owner.tenanopt');
	Route::post('unit_owner/unitopt', 'UnitOwnerController@unitopt')->name('unit_owner.unitopt');
	Route::post('unit_owner/insert', 'UnitOwnerController@insert')->name('unit_owner.insert');
	Route::post('unit_owner/update', 'UnitOwnerController@update')->name('unit_owner.update');
	Route::post('unit_owner/delete', 'UnitOwnerController@delete')->name('unit_owner.delete');

	//category asset
	Route::get('cat_asset','CategoryAssetController@index');
	Route::post('cat_asset/get', 'CategoryAssetController@get')->name('cat_asset.get');
	Route::post('cat_asset/insert', 'CategoryAssetController@insert')->name('cat_asset.insert');
	Route::post('cat_asset/update', 'CategoryAssetController@update')->name('cat_asset.update');
	Route::post('cat_asset/delete', 'CategoryAssetController@delete')->name('cat_asset.delete');

	//fixed assets
	// Route::get('fixed_asset','AssetsController@index');
	// Route::post('fixed_asset/get', 'AssetsController@get')->name('fixed_asset.get');
	// Route::post('fixed_asset/category_option', 'AssetsController@category_option')->name('fixed_asset.category_option');
	// Route::post('fixed_asset/insert', 'AssetsController@insert')->name('fixed_asset.insert');
	// Route::post('fixed_asset/update', 'AssetsController@update')->name('fixed_asset.update');
	// Route::post('fixed_asset/delete', 'AssetsController@delete')->name('fixed_asset.delete');

	//period meter
	Route::get('period_meter','PeriodMeterController@index');
	Route::post('period_meter/get', 'PeriodMeterController@get')->name('period_meter.get');
	Route::post('period_meter/insert', 'PeriodMeterController@insert')->name('period_meter.insert');
	Route::post('period_meter/update', 'PeriodMeterController@update')->name('period_meter.update');
	Route::post('period_meter/delete', 'PeriodMeterController@delete')->name('period_meter.delete');
	Route::post('period_meter/editModal', 'PeriodMeterController@editModal')->name('period_meter.detail');
	Route::post('period_meter/cdtupdate', 'PeriodMeterController@meterdetailUpdate')->name('period_meter.cdtupdate');
	Route::post('period_meter/approve', 'PeriodMeterController@approve')->name('period_meter.approve');
	Route::post('period_meter/unposting', 'PeriodMeterController@unposting')->name('period_meter.unposting');
	Route::get('period_meter/downloadExcel/{type}/{cost}', 'PeriodMeterController@downloadExcel');
	Route::post('period_meter/importExcel', 'PeriodMeterController@importExcel')->name('period_meter.importExcel');

	// journal
	Route::get('journal','JournalController@index')->name('journal.index');
	Route::post('journal/get','JournalController@get')->name('journal.get');
	Route::post('journal/edit','JournalController@edit')->name('journal.edittab');
	Route::post('journal/update','JournalController@update')->name('journal.update');
	Route::post('journal/detail', 'JournalController@getdetail')->name('journal.getdetail');
	Route::post('journal/insert','JournalController@insert')->name('journal.insert');
	Route::post('journal/delete','JournalController@delete')->name('journal.delete');
	Route::get('journal/optLedger','JournalController@accountSelect2')->name('ledger.select2');
	Route::get('transactionentry','JournalController@trEntry')->name('trentry.index');
	Route::get('closeentry','JournalController@clEntry')->name('clentry.index');
	Route::post('docloseentry','JournalController@clEntryUpdate')->name('clentry.update');

	// ledger
	Route::get('generalledger','JournalController@generalLedger')->name('genledger.index');
	Route::post('generalledger/get','JournalController@glGet')->name('genledger.get');

	// invoice list
	Route::get('invoice','InvoiceController@index')->name('invoice.index');
	Route::post('invoice/get','InvoiceController@get')->name('invoice.get');
	Route::post('invoice/getdetail','InvoiceController@getdetail')->name('invoice.getdetail');
	Route::get('invoice/sendreminder','InvoiceController@reminderPrintout')->name('invoice.reminder.send');
	Route::post('invoice/customreminder','InvoiceController@reminderPrintout2')->name('invoice.reminder.custom');
	Route::get('invoice/reminder','InvoiceController@reminder')->name('invoice.reminder');
	Route::post('invoice/reminder','InvoiceController@updateReminder')->name('invoice.reminder.updatetemplate');

	Route::get('generateinvoice','InvoiceController@generateInvoice');
	Route::post('progressgenerate','InvoiceController@progressGenerate');
	Route::post('generateinvoice','InvoiceController@postGenerateInvoice')->name('invoice.generate');
	Route::get('invoice/print_faktur', 'InvoiceController@print_faktur');
	Route::get('invoice/print_kwitansi', 'InvoiceController@print_kwitansi');
	Route::get('invoice/receipt', 'InvoiceController@kuitansi');
	Route::post('invoice/posting','InvoiceController@posting')->name('invoice.posting');
	Route::post('invoice/insert','InvoiceController@insert')->name('invoice.insert');
	Route::post('invoice/cancel','InvoiceController@cancel')->name('invoice.cancel');
	Route::post('invoice/ajax-get-footer','InvoiceController@ajaxGetFooter')->name('invoice.ajaxgetfooter');
	Route::post('invoice/ajax-store-footer','InvoiceController@ajaxStoreFooter')->name('invoice.ajaxstorefooter');

	// aging piutang
	Route::get('aging','AgingController@index')->name('aging.index');
	Route::post('aging/get','AgingController@get')->name('aging.get');
	Route::post('aging/getdetail','AgingController@getdetail')->name('aging.getdetail');
	Route::get('aging/downloadAgingExcel', 'AgingController@downloadAgingExcel');

	Route::post('payment/posting','PaymentController@posting')->name('payment.posting');
	// report
	Route::get('report/arview','ReportController@arview')->name('report.arview');
	Route::get('report/arbyinvoice','ReportController@arbyInvoice')->name('report.arbyinv');
	Route::get('report/arbyinvoicecancel','ReportController@arbyInvoiceCancel')->name('report.arbyinvcancel');
	Route::get('report/araging','ReportController@arAging')->name('report.aging');
	Route::get('report/outinv','ReportController@outInv')->name('report.outinv');
	Route::get('report/outcontr','ReportController@outContr')->name('report.outcontr');
	Route::get('report/payment','ReportController@paymHistory')->name('report.payment');
	Route::get('report/tenancyview','ReportController@tenancyview')->name('report.tenancyview');
	Route::get('report/r_meter','ReportController@HistoryMeter')->name('report.r_meter');
	Route::get('report/r_unit','ReportController@ReportUnit')->name('report.r_unit');
	Route::get('report/r_tenant','ReportController@ReportTenant')->name('report.r_tenant');

	Route::get('report/glreport','ReportController@glview')->name('report.glview');
	Route::get('report/ytd','ReportController@ytd')->name('report.ytd');
	Route::get('report/doglreport','ReportController@glreport')->name('report.glget');
	Route::get('report/doytdreport','ReportController@ytdreport')->name('report.ytdget');
	// payment
	Route::get('payment','PaymentController@index')->name('payment.index');
	Route::post('payment/get','PaymentController@get')->name('payment.get');
	Route::post('payment/detail', 'PaymentController@getdetail')->name('payment.getdetail');
	Route::get('payment/get_invoice','PaymentController@get_invoice')->name('payment.get_invoice');
	Route::post('payment/insert','PaymentController@insert')->name('payment.insert');
	Route::get('payment/void','PaymentController@void')->name('payment.void');

	// bank book
	Route::get('bankbook','BankbookController@index')->name('bankbook.index');
	Route::post('bankbook/get','BankbookController@get')->name('bankbook.get');
	Route::post('bankbook/insert','BankbookController@insert')->name('bankbook.insert');
	Route::post('bankbook/posting','BankbookController@posting')->name('bankbook.posting');
	Route::post('bankbook/delete','BankbookController@delete')->name('bankbook.delete');
	Route::post('bankbook/detail','BankbookController@detail')->name('bankbook.detail');
	Route::get('bankbook/transfer','BankbookController@transfer')->name('bankbook.transfer');
	Route::post('bankbook/transfer','BankbookController@dotransfer')->name('bankbook.dotransfer');
	Route::get('bankbook/transfer/{id}','BankbookController@edittransfer')->name('bankbook.edit.transfer');
	Route::post('bankbook/transfer/{id}','BankbookController@updatetransfer')->name('bankbook.edit.dotransfer');
	Route::get('bankbook/deposit','BankbookController@deposit')->name('bankbook.deposit');
	Route::post('bankbook/deposit','BankbookController@dodeposit')->name('bankbook.dodeposit');
	Route::get('bankbook/deposit/{id}','BankbookController@editdeposit')->name('bankbook.edit.deposit');
	Route::post('bankbook/deposit/{id}','BankbookController@updatedeposit')->name('bankbook.edit.dodeposit');
	Route::get('bankbook/withdraw','BankbookController@withdraw')->name('bankbook.withdraw');
	Route::post('bankbook/withdraw','BankbookController@dowithdraw')->name('bankbook.dowithdraw');
	Route::get('bankbook/withdraw/{id}','BankbookController@editwithdraw')->name('bankbook.edit.withdraw');
	Route::post('bankbook/withdraw/{id}','BankbookController@updatewithdraw')->name('bankbook.edit.dowithdraw');

	Route::get('reconcile', 'BankbookController@reconcile')->name('reconcile.index');
	Route::post('update-reconcile', 'BankbookController@reconcileUpdate')->name('reconcile.update');

	// account payable
	Route::get('accpayable', 'PayableController@index')->name('payable.index');
	Route::post('accpayable/get', 'PayableController@get')->name('payable.get');
	Route::post('accpayable/delete', 'PayableController@delete')->name('payable.delete');
	Route::get('accpayable/withpo', 'PayableController@withpo')->name('payable.withpo');
	Route::get('accpayable/withoutpo', 'PayableController@withoutpo')->name('payable.withoutpo');
	Route::post('accpayable/withoutpo', 'PayableController@withoutpoInsert')->name('payable.withoutpo.insert');
	Route::post('accpayable/withpo', 'PayableController@withpoInsert')->name('payable.withpo.insert');
	Route::post('accpayable/posting', 'PayableController@posting')->name('payable.posting');

	Route::get('accpayable/withoutpo/edit/{id}', 'PayableController@withoutpoEdit')->name('payable.withoutpo.edit');
	Route::get('accpayable/withpo/edit/{id}', 'PayableController@withpoEdit')->name('payable.withpo.edit');

	// purchase order
	Route::get('purchaseorder', 'PayableController@purchaseOrder')->name('po.index');
	Route::get('purchaseorder/add', 'PayableController@addPurchaseOrder')->name('po.add');
	Route::get('purchaseorder/pdf/{id}', 'PayableController@poPdf')->name('po.pdf');
	Route::get('purchaseorder/edit/{id}', 'PayableController@editPurchaseOrder')->name('po.edit');
	Route::post('purchaseorder/get', 'PayableController@getPurchaseOrder')->name('po.get');
	Route::post('purchaseorder/add', 'PayableController@insertPurchaseOrder')->name('po.insert');
	Route::post('purchaseorder/edit/{id}', 'PayableController@updatePurchaseOrder')->name('po.update');
	Route::post('purchaseorder/delete', 'PayableController@deletePurchaseOrder')->name('po.delete');
	Route::get('purchaseorder/select2','PayableController@getPOselect2')->name('po.select2');
	Route::post('purchaseorder/ajaxdtl','PayableController@getPOajax')->name('po.ajax');

	// acl
	Route::get('roles','AccountController@roles')->name('roles.index');
	Route::post('roles/insert','AccountController@rolesInsert')->name('roles.insert');
	Route::post('roles/update/{id}','AccountController@rolesUpdate')->name('roles.update');
	Route::post('roles/detail','AccountController@rolesDetail')->name('roles.detail');
	Route::post('roles/delete','AccountController@rolesDelete')->name('roles.delete');

	Route::get('users','AccountController@users')->name('users.index');
	Route::post('users/insert','AccountController@usersInsert')->name('users.insert');
	Route::post('users/update','AccountController@usersUpdate')->name('users.update');
	Route::post('users/detail','AccountController@usersDetail')->name('users.detail');
	Route::post('users/delete','AccountController@usersDelete')->name('users.delete');

	Route::get('profile','ProfileController@index');
	Route::post('profile/update', 'ProfileController@update')->name('profile.update');

	// layout
	Route::get('layouts','LayoutController@index')->name('layout.index');
	Route::post('layouts/get','LayoutController@get')->name('layout.get');
	Route::post('layouts/upsert','LayoutController@upsert')->name('layout.upsert');
	Route::post('layouts/delete','LayoutController@destroy')->name('layout.delete');
	Route::post('layouts/detail/get','LayoutController@getDetail')->name('layout.detail.get');
	Route::post('layouts/detail/upsert','LayoutController@updateDetail')->name('layout.detail.upsert');
	Route::get('layouts/detail/preview','LayoutController@preview')->name('layout.detail.preview');

	Route::get('report/ledger','ReportController@ledger_view')->name('report.ledger_view');
	Route::get('report/rledger','ReportController@rledger')->name('report.rledger');
	Route::get('report/trial','ReportController@tb_view')->name('report.tb_view');
	Route::get('report/dotrial','ReportController@dotrial')->name('report.dotrial');

	Route::get('report/neraca','ReportController@neraca')->name('report.neraca');
	Route::get('report/neracatpl','ReportController@neracatpl')->name('report.neracatpl');
	Route::get('report/profitloss','ReportController@profitloss')->name('report.profitloss');
	Route::get('report/profitlosstpl','ReportController@profitlosstpl')->name('report.profitlosstpl');

	// kurs
	Route::get('kurs', 'KursController@index');
	Route::post('kurs/get', 'KursController@get')->name('kurs.get');
	Route::post('kurs/insert', 'KursController@insert')->name('kurs.insert');
	Route::post('kurs/update', 'KursController@update')->name('kurs.update');
	Route::post('kurs/delete', 'KursController@delete')->name('kurs.delete');

	// kurs
	Route::get('ppn', 'PPNController@index');
	Route::post('ppn/get', 'PPNController@get')->name('ppn.get');
	Route::post('ppn/insert', 'PPNController@insert')->name('ppn.insert');
	Route::post('ppn/update', 'PPNController@update')->name('ppn.update');
	Route::post('ppn/delete', 'PPNController@delete')->name('ppn.delete');

	// treasury
	Route::get('treasury', 'TreasuryController@index');
	Route::post('treasury/get', 'TreasuryController@get')->name('treasury.get');
	Route::post('treasury/getdetail', 'TreasuryController@getDetail')->name('treasury.getdetail');
	Route::get('treasury/getapsupplier', 'TreasuryController@getAPofSupplier')->name('treasury.getapsupplier');
	Route::post('treasury/insert', 'TreasuryController@insert')->name('treasury.insert');
	Route::get('treasury/void','TreasuryController@void')->name('treasury.void');
	Route::post('treasury/posting','TreasuryController@posting')->name('treasury.posting');
	Route::get('report/apview','ReportController@apview')->name('report.apview');
	Route::get('accpayable/optSupplier','PayableController@getOptSupplier')->name('supplier.select2');
	Route::get('report/apaging','ReportController@apAging')->name('report.ap_aging');

	Route::get('backup', 'BackupRestoreController@index');
	Route::post('backup/download', 'BackupRestoreController@dump')->name('backup.download');
	Route::post('backup/restore', 'BackupRestoreController@restore')->name('backup.restore');

	// fixed asset
	Route::get('assets', 'FixedAssetController@index')->name('fixed_asset.index');
	Route::post('assets/add', 'FixedAssetController@add')->name('fixed_asset.modal.add');
	Route::post('assets/edit', 'FixedAssetController@edit')->name('fixed_asset.modal.edit');
	Route::post('assets/fiskal', 'FixedAssetController@fiskal')->name('fixed_asset.modal.fiskal');
	Route::post('assets/komersial', 'FixedAssetController@komersial')->name('fixed_asset.modal.komersial');
	Route::post('assets/custom', 'FixedAssetController@custom')->name('fixed_asset.modal.custom');
	Route::post('assets/mutasi', 'FixedAssetController@mutasi')->name('fixed_asset.modal.mutasi');

	Route::post('assets/perawatan', 'FixedAssetController@perawatan')->name('fixed_asset.modal.perawatan');
	Route::post('assets/perawatan/get', 'FixedAssetController@getPerawatan')->name('fixed_asset.modal.perawatan.get');
	Route::post('assets/perawatan/insert', 'FixedAssetController@insertPerawatan')->name('fixed_asset.modal.perawatan.insert');
	Route::post('assets/perawatan/update', 'FixedAssetController@updatePerawatan')->name('fixed_asset.modal.perawatan.update');
	Route::post('assets/perawatan/delete', 'FixedAssetController@deletePerawatan')->name('fixed_asset.modal.perawatan.delete');

	Route::post('assets/asuransi', 'FixedAssetController@asuransi')->name('fixed_asset.modal.asuransi');
	Route::post('assets/asuransi/get', 'FixedAssetController@getAsuransi')->name('fixed_asset.modal.asuransi.get');
	Route::post('assets/asuransi/insert', 'FixedAssetController@insertAsuransi')->name('fixed_asset.modal.asuransi.insert');
	Route::post('assets/asuransi/update', 'FixedAssetController@updateAsuransi')->name('fixed_asset.modal.asuransi.update');
	Route::post('assets/asuransi/delete', 'FixedAssetController@deleteAsuransi')->name('fixed_asset.modal.asuransi.delete');

	Route::post('assets/insert', 'FixedAssetController@insert')->name('fixed_asset.insert');
	Route::post('assets/update', 'FixedAssetController@update')->name('fixed_asset.update');
	Route::post('assets/delete', 'FixedAssetController@delete')->name('fixed_asset.delete');

	Route::post('group_aktiva/insert', 'FixedAssetController@gaInsert')->name('group_aktiva.insert');
	Route::post('group_aktiva/delete', 'FixedAssetController@gaDelete')->name('group_aktiva.delete');

	Route::get('asset-type', 'FixedAssetController@indexTypes')->name('fixed_asset.type.index');
	Route::post('asset-type/add', 'FixedAssetController@addTypes')->name('fixed_asset.type.modal.add');
	Route::post('asset-type/edit', 'FixedAssetController@editTypes')->name('fixed_asset.type.modal.edit');
	Route::post('asset-type/insert', 'FixedAssetController@insertTypes')->name('fixed_asset.type.insert');
	Route::post('asset-type/update', 'FixedAssetController@updateTypes')->name('fixed_asset.type.update');
	Route::post('asset-type/delete', 'FixedAssetController@deleteTypes')->name('fixed_asset.type.delete');
	Route::post('assets/get', 'FixedAssetController@get')->name('fixed_asset.get');
	Route::post('asset-type/get', 'FixedAssetController@getTypes')->name('fixed_asset.type.get');
	Route::get('fixed-asset-report', 'FixedAssetController@report')->name('fixed_asset.report');

});

Route::get('membership', 'Auth\AuthController@membership');
Route::get('logout','Auth\AuthController@logout');
