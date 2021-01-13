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
use App\Models\AkasaOutstanding;
use App\Models\EmailQueue;
use App\Models\ExcessPayment;
use App\Models\LogExcessPayment;
use App\Models\LogPaymentUsed;
use App\Models\KwitansiCounter;
use App\Models\Numcounter;
use App\Models\TrInvoiceDetail;
use App\Models\MsUnit;
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
            $keyword = @$request->q;
            $invtype = @$request->invtype;
            $datefrom = @$request->datefrom;
            $dateto = @$request->dateto;

            $page = $request->page;
            $perPage = $request->rows;
            $page-=1;
            $offset = $page * $perPage;
            $sort = @$request->sort;
            $order = @$request->order;
            $filters = @$request->filterRules;
            if(!empty($filters)) $filters = json_decode($filters);

            $count = TrInvoicePaymhdr::count();
            $fetch = TrInvoicePaymhdr::select('tr_invoice_paymhdr.*', 'ms_tenant.tenan_name')
                    ->join('ms_tenant', 'ms_tenant.id',"=",'tr_invoice_paymhdr.tenan_id')
                    ->where('status_void', '=', false);

            if(!empty($filters) && count($filters) > 0){
                foreach($filters as $filter){
                    $op = "like";
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
                    if($op == 'like'){
                        if($filter->field == 'inv_no'){
                            $fetch = $fetch->whereHas('TrInvoicePaymdtl.TrInvoice', function($query) use($filter){
                                $query->where('inv_number','ilike', "%$filter->value%");
                            });
                        }else if($filter->field == 'unit_code'){
                            $fetch = $fetch->whereHas('TrInvoicePaymdtl.TrInvoice.unit', function($query) use($filter){
                                $query->where('unit_code','ilike', "%$filter->value%");
                            });
                        }else{
                           $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                        }
                    }else{
                        $fetch = $fetch->where($filter->field, $op, $filter->value);
                    }

                }
            }

            if(!empty($keyword)) $fetch = $fetch->where(function($query) use($keyword){
                                        $query->where(\DB::raw('lower(trim("invpayh_checkno"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("tenan_name"::varchar))'),'like','%'.$keyword.'%');
                                    });
            if(!empty($datefrom)) $fetch = $fetch->where('tr_invoice_paymhdr.invpayh_date','>=',$datefrom);
            if(!empty($dateto)) $fetch = $fetch->where('tr_invoice_paymhdr.invpayh_date','<=',$dateto);
            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);

            $fetch = $fetch->with('TrInvoicePaymdtl.TrInvoice')->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['paymtp_name'] = $value->paymentType->paymtp_name;
                $temp['unit_code'] = "";
                if(count($value->TrInvoicePaymdtl) > 0){
                    $unitcd = array();
                    foreach ($value->TrInvoicePaymdtl as $paydt) {
                        if(!empty(@$paydt->TrInvoice->TrContract->MsUnit)) 
                            array_push($unitcd, $paydt->TrInvoice->TrContract->MsUnit->unit_code);
                            //$temp['unit_code'] .= $paydt->TrInvoice->TrContract->MsUnit->unit_code."<br>";
                    }
                    $unit2 = array_unique($unitcd);
                    if(count($unit2) > 0){
                        $temp['unit_code'] = implode('<br>', $unit2);
                    }else{
                        $temp['unit_code'] = $unit2;
                    }
                }
                $temp['invpayh_date'] = $value->invpayh_date;
                $temp['invpayh_amount'] = "Rp. ".number_format($value->invpayh_amount);
                $temp['lebih_pembayaran'] = "Rp. ".number_format($value->lebih_pembayaran);
                $temp['cashbk_name'] = $value->Cashbank->cashbk_name;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['posting_at'] = ($value->posting_at == NULL ? '' : date('Y-m-d',strtotime($value->posting_at)));
                $temp['invpayh_note'] = $value->invpayh_note;
                $temp['checkbox'] = '<input type="checkbox" name="check" value="'.$value->id.'">';
                $invpayh_post = $temp['invpayh_post'] = !empty($value->invpayh_post) ? 'yes' : 'no';

                $temp['inv_no'] = "";
                $total_inv = 0;
                if(count($value->TrInvoicePaymdtl) > 0){
                    foreach ($value->TrInvoicePaymdtl as $paydt) {
                        if(!empty(@$paydt->TrInvoice->inv_number)){
                            $temp['inv_no'] .= $paydt->TrInvoice->inv_number."<br>";
                            $total_inv = $total_inv + $paydt->TrInvoice->inv_amount;
                        } 
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
                
                if($value->lunas == 1){
                $action_button .= ' | <a href="'.url('invoice/print_kwitansi?id='.$value->id).'" class="print-window" data-width="640" data-height="660"><i class="fa fa-file"></i></a>';
                $action_button .= ' | <a href="'.url('invoice/print_kwitansi?id='.$value->id.'&send=1').'" class="print-window" data-width="640" data-height="300"><i class="fa fa-paper-plane"></i></a>';
                }

                $temp['action_button'] = $action_button;

                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function get_invoice(Request $request){
        $unit_id = @$request->tenan_id;

        $invoice_data = TrInvoice::select('tr_invoice.id', 'tr_invoice.inv_number', 'tr_invoice.inv_date', 'tr_invoice.unit_id', 'tr_invoice.inv_duedate', 'tr_invoice.inv_outstanding', 'ms_unit.unit_name', 'ms_floor.floor_name', 'ms_tenant.tenan_name', 'tr_invoice.tenan_id')
        ->leftJoin('tr_contract', 'tr_contract.id',"=",'tr_invoice.contr_id')
        ->leftJoin('ms_unit', 'tr_contract.unit_id',"=",'ms_unit.id')
        ->leftJoin('ms_tenant','tr_contract.tenan_id',"=",'ms_tenant.id')
        ->leftJoin('ms_floor', 'ms_unit.floor_id',"=",'ms_floor.id')
        ->where('tr_invoice.unit_id', '=',$unit_id)
        ->where('tr_invoice.inv_outstanding', '>', 0)
        ->where('tr_invoice.inv_post', 1)
        ->where('tr_invoice.inv_iscancel', 0)
        ->orderBy('tr_invoice.inv_date','asc')
        ->get();

        return view('get_invoice', array(
            'invoice_data' => $invoice_data
        ));
    }

    public function get_invoice_creditnote(Request $request){
        $unit_id = @$request->tenan_id;

        $invoice_data = TrInvoice::select('tr_invoice.id', 'tr_invoice.inv_number', 'tr_invoice.inv_date', 'tr_invoice.unit_id', 'tr_invoice.inv_duedate', 'tr_invoice.inv_outstanding', 'ms_unit.unit_name', 'ms_floor.floor_name', 'ms_tenant.tenan_name', 'tr_invoice.tenan_id')
        ->leftJoin('tr_contract', 'tr_contract.id',"=",'tr_invoice.contr_id')
        ->leftJoin('ms_unit', 'tr_contract.unit_id',"=",'ms_unit.id')
        ->leftJoin('ms_tenant','tr_contract.tenan_id',"=",'ms_tenant.id')
        ->leftJoin('ms_floor', 'ms_unit.floor_id',"=",'ms_floor.id')
        ->where('tr_invoice.unit_id', '=',$unit_id)
        ->where('tr_invoice.inv_outstanding', '>', 0)
        ->where('tr_invoice.inv_post', 1)
        ->where('tr_invoice.inv_iscancel', 0)
        ->get();

        return view('get_invoice_creditnote', array(
            'invoice_data' => $invoice_data
        ));
    }

    public function getdetail(Request $request){
        $id = $request->id;
        $paymHdr = TrInvoicePaymhdr::find($id);

        return view('modal.payment', ['header' => $paymHdr]);
    }

    public function insert(Request $request){
        $messages = [
            'tenan_id.required' => 'Tenan name is required',
            'cashbk_id.required' => 'Bank is required',
            'paymtp_code.required' => 'Payment Type is required',
            'invpayh_date.required' => 'Payment Date is required',
        ];

        $validator = Validator::make($request->all(), [
            'tenan_id' => 'required:tr_invoice_paymhdr',
            'cashbk_id' => 'required:tr_invoice_paymhdr',
            'paymtp_code' => 'required:tr_invoice_paymhdr',
            'invpayh_date' => 'required:tr_invoice_paymhdr'
        ], $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ['status' => 0, 'message' => $errors];
        }

        $data_payment = $request->input('data_payment');
        $form_secret = !empty($request->input('form_secret')) ? $request->input('form_secret') : '' ;
        $detail_payment = array();
        $cek_pay = false;
        $total = 0;
        $payment_ids = [];
<<<<<<< Updated upstream
<<<<<<< Updated upstream
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

        $unit_data = 0;
        if(!empty($request->session()->get('FORM_SECRET'))) {
            if(strcasecmp($form_secret, $request->session()->get('FORM_SECRET')) === 0) {
                if(!empty($data_payment) && count($data_payment['invpayd_amount']) > 0){
                    //CEK LAST PAYMENT DI TABLE KWITANSI COUNTER
                    $lastPayment = KwitansiCounter::where(\DB::raw('tahun'),'=',date('Y'))
                                        ->where(\DB::raw('bulan'),'=',date('m'))->first();
                    $indexNumber = null;
                    
                    if($lastPayment){
                        $index = $lastPayment->last_counter;
                        $index+= 1;
                        $indexNumber = $index;
                        $index = str_pad($index, 4, "0", STR_PAD_LEFT);
                        $lastPayment->update(['last_counter'=>$index]);
                    }else{
                        $index = "0001";
                        $indexNumber = 1;
                        $lastcounter = new KwitansiCounter;
                        $lastcounter->tahun = date('Y');
                        $lastcounter->bulan = date('m');
                        $lastcounter->last_counter = 1;
                        $lastcounter->save();
                    }
                    $totalinv = 0;
                    $lebih = 0;
                    $inv = array();
                    foreach ($data_payment['invpayd_amount'] as $key => $value) {
                        $tempAmount = $currentOutstanding = 0;
                        $invoice = TrInvoice::find($key);
                        if(!empty($value)){
                            $cek_pay = true;

                            $payVal = (int)$data_payment['totalpay'][$key];
                            $total = $total + $payVal;
                            $totalinv = $totalinv + $invoice->inv_outstanding;
                            $detail_payment[] = array(
                                'invpayd_amount' => $payVal,
                                'inv_id' => $key
                            );
                        }
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
                    $action->tenan_id = $invoice->tenan_id;
                    $action->invpayh_settlamt = 1;
                    $action->invpayh_adjustamt = 1;
                    $action->invpayh_amount = ($total >= $totalinv ? $totalinv : $total);
                    $action->lebih_pembayaran = ($total > $totalinv ? ($total - $totalinv) : 0);
                    $action->lunas = ($total >= $totalinv ? 1 : 0);
                    $action->updated_by = $action->created_by = Auth::id();
                    $action->status_void = false;

                    // payment detail
                    if($action->save()){
                        $payment_id = $action->id;
                        $payment_ids[] = $payment_id;
                        for($i=0; $i<count($detail_payment); $i++){
                            $invoice = TrInvoice::find($detail_payment[$i]['inv_id']);
                            $action_detail = new TrInvoicePaymdtl;
                            $inv_amount = $detail_payment[$i]['invpayd_amount'];
                            $invoice_has_paid = TrInvoicePaymdtl::select('tr_invoice_paymhdr.*', 'tr_invoice_paymdtl.*')
                                        ->join('tr_invoice_paymhdr','tr_invoice_paymdtl.invpayh_id','=','tr_invoice_paymhdr.id')
                                        ->where('status_void', '=', false)
                                        ->where('inv_id', '=', $detail_payment[$i]['inv_id'])
                                        ->get()->first();
                            if(!empty($invoice_has_paid)){
                                $invoice_has_paid = $invoice_has_paid->sum('invpayd_amount');
                            }else{
                                $invoice_has_paid = 0;
                            }
                            $total_has_paid = $invoice_has_paid + $detail_payment[$i]['invpayd_amount'];
                            $outstand = $inv_amount - $total_has_paid;

                            if($outstand <= 0){
                                $outstand = 0;
                            }
                            $action_detail->invpayd_amount = $detail_payment[$i]['invpayd_amount'];
                            $action_detail->inv_id = $detail_payment[$i]['inv_id'];
                            $action_detail->invpayh_id = $payment_id;
                            $action_detail->last_outstanding = $detail_payment[$i]['invpayd_amount'];
                            $action_detail->save();

                            if(isset($invoice->inv_outstanding)){
                                $currentOutstanding = $invoice->inv_outstanding;
                                $tempAmount = $invoice->inv_outstanding - (int)$detail_payment[$i]['invpayd_amount'];
                                // update
                                if((int)$tempAmount < 0){
                                    $lebih = $lebih + ($tempAmount * -1);  
                                    $tempAmount = 0;
                                }
                                $invoice->inv_outstanding = (int)$tempAmount;
                                $invoice->save();
                            }
                            $unit_data = $invoice->unit_id;
                        }

                        //CHECK APAKAH SUDAH ADA DI TABEL LEBIH BAYAR
                        $check_lebih = ExcessPayment::where('unit_id',$unit_data)->first();
                        if(count($check_lebih) > 0){
                            if($lebih > 0){
                                $current_lebih = $check_lebih->total_amount + $lebih;
                                $check_lebih->total_amount = $current_lebih;
                                $check_lebih->save();

                                //INSERT TO LOG LEBIH BAYAR
                                $excess_log = new LogExcessPayment;
                                $excess_log->invpayh_id = $payment_id;
                                $excess_log->unit_id = $unit_data;
                                $excess_log->excess_amount = $lebih;
                                $excess_log->save();
                            }
                        }else{
                            $excess_detail = new ExcessPayment;
                            $excess_detail->unit_id = $unit_data;
                            if($lebih == 0){   
                                $excess_detail->total_amount = 0;
                            }else{
                                $excess_detail->total_amount = $lebih;
                                //INSERT TO LOG LEBIH BAYAR
                                $excess_log = new LogExcessPayment;
                                $excess_log->invpayh_id = $payment_id;
                                $excess_log->unit_id = $unit_data;
                                $excess_log->excess_amount = $lebih;
                                $excess_log->save();
                            }
                            $excess_detail->save();
                        }
                    }
                    $request->session()->forget('FORM_SECRET');
                }else{
                    return ['status' => 0, 'message' => 'Please Check at least one of Invoice for payment'];
                }
                return ['status' => 1, 'message' => 'Insert Success', 'paym_id' => $payment_ids];
            }
        }else{
            return ['status' => 0, 'message' => 'Payment already process'];
        }
    }

    private function saveToTrBank($action, $invoice, $total)
    {
        try{
            if($action->paymtp_code == 2){
                $header = new TrBank;
                $header->trbank_no = "BM".str_random(5).$action->no_kwitansi;
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
        }catch(\Exception $e){
            // do nothing
        }
    }

    public function get_token(Request $request){
        $type = @$request->type;
        switch ($type) {
            case '10':
                # token posting payment invoice
                $secret=md5(uniqid(rand(), true));
                \Session::set('FORM_SECRET_'.$type, $secret); 
                break;
            
            default:
                # code...
                $secret = $type;
                break;
        }
        return $secret;
    }

    public function posting(Request $request){
        $ids = $request->id;
        $postingdate = (!empty($request->posting_date) ? date('Y-m-d',strtotime($request->posting_date)) : date('Y-m-d'));
        $form_secret = !empty($request->input('token')) ? $request->input('token') : '' ; 
        $send_flag = $request->send_flag;
        if(!is_array($ids)) $ids = explode(',',$ids);
        $ids = TrInvoicePaymhdr::where('invpayh_post',0)->whereIn('id', $ids)->pluck('id');
        //if(count($ids) < 1) return response()->json(['error'=>1, 'message'=> "0 Invoice Terposting"]);

        $coayear = date('Y',strtotime($request->posting_date));
        $month = date('m',strtotime($request->posting_date));
        $journal = [];
        $payJournal = [];
        $storeTrBank = [];

        $successPosting = 0;
        $successIds = [];
        $piutangIds = [];

        // cek backdate dr closing bulanan/tahunan
        $lastclose = TrLedger::whereNotNull('closed_at')->orderBy('closed_at','desc')->first();
        $limitMinPostingDate = null;
        if($lastclose) $limitMinPostingDate = date('Y-m-t', strtotime($lastclose->closed_at));

        if(!empty($request->session()->get('FORM_SECRET_10'))) {
            if(strcasecmp($form_secret, $request->session()->get('FORM_SECRET_10')) === 0) {
                foreach ($ids as $id) {

                    // cari last prefix, order by journal type
                    $jourType = MsJournalType::where('jour_type_prefix','BRV')->first();
                    if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "AR" first before posting an invoice']);
                    $lastJournal = Numcounter::where('numtype','BRV')->where('tahun',$coayear)->where('bulan',$month)->first();
                    if(count($lastJournal) > 0){
                        $lst = $lastJournal->last_counter;
                        $nextJournalNumber = $lst + 1;
                        $lastJournal->update(['last_counter'=>$nextJournalNumber]);
                    }else{
                        $nextJournalNumber = 1;
                        $lastcounter = new Numcounter;
                        $lastcounter->numtype = 'BRV';
                        $lastcounter->tahun = $coayear;
                        $lastcounter->bulan = $month;
                        $lastcounter->last_counter = 1;
                        $lastcounter->save();
                    }

                    $nextJournalNumber = str_pad($nextJournalNumber, 6, 0, STR_PAD_LEFT);
                    $journalNumber = "BRV/".$coayear."/".$this->Romawi($month)."/".$nextJournalNumber;

                    // get payment header
                    $paymentHd = TrInvoicePaymhdr::with('Cashbank')->find($id);
                    // validasi backdate posting
                    if(!empty($limitMinPostingDate) && $paymentHd->invpayh_date < $limitMinPostingDate){
                        return response()->json(['error'=>1, 'message'=> "You can't posting if one of these payment date is before last close date"]);
                    }

                    if(!isset($paymentHd->Cashbank->coa_code)) return response()->json(['error'=>1, 'message'=> 'Cashbank Name: '.$paymentHd->Cashbank->cashbk_name.' need to be set with COA code']);
                    // create journal DEBET utk piutang
                    $coaDebet = MsMasterCoa::where('coa_code',$paymentHd->Cashbank->coa_code)->orderBy('id','desc')->first();
                    if(empty($coaDebet)) return response()->json(['error'=>1, 'message'=>'COA Code: '.$paymentHd->Cashbank->coa_code.' is not found on this year list. Please ReInsert this COA Code']);

                    // COA titipan
                    $coaHutangTitipanVal = @MsConfig::where('name','coa_hutang_titipan')->first()->value;
                    $coaHutangTitipan = MsMasterCoa::where('coa_code', $coaHutangTitipanVal)->first();
                    if(empty($coaHutangTitipan)) return response()->json(['error'=>1, 'message'=>'COA hutang titipan is not found. Please set this COA Code in Config']);

                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    // Cashbank Jadi DEBET di Payment
                    $paymentDtl = TrInvoicePaymdtl::join('tr_invoice','tr_invoice_paymdtl.inv_id','=','tr_invoice.id')
                                                    ->join('ms_tenant','ms_tenant.id','=','tr_invoice.tenan_id')
                                                    ->where('invpayh_id',$id)->get();
                    $refno = !empty($paymentHd->invpayh_checkno) ? $paymentHd->invpayh_checkno : $paymentHd->no_kwitansi;

                    foreach ($paymentDtl as $dtl) {
                        $totalpayDebet = $dtl->invpayd_amount;
                        if($dtl->inv_outstanding <= 0){
                            $totalpayDebet = $dtl->invpayd_amount;
                            // KALAU LUNAS BALIKIN SEMUA HUTANG TITIPAN
                            $checkTitipan = TrLedger::where('ledg_description',"Hutang Titipan ".$dtl->inv_number)->get();
                            if(count($checkTitipan) > 0){
                                foreach ($checkTitipan as $titipan) {
                                    $journal[] = [
                                                    'ledg_id' => "JRNL".substr($microtime,-10).str_random(5),
                                                    'ledge_fisyear' => $coayear,
                                                    'ledg_number' => $journalNumber,
                                                    'ledg_date' => $postingdate,
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
                                                    'dept_id' => $titipan->dept_id,
                                                    'modulname' => 'AR Payment',
                                                    'refnumber' =>$id
                                                ];
                                }
                            }
                        }
                        $journal[] = [
                                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                        'ledge_fisyear' => $coayear,
                                        'ledg_number' => $journalNumber,
                                        'ledg_date' => $postingdate,
                                        'ledg_refno' => $refno,
                                        'ledg_debit' => $totalpayDebet,
                                        'ledg_credit' => 0,
                                        'ledg_description' => $dtl->tenan_name." - ".$dtl->inv_number,
                                        'coa_year' => $coaDebet->coa_year,
                                        'coa_code' => $coaDebet->coa_code,
                                        'created_by' => Auth::id(),
                                        'updated_by' => Auth::id(),
                                        'jour_type_id' => $jourType->id,
                                        'dept_id' => 3, //hardcode utk finance
                                        'modulname' => 'AR Payment',
                                        'refnumber' =>$id
                                    ];

                        $payJournal[] = [
                                'ipayjour_date' => $postingdate,
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
                        // setiap pembayaran inv, cek where outstanding nya yg 0 aja yg di post
                        foreach($paymentDtl as $dtl){
                            $paymentPercentage = $dtl->invpayd_amount / (($dtl->inv_amount - $dtl->total_excess_payment) == 0 ? 1 : ($dtl->inv_amount - $dtl->total_excess_payment));
                            if($paymentPercentage > 1){
                                $paymentPercentage = 1;
                            }
                            /*
                            //ambil salah satu dr ledger cek lawanan nya suda ada belum
                            $checkDebitLedger = TrLedger::where('ledg_refno',$dtl->inv_number)->where('ledg_credit',0)->first();
                            $checkCreditLedger = TrLedger::where('ledg_refno',$dtl->inv_number)->where('coa_code',$checkDebitLedger->coa_code)->where('ledg_debit',0)->first();
                            */
                            /*RACHMAT */
                            $debitLedger = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.invdt_amount','tr_invoice_detail.coa_code','ms_cost_item.ar_coa_code','ms_cost_detail.costd_name','tr_invoice.total_excess_payment')
                                        ->leftJoin('tr_invoice','tr_invoice.id','=','tr_invoice_detail.inv_id')
                                        ->leftjoin('ms_cost_detail','ms_cost_detail.id','=','tr_invoice_detail.costd_id')
                                        ->leftjoin('ms_cost_item','ms_cost_item.id','=','ms_cost_detail.cost_id')
                                        ->where('tr_invoice_detail.inv_id',$dtl->inv_id)->get();

                            // JIKA SUDAH LUNAS
                            if($dtl->inv_outstanding <= 0){
                                // JIKA LUNAS & ADA LEBIH BAYAR
                                if($dtl->invpayd_amount > $dtl->inv_amount){
                                    $lebihBayar = $dtl->invpayd_amount - ($dtl->inv_amount - $dtl->total_excess_payment);
                                    if($lebihBayar > 0){
                                        $journal[] = [
                                                        'ledg_id' => "JRNL".substr($microtime,-10).str_random(5),
                                                        'ledge_fisyear' => $coayear,
                                                        'ledg_number' => $journalNumber,
                                                        'ledg_date' => $postingdate,
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
                                                        'dept_id' => 3,
                                                        'modulname' => 'AR Payment',
                                                        'refnumber' =>$id
                                                    ];
                                    }
                                }
                                foreach($debitLedger as $dbt){
                                    // clone dr debit
                                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                                    $nilai_credit = ($paymentPercentage * $dbt->invdt_amount) - $dbt->total_excess_payment;
                                    if($dtl->inv_amount == $dbt->total_excess_payment){
                                        $nilai_credit = $dbt->total_excess_payment;
                                    }
                                    $journal[] = [
                                                    'ledg_id' => "JRNL".substr($microtime,-10).str_random(5),
                                                    'ledge_fisyear' => $coayear,
                                                    'ledg_number' => $journalNumber,
                                                    'ledg_date' => $postingdate,
                                                    'ledg_refno' => $dtl->inv_number,
                                                    'ledg_debit' => 0,
                                                    'ledg_credit' => $nilai_credit,
                                                    'ledg_description' => $dbt->costd_name.' - '.$dtl->inv_number,
                                                    'coa_year' => $coayear,
                                                    'coa_code' => ($dbt->ar_coa_code == NULL ? $dbt->coa_code : $dbt->ar_coa_code),
                                                    'created_by' => Auth::id(),
                                                    'updated_by' => Auth::id(),
                                                    'jour_type_id' => 11,
                                                    'dept_id' => 3,
                                                    'modulname' => 'AR Payment',
                                                    'refnumber' =>$id
                                                ];
                                    $payJournal[] = [
                                                'ipayjour_date' => $postingdate,
                                                'ipayjour_voucher' => $journalNumber,
                                                'ipayjour_note' => $dbt->costd_name.' - '.$dtl->inv_number,
                                                'coa_code' => ($dbt->ar_coa_code == NULL ? $dbt->coa_code : $dbt->ar_coa_code),
                                                'ipayjour_debit' => 0,
                                                'ipayjour_credit' => $paymentPercentage * $dbt->invdt_amount,
                                                'invpayh_id' => $id
                                            ];

                                }
                            }else{
                                // JIKA BELUM LUNAS
                                foreach($debitLedger as $dbt){
                                    // clone dr debit
                                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                                    $journal[] = [
                                                    'ledg_id' => "JRNL".substr($microtime,-10).str_random(5),
                                                    'ledge_fisyear' => $coayear,
                                                    'ledg_number' => $journalNumber,
                                                    'ledg_date' => $postingdate,
                                                    'ledg_refno' => $dtl->inv_number,
                                                    'ledg_debit' => 0,
                                                    'ledg_credit' => ($paymentPercentage * $dbt->invdt_amount) - $dbt->total_excess_payment,
                                                    'ledg_description' => $dbt->costd_name.' - '.$dtl->inv_number,
                                                    'coa_year' => $coayear,
                                                    'coa_code' => ($dbt->ar_coa_code == NULL ? $dbt->coa_code : $dbt->ar_coa_code),
                                                    'created_by' => Auth::id(),
                                                    'updated_by' => Auth::id(),
                                                    'jour_type_id' => 11,
                                                    'dept_id' => 3,
                                                    'modulname' => 'AR Payment',
                                                    'refnumber' =>$id
                                                ];
                                    $payJournal[] = [
                                                'ipayjour_date' => $postingdate,
                                                'ipayjour_voucher' => $journalNumber,
                                                'ipayjour_note' => $dbt->costd_name.' - '.$dtl->inv_number,
                                                'coa_code' => ($dbt->ar_coa_code == NULL ? $dbt->coa_code : $dbt->ar_coa_code),
                                                'ipayjour_debit' => 0,
                                                'ipayjour_credit' => $paymentPercentage * $dbt->invdt_amount,
                                                'invpayh_id' => $id
                                            ];

                                }
                            }
                            $successIds[] = $id;
                            $piutangIds[] = $dtl->inv_number;
                        }
                        
                    }catch(\Exception $e){

                    }
                    $nextJournalNumber++;
                }
                $successPosting = count($piutangIds);
                $unsuccessPosting = count($ids) - $successPosting;
                $message = $successPosting.' Invoice Terposting';
                if($unsuccessPosting  > 0) $message .= ', Invoice Belum Lunas / Terposting masih tersisa '.$unsuccessPosting;
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
                            TrInvoicePaymhdr::where('id', $id)->update(['invpayh_post'=>1, 'posting_at'=>$postingdate, 'posting_by'=>Auth::id()]);
                            if($send_flag == 2){
                                //AUTO KIRIM KWITANSI SAAT POSTING
                                $buktifaktur = TrInvoicePaymhdr::find($id);
                                $cc = @MsConfig::where('name','cc_email')->first()->value;
                                if(empty($cc)) $cc = [];

                                $queue = new EmailQueue;
                                $queue->status = 'new';
                                $queue->mailclass = '\App\Mail\KwitansiMail';
                                $queue->ref_id = $buktifaktur->id;
                                $queue->to = $buktifaktur->tenant->tenan_email;
                                if(!empty($cc)) $queue->cc = $cc;
                                $queue->save(); 

                                if($buktifaktur->tenant->cc_email != NULL || $buktifaktur->tenant->cc_email != ''){
                                    $cc_tenant = explode('|', $buktifaktur->tenant->cc_email);
                                    if(count($cc_tenant) > 0){
                                        for($i=0; $i<count($cc_tenant); $i++){
                                            $tnt2 = explode('~', $cc_tenant[$i]);
                                            if(count($tnt2) > 0){
                                                $unit_dt = $tnt2[0];
                                                $units = MsUnit::where('unit_code', $unit_dt)->first();
                                                $kirim = explode(';', $tnt2[1]);
                                                if(count($units) > 0){
                                                    //if($units->id == $invoice->unit_id){ 
                                                        if(count($kirim) > 0){
                                                            for($j=0; $j<count($kirim); $j++){
                                                                $queue = new EmailQueue;
                                                                $queue->status = 'new';
                                                                $queue->mailclass = '\App\Mail\KwitansiMail';
                                                                $queue->ref_id = $buktifaktur->id;
                                                                $queue->to = $kirim[$j];
                                                                if(!empty($cc)) $queue->cc = $cc;
                                                                $queue->save();
                                                            }
                                                        }
                                                    //}
                                                } 
                                            }
                                        }
                                    }
                                }
                            }
                            foreach ($storeTrBank as $val) {
                                //$this->saveToTrBank($paymentHd, $val['invoice'], $val['total']);
                            }
                        }
                    }
                    
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                    //return response()->json(['error'=>1, 'message'=> $e->getMessage()]);
                    return response()->json(['success'=>1, 'message'=> 'Success Posting !!!']);
                }

                $request->session()->forget('FORM_SECRET_10');
                return response()->json(['success'=>1, 'message'=> $message]);
            }
        }else{
            return response()->json(['success'=>1, 'message'=> 'Invoice Terposting!!']);
        }
    }

    public function unposting(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

        $ids = TrInvoicePaymhdr::where('invpayh_post',1)->whereIn('id', $ids)->pluck('id');
        if(count($ids) < 1) return response()->json(['error'=>1, 'message'=> "0 Invoice Ter-unposting"]);
        $sc = 0;
        foreach ($ids as $id) {
            /*
            $no_kw = TrInvoicePaymhdr::where('id',$id)->get();
            if(count($no_kw) > 0){
                $paymentDtl = TrInvoicePaymdtl::join('tr_invoice','tr_invoice_paymdtl.inv_id','=','tr_invoice.id')
                                                ->join('tr_invoice_paymhdr','tr_invoice_paymdtl.invpayh_id','=','tr_invoice_paymhdr.id')
                                                ->join('ms_tenant','ms_tenant.id','=','tr_invoice.tenan_id')
                                                ->where('invpayh_id',$id)->get();
                if(count($paymentDtl) > 0){
                    foreach ($paymentDtl as $dtl) {
                        $ledg_detail = TrLedger::where('ledg_refno', $dtl->inv_number)->where('ledg_debit','>',0)->get();
                        if(count($ledg_detail) > 0){
                            foreach($ledg_detail  as $ledg){
                                TrLedger::where('ledg_refno', $dtl->inv_number)->where('ledg_debit',0)->where('ledg_description',$ledg->ledg_description)->delete();
                                //DELETE HUTANG TITIPAN
                                $desc = 'Hutang Titipan '.$dtl->inv_number;
                                TrLedger::where('ledg_refno', $dtl->inv_number)->where('ledg_debit',0)->where('ledg_description',$desc)->delete();
                            }
                        }
                    }
                }
                $bank = TrBank::where(\DB::raw('SUBSTRING("trbank_no" FROM 8)'),$no_kw[0]->no_kwitansi)->get();
                if(count($bank) > 0){
                    foreach($bank as $bks) {
                        TrBankJv::where('trbank_id',$bks->id)->delete();
                        TrBank::where('id',$bks->id)->delete();
                    }
                }
            */
                TrLedger::where('refnumber', $id)->where('modulname','AR Payment')->delete();
                $pay = TrInvoicePaymhdr::find($id);
                $pay->update(['invpayh_post'=>0,'posting_by'=>NULL,'posting_at'=>NULL]);
                TrInvpaymJournal::where('invpayh_id',$id)->delete();
                $sc++;
            //}
        }
        $message = $sc.' Invoice Unposting';
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

            //DELETE VOID AJA
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
            TrInvoicePaymdtl::where('invpayh_id',$id)->delete();
            TrInvoicePaymhdr::where('id',$id)->delete();
            $bayar_lebih = LogExcessPayment::where('invpayh_id',$id)->get();
            if(count($bayar_lebih) > 0){
                foreach ($bayar_lebih as $dtl) {
                    $nilai = $dtl->excess_amount;
                    $unt = $dtl->unit_id;
                    $unit_lebih = ExcessPayment::where('unit_id',$unt)->get()->first();
                    $current_data = $unit_lebih->total_amount;
                    $unit_lebih->total_amount = $current_data - $nilai;
                    $unit_lebih->save();
                }
                LogExcessPayment::where('invpayh_id',$id)->delete();
            }

            $result = array(
                'status'=>1,
                'message'=> 'Success void payment'
            );

        }else{
            return response()->json($result);
        }

        return response()->json($result);
    }

    public function sendkwitansi(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];
        $successSend = 0;
        foreach ($ids as $id) {
            $successIds[] = $id;
            $successSend++;
        }

        try{
            DB::transaction(function () use($successIds){
                if(count($successIds) > 0){
                    
                    foreach ($successIds as $id) {
                        $buktifaktur = TrInvoicePaymhdr::find($id);
                        $cc = @MsConfig::where('name','cc_email')->first()->value;
                        if(empty($cc)) $cc = [];

                        $queue = new EmailQueue;
                        $queue->status = 'new';
                        $queue->mailclass = '\App\Mail\KwitansiMail';
                        $queue->ref_id = $buktifaktur->id;
                        $queue->to = $buktifaktur->tenant->tenan_email;
                        if(!empty($cc)) $queue->cc = $cc;
                        $queue->save(); 

                        if($buktifaktur->tenant->cc_email != NULL || $buktifaktur->tenant->cc_email != ''){
                            $cc_tenant = explode('|', $buktifaktur->tenant->cc_email);
                            if(count($cc_tenant) > 0){
                                for($i=0; $i<count($cc_tenant); $i++){
                                    $tnt2 = explode('~', $cc_tenant[$i]);
                                    if(count($tnt2) > 0){
                                        $unit_dt = $tnt2[0];
                                        $units = MsUnit::where('unit_code', $unit_dt)->first();
                                        $kirim = explode(';', $tnt2[1]);
                                        if(count($units) > 0){
                                            //if($units->id == $invoice->unit_id){ 
                                                if(count($kirim) > 0){
                                                    for($j=0; $j<count($kirim); $j++){
                                                        $queue = new EmailQueue;
                                                        $queue->status = 'new';
                                                        $queue->mailclass = '\App\Mail\KwitansiMail';
                                                        $queue->ref_id = $buktifaktur->id;
                                                        $queue->to = $kirim[$j];
                                                        if(!empty($cc)) $queue->cc = $cc;
                                                        $queue->save();
                                                    }
                                                }
                                            //}
                                        } 
                                    }
                                }
                            }
                        }
                        
                    }
                }
            });
        }catch(\Exception $e){
            return response()->json(['error'=>1, 'message'=> 'Error occured when send email kwitansi']);
        }
        return response()->json(['success'=>1, 'message'=>$successSend.' Kwitansi send Successfully']);
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
