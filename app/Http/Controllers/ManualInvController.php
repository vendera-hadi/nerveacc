<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\TrLedger;
use App\Models\MsTenant;
use App\Models\MsConfig;
use App\Models\Numcounter;
use App\Models\MsUnit;
use App\Models\TrContract;
use App\Models\MsJournalType;
use App\Models\MsCompany;
use App\Models\MsMasterCoa;
use App\Models\ManualHdr;
use App\Models\ManualDtl;
use App\Models\MsCashBank;
use App\Models\ManualType;

use Auth;
use DB;
use Validator;

class ManualInvController extends Controller
{
    public function index(){
        $cashbank_data = MsCashBank::all()->toArray();
        $type = ManualType::all()->toArray();
        return view('manualinv', array(
            'cashbank_data' => $cashbank_data,
            'type_data' => $type
        ));
    }

    public function manualinv_get(Request $request){
        try{
            $keyword = @$request->q;
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

            $count = ManualHdr::count();
            $fetch = ManualHdr::select('tr_manualinv_hdr.*','ms_unit.unit_code','ms_tenant.tenan_name','cashbk_name','ms_manual_type.name')->join('ms_unit','ms_unit.id',"=",'tr_manualinv_hdr.unit_id')->join('ms_tenant','ms_tenant.id',"=",'tr_manualinv_hdr.tenan_id')->join('ms_cash_bank','tr_manualinv_hdr.cashbk_id',"=",'ms_cash_bank.id')->join('ms_manual_type','tr_manualinv_hdr.manual_type',"=",'ms_manual_type.id');

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
                                        $query->where(\DB::raw('lower(trim("manual_number"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("tenan_name"::varchar))'),'like','%'.$keyword.'%');
                                    });
            if(!empty($datefrom)) $fetch = $fetch->where('manual_date.fit_date','>=',$datefrom);
            if(!empty($dateto)) $fetch = $fetch->where('manual_date.fit_date','<=',$dateto);
            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);

            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['unit_code'] = $value->unit_code;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['manual_number'] = $value->manual_number;
                $temp['manual_date'] = date('d/m/Y',strtotime($value->manual_date));
                $temp['manual_amount'] = "Rp. ".number_format($value->manual_amount);
                $temp['cashbk_name'] = $value->cashbk_name;
                $temp['manual_refno'] = $value->manual_refno;
                $temp['name'] = $value->name;
                $temp['posting_at'] = ($value->posting_at == NULL ? '' : date('d/m/Y',strtotime($value->posting_at)));
                $temp['checkbox'] = '<input type="checkbox" name="check" value="'.$value->id.'">';
                $fit_post = $temp['manual_post'] = !empty($value->manual_post) ? 'yes' : 'no';

                $action_button = '<a href="'.url('manualinv/print_manualinv?id='.$value->id).'" class="print-window" data-width="640" data-height="660"><i class="fa fa-file"></i></a>';

                if($fit_post == 'no'){
                    if(\Session::get('role')==1 || in_array(78,\Session::get('permissions'))){
                        $action_button .= ' | <a href="#" data-id="'.$value->id.'" title="Posting Manual Invoice" class="posting-confirm"><i class="fa fa-arrow-circle-o-up"></i></a>';
                        $action_button .= ' | <a href="#" data-id="'.$value->id.'" title="Edit Fitting In" class="edit-confirm"><i class="fa fa-pencil"></i></a>';
                    }
                    if(\Session::get('role')==1 || in_array(78,\Session::get('permissions'))){
                        $action_button .= ' | <a href="manualinv/void?id='.$value->id.'" title="Void" class="void-confirm"><i class="fa fa-ban"></i></a>';
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


    public function insertmanual(Request $request){
        $upd_id = $request->input('upd_id');
        $unt_id = $request->input('unt_id');
        $form_secret = !empty($request->input('form_secret')) ? $request->input('form_secret') : '' ;
        if($upd_id != 0){
            //UPDATE
            if(!empty($request->session()->get('FORM_SECRET'))) {
                if(strcasecmp($form_secret, $request->session()->get('FORM_SECRET')) === 0) {
                    $tp = ManualType::where('id',$request->input('type_id'))->first();

                    $invoice_data = TrContract::select('unit_id', 'tenan_id')
                            ->where('unit_id', '=',$unt_id)
                            ->where('contr_status', '=', 'confirmed')
                            ->first();
                    ManualHdr::where('id', $upd_id)->update(['unit_id'=>$unt_id, 'tenan_id'=>$invoice_data->tenan_id, 'manual_date'=>$request->input('manual_date'), 'manual_amount'=>$request->input('manual_amount'), 'cashbk_id'=>$request->input('cashbk_id'), 'manual_type'=>$request->input('type_id'), 'manual_refno'=>$request->input('manual_refno'), 'manual_footer'=>$request->input('manual_footer')]);

                    ManualDtl::where('manual_id', $upd_id)->update([
                        'manual_keterangan'=>$tp->name,
                        'manuald_amount'=>$request->input('manual_amount'),
                        'coa_code'=>$request->input('manual_coa')
                    ]);
                    $request->session()->forget('FORM_SECRET');
                }
            }
            return ['status' => 1, 'message' => 'Update Success'];
        }else{
            if(!empty($request->session()->get('FORM_SECRET'))) {
                if(strcasecmp($form_secret, $request->session()->get('FORM_SECRET')) === 0) {
                    $messages = [
                        'manual_date.required' => 'Date is required',
                    ];

                    $validator = Validator::make($request->all(), [
                        'manual_date' => 'required:tr_fitting_in'
                    ], $messages);

                    if ($validator->fails()) {
                        $errors = $validator->errors()->first();
                        return ['status' => 0, 'message' => $errors];
                    }

                    $coayear = date('Y',strtotime($request->input('manual_date')));
                    $month = date('m',strtotime($request->input('manual_date')));

                    $lastJournal = Numcounter::where('numtype','FA-TTS-MN')->where('tahun',$coayear)->where('bulan',$month)->first();
                    if(count($lastJournal) > 0){
                        $lst = $lastJournal->last_counter;
                        $nextJournalNumber = $lst + 1;
                        $lastJournal->update(['last_counter'=>$nextJournalNumber]);
                    }else{
                        $nextJournalNumber = 1;
                        $lastcounter = new Numcounter;
                        $lastcounter->numtype = 'FA-TTS-MN';
                        $lastcounter->tahun = date('Y');
                        $lastcounter->bulan = date('m');
                        $lastcounter->last_counter = 1;
                        $lastcounter->save();
                    }
                    $nextJournalNumber = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                    $journalNumber = "FA-TTS-MN/".$coayear."/".$this->Romawi($month)."/".$nextJournalNumber;

                    $invoice_data = TrContract::select('unit_id', 'tenan_id')
                    ->where('unit_id', '=',$request->input('tenan_id'))
                    ->where('contr_status', '=', 'confirmed')
                    ->first();

                    $coa = $request->input('manual_coa');
                    $bank = MsCashbank::where('id',$request->input('cashbk_id'))->first();
                    $bank_coa = $bank->coa_code;

                    $tp = ManualType::where('id',$request->input('type_id'))->first();

                    $action = new ManualHdr;
                    $action->unit_id = $request->input('tenan_id');
                    $action->tenan_id = $invoice_data->tenan_id;
                    $action->manual_number = $journalNumber;
                    $action->manual_date = $request->input('manual_date');
                    $action->manual_duedate = $request->input('manual_date');
                    $action->manual_amount = $request->input('manual_amount');
                    $action->cashbk_id = $request->input('cashbk_id');
                    $action->manual_type = $request->input('type_id');
                    $action->manual_footer = $request->input('manual_footer');
                    $action->manual_post = !empty($request->input('manual_post')) ? true : false;
                    $action->updated_by = $action->created_by = Auth::id();
                    $action->manual_refno = $request->input('manual_refno');

                    if($action->save()){
                        $manual_id = $action->id;
                        $action_detail = new ManualDtl;
                        $action_detail->manual_id = $manual_id;
                        $action_detail->manual_keterangan = $tp->name;
                        $action_detail->manuald_amount = $request->input('manual_amount');
                        $action_detail->coa_code = $request->input('manual_coa');
                        $action_detail->save();
                        $request->session()->forget('FORM_SECRET');
                        return ['status' => 1, 'message' => 'Insert Success'];
                    }else{
                        return ['status' => 0, 'message' => 'Something Wrong'];
                    }
                }
            }else{
                return ['status' => 1, 'message' => 'Insert Success!!'];
            }
        }
    }

    public function posting_manualinv(Request $request){
        $ids = $request->id;
        $postingdate = date('Y-m-d',strtotime($request->posting_date));
        if(!is_array($ids)) $ids = explode(',',$ids);
        $ids = ManualHdr::where('manual_post',0)->whereIn('id', $ids)->pluck('id');
        if(count($ids) < 1) return response()->json(['error'=>1, 'message'=> "0 Invoice Terposting"]);

        $coayear = date('Y');
        $month = date('m');
        $journal = [];

        $jourType = MsJournalType::where('jour_type_prefix','AR')->first();
        if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "AR" first before posting an fitting in']);
        $successPosting = 0;
        $successIds = [];
        $piutangIds = [];

        // cek backdate dr closing bulanan/tahunan
        $lastclose = TrLedger::whereNotNull('closed_at')->orderBy('closed_at','desc')->first();
        $limitMinPostingDate = null;
        if($lastclose) $limitMinPostingDate = date('Y-m-t', strtotime($lastclose->closed_at));

        foreach ($ids as $id) {
            $lastJournal = Numcounter::where('numtype','JG')->where('tahun',$coayear)->where('bulan',$month)->first();
            if(count($lastJournal) > 0){
                $lst = $lastJournal->last_counter;
                $nextJournalNumber = $lst + 1;
                $lastJournal->update(['last_counter'=>$nextJournalNumber]);
            }else{
                $nextJournalNumber = 1;
                $lastcounter = new Numcounter;
                $lastcounter->numtype = 'JG';
                $lastcounter->tahun = date('Y');
                $lastcounter->bulan = date('m');
                $lastcounter->last_counter = 1;
                $lastcounter->save();
            }
            $nextJournalNumber = str_pad($nextJournalNumber, 6, 0, STR_PAD_LEFT);
            $journalNumber = "JG/".$coayear."/".$this->Romawi($month)."/".$nextJournalNumber;
            $paymentHd = ManualHdr::select('manual_number','manual_amount','name','unit_code','tenan_name','ms_cash_bank.coa_code AS coa_debet','ms_manual_type.coa_code AS coa_credit')
            ->join('ms_tenant','ms_tenant.id','=','tr_manualinv_hdr.tenan_id')
            ->join('ms_unit','ms_unit.id','=','tr_manualinv_hdr.unit_id')
            ->join('ms_cash_bank','ms_cash_bank.id','=','tr_manualinv_hdr.cashbk_id')
            ->join('ms_manual_type','ms_manual_type.id','=','tr_manualinv_hdr.manual_type')
            ->find($id);

            //DEBET
            $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                $journal[] = [
                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => $postingdate,
                        'ledg_refno' => $paymentHd->manual_number,
                        'ledg_debit' => $paymentHd->manual_amount,
                        'ledg_credit' => 0,
                        'ledg_description' => $paymentHd->name.' '.$paymentHd->unit_code.' - '.$paymentHd->tenan_name,
                        'coa_year' => date('Y',strtotime($postingdate)),
                        'coa_code' => $paymentHd->coa_debet,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id, //hardcode utk finance
                        'dept_id' => 3, //hardcode utk finance
                        'modulname' => 'ManualInv',
                        'refnumber' =>$id
                    ];

                //KREDIT
                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                $journal[] = [
                            'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                            'ledge_fisyear' => $coayear,
                            'ledg_number' => $journalNumber,
                            'ledg_date' => $postingdate,
                            'ledg_refno' => $paymentHd->manual_number,
                            'ledg_debit' => 0,
                            'ledg_credit' => $paymentHd->manual_amount,
                            'ledg_description' => $paymentHd->name.' '.$paymentHd->unit_code.' - '.$paymentHd->tenan_name,
                            'coa_year' => date('Y',strtotime($postingdate)),
                            'coa_code' => $paymentHd->coa_credit,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'jour_type_id' => $jourType->id, //hardcode utk finance
                            'dept_id' => 3, //hardcode utk finance
                            'modulname' => 'ManualInv',
                            'refnumber' =>$id
                        ];
                
            $successIds[] = $id;
        }
        // INSERT DATABASE
        DB::beginTransaction();
        try{
            // insert journal
            TrLedger::insert($journal);

            if(count($successIds) > 0){
                foreach ($successIds as $id) {
                    ManualHdr::where('id', $id)->update(['manual_post'=>1, 'posting_at'=>$postingdate]);
                }
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error'=>1, 'message'=> $e->getMessage()]);
        }
        $message = count($successIds).' Invoice Terposting';
        return response()->json(['success'=>1, 'message'=> $message]);
    }

    public function unposting_manualinv(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

        $ids = ManualHdr::where('manual_post',1)->whereIn('id', $ids)->pluck('id');
        if(count($ids) < 1) return response()->json(['error'=>1, 'message'=> "0 Invoice Ter-unposting"]);
        $sc = 0;
        foreach ($ids as $id) {
            TrLedger::where('refnumber', $id)->where('modulname','ManualInv')->delete();
            $pay = ManualHdr::find($id);
            $pay->update(['manual_post'=>0,'posting_at'=>NULL]);
            $sc++;   
        }
        $message = $sc.' Invoice Unposting';
        return response()->json(['success'=>1, 'message'=> $message]);
    }

    public function manualinv_void(Request $request){
        $id = $request->id;
        $paymHeader = ManualHdr::find($id);
        // default result
        $result = array(
            'status'=>0,
            'message'=> 'Data not found'
        );
        if(!empty($paymHeader)){
            if($paymHeader->fit_post == 't'){
                $result['message'] = 'You can\'t void posted invoice';
                return response()->json($result);
            }
            
            ManualHdr::where('id',$id)->delete();
            ManualDtl::where('manual_id',$id)->delete();

            $result = array(
                'status'=>1,
                'message'=> 'Success void invoice'
            );
        }else{
            return response()->json($result);
        }
        return response()->json($result);
    }

    public function print_manualinv(Request $request){
        try{

            $inv_id = $request->id;
            if(!is_array($inv_id)) $inv_id = [$inv_id];
            $type = $request->type;

            $invoice_data = ManualHdr::select('tr_manualinv_hdr.*', 'unit_code', 'tenan_name', 'name','name_detail')
                                    ->leftJoin('ms_tenant','ms_tenant.id','=','tr_manualinv_hdr.tenan_id')
                                    ->leftJoin('ms_unit','ms_unit.id','=','tr_manualinv_hdr.unit_id')
                                    ->leftJoin('ms_manual_type','ms_manual_type.id','=','tr_manualinv_hdr.manual_type')
                                    ->whereIn('tr_manualinv_hdr.id',$inv_id)->get()->toArray();
            foreach ($invoice_data as $key => $inv) {
                $result = ManualDtl::where('manual_id',$inv['id'])->get()->toArray();

                $invoice_data[$key]['details'] = $result;
                $terbilang = $this->terbilang(($inv['manual_amount']));
                $invoice_data[$key]['terbilang'] = '## '.$terbilang.' Rupiah ##';
                
            }

            $company = MsCompany::with('MsCashbank')->first()->toArray();
            $signature = @MsConfig::where('name','digital_signature')->first()->value;
            $signatureFlag = @MsConfig::where('name','invoice_signature_flag')->first()->value;

            $set_data = array(
                'invoice_data' => $invoice_data,
                'result' => $result,
                'company' => $company,
                'type' => $type,
                'signature' => $signature,
                'signatureFlag' => $signatureFlag
            );

            if($type == 'pdf'){
                $pdf = PDF::loadView('print_faktur', $set_data)->setPaper('a4');

                return $pdf->download('FAKTUR-INV.pdf');
            }else{
                return view('print_manualinv', $set_data);
            }
         }catch(\Exception $e){
             return $e->getMessage();
         }
    }

    public function editmanual(Request $request){
        $ids = $request->id;
        $result = ManualHdr::join('ms_unit','ms_unit.id','=','tr_manualinv_hdr.unit_id')
        ->join('tr_manualinv_dtl','tr_manualinv_hdr.id','=','tr_manualinv_dtl.manual_id')
        ->join('ms_cash_bank','tr_manualinv_hdr.cashbk_id','=','ms_cash_bank.id')
        ->join('ms_manual_type','tr_manualinv_hdr.manual_type','=','ms_manual_type.id')
        ->where('tr_manualinv_hdr.id',$ids)->first();

        return ['unit_code' => $result->unit_code, 'unit_id' => $result->unit_id, 'tenan_id' => $result->tenan_id, 'manual_amount' => (float)$result->manual_amount, 'manual_date' => $result->manual_date, 'manual_keterangan'=>$result->manual_footer, 'manual_refno' => $result->manual_refno, 'cashbk_id' => $result->cashbk_id, 'manual_type' => $result->manual_type];
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
}