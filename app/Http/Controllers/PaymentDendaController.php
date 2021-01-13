<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\TrLedger;
use App\Models\TrInvoice;
use App\Models\TrBankJv;
use App\Models\MsCashBank;
use App\Models\MsPaymentType;
use App\Models\MsTenant;
use App\Models\MsUnit;
use App\Models\MsUnitOwner;
use App\Models\TrInvoicePaymhdr;
use App\Models\TrInvoicePaymdtl;
use App\Models\TrInvpaymJournal;
use App\Models\MsMasterCoa;
use App\Models\MsJournalType;
use App\Models\MsConfig;
use App\Models\TrBank;
use App\Models\EmailQueue;
use App\Models\KwitansiCounter;
use App\Models\Numcounter;
use App\Models\TrDendaPayment;
use App\Models\ReminderH;
use App\Models\MsCompany;
use Auth;
use DB;
use Validator;

class PaymentDendaController extends Controller
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

        return view('paymentdenda', array(
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

            $count = TrDendaPayment::count();
            $fetch = TrDendaPayment::select('tr_denda_payment.*', 'ms_tenant.tenan_name', 'ms_unit.unit_code', 'reminder_header.reminder_no', 'ms_cash_bank.cashbk_name')
                    ->join('ms_tenant', 'ms_tenant.id',"=",'tr_denda_payment.tenan_id')
                    ->join('ms_unit', 'ms_unit.id',"=",'tr_denda_payment.unit_id')
                    ->join('reminder_header', 'reminder_header.id',"=",'tr_denda_payment.reminderh_id')
                    ->join('ms_cash_bank', 'ms_cash_bank.id',"=",'tr_denda_payment.bank_id');

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
                        $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                    }else{
                        $fetch = $fetch->where($filter->field, $op, $filter->value);
                    }

                }
            }

            if(!empty($keyword)) $fetch = $fetch->where(function($query) use($keyword){
                                        $query->where(\DB::raw('lower(trim("denda_keterangan"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("tenan_name"::varchar))'),'like','%'.$keyword.'%');
                                    });
            if(!empty($datefrom)) $fetch = $fetch->where('denda_date','>=',$datefrom);
            if(!empty($dateto)) $fetch = $fetch->where('denda_date','<=',$dateto);
            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            $fetch = $fetch->skip($offset)->take($perPage)->get();

            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['unit_code'] = $value->unit_code;
                $temp['reminder_no'] = $value->reminder_no;
                $temp['cashbk_name'] = $value->cashbk_name;
                $temp['denda_keterangan'] = $value->denda_keterangan;
                $temp['denda_date'] = $value->denda_date;
                $temp['denda_amount'] = "Rp. ".number_format($value->denda_amount);
                $temp['posting_at'] = ($value->posting_at == NULL ? '' : date('Y-m-d',strtotime($value->posting_at)));
                $temp['checkbox'] = '<input type="checkbox" name="check" value="'.$value->id.'">';
                $temp['cashbk_name'] = $value->cashbk_name;
                $invpayh_post = $temp['posting'] = !empty($value->posting) ? 'yes' : 'no';
                $status_void = $temp['status_void'] = ($value->status_void == 1 ? 'yes' : 'no');

                $action_button = '';
                if($value->status_void == 0){
                    if($invpayh_post == 'no'){
                        $action_button .= ' | <a href="paymentdenda/void?id='.$value->id.'" title="Void" class="void-confirm"><i class="fa fa-ban"></i></a>';
                        $action_button .= ' | <a href="paymentdenda/delete?id='.$value->id.'" title="Delete" class="delete-confirm"><i class="fa fa-trash"></i></a>';
                    }else{
                        $action_button .= '<a href="'.url('paymentdenda/print?id='.$value->id).'" class="print-window" data-width="640" data-height="660"><i class="fa fa-file"></i></a>';
                    }
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

        $invoice_data = ReminderH::select('reminder_header.*', 'ms_unit.unit_code')
        ->leftJoin('ms_unit', 'reminder_header.unit_id',"=",'ms_unit.id')
        ->where('reminder_header.unit_id', '=',$unit_id)
        ->where('reminder_header.active_tagih', 1)
        ->where('reminder_header.posting', 1)
        ->orderBy('reminder_header.reminder_date','asc')
        ->get();

        return view('get_reminder', array(
            'invoice_data' => $invoice_data
        ));
    }

    public function insert(Request $request){
        $messages = [
            'unit_id.required' => 'Unit is required',
            'bank_id.required' => 'Bank is required',
            'denda_date.required' => 'Payment Date is required',
        ];

        $validator = Validator::make($request->all(), [
            'unit_id' => 'required:tr_denda_payment',
            'bank_id' => 'required:tr_denda_payment',
            'denda_date' => 'required:tr_denda_payment'
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

        if(!empty($request->session()->get('FORM_SECRET'))) {
            if(strcasecmp($form_secret, $request->session()->get('FORM_SECRET')) === 0) {
                if(!empty($data_payment) && count($data_payment['invpayd_amount']) > 0){
                    //CEK LAST COUNTER
                    $coayear = date('Y',strtotime($request->denda_date));
                    $month = date('m',strtotime($request->denda_date));

                    $lastJournal = Numcounter::where('numtype','DN')->where('tahun',$coayear)->where('bulan',$month)->first();
                    if(count($lastJournal) > 0){
                        $lst = $lastJournal->last_counter;
                        $nextJournalNumber = $lst + 1;
                        $lastJournal->update(['last_counter'=>$nextJournalNumber]);
                    }else{
                        $nextJournalNumber = 1;
                        $lastcounter = new Numcounter;
                        $lastcounter->numtype = 'DN';
                        $lastcounter->tahun = $coayear;
                        $lastcounter->bulan = $month;
                        $lastcounter->last_counter = 1;
                        $lastcounter->save();
                    }
                    $nextJournalNumber = str_pad($nextJournalNumber, 6, 0, STR_PAD_LEFT);
                    $journalNumber = "DN/".$coayear."/".$this->Romawi($month)."/".$nextJournalNumber;
                    $totalinv = 0;

                    foreach ($data_payment['invpayd_amount'] as $key => $value) {
                        $payVal = (int)$data_payment['totalpay'][$key];
                        $totalinv = $totalinv + $payVal;
                        $reminderhid = $key;
                    }

                    $tenandata = MsUnitOwner::where('unit_id',$request->input('unit_id'))->where('deleted_at',NULL)->first();

                    // create paym header
                    $action = new TrDendaPayment;
                    $action->denda_number = $journalNumber;
                    $action->denda_date = $request->input('denda_date');
                    $action->denda_amount = $totalinv;
                    $action->unit_id = $request->input('unit_id');
                    $action->tenan_id = $tenandata->tenan_id;
                    $action->reminderh_id = $reminderhid;
                    $action->bank_id = $request->input('bank_id');
                    $action->status_void = 0;
                    $action->denda_keterangan = $request->input('denda_keterangan');
                    $action->posting = 0;

                    // payment detail
                    if($action->save()){
                        $payment_id = $action->id;
                        $payment_ids[] = $payment_id;
                        ReminderH::where('id',$reminderhid)->update(['active_tagih'=>0]);
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

    public function void(Request $request){
        $id = $request->id;

        TrDendaPayment::where('id',$id)->update(['status_void'=>1]);
        $result = array(
            'status'=>1,
            'message'=> 'Success void payment'
        );
        return response()->json($result);
    }

    public function posting(Request $request)
    {
        $ids = $request->id;
        $postingdate = (!empty($request->posting_date) ? date('Y-m-d',strtotime($request->posting_date)) : date('Y-m-d')); 
        if(!is_array($ids)) $ids = explode(',',$ids);
        
        $coayear = date('Y',strtotime($request->posting_date));
        $month = date('m',strtotime($request->posting_date));
        $journal = [];

        $jourType = MsJournalType::where('jour_type_prefix','AR')->first();
        if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "AR" first before posting an invoice']);

        // cek backdate dr closed_at bulanan/tahunan
        $lastclose = TrLedger::whereNotNull('closed_at')->orderBy('closed_at','desc')->first();
        $limitMinPostingDate = null;
        if($lastclose) $limitMinPostingDate = date('Y-m-t', strtotime($lastclose->closed_at));
        
        \DB::beginTransaction();
        try{
            // looping per payment id
            foreach ($ids as $id) {
                // Hutang (DEBET)
                $lastJournal = Numcounter::where('numtype','JG')->where('tahun',$coayear)->where('bulan',$month)->first();
                if(count($lastJournal) > 0){
                    $lst = $lastJournal->last_counter;
                    $nextJournalNumber = $lst + 1;
                    $lastJournal->update(['last_counter'=>$nextJournalNumber]);
                }else{
                    $nextJournalNumber = 1;
                    $lastcounter = new Numcounter;
                    $lastcounter->numtype = 'JG';
                    $lastcounter->tahun = $coayear;
                    $lastcounter->bulan = $month;
                    $lastcounter->last_counter = 1;
                    $lastcounter->save();
                }

                $nextJournalNumber = str_pad($nextJournalNumber, 6, 0, STR_PAD_LEFT);
                $journalNumber = "JG/".$coayear."/".$this->Romawi($month)."/".$nextJournalNumber;

                $coa_denda = @MsConfig::where('name','coa_denda')->first()->value;

                $appaymentdtl = TrDendaPayment::where('id', $id)->get();
                foreach ($appaymentdtl as $dtl) {
                    $coa_bank = MsCashBank::find($dtl->bank_id);
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    if(!empty($limitMinPostingDate) && $dtl->denda_date < $limitMinPostingDate){
                        \DB::rollback();
                        return response()->json(['error'=>1, 'message'=> "You can't posting if one of these invoice date is before last close date"]);
                    }
                    $journal = [
                            'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                            'ledge_fisyear' => $coayear,
                            'ledg_number' => $journalNumber,
                            'ledg_date' => $postingdate,
                            'ledg_refno' => $dtl->denda_number,
                            'ledg_debit' => $dtl->denda_amount,
                            'ledg_credit' => 0,
                            'ledg_description' => $dtl->denda_number,
                            'coa_year' => $coayear,
                            'coa_code' => $coa_bank->coa_code,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'jour_type_id' => 6,
                            'dept_id' => 3,
                            'modulname' => 'Denda Payment',
                            'refnumber' =>$id
                        ];
                    TrLedger::create($journal);
                    $nextJournalNumber++;
                    $dtl->posting = 1;
                    $dtl->posting_at = $postingdate;
                    $dtl->posting_by = Auth::id();
                    $dtl->save();
                }

                // Pendapatan Lain-lain (KREDIT)
                
                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                $journal = [
                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => $postingdate,
                        'ledg_refno' => $dtl->denda_number,
                        'ledg_debit' => 0,
                        'ledg_credit' => $dtl->denda_amount,
                        'ledg_description' => $dtl->denda_number,
                        'coa_year' => $coayear,
                        'coa_code' => $coa_denda,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => 6,
                        'dept_id' => 3,
                        'modulname' => 'Denda Payment',
                        'refnumber' =>$id
                    ];
                TrLedger::create($journal);
            }
            // end foreach
            \DB::commit();
            return response()->json(['success'=>1, 'message'=>'Denda Payment posted Successfully']);
        }catch(\Exception $e){
            \DB::rollback();
            return response()->json(['error'=>1, 'message'=> $e->getMessage()]);
        }
    }

    public function unposting(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

        $ids = TrDendaPayment::where('posting',1)->whereIn('id', $ids)->pluck('id');
        if(count($ids) < 1) return response()->json(['error'=>1, 'message'=> "0 Denda Payment Ter-unposting"]);
        $sc = 0;
        foreach ($ids as $id) {
            TrLedger::where('refnumber', $id)->where('modulname','Denda Payment')->delete();
            $pay = TrDendaPayment::find($id);
            $pay->update(['posting'=>0,'posting_at'=>NULL]);
            $sc++;
        }
        $message = $sc.' Denda Payment Unposting';
        return response()->json(['success'=>1, 'message'=> $message]);
    }

    public function delete(Request $request){
        $id = $request->id;

        $isi = TrDendaPayment::where('id',$id)->first();
        $result = array(
            'status'=>0,
            'message'=> 'Error'
        );
        if(count($isi) > 0){
            $reminder = $isi->reminderh_id;
            ReminderH::where('id',$reminder)->update(['active_tagih'=>1]);
            $isi->delete();
        }

        $result = array(
            'status'=>1,
            'message'=> 'Success delete payment'
        );
        return response()->json($result);
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

    public function terbilang ($angka) {
        $angka = (float)$angka;
        $bilangan = array('','Satu','Dua','Tiga','Empat','Lima','Enam','Tujuh','Delapan','Sembilan','Sepuluh','Sebelas');
        if ($angka < 12) {
            return $bilangan[$angka];
        } else if ($angka < 20) {
            return $bilangan[$angka - 10] . ' Belas';
        } else if ($angka < 100) {
            $hasil_bagi = (int)($angka / 10);
            $hasil_mod = $angka % 10;
            return trim(sprintf('%s Puluh %s', $bilangan[$hasil_bagi], $bilangan[$hasil_mod]));
        } else if ($angka < 200) { return sprintf('Seratus %s', $this->terbilang($angka - 100));
        } else if ($angka < 1000) { $hasil_bagi = (int)($angka / 100); $hasil_mod = $angka % 100; return trim(sprintf('%s Ratus %s', $bilangan[$hasil_bagi], $this->terbilang($hasil_mod)));
        } else if ($angka < 2000) { return trim(sprintf('Seribu %s', $this->terbilang($angka - 1000)));
        } else if ($angka < 1000000) { $hasil_bagi = (int)($angka / 1000); $hasil_mod = $angka % 1000; return sprintf('%s Ribu %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod));
        } else if ($angka < 1000000000) { $hasil_bagi = (int)($angka / 1000000); $hasil_mod = $angka % 1000000; return trim(sprintf('%s Juta %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
        } else if ($angka < 1000000000000) { $hasil_bagi = (int)($angka / 1000000000); $hasil_mod = fmod($angka, 1000000000); return trim(sprintf('%s Milyar %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
        } else if ($angka < 1000000000000000) { $hasil_bagi = $angka / 1000000000000; $hasil_mod = fmod($angka, 1000000000000); return trim(sprintf('%s Triliun %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
        } else {
            return 'Data Salah';
        }
    }

    public function print(Request $request){
        $company = MsCompany::with('MsCashbank')->first()->toArray();
        $paymentHeader = TrDendaPayment::join('ms_unit','ms_unit.id','=','tr_denda_payment.unit_id')->join('ms_tenant','ms_tenant.id','=','tr_denda_payment.tenan_id')->find($request->id);

        $terbilang = $this->terbilang($paymentHeader->denda_amount);

        $set_data = array(
                'company' => $company,
                'header' => $paymentHeader,
                'terbilang' => $terbilang.' Rupiah',
                'tenan' => $paymentHeader->tenan_name,
                'unit' => $paymentHeader->unit_code,
                'type' => null
            );

        return view('print_denda', $set_data);
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
                        $buktifaktur = TrDendaPayment::find($id);
                        $cc = @MsConfig::where('name','cc_email')->first()->value;
                        if(empty($cc)) $cc = [];

                        $queue = new EmailQueue;
                        $queue->status = 'new';
                        $queue->mailclass = '\App\Mail\KwitansiDendaMail';
                        $queue->ref_id = $buktifaktur->id;
                        $queue->to = $buktifaktur->tenant->tenan_email;
                        if(!empty($cc)) $queue->cc = $cc;
                        $queue->save(); 
                        
                    }
                }
            });
        }catch(\Exception $e){
            return response()->json(['error'=>1, 'message'=> 'Error occured when send email kwitansi']);
        }
        return response()->json(['success'=>1, 'message'=>$successSend.' Kwitansi send Successfully']);
    }

}