<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\TrLedger;
use App\Models\TrBankJv;
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
use App\Models\TrBank;
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
        $tenan_id = @$request->tenan_id;

        $invoice_data = TrInvoice::select('tr_invoice.id', 'tr_invoice.inv_number', 'tr_invoice.inv_date', 'tr_invoice.inv_duedate', 'tr_invoice.inv_outstanding', 'ms_unit.unit_name', 'ms_floor.floor_name')
        ->leftJoin('tr_contract', 'tr_contract.id',"=",'tr_invoice.contr_id')
        ->leftJoin('ms_unit', 'tr_contract.unit_id',"=",'ms_unit.id')
        ->leftJoin('ms_floor', 'ms_unit.floor_id',"=",'ms_floor.id')
        ->where('tr_invoice.tenan_id', '=',$tenan_id)
        ->where('tr_invoice.inv_outstanding', '>', 0)
        ->where('tr_invoice.inv_post', 1)
        ->where('tr_invoice.inv_iscancel', 0)
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
        $payment_ids = [];
        if(!empty($data_payment) && count($data_payment['invpayd_amount']) > 0){
            $lastPayment = TrInvoicePaymhdr::where(\DB::raw('EXTRACT(YEAR FROM created_at)'),'=',date('Y'))
                                ->where(\DB::raw('EXTRACT(MONTH FROM created_at)'),'=',date('m'))
                                ->orderBy('created_at','desc')->first();
            $indexNumber = null;
            if($lastPayment){
                $index = explode('.',$lastPayment->no_kwitansi);
                $index = (int) end($index);
                $index+= 1;
                $indexNumber = $index;
                $index = str_pad($index, 3, "0", STR_PAD_LEFT);
            }else{
                $index = "001";
                $indexNumber = 1;
            }

            // 1 payment 1 row aja
            foreach ($data_payment['invpayd_amount'] as $key => $value) {
                if(!empty($value)){
                    $cek_pay = true;

                    $payVal = (int)$data_payment['totalpay'][$key];
                    $total = $payVal;
                    // echo "Terbayar $total<br>";
                    $detail_payment = array(
                        // 'invpayd_amount' => $value,
                        'invpayd_amount' => $payVal,
                        'inv_id' => $key
                    );

                    $tempAmount = $currentOutstanding = 0;
                    $invoice = TrInvoice::find($key);
                    if(isset($invoice->inv_outstanding)){
                        // $tempAmount = $invoice->inv_outstanding - $value;
                        $currentOutstanding = $invoice->inv_outstanding;
                        $tempAmount = $invoice->inv_outstanding - $payVal;
                        // update
                        if((int)$tempAmount < 0) $tempAmount = 0;
                        // echo "Outstanding tersisa $tempAmount<br>";
                        $invoice->inv_outstanding = (int)$tempAmount;
                        $invoice->save();
                    }

                    // create paym header
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

                    // payment detail
                    if($action->save()){
                        $payment_id = $action->id;
                        $payment_ids[] = $payment_id;

                        // foreach ($detail_payment as $key => $value) {
                            $action_detail = new TrInvoicePaymdtl;
                            $invoice_data = $invoice->get()->first();

                            if(!empty($invoice_data)){
                                $invoice_data = $invoice_data->toArray();

                                $inv_amount = $invoice_data['inv_amount'];

                                $invoice_has_paid = TrInvoicePaymdtl::select('tr_invoice_paymhdr.*', 'tr_invoice_paymdtl.*')
                                    ->join('tr_invoice_paymhdr','tr_invoice_paymdtl.invpayh_id','=','tr_invoice_paymhdr.id')
                                    ->where('status_void', '=', false)
                                    ->where('inv_id', '=', $detail_payment['inv_id'])
                                    ->get()->first();

                                if(!empty($invoice_has_paid)){
                                    $invoice_has_paid = $invoice_has_paid->sum('invpayd_amount');
                                }else{
                                    $invoice_has_paid = 0;
                                }

                                $total_has_paid = $invoice_has_paid + $detail_payment['invpayd_amount'];
                                $outstand = $inv_amount - $total_has_paid;

                                if($outstand <= 0){
                                    $outstand = 0;
                                }

                                $action_detail->invpayd_amount = $detail_payment['invpayd_amount'];
                                $action_detail->inv_id = $detail_payment['inv_id'];
                                $action_detail->invpayh_id = $payment_id;
                                $action_detail->last_outstanding = $currentOutstanding;
                                $action_detail->save();
                            }
                        // }

                    }

                    $indexNumber++;
                    $index = str_pad($indexNumber, 3, "0", STR_PAD_LEFT);
                }
            }
        }else{
            return ['status' => 0, 'message' => 'Please Check at least one of Invoice for payment'];
        }

        return ['status' => 1, 'message' => 'Insert Success', 'paym_id' => $payment_ids];
    }

    private function saveToTrBank($action, $invoice, $total)
    {
        if($action->paymtp_code == 2){
            $header = new TrBank;
            $header->trbank_no = "BM".date('YmdHis').$action->no_kwitansi;
            $header->trbank_date = $action->invpayh_date;
            $header->trbank_recipient = MsCashBank::find($action->cashbk_id)->cashbk_name;
            $header->trbank_in = $total;
            $header->trbank_group = 'BM';
            if(!empty($header->trbank_girodate)) $header->trbank_girodate = $action->invpayh_date;
            $header->trbank_girono = $action->invpayh_giro;
            $header->cashbk_id = $action->cashbk_id;
            $header->coa_code = MsCashBank::find($action->cashbk_id)->coa_code;
            $header->paymtp_id = 2;
            $header->trbank_note = $action->invpayh_note;
            $header->created_by = \Auth::id();
            $header->updated_by = \Auth::id();
            $header->kurs_id = 1;
            $header->currency_val = $action->invpayh_amount;
            $header->trbank_post = true;
            $header->posting_at = date('Y-m-d H:i:s');

            // payment DEBIT
            $detail = new TrBankJv;
            $detail->coa_code = $header->coa_code;
            $detail->debit = $action->invpayh_amount;
            $detail->credit = 0;
            $detail->note = $invoice->MsTenant->tenan_name." - ".$invoice->inv_number;
            $detail->dept_id = 3;
            $details[] = $detail;

            // lawanan piutang KREDIT
            $debitLedger = TrLedger::where('ledg_refno',$invoice->inv_number)->where('ledg_credit',0)->get();
            $paymentPercentage = $action->invpayh_amount / $invoice->inv_amount;
            if($debitLedger){
                 foreach($debitLedger as $dbt){
                    $detail = new TrBankJv;
                    $detail->coa_code = $dbt->coa_code;
                    $detail->debit = 0;
                    $detail->credit = $paymentPercentage * $dbt->ledg_debit;
                    $detail->note = $dbt->ledg_description;
                    $detail->dept_id = $dbt->dept_id;
                    $details[] = $detail;
                }
            }

            $header->save();
            $header->detail()->saveMany($details);
        }
    }

    public function posting(Request $request){
    	$ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

    	$coayear = date('Y');
        $month = date('m');
        $journal = [];
        $payJournal = [];
        $storeTrBank = [];

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
        $piutangIds = [];

        // cek backdate dr closing bulanan/tahunan
        $lastclose = TrLedger::whereNotNull('closed_at')->orderBy('closed_at','desc')->first();
        $limitMinPostingDate = null;
        if($lastclose) $limitMinPostingDate = date('Y-m-t', strtotime($lastclose->closed_at));

        foreach ($ids as $id) {
        	// get payment header
        	$paymentHd = TrInvoicePaymhdr::with('Cashbank')->find($id);
            // validasi backdate posting
            if(!empty($limitMinPostingDate) && $paymentHd->invpayh_date < $limitMinPostingDate){
                return response()->json(['error'=>1, 'message'=> "You can't posting if one of these payment date is before last close date"]);
            }

            if(!isset($paymentHd->Cashbank->coa_code)) return response()->json(['error'=>1, 'message'=> 'Cashbank Name: '.$paymentHd->Cashbank->cashbk_name.' need to be set with COA code']);
            // create journal DEBET utk piutang
            $coaDebet = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$paymentHd->Cashbank->coa_code)->first();
            if(empty($coaDebet)) return response()->json(['error'=>1, 'message'=>'COA Code: '.$paymentHd->Cashbank->coa_code.' is not found on this year list. Please ReInsert this COA Code']);

            // COA titipan
            $coaHutangTitipanVal = @MsConfig::where('name','coa_hutang_titipan')->first()->value;
            $coaHutangTitipan = MsMasterCoa::where('coa_year',$coayear)->where('coa_code', $coaHutangTitipanVal)->first();
            if(empty($coaHutangTitipan)) return response()->json(['error'=>1, 'message'=>'COA hutang titipan is not found. Please set this COA Code in Config']);

            $nextJournalNumberConvert = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
            $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumberConvert;
            $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
            // Cashbank Jadi DEBET di Payment
            $paymentDtl = TrInvoicePaymdtl::join('tr_invoice','tr_invoice_paymdtl.inv_id','=','tr_invoice.id')
                                            ->join('ms_tenant','ms_tenant.id','=','tr_invoice.tenan_id')
                                            ->where('invpayh_id',$id)->get();
            $refno = !empty($paymentHd->invpayh_checkno) ? $paymentHd->invpayh_checkno : $paymentHd->no_kwitansi;


            foreach ($paymentDtl as $dtl) {
                $totalpayDebet = $dtl->invpayd_amount;
                if($dtl->inv_outstanding <= 0){
                    $totalpayDebet = $dtl->inv_amount;
                    // KALAU LUNAS BALIKIN SEMUA HUTANG TITIPAN
                    $checkTitipan = TrLedger::where('ledg_description',"Hutang Titipan ".$dtl->inv_number)->get();
                    if(count($checkTitipan) > 0){
                        foreach ($checkTitipan as $titipan) {
                            $nextJournalNumber += 1;
                            $nextJournalNumberConvert = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                            $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumberConvert;

                            $journal[] = [
                                            'ledg_id' => "JRNL".substr($microtime,-10).str_random(5),
                                            'ledge_fisyear' => $coayear,
                                            'ledg_number' => $journalNumber,
                                            'ledg_date' => date('Y-m-d'),
                                            // REF NO HUTANG TITIPAN HRS DIPIKIRIN PAKE REFNO YG MANA
                                            'ledg_refno' => $titipan->ledg_refno,
                                            'ledg_debit' => $titipan->ledg_credit,
                                            'ledg_credit' => 0,
                                            'ledg_description' => $titipan->ledg_description,
                                            'coa_year' => $coayear,
                                            'coa_code' => $titipan->coa_code,
                                            'created_by' => Auth::id(),
                                            'updated_by' => Auth::id(),
                                            'jour_type_id' => $titipan->jour_type_id,
                                            'dept_id' => $titipan->dept_id
                                        ];
                        }
                    }
                }

                $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => date('Y-m-d'),
                                'ledg_refno' => $refno,
                                'ledg_debit' => $totalpayDebet,
                                'ledg_credit' => 0,
                                'ledg_description' => $dtl->tenan_name." - ".$dtl->inv_number,
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
                		'ipayjour_note' => $dtl->tenan_name." - ".$dtl->inv_number,
                		'coa_code' => $coaDebet->coa_code,
                		'ipayjour_debit' => $dtl->invpayd_amount,
                		'ipayjour_credit' => 0,
                		'invpayh_id' => $id
                	];

                // insert ke trbank ?? masi beresiko kalau diposting jd ttp masuk tp dibikin posted
                $storeTrBank[] = [
                        'invoice' => $dtl->TrInvoice,
                        'total' => $dtl->invpayd_amount
                    ];
            }
            // End DEBET

            // Create CREDIT
            // Piutang yang dijadiin debet di Invoice, sekarang jadiin kredit
            try{
                $paymentDtl = TrInvoicePaymdtl::join('tr_invoice','tr_invoice_paymdtl.inv_id','=','tr_invoice.id')
                								->join('ms_invoice_type','tr_invoice.invtp_id','=','ms_invoice_type.id')
                								->where('invpayh_id',$id)->get();
                // echo "paymDtl : $paymentDtl<br>"; die();
                // setiap pembayaran inv, cek where outstanding nya yg 0 aja yg di post
                foreach($paymentDtl as $dtl){
                    // if(empty(@$paymentDtl->invtp_coa_ar)) return response()->json(['error'=>1, 'message'=> 'Invoice Type Name: '.$paymentDtl->invtp_name.' need to be set with COA code']);
                    $paymentPercentage = $dtl->invpayd_amount / $dtl->inv_amount;

                    //ambil salah satu dr ledger cek lawanan nya suda ada belum
                    $checkDebitLedger = TrLedger::where('ledg_refno',$dtl->inv_number)->where('ledg_credit',0)->first();
                    $checkCreditLedger = TrLedger::where('ledg_refno',$dtl->inv_number)->where('coa_code',$checkDebitLedger->coa_code)->where('ledg_debit',0)->first();
                    // blum ada lawanan & outstanding suda 0, insert
                    if(empty($checkCreditLedger)){
                        $debitLedger = TrLedger::where('ledg_refno',$dtl->inv_number)->where('ledg_credit',0)->get();
                        // echo "Masuk :<br>";
                        // echo $debitLedger."<br><br>";

                        // JIKA SUDAH LUNAS
                        if($dtl->inv_outstanding <= 0){

                            // JIKA LUNAS & ADA LEBIH BAYAR
                            if($dtl->invpayd_amount > $dtl->last_outstanding){
                                $lebihBayar = $dtl->invpayd_amount - $dtl->last_outstanding;
                                $nextJournalNumber += 1;
                                $nextJournalNumberConvert = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                                $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumberConvert;

                                $journal[] = [
                                                'ledg_id' => "JRNL".substr($microtime,-10).str_random(5),
                                                'ledge_fisyear' => $coayear,
                                                'ledg_number' => $journalNumber,
                                                'ledg_date' => date('Y-m-d'),
                                                // REF NO HUTANG TITIPAN HRS DIPIKIRIN PAKE REFNO YG MANA
                                                'ledg_refno' => $dtl->inv_number,
                                                'ledg_debit' => 0,
                                                'ledg_credit' => $lebihBayar,
                                                'ledg_description' => "Hutang Titipan ".$dtl->inv_number,
                                                'coa_year' => $coayear,
                                                'coa_code' => $coaHutangTitipan->coa_code,
                                                'created_by' => Auth::id(),
                                                'updated_by' => Auth::id(),
                                                'jour_type_id' => $jourType->id,
                                                'dept_id' => 3
                                            ];
                            }

                            foreach($debitLedger as $dbt){
                                // clone dr debit
                                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                                $journal[] = [
                                                'ledg_id' => "JRNL".substr($microtime,-10).str_random(5),
                                                'ledge_fisyear' => $dbt->ledge_fisyear,
                                                'ledg_number' => $dbt->ledg_number,
                                                'ledg_date' => date('Y-m-d'),
                                                'ledg_refno' => $dbt->ledg_refno,
                                                'ledg_debit' => 0,
                                                'ledg_credit' => $paymentPercentage * $dbt->ledg_debit,
                                                'ledg_description' => $dbt->ledg_description,
                                                'coa_year' => $dbt->coa_year,
                                                'coa_code' => $dbt->coa_code,
                                                'created_by' => Auth::id(),
                                                'updated_by' => Auth::id(),
                                                'jour_type_id' => $dbt->jour_type_id,
                                                'dept_id' => $dbt->dept_id
                                            ];

                                $payJournal[] = [
                                        'ipayjour_date' => date('Y-m-d'),
                                        'ipayjour_voucher' => $journalNumber,
                                        'ipayjour_note' => $dbt->ledg_refno." - ".$dbt->ledg_description,
                                        'coa_code' => $dbt->coa_code,
                                        'ipayjour_debit' => 0,
                                        'ipayjour_credit' => $paymentPercentage * $dbt->ledg_debit,
                                        'invpayh_id' => $id
                                    ];

                            }
                        }else{
                            // JIKA BELUM LUNAS
                            $nextJournalNumber += 1;
                            $nextJournalNumberConvert = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                            $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumberConvert;

                            $journal[] = [
                                            'ledg_id' => "JRNL".substr($microtime,-10).str_random(5),
                                            'ledge_fisyear' => $coayear,
                                            'ledg_number' => $journalNumber,
                                            'ledg_date' => date('Y-m-d'),
                                            // REF NO HUTANG TITIPAN HRS DIPIKIRIN PAKE REFNO YG MANA
                                            'ledg_refno' => $dtl->inv_number,
                                            'ledg_debit' => 0,
                                            'ledg_credit' => $dtl->invpayd_amount,
                                            'ledg_description' => "Hutang Titipan ".$dtl->inv_number,
                                            'coa_year' => $coayear,
                                            'coa_code' => $coaHutangTitipan->coa_code,
                                            'created_by' => Auth::id(),
                                            'updated_by' => Auth::id(),
                                            'jour_type_id' => $jourType->id,
                                            'dept_id' => 3
                                        ];
                        }

                        $successIds[] = $id;
                        $piutangIds[] = $dtl->inv_number;
                        // }
                    }

                }
            }catch(\Exception $e){
                // do nothing
            }

                $nextJournalNumber++;
                // $successPosting++;
        }
        $successPosting = count($piutangIds);
        $unsuccessPosting = count($ids) - $successPosting;
        $message = $successPosting.' Invoice Terposting';
        if($unsuccessPosting  > 0) $message .= ', Invoice Belum Lunas / Terposting masih tersisa '.$unsuccessPosting;
        // var_dump($journal); die();
        // echo $message; die();
        // var_dump($payJournal);

        // INSERT DATABASE
        DB::beginTransaction();
        try{
            // insert journal
            TrLedger::insert($journal);
            // insert invoice payment journal
            TrInvpaymJournal::insert($payJournal);
            // update posting to yes
            if(count($successIds) > 0){
                foreach ($successIds as $id) {
                    TrInvoicePaymhdr::where('id', $id)->update(['invpayh_post'=>1, 'posting_at'=>date('Y-m-d'), 'posting_by'=>Auth::id()]);
                    foreach ($storeTrBank as $val) {
                        $this->saveToTrBank($paymentHd, $val['invoice'], $val['total']);
                    }
                }
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error'=>1, 'message'=> 'Error occured when posting invoice payment']);
        }

        return response()->json(['success'=>1, 'message'=> $message]);
    }

    public function void(Request $request){
        $id = $request->id;

        $paymHeader = TrInvoicePaymhdr::find($id);

        $result = array(
            'status'=>0,
            'message'=> 'Data not found'
        );

        if(!empty($paymHeader)){
            if($paymHeader->invpayh_post){
                $result['message'] = 'You can\'t void posted payment';
                return response()->json($result);
            }
            // void payment
            $paymHeader->status_void = true;
            if($paymHeader->save()){
                foreach ($paymHeader->TrInvoicePaymdtl as $payDtl) {
                    $invoice_id = $payDtl->inv_id;
                    $invoice = TrInvoice::find($invoice_id);
                    if($invoice){
                        if($payDtl->invpayd_amount > $payDtl->last_outstanding){
                            $invoice->inv_outstanding += $payDtl->last_outstanding;
                        }else{
                            $invoice->inv_outstanding += $payDtl->invpayd_amount;
                        }
                        $invoice->save();
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
        }else{
            return response()->json($result);
        }

        return response()->json($result);
    }
}
