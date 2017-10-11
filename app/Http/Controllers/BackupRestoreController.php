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
			->setDumpBinaryPath('C:\\PostgreSQL\\pg96\\bin\\pg_dump.exe')
			->setHost('127.0.0.1')
    		->setDbName(env('DB_DATABASE'))
    		->setUserName(env('DB_USERNAME'))
    		->setPassword(env('DB_PASSWORD'))
    		->setStorePath('D:\\backup.sql')
    		->includeTables(['tr_ledger', 'tr_invoice', 'tr_invoice_detail']);
    		
    	if($dumpDB->dumpToFile()) return $dumpDB->downloadBackup();
	}

	public function restore(Request $request)
	{
		try{
			$path = $request->dbfile->store('upload');
			$realpath = storage_path('app'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path));
			$dumpDB = PostgreSql::create()
				->setDumpBinaryPath('C:\\PostgreSQL\\pg96\\bin\\pg_restore.exe')
				->setHost('127.0.0.1')
	    		->setDbName(env('DB_DATABASE'))
	    		->setUserName(env('DB_USERNAME'))
	    		->setPassword(env('DB_PASSWORD'))
	    		->setRestoreFile($realpath);
	    	if($dumpDB->restoreDB()) return redirect()->back()->with('success', 'Data Restored Successfully');
	    	else return redirect()->back()->with('error', 'Error Occured when restoring backup');
	    }catch(\Exception $e){
	    	return redirect()->back()->with('error', 'Error Occured when restoring backup');
	    }
	}

}