<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\TrLedger;
use App\Models\TrInvoice;
use App\Models\MsCashBank;
use App\Models\MsPaymentType;
use App\Models\MsTenant;
use App\Models\TrInvoicePaymhdr;
use App\Models\TrInvoicePaymdtl;
use App\Models\TrInvpaymJournal;
use App\Models\MsMasterCoa;
use App\Models\MsJournalType;
use App\Models\MsConfig;
use Auth;
use DB;
use Validator;

class PaymentController extends Controller
{
    public function index(){
        
        $contract_data = TrInvoice::select('ms_tenant.tenan_name', 'tr_contract.contr_code', 'tr_contract.id', 'tr_invoice.contr_id')
        ->join('ms_tenant','tr_invoice.tenan_id','=','ms_tenant.id')
        ->join('tr_contract','tr_invoice.contr_id','=','tr_contract.id')
        ->orderBy('ms_tenant.tenan_name', 'ASC')
        ->groupBy('tr_invoice.contr_id', 'ms_tenant.tenan_name', 'tr_contract.contr_code', 'tr_contract.id')
        ->where('tr_invoice.inv_outstanding', '>', 0)
        ->get()
        ->toArray();

        $cashbank_data = MsCashBank::all()->toArray();
        $payment_type_data = MsPaymentType::all()->toArray();
        
        if(!empty($contract_data)){
            $temp = array();
            foreach ($contract_data as $key => $value) {
                $temp[] = array(
                    'id' => $value['id'],
                    'tenan_name' => sprintf('%s | %s', $value['tenan_name'], $value['contr_code'])
                );
            }

            $contract_data = $temp;
        }

        return view('payment', array(
            'contract_data' => $contract_data,
            'cashbank_data' => $cashbank_data,
            'payment_type_data' => $payment_type_data
        ));
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
            $count = TrInvoicePaymhdr::count();
            $fetch = TrInvoicePaymhdr::select('tr_invoice_paymhdr.*', 'ms_tenant.tenan_name','tr_contract.contr_no', 'ms_unit.unit_code','ms_floor.floor_name', 'ms_payment_type.paymtp_name')
                    ->join('ms_payment_type',   'ms_payment_type.id',"=",'tr_invoice_paymhdr.paymtp_code')
                    ->join('tr_contract',       'tr_contract.id',"=",'tr_invoice_paymhdr.contr_id')
                    ->join('ms_unit',           'tr_contract.unit_id',"=",'ms_unit.id')
                    ->join('ms_floor',          'ms_unit.floor_id',"=",'ms_floor.id')
                    ->join('ms_tenant',         'ms_tenant.id',"=",'tr_contract.tenan_id')
                    ->where('status_void', '=', false);

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
            // jika ada keyword
            if(!empty($keyword)) $fetch = $fetch->where(function($query) use($keyword){
                                        $query->where(\DB::raw('lower(trim("contr_no"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("invpayh_checkno"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("tenan_name"::varchar))'),'like','%'.$keyword.'%');
                                    });
            // jika ada date from
            if(!empty($datefrom)) $fetch = $fetch->where('tr_invoice_paymhdr.invpayh_date','>=',$datefrom);
            // jika ada date to
            if(!empty($dateto)) $fetch = $fetch->where('tr_invoice_paymhdr.invpayh_date','<=',$dateto);

            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            
            $fetch = $fetch->with('TrInvoicePaymdtl.TrInvoice')->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['unit_code'] = $value->unit_code;
                $temp['unit'] = $value->unit_name." (".$value->floor_name.")";
                $temp['paymtp_name'] = $value->paymtp_name;
                $temp['invpayh_date'] = date('d/m/Y',strtotime($value->invpayh_date));
                $temp['invpayh_amount'] = "Rp. ".number_format($value->invpayh_amount);
                $temp['invtp_name'] = $value->invtp_name;
                $temp['contr_id'] = $value->contr_id;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['checkbox'] = '<input type="checkbox" name="check" value="'.$value->id.'">';
                $invpayh_post = $temp['invpayh_post'] = !empty($value->invpayh_post) ? 'yes' : 'no';

                $temp['inv_no'] = "";
                if(count($value->TrInvoicePaymdtl) > 0){
                    foreach ($value->TrInvoicePaymdtl as $paydt) {
                        if(!empty(@$paydt->TrInvoice->inv_number)) $temp['inv_no'] .= $paydt->TrInvoice->inv_number."<br>";
                    }
                }

                $action_button = '<a href="#" title="View Detail" data-toggle="modal" data-target="#detailModal" data-id="'.$value->id.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                
                if($invpayh_post == 'no'){
                    if(\Session::get('role')==1 || in_array(70,\Session::get('permissions'))){
                        $action_button .= ' | <a href="#" data-id="'.$value->id.'" title="Posting Payment" class="posting-confirm"><i class="fa fa-arrow-circle-o-up"></i></a>';
                    }
                    if(\Session::get('role')==1 || in_array(71,\Session::get('permissions'))){
                        $action_button .= ' | <a href="payment/void?id='.$value->id.'" title="Void" class="void-confirm"><i class="fa fa-ban"></i></a>';
                    }
                }
                $action_button .= ' | <a href="'.url('invoice/print_kwitansi?id='.$value->id).'" class="print-window" data-width="640" data-height="660"><i class="fa fa-file"></i></a>';

                $temp['action_button'] = $action_button;

                // $temp['daysLeft']
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function get_invoice(Request $request){
        $contract_id = @$request->contract_id;

        $invoice_data = TrInvoice::select('tr_invoice.id', 'tr_invoice.inv_number', 'tr_invoice.inv_date', 'tr_invoice.inv_duedate', 'tr_invoice.inv_outstanding', 'ms_unit.unit_name', 'ms_floor.floor_name')
        ->join('tr_contract', 'tr_contract.id',"=",'tr_invoice.contr_id')
        ->join('ms_unit', 'tr_contract.unit_id',"=",'ms_unit.id')
        ->join('ms_floor', 'ms_unit.floor_id',"=",'ms_floor.id')
        ->where('tr_invoice.contr_id', '=',$contract_id)
        ->where('tr_invoice.inv_outstanding', '>', 0)
        ->where('tr_invoice.inv_post', 1)
        ->get();

        if(!empty($invoice_data)){
            $invoice_data = $invoice_data->toArray();
        }

        return view('get_invoice', array(
            'invoice_data' => $invoice_data
        ));
    }

    public function getdetail(Request $request){
        $id = $request->id;
        
        $invoice = TrInvoicePaymhdr::where('id', $id)->with('TrInvoicePaymdtl', 'TrContract')->get()->first();

        if(!empty($invoice)){
            $invoice = $invoice->toArray();

            if(!empty($invoice['tr_invoice_paymdtl'])){
                foreach ($invoice['tr_invoice_paymdtl'] as $key => $value) {
                    $inv_id = !empty($value['inv_id']) ? $value['inv_id'] : false;

                    $invoice_data = TrInvoice::where('id', $inv_id)->get()->first();

                    if(!empty($invoice_data)){
                        $invoice['tr_invoice_paymdtl'][$key] = array_merge($invoice['tr_invoice_paymdtl'][$key], $invoice_data->toArray());
                    }
                }
            }

            if(!empty($invoice['tr_contract']['tenan_id'])){
                $ms_tenant = MsTenant::where('id', $invoice['tr_contract']['tenan_id'])->get()->first();
                
                if(!empty($ms_tenant)){
                    $invoice['ms_tenant'] = $ms_tenant->toArray();
                }
            }
        }
        
        return view('modal.payment', ['invoice' => $invoice]);
    }

    public function insert(Request $request){
        $messages = [
            'contr_id.required' => 'Tenan name is required',
            'cashbk_id.required' => 'Bank is required',
            'paymtp_code.required' => 'Payment Type is required',
            'invpayh_date.required' => 'Payment Date is required',
        ];

        $validator = Validator::make($request->all(), [
            'contr_id' => 'required:tr_invoice_paymhdr',
            'cashbk_id' => 'required:tr_invoice_paymhdr',
            'paymtp_code' => 'required:tr_invoice_paymhdr',
            'invpayh_date' => 'required:tr_invoice_paymhdr'
        ], $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ['status' => 0, 'message' => $errors];
        }
        
        $data_payment = $request->input('data_payment');
        // dd($data_payment);
        $detail_payment = array();

        $cek_pay = false;
        $total = 0;
        if(!empty($data_payment) && count($data_payment['invpayd_amount']) > 0){
            foreach ($data_payment['invpayd_amount'] as $key => $value) {
                if(!empty($value)){
                    $cek_pay = true;

                    $payVal = (int)$data_payment['totalpay'][$key];
                    $total += $payVal;
                    // $total += (int) $value;
                    $detail_payment[] = array(
                        // 'invpayd_amount' => $value,
                        'invpayd_amount' => $payVal,
                        'inv_id' => $key
                    );

                    $tempAmount = 0;
                    $invoice = TrInvoice::find($key);
                    if(isset($invoice->inv_outstanding)){
                        // $tempAmount = $invoice->inv_outstanding - $value;
                        $tempAmount = $invoice->inv_outstanding - $payVal;
                        // update
                        $invoice->inv_outstanding = (int)$tempAmount;
                        $invoice->save();
                    }
                }
            }
        }else{
            return ['status' => 0, 'message' => 'Please Check at least one of Invoice for payment'];
        }

        try{
            if($total <= 0){
                return ['status' => 0, 'message' => 'You have not entered payment'];
            }else{
                $lastPayment = TrInvoicePaymhdr::where(\DB::raw('EXTRACT(YEAR FROM created_at)'),'=',date('Y'))
                                ->where(\DB::raw('EXTRACT(MONTH FROM created_at)'),'=',date('m'))
                                ->orderBy('created_at','desc')->first();
                if($lastPayment){
                    $index = explode('.',$lastPayment->no_kwitansi);
                    $index = (int) end($index);
                    $index+= 1;
                    $index = str_pad($index, 3, "0", STR_PAD_LEFT);
                }else{
                    $index = "001";
                }

                $action = new TrInvoicePaymhdr;

                $prefixKuitansi = @MsConfig::where('name','prefix_kuitansi')->first()->value;
                $action->no_kwitansi = $prefixKuitansi.'-'.date('Y-m').'.'.$index;
                $action->invpayh_date = $request->input('invpayh_date');
                $action->invpayh_checkno = $request->input('invpayh_checkno');
                $action->invpayh_giro = !empty($request->input('invpayh_giro')) ? $request->input('invpayh_giro') : null ;
                $action->invpayh_note = $request->input('invpayh_note');
                $action->invpayh_post = !empty($request->input('invpayh_post')) ? true : false;
                $action->paymtp_code = $request->input('paymtp_code');
                $action->cashbk_id = $request->input('cashbk_id');
                $action->contr_id = $request->input('contr_id');
                $action->invpayh_settlamt = 1;
                $action->invpayh_adjustamt = 1;
                $action->invpayh_amount = $total;
                $action->updated_by = $action->created_by = Auth::id();
                $action->status_void = false;

                if($action->save()){
                    $payment_id = $action->id;

                    foreach ($detail_payment as $key => $value) {
                        $action_detail = new TrInvoicePaymdtl;

                        $invoice_data = $invoice->get()->first();
                        
                        if(!empty($invoice_data)){
                            $invoice_data = $invoice_data->toArray();
                            
                            $inv_amount = $invoice_data['inv_amount'];

                            $invoice_has_paid = TrInvoicePaymdtl::select('tr_invoice_paymhdr.*', 'tr_invoice_paymdtl.*')
                                ->join('tr_invoice_paymhdr','tr_invoice_paymdtl.invpayh_id','=','tr_invoice_paymhdr.id')
                                ->where('status_void', '=', false)
                                ->where('inv_id', '=', $value['inv_id'])
                                ->get()->first();

                            if(!empty($invoice_has_paid)){
                                $invoice_has_paid = $invoice_has_paid->sum('invpayd_amount');
                            }else{
                                $invoice_has_paid = 0;
                            }
                            
                            $total_has_paid = $invoice_has_paid + $value['invpayd_amount'];
                            $outstand = $inv_amount - $total_has_paid;

                            if($outstand <= 0){
                                $outstand = 0;
                            }

                            $action_detail->invpayd_amount = $value['invpayd_amount'];
                            $action_detail->inv_id = $value['inv_id'];
                            $action_detail->invpayh_id = $payment_id;

                            $action_detail->save();
                        }
                    }
                }else{
                    return ['status' => 0, 'message' => 'Failed to submit payment'];
                }
            }
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
        return ['status' => 1, 'message' => 'Insert Success', 'paym_id' => $payment_id];
    }
    
    public function posting(Request $request){
    	$ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];
        
    	$coayear = date('Y');
        $month = date('m');
        $journal = [];
        $payJournal = [];
    	
        // cari last prefix, order by journal type
        $jourType = MsJournalType::where('jour_type_prefix','AR')->first();
        if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "AR" first before posting an invoice']);
        $lastJournal = TrLedger::where('jour_type_id',$jourType->id)->latest()->first();
        if($lastJournal){
            $lastJournalNumber = explode(" ", $lastJournal->ledg_number);
            $lastJournalNumber = (int) end($lastJournalNumber);
            $nextJournalNumber = $lastJournalNumber + 1;
        }else{
            $nextJournalNumber = 1;
        }
        $successPosting = 0;
        $successIds = [];
        foreach ($ids as $id) {
        	// get payment header
        	$paymentHd = TrInvoicePaymhdr::with('Cashbank')->find($id);
            if(!isset($paymentHd->Cashbank->coa_code)) return response()->json(['error'=>1, 'message'=> 'Cashbank Name: '.$paymentHd->Cashbank->cashbk_name.' need to be set with COA code']);
            // create journal DEBET utk piutang
            $coaDebet = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$paymentHd->Cashbank->coa_code)->first();
            if(empty($coaDebet)) return response()->json(['error'=>1, 'message'=>'COA Code: '.$paymentHd->Cashbank->coa_code.' is not found on this year list. Please ReInsert this COA Code']);
            
            $nextJournalNumberConvert = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
            $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumberConvert;
            $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
            // Cashbank Jadi DEBET di Payment
            $journal[] = [
                            'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                            'ledge_fisyear' => $coayear,
                            'ledg_number' => $journalNumber,
                            'ledg_date' => date('Y-m-d'),
                            'ledg_refno' => $paymentHd->invpayh_checkno,
                            'ledg_debit' => $paymentHd->invpayh_amount,
                            'ledg_credit' => 0,
                            'ledg_description' => $coaDebet->coa_name,
                            'coa_year' => $coaDebet->coa_year,
                            'coa_code' => $coaDebet->coa_code,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'jour_type_id' => $jourType->id,
                            'dept_id' => 3 //hardcode utk finance
                        ];

            $payJournal[] = [
            		'ipayjour_date' => date('Y-m-d'),
            		'ipayjour_voucher' => $journalNumber,
            		'ipayjour_note' => 'Posting Payment '.$paymentHd->invpayh_checkno,
            		'coa_code' => $coaDebet->coa_code,
            		'ipayjour_debit' => $paymentHd->invpayh_amount,
            		'ipayjour_credit' => 0,
            		'invpayh_id' => $id
            	];
            // End DEBET

            // Create CREDIT
            // Piutang yang dijadiin debet di Invoice, sekarang jadiin kredit
            try{
                $paymentDtl = TrInvoicePaymdtl::join('tr_invoice','tr_invoice_paymdtl.inv_id','=','tr_invoice.id')
                								->join('ms_invoice_type','tr_invoice.invtp_id','=','ms_invoice_type.id')
                								->where('invpayh_id',$id)->first();
                if(empty(@$paymentDtl->invtp_coa_ar)) return response()->json(['error'=>1, 'message'=> 'Invoice Type Name: '.$paymentDtl->invtp_name.' need to be set with COA code']);
                
               	$coaCredit = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$paymentDtl->invtp_coa_ar)->first();
                if(empty($coaCredit)) return response()->json(['error'=>1, 'message'=>'COA Code: '.$paymentDtl->invtp_coa_ar.' is not found on this year list. Please ReInsert this COA Code']);
                
                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,-10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => date('Y-m-d'),
                                'ledg_refno' => $paymentHd->invpayh_checkno,
                                'ledg_debit' => 0,
                                'ledg_credit' => $paymentHd->invpayh_amount,
                                'ledg_description' => $coaCredit->coa_name,
                                'coa_year' => $coaCredit->coa_year,
                                'coa_code' => $coaCredit->coa_code,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => $jourType->id,
                                'dept_id' => 3 //hardcode utk finance
                            ];

                $payJournal[] = [
                		'ipayjour_date' => date('Y-m-d'),
                		'ipayjour_voucher' => $journalNumber,
                		'ipayjour_note' => 'Posting Payment '.$paymentHd->invpayh_checkno,
                		'coa_code' => $coaCredit->coa_code,
                		'ipayjour_debit' => 0,
                		'ipayjour_credit' => $paymentHd->invpayh_amount,
                		'invpayh_id' => $id
                	];
                $successIds[] = $id;
                $nextJournalNumber++;
                $successPosting++;
            }catch(\Exception $e){
                // do nothing
            }
        }
        // var_dump($journal);
        // var_dump($payJournal);
        
        // INSERT DATABASE
        try{
            DB::transaction(function () use($successIds, $payJournal, $journal){
                // insert journal
                TrLedger::insert($journal);
                // insert invoice payment journal
                TrInvpaymJournal::insert($payJournal);
                // update posting to yes
                if(count($successIds) > 0){
                    foreach ($successIds as $id) {
                        TrInvoicePaymhdr::where('id', $id)->update(['invpayh_post'=>1, 'posting_at'=>date('Y-m-d'), 'posting_by'=>Auth::id()]);
                    }
                }
            });
        }catch(\Exception $e){
            return response()->json(['error'=>1, 'message'=> 'Error occured when posting invoice payment']);
        } 

        return response()->json(['success'=>1, 'message'=>$successPosting.' Invoice Payment posted Successfully']);
    }

    public function void(Request $request){
        $id = $request->id;

        $invoice = TrInvoicePaymhdr::
            where('id', $id)
            ->where('invpayh_post', '=', false)
            ->with('TrInvoicePaymdtl', 'TrContract')->get()->first();
        
        $result = array(
            'status'=>0, 
            'message'=> 'Data not found'
        );

        if(!empty($invoice)){
            $action = TrInvoicePaymhdr::find($id);
            
            $action->status_void = true;

            if($action->save()){
                $invoice = $invoice->toArray();

                if(!empty($invoice['tr_invoice_paymdtl'])){
                    foreach ($invoice['tr_invoice_paymdtl'] as $key => $value) {
                        $invoice_id = $value['inv_id'];

                        $invoice_has_paid = TrInvoicePaymdtl::select('tr_invoice_paymhdr.*', 'tr_invoice_paymdtl.*')
                            ->join('tr_invoice_paymhdr','tr_invoice_paymdtl.invpayh_id','=','tr_invoice_paymhdr.id')
                            ->where('status_void', '=', false)
                            ->where('inv_id', '=', $invoice_id)
                            ->first();

                        if(!empty($invoice_has_paid)){
                            $invoice_has_paid = $invoice_has_paid->sum('invpayd_amount');
                        }else{
                            $invoice_has_paid = 0;
                        }
                        
                        $invoice_target = TrInvoice::find($invoice_id);

                        $invoice_data = $invoice_target->get()->first();

                        if(!empty($invoice_data)){
                            $invoice_data = $invoice_data->toArray();

                            $inv_amount = $invoice_data['inv_amount'];

                            $outstand = $inv_amount - $invoice_has_paid;

                            if($outstand <= 0){
                                $outstand = 0;
                            }

                            $invoice_target->inv_outstanding = $outstand;

                            $invoice_target->save();
                        }
                    }
                }

                $result = array(
                    'status'=>1, 
                    'message'=> 'Success void payment'
                );
            }else{
                $result = array(
                    'status'=>0, 
                    'message'=> 'Cannot void payment, try again later'
                );
            }
        }

        return response()->json($result);
    }
}
