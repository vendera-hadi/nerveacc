<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\TrLedger;
use App\Models\TrInvoicePaymhdr;
use App\Models\TrInvoicePaymdtl;
use App\Models\TrInvpaymJournal;
use App\Models\MsMasterCoa;
use App\Models\MsJournalType;
use Auth;
use DB;

class PaymentController extends Controller
{
    public function index(){
        
        $contract_data = TrInvoice::select('ms_tenant.tenan_name', 'tr_contract.contr_code', 'tr_contract.id', 'tr_invoice.contr_id')
        ->join('ms_tenant','tr_invoice.tenan_id','=','ms_tenant.id')
        ->join('tr_contract','tr_invoice.contr_id','=','tr_contract.id')
        ->orderBy('ms_tenant.tenan_name', 'ASC')
        ->groupBy('tr_invoice.contr_id', 'ms_tenant.tenan_name', 'tr_contract.contr_code', 'tr_contract.id')
        // ->where('tr_invoice.inv_outstanding', '>', 0)
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
            $fetch = TrInvoicePaymhdr::select('tr_invoice_paymhdr.*', 'ms_tenant.tenan_name','tr_contract.contr_no', 'ms_unit.unit_name','ms_floor.floor_name')
                    ->join('ms_payment_type',   'ms_payment_type.id',"=",'tr_invoice_paymhdr.paymtp_code')
                    ->join('tr_contract',       'tr_contract.id',"=",'tr_invoice_paymhdr.contr_id')
                    ->join('ms_unit',           'tr_contract.unit_id',"=",'ms_unit.id')
                    ->join('ms_floor',          'ms_unit.floor_id',"=",'ms_floor.id')
                    ->join('ms_tenant',         'ms_tenant.id',"=",'tr_contract.tenan_id');

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
            
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['contr_no'] = $value->contr_no;
                $temp['unit'] = $value->unit_name." (".$value->floor_name.")";
                $temp['invpayh_checkno'] = $value->invpayh_checkno;
                $temp['invpayh_date'] = date('d/m/Y',strtotime($value->invpayh_date));
                $temp['invpayh_amount'] = "Rp. ".$value->invpayh_amount;
                $temp['invtp_name'] = $value->invtp_name;
                $temp['contr_id'] = $value->contr_id;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['invpayh_post'] = !empty($value->invpayh_post) ? 'yes' : 'no';

                $action_button = '<a href="#" title="View Detail" data-toggle="modal" data-target="#detailModal" data-id="'.$value->id.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                
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
        ->where(array(
            array('tr_invoice.contr_id', '=',$contract_id),
            // array('tr_invoice.inv_outstanding', '>', 0)
        ))->get();

        if(!empty($invoice_data)){
            $invoice_data = $invoice_data->toArray();
        }

        return view('get_invoice', array(
            'invoice_data' => $invoice_data
        ));
    }

    public function getdetail(Request $request){
        $id = $request->id;
        
        $invoice = TrInvoicePaymhdr::find($id)->with('TrInvoicePaymdtl', 'TrContract')->get()->first();

        if(!empty($invoice)){
            $invoice = $invoice->toArray();

            if(!empty($invoice['tr_invoice_paymdtl'])){
                foreach ($invoice['tr_invoice_paymdtl'] as $key => $value) {
                    $inv_id = !empty($value['inv_id']) ? $value['inv_id'] : false;

                    $invoice_data = TrInvoice::find($inv_id);

                    if(!empty($invoice_data)){
                        $invoice['tr_invoice_paymdtl'][$key] = array_merge($invoice['tr_invoice_paymdtl'][$key], $invoice_data->toArray());
                    }
                }
            }

            if(!empty($invoice['tr_contract']['tenan_id'])){
                $ms_tenant = MsTenant::find($invoice['tr_contract']['tenan_id'])->get()->first();
                
                if(!empty($ms_tenant)){
                    $invoice['ms_tenant'] = $ms_tenant->toArray();
                }
            }
        }
        
        return view('modal.payment', ['invoice' => $invoice]);
    }

