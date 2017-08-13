<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
// load model
use App\Models\MsMasterCoa;
use App\Models\MsCashBank;
use App\Models\MsPaymentType;
use App\Models\MsDepartment;
use App\Models\MsJournalType;
use App\Models\TrApDetail;
use App\Models\TrApHeader;
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

	public function withoutpoInsert(Request $request)
	{
		\DB::beginTransaction();
        try{
            // dd($request->all());
            $header = new TrApHeader;
            $header->spl_id = $request->spl_id;
            $header->invoice_date = $request->invoice_date;
            $header->invoice_duedate = $request->invoice_duedate;
            $header->invoice_no = $request->invoice_no;
            $header->created_by = \Auth::id();
            $header->updated_by = \Auth::id();
            
            $coa_code = $request->coa_code;
            $notes = $request->note;
            $qty = $request->qty;
            $amount = $request->amount;
            $ppn_amount = $request->ppn_amount;
            $ppn_flag = $request->is_ppn;
            $dept = $request->dept_id;
            foreach ($coa_code as $key => $coa) {
            	$detail = new TrApDetail;
            	$detail->note = $notes[$key];
            	$detail->qty = $qty[$key];
            	$detail->amount = $amount[$key];
            	$detail->ppn_amount = $ppn_amount[$key];
            	$detail->is_ppn = (bool)$ppn_flag[$key];
            	$detail->coa_code = $coa;
            	$detail->dept_id = $dept[$key];
            	$details[] = $detail;
            }
            $header->save();
            $header->detail()->saveMany($details);
            \DB::commit();
            return redirect()->back()->with(['success' => 'Insert Success']);
        }catch(\Exception $e){
            \DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
	}
}