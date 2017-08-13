<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
// load model
use App\Models\MsMasterCoa;
use App\Models\MsCashBank;
use App\Models\MsPaymentType;
use App\Models\MsDepartment;
use App\Models\MsJournalType;
use App\Models\TrLedger;
use App\Models\Kurs;
use App\Models\MsSupplier;
use Auth;
use DB;

class PayableController extends Controller
{
	public function index()
	{
		$data = [];
		return view('accpayable',$data);
	}

	public function withpo(){
		return view('accpayable_withpo',$data);
	}

	public function withoutpo(){
		$coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
		$data['suppliers'] = MsSupplier::all();
		$data['departments'] = MsDepartment::where('dept_isactive',1)->get();
		return view('accpayable_withoutpo',$data);	
	}
}