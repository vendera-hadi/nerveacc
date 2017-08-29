<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
// load model
use App\Models\MsMasterCoa;
use App\Models\TrLedger;
use App\Models\MsPaymentType;
use App\Models\MsDepartment;
use App\Models\MsJournalType;
use App\Models\TrApDetail;
use App\Models\TrApHeader;
use App\Models\TrPODetail;
use App\Models\TrPOHeader;
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

	public function get(Request $request)
	{
		try{
            // params
            $page = $request->page;
            $perPage = $request->rows; 
            $page-=1;
            $offset = $page * $perPage;
            // @ -> isset(var) ? var : null
            $sort = @$request->sort;
            $order = @$request->order;
            $filters = @$request->filterRules;
            if(!empty($filters)) $filters = json_decode($filters);

            // olah data
            $count = TrApHeader::count();
            $fetch = TrApHeader::select('tr_ap_invoice_hdr.*', 'tr_purchase_order_hdr.po_number')
                    ->leftJoin('tr_purchase_order_hdr','tr_purchase_order_hdr.id','=','tr_ap_invoice_hdr.po_id');

             if(!empty($filters) && count($filters) > 0){
                foreach($filters as $filter){
                    $op = "like";
                    // tentuin operator
                    switch ($filter->op) {
                        case 'contains':
                            $op = 'like';
                            break;
                        case 'less':
                            $op = '<=';
                            break;
                        case 'greater':
                            $op = '>=';
                            break;
                        default:
                            break;
                    }
                    // end special condition
                    if($op == 'like') $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                    else $fetch = $fetch->where($filter->field, $op, $filter->value);
                }
            }

            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['checkbox'] = '<input type="checkbox" name="check" value="'.$value->id.'" data-posting="'.$value->posting.'">';
                $temp['id'] = $value->id;
                $temp['invoice_no'] = $value->invoice_no;
                $temp['invoice_date'] = $value->invoice_date;
                $temp['invoice_duedate'] = $value->invoice_duedate;
                $temp['total'] = $value->total;
                $temp['posting'] = $value->posting ? 'yes' : 'no';
                $temp['po_no'] = !empty($value->po_number) ? $value->po_number : "-";
                $action_button = "";
                if($temp['posting'] == 'no'){
                    // if(!empty($value->po_number))
                    //     $action_button = '<a href="'.route('payable.withpo.edit',$value->id).'" ><i class="fa fa-pencil"></i></a>';
                    // else  
                    //     $action_button = '<a href="'.route('payable.withoutpo.edit',$value->id).'" ><i class="fa fa-pencil"></i></a>';
                    $action_button .= '&nbsp;&nbsp; <a href="#" data-id="'.$value->id.'" class="remove"><i class="fa fa-times"></i></a>';
                }
                $temp['action_button'] = $action_button;
                $result['rows'][] = $temp;
            }

           	return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
	}

	public function withpo(){
        $coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        $data['suppliers'] = MsSupplier::all();
        $data['departments'] = MsDepartment::where('dept_isactive',1)->get();
        $data['payment_terms'] = DB::table('ms_payment_terms')->get();
        $data['ppn_options'] = DB::table('ms_ppn')->get();

        $temp = "INV-".date('my')."-".strtoupper(str_random(8));
        do{
            $check = TrApHeader::where('invoice_no',$temp)->first();
        }while(!empty($check));
        $data['inv_number'] = $temp;
		return view('accpayable_withpo',$data);
	}

	public function withoutpo(){
		$coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
		$data['suppliers'] = MsSupplier::all();
		$data['departments'] = MsDepartment::where('dept_isactive',1)->get();
		$data['payment_terms'] = DB::table('ms_payment_terms')->get();
		$data['ppn_options'] = DB::table('ms_ppn')->get();
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
            $header->terms = $request->terms;
            $header->note = $request->hdnote;
            $header->apdate = date('Y-m-d');
            $header->created_by = \Auth::id();
            $header->updated_by = \Auth::id();

            $coa_code = $request->coa_code;
            $ppn_coa = $request->ppn_coa_code;
            $notes = $request->note;
            $qty = $request->qty;
            $amount = $request->amount;
            $ppn_amount = $request->ppn_amount;
            $ppn_flag = $request->is_ppn;
            $dept = $request->dept_id;
            $total = $totalppn = 0;
            foreach ($coa_code as $key => $coa) {
            	$detail = new TrApDetail;
            	$detail->note = $notes[$key];
            	$detail->qty = $qty[$key];
            	$detail->amount = (float)$amount[$key];
            	$detail->ppn_amount = (float)$ppn_amount[$key];
            	$detail->is_ppn = !empty($ppn_amount[$key]) ? true : false;
            	$detail->ppn_coa_code = !empty($ppn_coa[$key]) ? $ppn_coa[$key] : null;
            	$detail->coa_code = $coa;
            	$detail->dept_id = $dept[$key];
            	$details[] = $detail;
            	$total += $qty[$key] * $amount[$key];
            	$totalppn += $ppn_amount[$key];
            }
            $header->total = $total;
            $header->outstanding = $header->total;
            $header->ppn = $totalppn;
            $header->save();
            $header->detail()->saveMany($details);
            \DB::commit();
            return redirect()->back()->with(['success' => 'Insert Success']);
        }catch(\Exception $e){
            \DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
	}

    public function withpoInsert(Request $request)
    {
        \DB::beginTransaction();
        try{
            // dd($request->all());
            $po = TrPOHeader::find($request->po_id);

            $header = new TrApHeader;
            $header->spl_id = $po->spl_id;
            $header->invoice_date = $po->po_date;
            $header->invoice_duedate = $po->due_date;
            $header->invoice_no = $request->invoice_no;
            $header->terms = $po->terms;
            $header->note = $request->hdnote;
            $header->apdate = date('Y-m-d');
            $header->po_id = $request->po_id;
            $header->created_by = \Auth::id();
            $header->updated_by = \Auth::id();

            
            $total = $totalppn = 0;
            foreach ($po->detail as $key => $dt) {
                $detail = new TrApDetail;
                $detail->note = $dt->note;
                $detail->qty = $dt->qty;
                $detail->amount = $dt->amount;
                $detail->ppn_amount = $dt->ppn_amount;
                $detail->is_ppn = $dt->is_ppn;
                $detail->ppn_coa_code = $dt->ppn_coa_code;
                $detail->coa_code = $dt->coa_code;
                $detail->dept_id = $dt->dept_id;
                $details[] = $detail;
                $total += $dt->qty * $dt->amount;
                $totalppn += $dt->ppn_amount;
            }
            $header->total = $total;
            $header->outstanding = $header->total;
            $header->ppn = $totalppn;
            $header->save();
            $header->detail()->saveMany($details);
            \DB::commit();
            return redirect()->back()->with(['success' => 'Insert Success']);
        }catch(\Exception $e){
            \DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        try{
            $id = $request->id;
            TrApHeader::destroy($id);
            return response()->json(['success' => 1,'message' => 'Delete Success']);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function posting(Request $request)
    {
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];
        \DB::beginTransaction();
        try{
            $coayear = date('Y');
            $month = date('m');
            $journaltype = 'JU';
            foreach ($ids as $id) {
                // cari last prefix, order by journal type
                $jourType = MsJournalType::where('jour_type_prefix',$journaltype)->first();
                if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "'.$journaltype.'" ']);
                $lastJournal = TrLedger::where('jour_type_id',$jourType->id)->latest()->first();
                if($lastJournal){
                    $lastJournalNumber = explode(" ", $lastJournal->ledg_number);
                    $lastJournalNumber = (int) end($lastJournalNumber);
                    $nextJournalNumber = $lastJournalNumber + 1;
                }else{
                    $nextJournalNumber = 1;
                }

                $header = TrApHeader::find($id);
                // lawanan hutang (KREDIT)
                $total = 0;
                foreach ($header->detail as $detail) {
                    $nextJournalNumberConvert = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                    $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumberConvert;
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    $journal = [
                                    'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                    'ledge_fisyear' => $coayear,
                                    'ledg_number' => $journalNumber,
                                    'ledg_date' => date('Y-m-d'),
                                    'ledg_refno' => $header->invoice_no,
                                    'ledg_debit' => $detail->amount,
                                    'ledg_credit' => 0,
                                    'ledg_description' => $header->invoice_no,
                                    'coa_year' => $coayear,
                                    'coa_code' => $detail->coa_code,
                                    'created_by' => Auth::id(),
                                    'updated_by' => Auth::id(),
                                    'jour_type_id' => $jourType->id,
                                    'dept_id' => $detail->dept_id
                                ];
                    TrLedger::create($journal);

                    $total += $detail->amount;
                    if(!empty($detail->ppn_coa_code)){
                        $nextJournalNumber++;
                        $nextJournalNumberConvert = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                        $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumberConvert;
                        $journal = [
                                    'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                    'ledge_fisyear' => $coayear,
                                    'ledg_number' => $journalNumber,
                                    'ledg_date' => date('Y-m-d'),
                                    'ledg_refno' => $header->invoice_no,
                                    'ledg_debit' => $detail->ppn_amount,
                                    'ledg_credit' => 0,
                                    'ledg_description' => $header->invoice_no,
                                    'coa_year' => $coayear,
                                    'coa_code' => $detail->ppn_coa_code,
                                    'created_by' => Auth::id(),
                                    'updated_by' => Auth::id(),
                                    'jour_type_id' => $jourType->id,
                                    'dept_id' => $detail->dept_id
                                ];
                        TrLedger::create($journal);
                    }
                    $nextJournalNumber++;
                }
                // endforeach
                // Hutang (DEBET)
                $nextJournalNumberConvert = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumberConvert;
                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                $journal = [
                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => date('Y-m-d'),
                        'ledg_refno' => $header->invoice_no,
                        'ledg_debit' => 0,
                        'ledg_credit' => $total,
                        'ledg_description' => $header->invoice_no,
                        'coa_year' => $coayear,
                        'coa_code' => $detail->coa_code,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id,
                        'dept_id' => $detail->dept_id
                    ];
                TrLedger::create($journal);
            }
            $header->posting = true;
            $header->posting_at = date('Y-m-d');
            $header->save();
            \DB::commit();
            return response()->json(['success'=>1, 'message'=>'AP posted Successfully']);
        }catch(\Exception $e){
            \DB::rollback();
            return response()->json(['error'=>1, 'message'=> $e->getMessage()]);
        }

    }

	// purchase order
	public function purchaseOrder()
	{
		$data = [];
		return view('purchase_order',$data);
	}

	public function getPurchaseOrder(Request $request)
	{
		try{
			// params
            $page = $request->page;
            $perPage = $request->rows; 
            $page-=1;
            $offset = $page * $perPage;
            // @ -> isset(var) ? var : null
            $sort = @$request->sort;
            $order = @$request->order;
            $filters = @$request->filterRules;
            if(!empty($filters)) $filters = json_decode($filters);

            // olah data
            $count = TrPOHeader::count();
            $fetch = TrPOHeader::select('tr_purchase_order_hdr.*','ms_supplier.spl_name')->join('ms_supplier','tr_purchase_order_hdr.spl_id', '=', 'ms_supplier.id');

            if(!empty($filters) && count($filters) > 0){
                foreach($filters as $filter){
                    $op = "like";
                    // tentuin operator
                    switch ($filter->op) {
                        case 'contains':
                            $op = 'like';
                            break;
                        case 'less':
                            $op = '<=';
                            break;
                        case 'greater':
                            $op = '>=';
                            break;
                        default:
                            break;
                    }
                    // end special condition
                    if($op == 'like') $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                    else $fetch = $fetch->where($filter->field, $op, $filter->value);
                }
            }

            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);

            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
            	$temp = [];
                $temp['id'] = $value->id;
                $temp['po_number'] = $value->po_number;
                $temp['po_date'] = $value->po_date;
                $temp['due_date'] = $value->due_date;
                $temp['spl_name'] = $value->spl_name;
                $action_button = '<a href="'.route('po.edit',$value->id).'" ><i class="fa fa-pencil"></i></a>'; 
                $action_button .= '| <a href="#" data-id="'.$value->id.'" class="remove"><i class="fa fa-times"></i></a>';
                $temp['action_button'] = $action_button;

                $result['rows'][] = $temp;
            }

        	return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }

	}

    public function getPOselect2(Request $request)
    {
        $key = $request->q;
        $fetch = TrPOHeader::where(\DB::raw('LOWER(po_number)'),'like','%'.$key.'%')->get();

        $result['results'] = [];
        foreach ($fetch as $key => $value) {
            $temp = ['id'=>$value->id, 'text'=>$value->po_number];
            array_push($result['results'], $temp);
        }
        return json_encode($result);
    }

    public function getPOajax(Request $request)
    {
        $id = $request->id;
        $result = TrPOHeader::with('detail')->where('id',$id)->first();
        echo $result;
    }

	public function addPurchaseOrder()
	{
		$coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
		$data['suppliers'] = MsSupplier::all();
		$data['departments'] = MsDepartment::where('dept_isactive',1)->get();
		$data['payment_terms'] = DB::table('ms_payment_terms')->get();
		$data['ppn_options'] = DB::table('ms_ppn')->get();

		$temp = "PO-".date('my')."-".strtoupper(str_random(8));
		do{
			$check = TrPOHeader::where('po_number',$temp)->first();
		}while(!empty($check));
		$data['new_po_number'] = $temp;
		return view('purchase_order_add',$data);
	}

	public function editPurchaseOrder(Request $request, $id)
	{
		$coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
		$data['suppliers'] = MsSupplier::all();
		$data['departments'] = MsDepartment::where('dept_isactive',1)->get();
		$data['payment_terms'] = DB::table('ms_payment_terms')->get();
		$data['ppn_options'] = DB::table('ms_ppn')->get();

		$data['current'] = TrPOHeader::find($id);
		return view('purchase_order_edit',$data);
	}

	public function insertPurchaseOrder(Request $request)
	{
		\DB::beginTransaction();
        try{
            // dd($request->all());
            $header = new TrPOHeader;
            $header->spl_id = $request->spl_id;
            $header->po_number = $request->po_number;
            $header->po_date = $request->po_date;
            $header->due_date = $request->invoice_duedate;
            $header->terms = $request->terms;
            $header->note = $request->hdnote;
            $header->created_by = \Auth::id();
            $header->updated_by = \Auth::id();

            $coa_code = $request->coa_code;
            $ppn_coa = $request->ppn_coa_code;
            $notes = $request->note;
            $qty = $request->qty;
            $amount = $request->amount;
            $ppn_amount = $request->ppn_amount;
            $ppn_flag = $request->is_ppn;
            $dept = $request->dept_id;
            $total = $totalppn = 0;
            foreach ($coa_code as $key => $coa) {
            	$detail = new TrPODetail;
            	$detail->note = $notes[$key];
            	$detail->qty = $qty[$key];
            	$detail->amount = (float)$amount[$key];
            	$detail->ppn_amount = (float)$ppn_amount[$key];
            	$detail->is_ppn = !empty($ppn_amount[$key]) ? true : false;
            	$detail->ppn_coa_code = !empty($ppn_coa[$key]) ? $ppn_coa[$key] : null;
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

	public function updatePurchaseOrder(Request $request, $id)
	{
		\DB::beginTransaction();
        try{
            // dd($request->all());
            $header = TrPOHeader::find($id);
            $header->spl_id = $request->spl_id;
            $header->po_date = $request->po_date;
            $header->due_date = $request->invoice_duedate;
            $header->terms = $request->terms;
            $header->note = $request->hdnote;
            $header->updated_by = \Auth::id();

            TrPODetail::where('po_id',$id)->delete();
            $coa_code = $request->coa_code;
            $ppn_coa = $request->ppn_coa_code;
            $notes = $request->note;
            $qty = $request->qty;
            $amount = $request->amount;
            $ppn_amount = $request->ppn_amount;
            $ppn_flag = $request->is_ppn;
            $dept = $request->dept_id;
            $total = $totalppn = 0;
            foreach ($coa_code as $key => $coa) {
            	$detail = new TrPODetail;
            	$detail->note = $notes[$key];
            	$detail->qty = $qty[$key];
            	$detail->amount = (float)$amount[$key];
            	$detail->ppn_amount = (float)$ppn_amount[$key];
            	$detail->is_ppn = !empty($ppn_amount[$key]) ? true : false;
            	$detail->ppn_coa_code = !empty($ppn_coa[$key]) ? $ppn_coa[$key] : null;
            	$detail->coa_code = $coa;
            	$detail->dept_id = $dept[$key];
            	$details[] = $detail;
            }
            $header->save();
            $header->detail()->saveMany($details);
           	\DB::commit();
            return redirect()->back()->with(['success' => 'Update Success']);
        }catch(\Exception $e){
            \DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
	}

	public function deletePurchaseOrder(Request $request)
    {
        try{
            $id = $request->id;
            TrPOHeader::destroy($id);
            return response()->json(['success' => 1,'message' => 'Delete Success']);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

}