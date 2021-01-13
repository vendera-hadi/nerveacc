<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DbDumper\PostgreSql;

class BackupRestoreController extends Controller
{
	public function index()
	{
		return view('backup');
	}

	/*
		setDumpBinaryPath -> path tempat pg_dump.exe, di linux cb lgsg pg_dump aja
		setHost -> ip database postgre
		setDbName -> nama db
		setUserName-> nama username db
		setPassword -> pass username db
		setStorePath -> path tempat nyimpan db, usahain uda mode 775
		includeTables -> table2 yg mau diimport
	*/
	public function dump()
	{
		$dumpDB = PostgreSql::create()
			// ->setDumpBinaryPath('C:\\PostgreSQL\\pg96\\bin\\pg_dump.exe')
			->setDumpBinaryPath('pg_dump')
			->setHost(env('DB_HOST'))
    		->setDbName(env('DB_DATABASE'))
    		->setUserName(env('DB_USERNAME'))
    		->setPassword(env('DB_PASSWORD'))
    		->setStorePath(public_path('upload/backup.sql'));
    		//->includeTables(['tr_ap_invoice_dtl', 'tr_ap_invoice_hdr', 'tr_ap_invoice_dtl', 'tr_ap_invoice_hdr', 'tr_asset_mutations', 'tr_bank', 'tr_bankjv', 'tr_contract', 'tr_contract_invoice', 'tr_currency_rate', 'tr_invoice_journal', 'tr_invoice_paymdtl', 'tr_invoice_paymhdr', 'tr_invpaym_journal', 'tr_meter', 'tr_period_meter', 'tr_purchase_order_dtl', 'tr_purchase_order_hdr', 'tr_ledger', 'tr_invoice', 'tr_invoice_detail', 'ms_unit', 'ms_tenant', 'users']);

    	if($dumpDB->dumpToFile()) return $dumpDB->downloadBackup();
	}

	public function restore(Request $request)
	{
		try{
			$path = $request->dbfile->store('upload');
			$realpath = storage_path('app'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path));
			$dumpDB = PostgreSql::create()
				// ->setDumpBinaryPath('C:\\PostgreSQL\\pg96\\bin\\pg_restore.exe')
				->setDumpBinaryPath('pg_restore')
				->setHost(env('DB_HOST'))
	    		->setDbName(env('DB_DATABASE'))
	    		->setUserName(env('DB_USERNAME'))
	    		->setPassword(env('DB_PASSWORD'))
	    		->setRestoreFile($realpath);
	    	if($dumpDB->restoreDB()) return redirect()->back()->with('success', 'Data Restored Successfully');
	    	else return redirect()->back()->with('error', 'Error Occured when restoring backup');
	    }catch(\Exception $e){
		dd($e->getMessage());
	    	return redirect()->back()->with('error', 'Error Occured when restoring backup');
	    }
	}

}
