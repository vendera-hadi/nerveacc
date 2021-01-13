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
use App\Models\MsMasterCoa;
use App\Models\MsCompany;
use App\Models\MsConfig;
use App\Models\MsPPN;
use App\Models\Numcounter;
use Auth;
use DB;
use Validator;
use Carbon\Carbon;
use PDF;

class TreasuryController extends Controller
{
	public function index(){
        $data['suppliers'] = MsSupplier::all();
        $data['cashbank_data'] = MsCashBank::all();
        $data['payment_type_data'] = MsPaymentType::all();
        $data['ppn_data'] = MsPPN::all();
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
                }else{
                    $action_button .= ' | <a href="'.url('treasury/print_bpv?id='.$value->id).'" class="print-window" data-width="640" data-height="660">BPV</a> | <a href="'.url('treasury/print_bpv?id='.$value->id.'&type=pdf').'">PDF</a>';
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
                            ->leftJoin('tr_purchase_order_hdr', 'tr_ap_invoice_hdr.po_id',"=",'tr_purchase_order_hdr.id')
                            ->where('tr_ap_invoice_hdr.posting', 1)
                            ->where('outstanding', '>', 0)
                            ->where('tr_ap_invoice_hdr.spl_id', '=',$supplier_id)
                            ->orderBy('tr_ap_invoice_hdr.invoice_date','asc')
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

            $type_spl2 = MsSupplier::find($request->spl_id);
            $type_spl = $type_spl2->spl_pkp;

            $details = [];
            $total = 0;
            $total_dpp =0;
            $payamounts = $request->pay;
            foreach ($payamounts as $inv_id => $amount) {
                $detail = new TrApPaymentDetail;
                $detail->amount = ROUND($amount,0);
                $detail->aphdr_id = $inv_id;
                $details[] = $detail;
                $total += $amount;
                if($type_spl == 2){
                    $total_dpp += ROUND($amount/1.1,0);
                }else{
                    $total_dpp += ROUND($amount,0);
                }

                // kurangin outstanding ap inv header
                $invheader = TrApHeader::find($inv_id);
                $invheader->outstanding -= $amount;
                $invheader->save();
            }
            
            if(!empty($request->pajak_id)){
                $header->pajak_id = $request->pajak_id;
                $pembagi = MsPPN::where('id',$request->pajak_id)->first();
                $amt = ROUND($pembagi->amount * $total_dpp,0);
                $header->pajak_amount = $amt;
                $total = $total - $amt;
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
        $postingdate = (!empty($request->posting_date) ? date('Y-m-d',strtotime($request->posting_date)) : date('Y-m-d')); 
        if(!is_array($ids)) $ids = explode(',',$ids);
        
        $coayear = date('Y',strtotime($request->posting_date));
        $month = date('m',strtotime($request->posting_date));
        $journal = [];

        // cari last prefix, order by journal type
        // using JU utk default
        $jourType = MsJournalType::where('jour_type_prefix','AP')->first();
        if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "AP" first before posting an invoice']);

        // cek backdate dr closed_at bulanan/tahunan
        $lastclose = TrLedger::whereNotNull('closed_at')->orderBy('closed_at','desc')->first();
        $limitMinPostingDate = null;
        if($lastclose) $limitMinPostingDate = date('Y-m-t', strtotime($lastclose->closed_at));
        
        \DB::beginTransaction();
        try{
            // looping per payment id
            foreach ($ids as $id) {
                // Hutang (DEBET)
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
                $appaymentdtl = TrApPaymentDetail::where('appaym_id', $id)->get();
                foreach ($appaymentdtl as $dtl) {
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    $header = TrApHeader::find($dtl->aphdr_id);
                    if(!empty($limitMinPostingDate) && $header->invoice_date < $limitMinPostingDate){
                        \DB::rollback();
                        return response()->json(['error'=>1, 'message'=> "You can't posting if one of these invoice date is before last close date"]);
                    }
                    $journal = [
                            'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                            'ledge_fisyear' => $coayear,
                            'ledg_number' => $journalNumber,
                            'ledg_date' => $postingdate,
                            'ledg_refno' => $header->invoice_no,
                            'ledg_debit' => $dtl->amount,
                            'ledg_credit' => 0,
                            'ledg_description' => $dtl->header->note,
                            'coa_year' => $coayear,
                            'coa_code' => $header->supplier->coa_code,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'jour_type_id' => $jourType->id,
                            'dept_id' => 3,
                            'modulname' => 'AP Payment',
                            'refnumber' =>$id
                        ];
                    TrLedger::create($journal);
                    $nextJournalNumber++;
                    $dtl->header->posting = 1;
                    $dtl->header->posting_at = date('Y-m-d H:i:s');
                    $dtl->header->posting_by = Auth::id();
                    $dtl->header->save();
                }

                // Bank / Kas (KREDIT)
                 //KLO ADA PAJAK
                $pjk = TrApPaymentHeader::where('id',$id)->first();
                if(($pjk->pajak_id) != NULL){
                    $coa = MsPPN::where('id',$pjk->pajak_id)->first();
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    $journal = [
                            'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                            'ledge_fisyear' => $coayear,
                            'ledg_number' => $journalNumber,
                            'ledg_date' => $postingdate,
                            'ledg_refno' => $dtl->header->payment_code,
                            'ledg_debit' => 0,
                            'ledg_credit' => $pjk->pajak_amount,
                            'ledg_description' => $dtl->header->note,
                            'coa_year' => $coayear,
                            'coa_code' => $coa->coa_code,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'jour_type_id' => $jourType->id,
                            'dept_id' => 3,
                            'modulname' => 'AP Payment',
                            'refnumber' =>$id
                        ];
                    TrLedger::create($journal);
                }
                
                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                $journal = [
                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => $postingdate,
                        'ledg_refno' => $dtl->header->payment_code,
                        'ledg_debit' => 0,
                        'ledg_credit' => $dtl->header->amount,
                        'ledg_description' => $dtl->header->note,
                        'coa_year' => $coayear,
                        'coa_code' => $dtl->header->cashbank->coa_code,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id,
                        'dept_id' => 3,
                        'modulname' => 'AP Payment',
                        'refnumber' =>$id
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

    public function print_bpv(Request $request){
        try{

            $ap_id = $request->id;
            if(!is_array($ap_id)) $ap_id = [$ap_id];
            $type = $request->type;

            $ap_data = TrApPaymentHeader::select('tr_ap_payment_hdr.*', 'ms_supplier.spl_name', 'ms_payment_type.paymtp_name')
                                    ->leftJoin('ms_supplier','ms_supplier.id','=','tr_ap_payment_hdr.spl_id')
                                    ->leftJoin('ms_payment_type','ms_payment_type.id','=','tr_ap_payment_hdr.paymtp_id')
                                    ->whereIn('tr_ap_payment_hdr.id',$ap_id)->get()->toArray();
            $listcoa = MsMasterCoa::get();
            $lsc = array();
            foreach($listcoa as $row){
                $lsc[trim($row->coa_code)] = $row->coa_name;
            }
            foreach ($ap_data as $key => $payable) {
                $result = TrLedger::where('tr_ledger.refnumber',$payable['id'])->where('modulname','AP Payment')->orderBy('id','asc')->get()->toArray();
                $ap_data[$key]['details'] = $result;
                $terbilang = $this->terbilang($payable['amount']);
                $ap_data[$key]['terbilang'] = $terbilang.' Rupiah';
                
            }
        
            $company = MsCompany::with('MsCashbank')->first()->toArray();
            $signature = @MsConfig::where('name','digital_signature')->first()->value;
            $signatureFlag = @MsConfig::where('name','invoice_signature_flag')->first()->value;

            $set_data = array(
                'ap_data' => $ap_data,
                'result' => $result,
                'company' => $company,
                'lsc' => $lsc,
                'type' => $type,
                'signature' => $signature,
                'signatureFlag' => $signatureFlag
            );

            if($type == 'pdf'){
                $pdf = PDF::loadView('print_bpv', $set_data)->setPaper('a4');

                return $pdf->download('BPV.pdf');
            }else{
                return view('print_bpv', $set_data);
            }
         }catch(\Exception $e){
             return $e->getMessage();
         }
    }

    public function unposting(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

        $ids = TrApPaymentHeader::where('posting',1)->whereIn('id', $ids)->pluck('id');
        if(count($ids) < 1) return response()->json(['error'=>1, 'message'=> "0 AP Payment Ter-unposting"]);
        $sc = 0;
        foreach ($ids as $id) {
            TrLedger::where('refnumber', $id)->where('modulname','AP Payment')->delete();
            $pay = TrApPaymentHeader::find($id);
            $pay->update(['posting'=>0,'posting_at'=>NULL]);
            $sc++;
        }
        $message = $sc.' AP Payment Unposting';
        return response()->json(['success'=>1, 'message'=> $message]);
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