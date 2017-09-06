<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\TrApPaymentHeader;
use App\Models\TrApPaymentDetail;
use App\Models\MsSupplier;
use App\Models\MsCashBank;
use App\Models\MsPaymentType;
use App\Models\TrApHeader;
use App\Models\TrLedger;
use App\Models\MsDepartment;
use App\Models\MsJournalType;
use Auth;
use DB;
use Validator;
use Carbon\Carbon;

class TreasuryController extends Controller
{
	public function index(){
        $data['suppliers'] = MsSupplier::all();
        $data['cashbank_data'] = MsCashBank::all();
        $data['payment_type_data'] = MsPaymentType::all();
		return view('ap_payment', $data);
	}

	public function get(Request $request){
        try{
            // params
            $keyword = @$request->q;
            $invtype = @$request->invtype;
            $datefrom = @$request->datefrom;
            $dateto = @$request->dateto;  

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
            $count = TrApPaymentHeader::count();
            $fetch = TrApPaymentHeader::select('tr_ap_payment_hdr.*','ms_payment_type.paymtp_name','ms_supplier.spl_name')
            			->join('ms_payment_type',   'ms_payment_type.id',"=",'tr_ap_payment_hdr.paymtp_id')
        				->join('ms_supplier', 'ms_supplier.id', '=', 'tr_ap_payment_hdr.spl_id');
        
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
                
                $temp['payment_code'] = $value->payment_code;
                $temp['spl_name'] = $value->spl_name;
                $temp['invoice_no'] = collect($value->detail)->map(function($detail){
                	return $detail->apheader->invoice_no;
                })->implode(', ');
                $temp['paymtp_name'] = $value->paymtp_name;
                $temp['payment_date'] = date('d/m/Y',strtotime($value->payment_date));
                $temp['amount'] = "Rp. ".number_format($value->amount);
               
                $temp['checkbox'] = '<input type="checkbox" name="check" value="'.$value->id.'">';
                $invpayh_post = $temp['posting'] = !empty($value->posting_at) ? 'yes' : 'no';

                $action_button = '<a href="#" title="View Detail" data-toggle="modal" data-target="#detailModal" data-id="'.$value->id.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                
                if($invpayh_post == 'no'){
                    // if(\Session::get('role')==1 || in_array(70,\Session::get('permissions'))){
                        $action_button .= ' | <a href="#" data-id="'.$value->id.'" title="Posting Payment" class="posting-confirm"><i class="fa fa-arrow-circle-o-up"></i></a>';
                    // }
                    // if(\Session::get('role')==1 || in_array(71,\Session::get('permissions'))){
                        $action_button .= ' | <a href="treasury/void?id='.$value->id.'" title="Void" class="void-confirm"><i class="fa fa-ban"></i></a>';
                    // }
                }
                // $action_button .= ' | <a href="'.url('invoice/print_kwitansi?id='.$value->id).'" class="print-window" data-width="640" data-height="660"><i class="fa fa-file"></i></a>';

                $temp['action_button'] = $action_button;

                // $temp['daysLeft']
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getdetail(Request $request){
        $id = $request->id;
        $header = TrApPaymentHeader::find($id);
        return view('modal.treasury', ['header' => $header]);
	}

    public function getAPofSupplier(Request $request){
        $supplier_id = @$request->id;

        $invoice_data = TrApHeader::select('tr_ap_invoice_hdr.*','tr_purchase_order_hdr.po_number')
                            ->join('tr_purchase_order_hdr', 'tr_ap_invoice_hdr.po_id',"=",'tr_purchase_order_hdr.id')
                            ->where('tr_ap_invoice_hdr.posting', 1)
                            ->where('outstanding', '>', 0)
                            ->where('tr_ap_invoice_hdr.spl_id', '=',$supplier_id)
                            ->get();

        if(!empty($invoice_data)){
            $invoice_data = $invoice_data->toArray();
        }

        return view('get_apinvoice', array(
            'invoice_data' => $invoice_data
        ));
    }

    public function insert(Request $request)
    {
        $messages = [
            'spl_id.required' => 'Supplier is required',
            'payment_date.required' => 'Payment Date is required',
            'paymtp_id.required' => 'Payment Type is required',
            'payment_date.required' => 'Payment Date is required',
        ];

        $validator = Validator::make($request->all(), [
            'spl_id' => 'required',
            'cashbk_id' => 'required',
            'paymtp_id' => 'required',
            'payment_date' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        $temp = "APPAY-".date('my')."-".strtoupper(str_random(6));
        do{
            $check = TrApPaymentHeader::where('payment_code',$temp)->first();
        }while(!empty($check));

        // dd($request->all());
        \DB::beginTransaction();
        try{
            // insert ke tr ap payment header
            $header = new TrApPaymentHeader;
            $header->spl_id = $request->spl_id;
            $header->payment_code = $temp;
            $header->payment_date = $request->payment_date;
            $header->check_no = $request->check_no;
            if($request->check_date) $header->check_date = $request->check_date;
            $header->note = $request->note;
            $header->created_by = Auth::id();
            $header->updated_by = $header->created_by;
            $header->paymtp_id = $request->paymtp_id;
            $header->cashbk_id = $request->cashbk_id;

            $details = [];
            $total = 0;
            $payamounts = $request->pay;
            foreach ($payamounts as $inv_id => $amount) {
                $detail = new TrApPaymentDetail;
                $detail->amount = $amount;
                $detail->aphdr_id = $inv_id;
                $details[] = $detail;
                $total += $amount;

                // kurangin outstanding ap inv header
                $invheader = TrApHeader::find($inv_id);
                $invheader->outstanding -= $amount;
                $invheader->save();
            }
            $header->amount = $total;
            $header->save();
            $header->detail()->saveMany($details);
            \DB::commit();
            return redirect()->back()->with('success', 'Insert Success');
        }catch(\Exception $e){
            \DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function void(Request $request)
    {
        \DB::beginTransaction();
        try{
            $id = $request->id;
            // semua payment atas inv id dihapus
            $header = TrApPaymentHeader::find($id);
            $details = TrApPaymentDetail::where('appaym_id',$id);
            // balikin outstanding
            foreach ($details->get() as $dtl) {
                $inv = TrApHeader::find($dtl->aphdr_id);
                $inv->outstanding += $dtl->amount;
                $inv->save();
            }
            $header->delete();
            $details->delete();
            \DB::commit();
            $result = array(
                    'status'=>1, 
                    'message'=> 'Success void payment'
                );
        }catch(\Exception $e){
            \DB::rollback();
            $result = array(
                    'status'=>0, 
                    'message'=> 'Cannot void payment, try again later'
                );
        }
        return response()->json($result);
    }

    public function posting(Request $request)
    {
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];
        
        $coayear = date('Y');
        $month = date('m');
        $journal = [];

        // cari last prefix, order by journal type
        // using JU utk default
        $jourType = MsJournalType::where('jour_type_prefix','AP')->first();
        if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "AP" first before posting an invoice']);
        $lastJournal = TrLedger::where('jour_type_id',$jourType->id)->latest()->first();
        if($lastJournal){
            $lastJournalNumber = explode(" ", $lastJournal->ledg_number);
            $lastJournalNumber = (int) end($lastJournalNumber);
            $nextJournalNumber = $lastJournalNumber + 1;
        }else{
            $nextJournalNumber = 1;
        }
        
        \DB::beginTransaction();
        try{
            // looping per payment id
            foreach ($ids as $id) {
                // Hutang (KREDIT)
                $appaymentdtl = TrApPaymentDetail::where('appaym_id', $id)->get();
                foreach ($appaymentdtl as $dtl) {
                    $nextJournalNumberConvert = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                    $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumberConvert;
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    $header = TrApHeader::find($dtl->aphdr_id);
                    $journal = [
                            'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                            'ledge_fisyear' => $coayear,
                            'ledg_number' => $journalNumber,
                            'ledg_date' => date('Y-m-d'),
                            'ledg_refno' => $header->invoice_no,
                            'ledg_debit' => 0,
                            'ledg_credit' => $dtl->amount,
                            'ledg_description' => $dtl->header->payment_code,
                            'coa_year' => $coayear,
                            'coa_code' => $header->supplier->coa_code,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'jour_type_id' => $jourType->id,
                            'dept_id' => 3
                        ];
                    TrLedger::create($journal);
                    $nextJournalNumber++;
                    $dtl->header->posting = 1;
                    $dtl->header->posting_at = date('Y-m-d H:i:s');
                    $dtl->header->posting_by = Auth::id();
                    $dtl->header->save();
                }

                // Bank / Kas (DEBET)
                $nextJournalNumberConvert = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumberConvert;
                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                $journal = [
                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => date('Y-m-d'),
                        'ledg_refno' => $dtl->header->payment_code,
                        'ledg_debit' => $dtl->header->amount,
                        'ledg_credit' => 0,
                        'ledg_description' => $dtl->header->payment_code,
                        'coa_year' => $coayear,
                        'coa_code' => $dtl->header->cashbank->coa_code,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id,
                        'dept_id' => 3
                    ];
                TrLedger::create($journal);
            }
            // end foreach
            \DB::commit();
            return response()->json(['success'=>1, 'message'=>'AP Payment posted Successfully']);
        }catch(\Exception $e){
            \DB::rollback();
            return response()->json(['error'=>1, 'message'=> $e->getMessage()]);
        }
        // end try

    }

}