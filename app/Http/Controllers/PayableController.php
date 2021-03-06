<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
// load model
use App\Models\MsMasterCoa;
use App\Models\TrLedger;
use App\Models\MsPaymentType;
use App\Models\MsDepartment;
use App\Models\MsJournalType;
use App\Models\MsCompany;
use App\Models\MsConfig;
use App\Models\TrApDetail;
use App\Models\TrApHeader;
use App\Models\TrPODetail;
use App\Models\TrPOHeader;
use App\Models\MsSupplier;
use App\Models\Numcounter;
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
            $datefrom = @$request->datefrom;
            $dateto = @$request->dateto;
            $filters = @$request->filterRules;
            if(!empty($filters)) $filters = json_decode($filters);

            // olah data
            $count = TrApHeader::count();
            $fetch = TrApHeader::select('tr_ap_invoice_hdr.*', 'tr_purchase_order_hdr.po_number','ms_supplier.spl_name')
                    ->leftJoin('tr_purchase_order_hdr','tr_purchase_order_hdr.id','=','tr_ap_invoice_hdr.po_id')
                    ->leftJoin('ms_supplier','tr_ap_invoice_hdr.spl_id','=','ms_supplier.id');

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
             // jika ada date from
            if(!empty($datefrom)) $fetch = $fetch->where('tr_ap_invoice_hdr.invoice_date','>=',$datefrom);
            // jika ada date to
            if(!empty($dateto)) $fetch = $fetch->where('tr_ap_invoice_hdr.invoice_date','<=',$dateto);

            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);

            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['checkbox'] = '<input type="checkbox" name="check" value="'.$value->id.'" data-posting="'.$value->posting.'">';
                $temp['id'] = $value->id;
                $temp['spl_name'] = $value->spl_name;
                $temp['invoice_no'] = $value->invoice_no;
                $temp['invoice_date'] = $value->invoice_date;
                $temp['invoice_duedate'] = $value->invoice_duedate;
                $temp['total'] = number_format($value->total,2);
                $temp['posting'] = $value->posting ? 'yes' : 'no';
                $temp['po_no'] = !empty($value->po_number) ? $value->po_number : "-";
                $action_button = '<a href="javascript:void(0)" data-id="'.$value->id.'" class="detail"><i class="fa fa-eye"></i></a>';
                if($temp['posting'] == 'no'){
                    if(!empty($value->po_number)){
                        $action_button = '<a href="'.route('payable.withpo.edit',$value->id).'" ><i class="fa fa-pencil"></i></a>';
                    }else{
                        $action_button = '<a href="'.route('payable.withoutpo.edit',$value->id).'" ><i class="fa fa-pencil"></i></a>';
                    }
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

            $type_spl2 = MsSupplier::find($request->spl_id);
            $type_spl = $type_spl2->spl_pkp;

            $coa_code = $request->coa_code;
            $ppn_coa = $request->ppn_coa_code;
            $notes = $request->note;
            $qty = $request->qty;
            $amount = $request->amount;
            $coatype = $request->coa_type;
            // $ppn_amount = $request->ppn_amount;
            // $ppn_flag = $request->is_ppn;
            $dept = $request->dept_id;
            $total = $totalppn = 0;
            foreach ($coa_code as $key => $coa) {
            	$detail = new TrApDetail;
            	$detail->note = $notes[$key];
            	$detail->qty = $qty[$key];
            	$detail->amount = (float)$amount[$key];
            	// $detail->ppn_amount = (float)$ppn_amount[$key];
                $detail->ppn_amount = 0;
            	// $detail->is_ppn = !empty($ppn_amount[$key]) ? true : false;
            	// $detail->ppn_coa_code = !empty($ppn_coa[$key]) ? $ppn_coa[$key] : null;
            	$detail->coa_code = $coa;
            	$detail->dept_id = $dept[$key];
                $detail->coa_type = $coatype[$key];
            	$details[] = $detail;
                if($detail->coa_type == 'DEBET'){
            	   $total += $qty[$key] * $amount[$key];
                }else{
                    $total -= $qty[$key] * $amount[$key];
                }
            	// $totalppn += $ppn_amount[$key];
            }
            $header->total = $total + $totalppn;
            $header->outstanding = $header->total;
            $header->ppn = $totalppn;
            if($type_spl == 2){
                $header->dpp = ROUND($header->final_total/1.1,0);
            }else{
                $header->dpp = ROUND($header->final_total,0);
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

    public function withpoInsert(Request $request)
    {
        \DB::beginTransaction();
        try{
            // dd($request->all());
            $po = TrPOHeader::find($request->po_id);
            $po->is_ap = 1;
            $po->save();

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
                $detail->coa_type = $dt->coa_type;
                $details[] = $detail;
                if($dt->coa_type == 'DEBET'){
                    $total += $dt->qty * $dt->amount;
                }else{
                    $total -= $dt->qty * $dt->amount;
                }
                $totalppn += $dt->ppn_amount;
            }
            $header->total = $total + $totalppn;
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
        $postingdate = (!empty($request->posting_date) ? date('Y-m-d',strtotime($request->posting_date)) : date('Y-m-d')); 
        if(!is_array($ids)) $ids = explode(',',$ids);
        \DB::beginTransaction();
        try{
            $coayear = date('Y',strtotime($request->posting_date));
            $month = date('m',strtotime($request->posting_date));
            $journaltype = 'AP';

            // cek backdate dr closing bulanan/tahunan
            $lastclose = TrLedger::whereNotNull('closed_at')->orderBy('closed_at','desc')->first();
            $limitMinPostingDate = null;
            if($lastclose) $limitMinPostingDate = date('Y-m-t', strtotime($lastclose->closed_at));

            foreach ($ids as $id) {
                // cari last prefix, order by journal type
                $jourType = MsJournalType::where('jour_type_prefix',$journaltype)->first();
                if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "'.$journaltype.'" ']);
                $lastJournal = Numcounter::where('numtype','BPV')->where('tahun',$coayear)->where('bulan',$month)->first();
                if(count($lastJournal) > 0){
                    $lst = $lastJournal->last_counter;
                    $nextJournalNumber = $lst + 1;
                    $lastJournal->update(['last_counter'=>$nextJournalNumber]);
                }else{
                    $nextJournalNumber = 1;
                    $lastcounter = new Numcounter;
                    $lastcounter->numtype = 'BPV';
                    $lastcounter->tahun = $coayear;
                    $lastcounter->bulan = $month;
                    $lastcounter->last_counter = 1;
                    $lastcounter->save();
                }

                $nextJournalNumber = str_pad($nextJournalNumber, 6, 0, STR_PAD_LEFT);
                $journalNumber = "BPV/".$coayear."/".$this->Romawi($month)."/".$nextJournalNumber;

                $header = TrApHeader::find($id);
                if(!empty($limitMinPostingDate) && $header->invoice_date < $limitMinPostingDate){
                    \DB::rollback();
                    return response()->json(['error'=>1, 'message'=> "You can't posting if one of these invoice date is before last close date"]);
                }

                // lawanan hutang (DEBET)
                $total = 0;
                foreach ($header->detail as $detail) {
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    $journal = [
                                    'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                    'ledge_fisyear' => $coayear,
                                    'ledg_number' => $journalNumber,
                                    'ledg_date' => $header->invoice_date,
                                    'ledg_refno' => $header->invoice_no,
                                    'ledg_description' => $detail->note,
                                    'coa_year' => $coayear,
                                    'coa_code' => $detail->coa_code,
                                    'created_by' => Auth::id(),
                                    'updated_by' => Auth::id(),
                                    'jour_type_id' => $jourType->id,
                                    'dept_id' => $detail->dept_id,
                                    'modulname' => 'AP',
                                    'refnumber' =>$id
                                ];
                    if($detail->coa_type == 'DEBET'){
                        $journal['ledg_debit'] = $detail->qty * $detail->amount;
                        $journal['ledg_credit'] = 0;
                        $total += $detail->qty * $detail->amount;
                    }else{
                        $journal['ledg_credit'] = $detail->qty * $detail->amount;
                        $journal['ledg_debit'] = 0;
                        $total -= $detail->qty * $detail->amount;
                    }
                    TrLedger::create($journal);
                    $nextJournalNumber++;
                }
                // Hutang (KREDIT)
                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                $journal = [
                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => $header->invoice_date,
                        'ledg_refno' => $header->invoice_no,
                        'ledg_debit' => 0,
                        'ledg_credit' => $total,
                        'ledg_description' => $detail->note,
                        'coa_year' => $coayear,
                        'coa_code' => $header->supplier->coa_code,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id,
                        'dept_id' => $detail->dept_id,
                        'modulname' => 'AP',
                        'refnumber' =>$id
                    ];
                TrLedger::create($journal);

                $header->posting = true;
                $header->posting_at = date('Y-m-d');
                $header->save();
            }
            
            \DB::commit();
            return response()->json(['success'=>1, 'message'=>'AP posted Successfully']);
        }catch(\Exception $e){
            \DB::rollback();
            return response()->json(['error'=>1, 'message'=> $e->getMessage()]);
        }

    }

    public function unposting(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

        $ids = TrApHeader::where('posting',1)->whereIn('id', $ids)->pluck('id');
        if(count($ids) < 1) return response()->json(['error'=>1, 'message'=> "0 AP Ter-unposting"]);
        $sc = 0;
        foreach ($ids as $id) {
            TrLedger::where('refnumber', $id)->where('modulname','AP')->delete();
            $pay = TrApHeader::find($id);
            $pay->update(['posting'=>0,'posting_at'=>NULL]);
            $sc++;
        }
        $message = $sc.' AP Unposting';
        return response()->json(['success'=>1, 'message'=> $message]);
    }

    public function withoutpoEdit(Request $request, $id)
    {
        $coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        $data['suppliers'] = MsSupplier::all();
        $data['departments'] = MsDepartment::where('dept_isactive',1)->get();
        $data['payment_terms'] = DB::table('ms_payment_terms')->get();
        $data['ppn_options'] = DB::table('ms_ppn')->get();
        $data['header'] = TrApHeader::find($id);
        $data['detail'] = TrApDetail::join('ms_department','ms_department.id','=','tr_ap_invoice_dtl.dept_id')->where('aphdr_id',$id)->get();
        $supps= MsSupplier::find($data['header']->spl_id);
        $data['supps'] = $supps;
        return view('accpayable_edit_withoutpo',$data);
    }

    public function updateAP(Request $request)
    {

        $id = $request->ap_id;
        $header = TrApHeader::find($id);
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
        $discount = $request->discount;
        $coatype = $request->coa_type;
        $dept = $request->dept_id;
        $total = $totalppn = $totaldiscount = 0;

        //delete detail
        TrApDetail::where('aphdr_id',$id)->delete();

        foreach ($coa_code as $key => $coa) {
            $detail = new TrApDetail;
            $detail->aphdr_id = $id;
            $detail->note = $notes[$key];
            $detail->qty = $qty[$key];
            $detail->amount = (float)$amount[$key];
            $detail->discount = (float)$discount[$key];
            $detail->final_total = $detail->amount - $detail->discount;
            $detail->ppn_amount = 0;
            $detail->coa_code = $coa;
            $detail->dept_id = $dept[$key];
            $detail->coa_type = $coatype[$key];
            $details[] = $detail;
            if($detail->coa_type == 'DEBET'){
               // $total += $qty[$key] * $amount[$key];
                $total += $detail->final_total;
            }else{
                // $total -= $qty[$key] * $amount[$key];
                $total -= $detail->final_total;
            }
            // $totalppn += $ppn_amount[$key];
            $totaldiscount += $detail->discount;
        }
        $header->total = $total + $totalppn;
        $header->discount = $totaldiscount;
        $header->final_total = $header->total - $header->discount;
        $header->outstanding = $header->final_total;
        $header->ppn = $totalppn;
        $header->dpp = ROUND($header->final_total/1.1,0);
        $header->update();
        $header->detail()->saveMany($details);
        \DB::commit();
        return redirect()->back()->with(['success' => 'Update Success']);
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
                $action_button .= '| <a href="'.route('po.pdf',$value->id).'" ><i class="fa fa-file"></i></a>';
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
        $fetch = TrPOHeader::where(\DB::raw('LOWER(po_number)'),'like','%'.$key.'%')->where('is_ap',0)->get();

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
        $result = TrPOHeader::with('detail.dept')->where('id',$id)->first();
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

        $prefix = @MsConfig::where('name','po_prefix')->first()->value;
		$temp = $prefix."-".date('ymd')."-";
        $check = TrPOHeader::where('po_number','like',$temp."%")->orderBy('po_number','desc')->first();
        if(!$check || count($check) == 0){
            $temp .= '001';
        }else{
            $split = explode('-', $check->po_number);
            $nextNumber = $split[2] + 1;
            $temp .= str_pad($nextNumber, 3, 0, STR_PAD_LEFT);
        }
		// do{
		// 	$check = TrPOHeader::where('po_number',$temp)->first();
		// }while(!empty($check));
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
            $header = new TrPOHeader;
            $header->spl_id = $request->spl_id;
            if($request->number_mode == "auto"){
                $header->po_number = $request->po_number;
            }else{
                $check = TrPOHeader::where('po_number',$request->po_number_manual)->first();
                if($check) return redirect()->back()->with(['error' => 'PO Number is already exist']);
                $header->po_number = $request->po_number_manual;
            }
            $header->po_date = $request->po_date;
            $header->due_date = $request->invoice_duedate;
            $header->terms = $request->terms;
            $header->note = $request->hdnote;
            $header->created_by = \Auth::id();
            $header->updated_by = \Auth::id();

            $coa_code = $request->coa_code;
            $coatype = $request->coa_type;
            $ppn_coa = $request->ppn_coa_code;
            $notes = $request->note;
            $qty = $request->qty;
            $amount = $request->amount;
            // $ppn_amount = $request->ppn_amount;
            // $ppn_flag = $request->is_ppn;
            $dept = $request->dept_id;
            $total = $totalppn = 0;
            foreach ($coa_code as $key => $coa) {
            	$detail = new TrPODetail;
            	$detail->note = $notes[$key];
            	$detail->qty = $qty[$key];
            	$detail->amount = (float)$amount[$key];
            	$detail->ppn_amount = 0;
                // $detail->ppn_amount = (float)$ppn_amount[$key];
            	// $detail->is_ppn = !empty($ppn_amount[$key]) ? true : false;
            	// $detail->ppn_coa_code = !empty($ppn_coa[$key]) ? $ppn_coa[$key] : null;
            	$detail->coa_code = $coa;
            	$detail->dept_id = $dept[$key];
                $detail->coa_type = $coatype[$key];
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
            $coatype = $request->coa_type;
            // $ppn_amount = $request->ppn_amount;
            // $ppn_flag = $request->is_ppn;
            $dept = $request->dept_id;
            $total = $totalppn = 0;
            foreach ($coa_code as $key => $coa) {
            	$detail = new TrPODetail;
            	$detail->note = $notes[$key];
            	$detail->qty = $qty[$key];
            	$detail->amount = (float)$amount[$key];
            	$detail->ppn_amount = 0;
                // $detail->ppn_amount = (float)$ppn_amount[$key];
            	// $detail->is_ppn = !empty($ppn_amount[$key]) ? true : false;
            	// $detail->ppn_coa_code = !empty($ppn_coa[$key]) ? $ppn_coa[$key] : null;
            	$detail->coa_code = $coa;
            	$detail->dept_id = $dept[$key];
                $detail->coa_type = $coatype[$key];
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

    public function getOptSupplier(Request $request){
        $key = $request->q;
        $fetch = MsSupplier::select('id','spl_code','spl_name')->where(function($query) use($key){
            $query->where(\DB::raw('LOWER(spl_code)'),'like','%'.$key.'%')->orWhere(\DB::raw('LOWER(spl_name)'),'like','%'.$key.'%');
        })->where('spl_isactive', 'TRUE')->get();
        $result['results'] = [];
        foreach ($fetch as $key => $value) {
            $temp = ['id'=>$value->id, 'text'=>$value->spl_name." (".$value->spl_code.")"];
            array_push($result['results'], $temp);
        }
        return json_encode($result);
    }

    public function poPdf(Request $request, $id)
    {
        $data['po'] = TrPOHeader::find($id);
        $data['company'] = MsCompany::first()->toArray();
        $data['signature'] = @MsConfig::where('name','footer_signature_name')->first()->value;
        $data['position'] = @MsConfig::where('name','footer_signature_position')->first()->value;
        $data['footer'] = @MsConfig::where('name','footer_po')->first()->value;
        $data['label'] = @MsConfig::where('name','footer_label_po')->first()->value;
        return view('layouts.report_po', $data);
    }

    public function detail(Request $request)
    {
        $id = $request->id;
        $data['ap'] = TrApHeader::find($id);
        return view('modal.detailap', $data);
    }

    public function Romawi($n){
        $hasil = "";
        $iromawi = array("","I","II","III","IV","V","VI","VII","VIII","IX","X",20=>"XX",30=>"XXX",40=>"XL",50=>"L",60=>"LX",70=>"LXX",80=>"LXXX",90=>"XC",100=>"C",200=>"CC",300=>"CCC",400=>"CD",500=>"D",600=>"DC",700=>"DCC",800=>"DCCC",900=>"CM",1000=>"M",2000=>"MM",3000=>"MMM");
        if(array_key_exists($n,$iromawi)){
            $hasil = $iromawi[$n];
        }elseif($n >= 11 && $n <= 99){
            $i = $n % 10;
            $hasil = $iromawi[$n-$i].$this->Romawi($n % 10);
        }elseif($n >= 101 && $n <= 999){
            $i = $n % 100;
            $hasil = $iromawi[$n-$i].$this->Romawi($n % 100);
        }else{
            $i = $n % 1000;
            $hasil = $iromawi[$n-$i].$this->Romawi($n % 1000);
        }
        return $hasil;
    }

}