    public function insert(Request $request){
        $messages = [
            'contr_id' => 'Contract id must be choose',
            'cashbk_id' => 'cash bank must be choose',
        ];

        $validator = Validator::make($request->all(), [
            'contr_id' => 'required:tr_invoice_paymhdr',
            'contr_no' => 'required:tr_invoice_paymhdr',
        ], $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ['status' => 0, 'message' => $errors];
        }

        $input = [
            'invpayh_date' => $request->input('contr_code'),
            'invpayh_checkno' => $request->input('contr_no'),
            'invpayh_giro' => $request->input('contr_startdate'),
            'invpayh_note' => $request->input('contr_enddate'),
            'invpayh_amount' => $request->input('contr_bast_date'),
            'invpayh_post' => $request->input('contr_bast_by'),
            'paymtp_code' => $request->input('contr_note'),
            'cashbk_id' => 'inputed',
            'contr_id' => $request->input('tenan_id'),
        ];
        $costd_ids = $request->input('costd_is'); 
        $inv_type = $request->input('inv_type');
        $cost_name = $request->input('cost_name');
        $cost_code = $request->input('cost_code');

        $cost_id = $request->input('cost_id');
        $costd_name = $request->input('costd_name');
        $costd_unit = $request->input('costd_unit');
        $costd_rate = $request->input('costd_rate');
        $costd_burden = $request->input('costd_burden');
        $costd_admin = $request->input('costd_admin');
        $inv_type_custom = $request->input('inv_type_custom');
        $periods = $request->input('period');
        $is_meter = $request->input('is_meter');
        try{
            DB::transaction(function () use($input, $request, $cost_id, $costd_name, $costd_unit, $costd_rate, $costd_burden, $costd_admin, $inv_type, $is_meter, $costd_ids, $inv_type_custom, $cost_name, $cost_code, $periods) {
                $contract = TrContract::create($input);
                
                // insert
                if(count($costd_ids) > 0){
                    $total = 0;
                    foreach ($costd_ids as $key => $value) {
                        $inputContractInv = [
                            'contr_id' => $contract->id,
                            'invtp_code' => $inv_type[$key],
                            'costd_is' => $costd_ids[$key],
                            'continv_amount' => $total,
                            'continv_period' => $periods[$key]
                        ];
                        TrContractInvoice::create($inputContractInv);
                    }
                }

                 // unit jadi unavailable
                 MsUnit::where('id',$request->input('unit_id'))->update(['unit_isavailable'=>0]); 

                // insert custom
                if(count($cost_name) > 0){
                    foreach ($cost_name as $key => $value) {
                        // cost item
                        $input = [
                                    'cost_id' => 'COST'.str_replace(".", "", str_replace(" ", "",microtime())),
                                    'cost_code' => $cost_code[$key],
                                    'cost_name' => $cost_name[$key],
                                    'created_by' => \Auth::id(),
                                    'updated_by' => \Auth::id()
                                ];
                        $cost = MsCostItem::create($input);

                        // cost detail
                        $costd_is = 'COSTD'.str_replace(".", "", str_replace(" ", "",microtime())); 
                        $input = [
                            'costd_is' => $costd_is,
                            'cost_id' => $cost->id,
                            'costd_name' => $costd_name[$key],
                            'costd_unit' => $costd_unit[$key],
                            'costd_rate' => $costd_rate[$key],
                            'costd_burden' => $costd_burden[$key],
                            'costd_admin' => $costd_admin[$key],
                            'costd_ismeter' => $is_meter[$key] 
                        ];
                        $costdt = MsCostDetail::create($input);

                        // contract invoice
                        $total = 0;
                        // $total = $costd_rate[$key] + $costd_burden[$key] + $costd_admin[$key];
                        $inputContractInv = [
                            'contr_id' => $contract->id,
                            'invtp_code' => $inv_type_custom[$key],
                            'costd_is' => $costdt->id,
                            'continv_amount' => $total,
                            'continv_period' => $periods[$key]
                        ];
                        TrContractInvoice::create($inputContractInv);
                    }

                }

            });
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
        return ['status' => 1, 'message' => 'Insert Success'];
    }
    
    public function posting(Request $request){
    	$id = $request->id;
    	$coayear = date('Y');
        $month = date('m');
        $journal = [];
        $payJournal = [];
    	
    	// get payment header
    	$paymentHd = TrInvoicePaymhdr::with('Cashbank')->find($id);
        if(!isset($paymentHd->Cashbank->coa_code)) return response()->json(['error'=>1, 'message'=> 'Cashbank Name: '.$paymentHd->Cashbank->cashbk_name.' need to be set with COA code']);
        // create journal DEBET utk piutang
        $coaDebet = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$paymentHd->Cashbank->coa_code)->first();
        if(empty($coaDebet)) return response()->json(['error'=>1, 'message'=>'COA Code: '.$paymentHd->Cashbank->coa_code.' is not found on this year list. Please ReInsert this COA Code']);

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
        $nextJournalNumber = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
        $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumber;
        // Cashbank Jadi DEBET di Payment
        $journal[] = [
                        'ledg_id' => "JRNL".str_replace(".", "", str_replace(" ", "",microtime())),
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
        $paymentDtl = TrInvoicePaymdtl::join('tr_invoice','tr_invoice_paymdtl.inv_id','=','tr_invoice.id')
        								->join('ms_invoice_type','tr_invoice.invtp_id','=','ms_invoice_type.id')
        								->where('invpayh_id',$id)->first();
        if(!isset($paymentDtl->invtp_coa_ar)) return response()->json(['error'=>1, 'message'=> 'Invoice Type Name: '.$paymentDtl->invtp_name.' need to be set with COA code']);
        
       	$coaCredit = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$paymentDtl->invtp_coa_ar)->first();
        if(empty($coaCredit)) return response()->json(['error'=>1, 'message'=>'COA Code: '.$paymentDtl->invtp_coa_ar.' is not found on this year list. Please ReInsert this COA Code']);
        
        $journal[] = [
                        'ledg_id' => "JRNL".str_replace(".", "", str_replace(" ", "",microtime())),
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
        
        // INSERT DATABASE
        try{
            DB::transaction(function () use($id, $payJournal, $journal){
                // insert journal
                TrLedger::insert($journal);
                // insert invoice payment journal
                TrInvpaymJournal::insert($payJournal);
                // update posting to yes
                TrInvoicePaymhdr::where('id', $id)->update(['invpayh_post'=>1, 'posting_at'=>date('Y-m-d'), 'posting_by'=>Auth::id()]);
            });
        }catch(\Exception $e){
            return response()->json(['error'=>1, 'message'=> 'Error occured when posting invoice payment']);
        }

        return response()->json(['success'=>1, 'message'=>'Invoice Payment posted Successfully']);
    }
}
