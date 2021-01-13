<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\TrLedger;
use App\Models\TrInvoice;
use App\Models\MsTenant;
use App\Models\MsConfig;
use App\Models\Numcounter;
use App\Models\FittingIn;
use App\Models\FittingOut;
use App\Models\MsUnit;
use App\Models\TrContract;
use App\Models\MsJournalType;
use App\Models\MsCompany;
use App\Models\MsMasterCoa;
use App\Models\MsCashBank;
use Auth;
use DB;
use Validator;

class FittingController extends Controller
{
    public function index(){

        $cashbank_data = MsCashBank::all()->toArray();
        return view('fitting_in', array('cashbank_data' => $cashbank_data));
    }

    public function fittingin_get(Request $request){
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

            $count = FittingIn::count();
            $fetch = FittingIn::select('tr_fitting_in.*','ms_unit.unit_code','ms_tenant.tenan_name','tr_fitting_out.out_number')->join('ms_unit','ms_unit.id',"=",'tr_fitting_in.unit_id')->join('ms_tenant','ms_tenant.id',"=",'tr_fitting_in.tenan_id')->leftjoin('tr_fitting_out','tr_fitting_out.fit_id',"=",'tr_fitting_in.id');

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
                                        $query->where(\DB::raw('lower(trim("fit_number"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("tenan_name"::varchar))'),'like','%'.$keyword.'%');
                                    });
            if(!empty($datefrom)) $fetch = $fetch->where('tr_fitting_in.fit_date','>=',$datefrom);
            if(!empty($dateto)) $fetch = $fetch->where('tr_fitting_in.fit_date','<=',$dateto);
            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);

            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['unit_code'] = $value->unit_code;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['fit_number'] = $value->fit_number;
                $temp['fit_date'] = date('d/m/Y',strtotime($value->fit_date));
                $temp['fit_amount'] = "Rp. ".number_format($value->fit_amount);
                $temp['fit_refno'] = $value->fit_refno;
                $temp['posting_at'] = ($value->posting_at == NULL ? '' : date('d/m/Y',strtotime($value->posting_at)));
                $temp['out_number'] = ($value->out_number == NULL ? '' : $value->out_number);
                $temp['checkbox'] = '<input type="checkbox" name="check" value="'.$value->id.'">';
                $fit_post = $temp['fit_post'] = !empty($value->fit_post) ? 'yes' : 'no';

                $action_button = '<a href="'.url('fittingin/print_tts?id='.$value->id).'" class="print-window" data-width="640" data-height="660"><i class="fa fa-file"></i></a>';

                if($fit_post == 'no'){
                    if(\Session::get('role')==1 || in_array(78,\Session::get('permissions'))){
                        $action_button .= ' | <a href="#" data-id="'.$value->id.'" title="Posting Fitting In" class="posting-confirm"><i class="fa fa-arrow-circle-o-up"></i></a>';
                        $action_button .= ' | <a href="#" data-id="'.$value->id.'" title="Edit Fitting In" class="edit-confirm"><i class="fa fa-pencil"></i></a>';
                    }
                    if(\Session::get('role')==1 || in_array(78,\Session::get('permissions'))){
                        $action_button .= ' | <a href="fittingin/fittingin_void?id='.$value->id.'" title="Void" class="void-confirm"><i class="fa fa-ban"></i></a>';
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

    public function editfittingout(Request $request){
        $ids = $request->id;
        $result = FittingIn::join('ms_unit','ms_unit.id','=','tr_fitting_in.unit_id')->where('tr_fitting_in.id',$ids)->first();

        return ['unit_code' => $result->unit_code, 'unit_id' => $result->unit_id, 'tenan_id' => $result->tenan_id, 'fit_amount' => (float)$result->fit_amount, 'fit_date' => $result->fit_date, 'fit_keterangan'=>$result->fit_keterangan, 'fit_refno' => $result->fit_refno, 'cashbk_id' => $result->cashbk_id];
    }

    public function get_tenant(Request $request){
        $unit_id = @$request->tenan_id;

        $invoice_data = TrContract::select('unit_id', 'unit_code', 'tenan_id', 'tenan_name')
        ->leftJoin('ms_unit', 'tr_contract.unit_id',"=",'ms_unit.id')
        ->leftJoin('ms_tenant','tr_contract.tenan_id',"=",'ms_tenant.id')
        ->where('unit_id', '=',$unit_id)
        ->where('contr_status', '=', 'confirmed')
        ->first();

        return $invoice_data->tenan_name;
    }

    public function insertfittingin(Request $request){
        $upd_id = $request->input('upd_id');
        $unt_id = $request->input('unt_id');
        $messages = [
            'fit_date.required' => 'Date is required',
        ];

        $validator = Validator::make($request->all(), [
            'fit_date' => 'required:tr_fitting_in'
        ], $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ['status' => 0, 'message' => $errors];
        }

        $coayear = date('Y',strtotime($request->input('fit_date')));
        $month = date('m',strtotime($request->input('fit_date')));
        $form_secret = !empty($request->input('form_secret')) ? $request->input('form_secret') : '' ;

        if($upd_id != 0){
            //UPDATE
            if(!empty($request->session()->get('FORM_SECRET'))) {
                if(strcasecmp($form_secret, $request->session()->get('FORM_SECRET')) === 0) {
                    $invoice_data = TrContract::select('unit_id', 'tenan_id')
                            ->where('unit_id', '=',$unt_id)
                            ->where('contr_status', '=', 'confirmed')
                            ->first();
                    FittingIn::where('id', $upd_id)->update(['unit_id'=>$unt_id, 'tenan_id'=>$invoice_data->tenan_id, 'fit_date'=>$request->input('fit_date'), 'fit_amount'=>$request->input('fit_amount'), 'fit_keterangan'=>$request->input('fit_keterangan'),'fit_refno'=>$request->input('fit_refno'),'cashbk_id'=>$request->input('cashbk_id')]);
                    $request->session()->forget('FORM_SECRET');
                }
            }
            return ['status' => 1, 'message' => 'Update Success'];
        }else{

            if(!empty($request->session()->get('FORM_SECRET'))) {
                if(strcasecmp($form_secret, $request->session()->get('FORM_SECRET')) === 0) {

                    $lastJournal = Numcounter::where('numtype','FA-TTS-FO')->where('tahun',$coayear)->where('bulan',$month)->first();
                    if(count($lastJournal) > 0){
                        $lst = $lastJournal->last_counter;
                        $nextJournalNumber = $lst + 1;
                        $lastJournal->update(['last_counter'=>$nextJournalNumber]);
                    }else{
                        $last_month = (($month - 1) == 0 ? $month = 12 : ($month - 1));
                        if($last_month == 12){
                            $nextJournalNumber = 1;
                            $lastcounter = new Numcounter;
                            $lastcounter->numtype = 'FA-TTS-FO';
                            $lastcounter->tahun = $coayear;
                            $lastcounter->bulan = $month;
                            $lastcounter->last_counter = 1;
                            $lastcounter->save();
                        }else{
                            $lastJournal2 = Numcounter::where('numtype','FA-TTS-FO')->where('tahun',$coayear)->where('bulan',$last_month)->first();
                            if(count($lastJournal2) > 0){
                                $lst2 = $lastJournal2->last_counter;
                                $nextJournalNumber = $lst2 + 1;
                                $lastcounter = new Numcounter;
                                $lastcounter->numtype = 'FA-TTS-FO';
                                $lastcounter->tahun = $coayear;
                                $lastcounter->bulan = $month;
                                $lastcounter->last_counter = $nextJournalNumber;
                                $lastcounter->save();
                            }
                        }
                    }
                    $nextJournalNumber = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                    $journalNumber = "FA-TTS-FO/".$coayear."/".$this->Romawi($month)."/".$nextJournalNumber;

                    $invoice_data = TrContract::select('unit_id', 'tenan_id')
                    ->where('unit_id', '=',$request->input('tenan_id'))
                    ->where('contr_status', '=', 'confirmed')
                    ->first();

                    $action = new FittingIn;
                    $action->unit_id = $request->input('tenan_id');
                    $action->tenan_id = $invoice_data->tenan_id;
                    $action->fit_number = $journalNumber;
                    $action->fit_date = $request->input('fit_date');
                    $action->fit_amount = $request->input('fit_amount');
                    $action->fit_keterangan = $request->input('fit_keterangan');
                    $action->fit_refno = $request->input('fit_refno');
                    $action->fit_post = !empty($request->input('fit_post')) ? true : false;
                    $action->updated_by = $action->created_by = Auth::id();
                    $action->cashbk_id = $request->input('cashbk_id');

                    $request->session()->forget('FORM_SECRET');
                    if($action->save()){
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

    public function posting_fittingin(Request $request){
        $ids = $request->id;
        $postingdate = date('Y-m-d',strtotime($request->posting_date));
        if(!is_array($ids)) $ids = explode(',',$ids);
        $ids = FittingIn::where('fit_post',0)->whereIn('id', $ids)->pluck('id');
        if(count($ids) < 1) return response()->json(['error'=>1, 'message'=> "0 Fitting In Terposting"]);

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
                $lastcounter->tahun = $coayear;
                $lastcounter->bulan = $month;
                $lastcounter->last_counter = 1;
                $lastcounter->save();
            }
            $nextJournalNumber = str_pad($nextJournalNumber, 6, 0, STR_PAD_LEFT);
            $journalNumber = "JG/".$coayear."/".$this->Romawi($month)."/".$nextJournalNumber;
            $paymentHd = FittingIn::join('ms_tenant','ms_tenant.id','=','tr_fitting_in.tenan_id')->join('ms_unit','ms_unit.id','=','tr_fitting_in.unit_id')->find($id);

            $coa_bank = @MsCashbank::where('id', $paymentHd->cashbk_id)->first()->coa_code;
            $coa_fitting = @MsConfig::where('name','coa_fitting')->first()->value;
            $coa_adm_fitting = @MsConfig::where('name','coa_adm_fitting')->first()->value;

            $amt_fitting = @MsConfig::where('name','biaya_fitting')->first()->value;
            $amt_adm_fitting = ($paymentHd->fit_amount - $amt_fitting > 0 ? $paymentHd->fit_amount - $amt_fitting : 0);

            if($paymentHd->fit_amount >= $amt_fitting){
                if($amt_adm_fitting > 0){
                    //DEBET
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                        $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => $postingdate,
                                'ledg_refno' => $paymentHd->fit_number,
                                'ledg_debit' => $paymentHd->fit_amount,
                                'ledg_credit' => 0,
                                'ledg_description' => 'Fitting Out '.$paymentHd->unit_code.' - '.$paymentHd->tenan_name,
                                'coa_year' => date('Y',strtotime($postingdate)),
                                'coa_code' => $coa_bank,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => $jourType->id, //hardcode utk finance
                                'dept_id' => 3, //hardcode utk finance
                                'modulname' => 'FittingIn',
                                'refnumber' =>$id
                            ];

                    //KREDIT
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => $postingdate,
                                'ledg_refno' => $paymentHd->fit_number,
                                'ledg_debit' => 0,
                                'ledg_credit' => $amt_fitting,
                                'ledg_description' => 'Fitting Out '.$paymentHd->unit_code.' - '.$paymentHd->tenan_name,
                                'coa_year' => date('Y',strtotime($postingdate)),
                                'coa_code' => $coa_fitting,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => $jourType->id, //hardcode utk finance
                                'dept_id' => 3, //hardcode utk finance
                                'modulname' => 'FittingIn',
                                'refnumber' =>$id
                            ];
                    $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => $postingdate,
                                'ledg_refno' => $paymentHd->fit_number,
                                'ledg_debit' => 0,
                                'ledg_credit' => $amt_adm_fitting,
                                'ledg_description' => 'Fitting Out '.$paymentHd->unit_code.' - '.$paymentHd->tenan_name,
                                'coa_year' => date('Y',strtotime($postingdate)),
                                'coa_code' => $coa_adm_fitting,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => $jourType->id, //hardcode utk finance
                                'dept_id' => 3, //hardcode utk finance
                                'modulname' => 'FittingIn',
                                'refnumber' =>$id
                            ];
                }else{
                    //DEBET
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                        $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => $postingdate,
                                'ledg_refno' => $paymentHd->fit_number,
                                'ledg_debit' => $paymentHd->fit_amount,
                                'ledg_credit' => 0,
                                'ledg_description' => 'Fitting Out '.$paymentHd->unit_code.' - '.$paymentHd->tenan_name,
                                'coa_year' => date('Y',strtotime($postingdate)),
                                'coa_code' => $coa_bank,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => $jourType->id, //hardcode utk finance
                                'dept_id' => 3, //hardcode utk finance
                                'modulname' => 'FittingIn',
                                'refnumber' =>$id
                            ];

                    //KREDIT
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => $postingdate,
                                'ledg_refno' => $paymentHd->fit_number,
                                'ledg_debit' => 0,
                                'ledg_credit' => $amt_fitting,
                                'ledg_description' => 'Fitting Out '.$paymentHd->unit_code.' - '.$paymentHd->tenan_name,
                                'coa_year' => date('Y',strtotime($postingdate)),
                                'coa_code' => $coa_fitting,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => $jourType->id, //hardcode utk finance
                                'dept_id' => 3, //hardcode utk finance
                                'modulname' => 'FittingIn',
                                'refnumber' =>$id
                            ];
                }
            }else{
                //DEBET
                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    $journal[] = [
                            'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                            'ledge_fisyear' => $coayear,
                            'ledg_number' => $journalNumber,
                            'ledg_date' => $postingdate,
                            'ledg_refno' => $paymentHd->fit_number,
                            'ledg_debit' => $paymentHd->fit_amount,
                            'ledg_credit' => 0,
                            'ledg_description' => 'Fitting Out '.$paymentHd->unit_code.' - '.$paymentHd->tenan_name,
                            'coa_year' => date('Y',strtotime($postingdate)),
                            'coa_code' => $coa_bank,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'jour_type_id' => $jourType->id, //hardcode utk finance
                            'dept_id' => 3, //hardcode utk finance
                            'modulname' => 'FittingIn',
                            'refnumber' =>$id
                        ];

                //KREDIT
                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                $journal[] = [
                            'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                            'ledge_fisyear' => $coayear,
                            'ledg_number' => $journalNumber,
                            'ledg_date' => $postingdate,
                            'ledg_refno' => $paymentHd->fit_number,
                            'ledg_debit' => 0,
                            'ledg_credit' => $paymentHd->fit_amount,
                            'ledg_description' => 'Fitting Out '.$paymentHd->unit_code.' - '.$paymentHd->tenan_name,
                            'coa_year' => date('Y',strtotime($postingdate)),
                            'coa_code' => $coa_adm_fitting,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'jour_type_id' => $jourType->id, //hardcode utk finance
                            'dept_id' => 3, //hardcode utk finance
                            'modulname' => 'FittingIn',
                            'refnumber' =>$id
                        ];
            }
            $successIds[] = $id;
        }
        // INSERT DATABASE
        DB::beginTransaction();
        try{
            // insert journal
            TrLedger::insert($journal);

            if(count($successIds) > 0){
                foreach ($successIds as $id) {
                    FittingIn::where('id', $id)->update(['fit_post'=>1, 'posting_at'=>$postingdate]);
                }
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error'=>1, 'message'=> $e->getMessage()]);
        }
        $message = count($successIds).' Fitting Terposting';
        return response()->json(['success'=>1, 'message'=> $message]);
    }

    public function unposting_fittingin(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

        $ids = FittingIn::where('fit_post',1)->whereIn('id', $ids)->pluck('id');
        if(count($ids) < 1) return response()->json(['error'=>1, 'message'=> "0 Fitting In Ter-unposting"]);
        $sc = 0;
        foreach ($ids as $id) {
            TrLedger::where('refnumber', $id)->where('modulname','FittingIn')->delete();
            $pay = FittingIn::find($id);
            $pay->update(['fit_post'=>0,'posting_at'=>NULL]);
            $sc++;   
        }
        $message = $sc.' Fitting In Unposting';
        return response()->json(['success'=>1, 'message'=> $message]);
    }

    public function fittingin_void(Request $request){
        $id = $request->id;
        $paymHeader = FittingIn::find($id);
        // default result
        $result = array(
            'status'=>0,
            'message'=> 'Data not found'
        );
        if(!empty($paymHeader)){
            if($paymHeader->fit_post == 't'){
                $result['message'] = 'You can\'t void posted fitting in';
                return response()->json($result);
            }
            
            FittingIn::where('id',$id)->delete();

            $result = array(
                'status'=>1,
                'message'=> 'Success void fitting In'
            );
        }else{
            return response()->json($result);
        }
        return response()->json($result);
    }

    public function print_tts(Request $request){
        $company = MsCompany::with('MsCashbank')->first()->toArray();
        $paymentHeader = FittingIn::find($request->id);
        $contract = TrContract::where('tr_contract.tenan_id',$paymentHeader->tenan_id)->where('tr_contract.unit_id',$paymentHeader->unit_id)->where('contr_status','confirmed')->first();

        $terbilang = $this->terbilang($paymentHeader->fit_amount);

        $set_data = array(
                'company' => $company,
                'header' => $paymentHeader,
                'terbilang' => $terbilang.' Rupiah',
                'tenan' => @$contract->MsTenant->tenan_name,
                'unit' => @$contract->MsUnit->unit_code
            );

        return view('print_tts', $set_data);
    }

    public function fittingout(){
        $cashbank_data = MsCashBank::all()->toArray();
        return view('fitting_out', array('cashbank_data' => $cashbank_data));
    }

    public function fittingout_get(Request $request){
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

            $count = FittingOut::count();
            $fetch = FittingOut::select('tr_fitting_out.*','tr_fitting_in.fit_number','ms_unit.unit_code','ms_tenant.tenan_name','ms_cash_bank.cashbk_name')
            ->leftjoin('tr_fitting_in','tr_fitting_out.fit_id',"=",'tr_fitting_in.id')
            ->join('ms_unit','ms_unit.id',"=",'tr_fitting_in.unit_id')
            ->join('ms_cash_bank','ms_cash_bank.id','=','tr_fitting_out.bank_id')
            ->join('ms_tenant','ms_tenant.id',"=",'tr_fitting_in.tenan_id');

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
                                        $query->where(\DB::raw('lower(trim("fit_number"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("tenan_name"::varchar))'),'like','%'.$keyword.'%');
                                    });
            if(!empty($datefrom)) $fetch = $fetch->where('tr_fitting_out.out_date','>=',$datefrom);
            if(!empty($dateto)) $fetch = $fetch->where('tr_fitting_out.out_date','<=',$dateto);
            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);

            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['unit_code'] = $value->unit_code;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['out_number'] = $value->out_number;
                $temp['fit_number'] = $value->fit_number;
                $temp['out_date'] = date('d/m/Y',strtotime($value->out_date));
                $temp['out_amount'] = "Rp. ".number_format($value->out_amount);
                $temp['out_refno'] = $value->out_refno;
                $temp['posting_at'] = ($value->posting_at == NULL ? '' : date('d/m/Y',strtotime($value->posting_at)));
                $temp['checkbox'] = '<input type="checkbox" name="check" value="'.$value->id.'">';
                $fit_post = $temp['out_post'] = !empty($value->out_post) ? 'yes' : 'no';
                $temp['cashbk_name'] = $value->cashbk_name;
                $action_button = '';

                if($fit_post == 'no'){
                    if(\Session::get('role')==1 || in_array(78,\Session::get('permissions'))){
                        $action_button .= ' | <a href="#" data-id="'.$value->id.'" title="Posting Fitting In" class="posting-confirm"><i class="fa fa-arrow-circle-o-up"></i></a>';
                    }
                    if(\Session::get('role')==1 || in_array(78,\Session::get('permissions'))){
                        $action_button .= ' | <a href="fittingout/fittingout_void?id='.$value->id.'" title="Void" class="void-confirm"><i class="fa fa-ban"></i></a>';
                    }
                }else{
                    $action_button .= '<a href="'.url('fittingout/print_bpv?id='.$value->id).'" class="print-window" data-width="640" data-height="660">BPV</a>';
                }
                $temp['action_button'] = $action_button;

                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function get_tts(Request $request){
        $unit_id = @$request->tenan_id;

        $invoice_data = FittingIn::select('id','fit_number')
        ->where('unit_id','=',$unit_id)
        ->where('flag_selesai',0)
        ->get();
        $ary[] = array('id'=>'PILIH NOMOR TTS', 'Name'=>'PILIH NOMOR TTS');
        if(count($invoice_data) > 0){
            foreach($invoice_data as $row){
                $ary[] = array('id'=>$row->id, 'Name'=>$row->fit_number);
            }
        }

        return response()->json($ary);
    }

    public function insertfittingout(Request $request){
        $messages = [
            'out_date.required' => 'Date is required',
        ];

        $validator = Validator::make($request->all(), [
            'out_date' => 'required:tr_fitting_out'
        ], $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ['status' => 0, 'message' => $errors];
        }

        $coayear = date('Y',strtotime($request->input('out_date')));
        $month = date('m',strtotime($request->input('out_date')));

        $lastJournal = Numcounter::where('numtype','OUT-TTS')->where('tahun',$coayear)->where('bulan',$month)->first();
        if(count($lastJournal) > 0){
            $lst = $lastJournal->last_counter;
            $nextJournalNumber = $lst + 1;
            $lastJournal->update(['last_counter'=>$nextJournalNumber]);
        }else{
            $nextJournalNumber = 1;
            $lastcounter = new Numcounter;
            $lastcounter->numtype = 'OUT-TTS';
            $lastcounter->tahun = $coayear;
            $lastcounter->bulan = $month;
            $lastcounter->last_counter = 1;
            $lastcounter->save();
        }
        $nextJournalNumber = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
        $journalNumber = "OUT-TTS/".$coayear."/".$this->Romawi($month)."/".$nextJournalNumber;

        $action = new FittingOut;
        $action->fit_id = $request->input('tts_number');
        $action->out_number = $journalNumber;
        $action->out_date = $request->input('out_date');
        $action->out_amount = $request->input('out_amount');
        $action->out_keterangan = $request->input('out_keterangan');
        $action->out_refno = $request->input('out_refno');
        $action->out_post = !empty($request->input('out_post')) ? true : false;
        $action->updated_by = $action->created_by = Auth::id();
        $action->bank_id = $request->input('cashbk_id');

        //UPDATE FITTING OUT
        $upd = FittingIn::where('id',$request->input('tts_number'))->first();
        $upd->update(['flag_selesai'=>1]);


        if($action->save()){
            return ['status' => 1, 'message' => 'Insert Success'];
        }else{
            return ['status' => 0, 'message' => 'Something Wrong'];
        }
    }

    public function posting_fittingout(Request $request){
        $ids = $request->id;
        $postingdate = date('Y-m-d',strtotime($request->posting_date));
        if(!is_array($ids)) $ids = explode(',',$ids);
        $ids = FittingOut::where('out_post',0)->whereIn('id', $ids)->pluck('id');
        if(count($ids) < 1) return response()->json(['error'=>1, 'message'=> "0 Fitting Out Terposting"]);

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
                $lastcounter->tahun = $coayear;
                $lastcounter->bulan = $month;
                $lastcounter->last_counter = 1;
                $lastcounter->save();
            }
            $nextJournalNumber = str_pad($nextJournalNumber, 6, 0, STR_PAD_LEFT);
            $journalNumber = "JG/".$coayear."/".$this->Romawi($month)."/".$nextJournalNumber;
            $paymentHd = FittingOut::leftjoin('tr_fitting_in','tr_fitting_out.fit_id',"=",'tr_fitting_in.id')
            ->join('ms_tenant','ms_tenant.id','=','tr_fitting_in.tenan_id')
            ->join('ms_cash_bank','ms_cash_bank.id','=','tr_fitting_out.bank_id')
            ->join('ms_unit','ms_unit.id','=','tr_fitting_in.unit_id')->find($id);

            $coa_bank = @MsConfig::where('name','akasa_coa_deposit_bank')->first()->value;
            $coa_fitting = @MsConfig::where('name','coa_fitting')->first()->value;

            //DEBET
            $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                $journal[] = [
                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => $postingdate,
                        'ledg_refno' => $paymentHd->out_number,
                        'ledg_debit' => $paymentHd->out_amount,
                        'ledg_credit' => 0,
                        'ledg_description' => 'Pengembalian Fitting Out '.$paymentHd->unit_code.' - '.$paymentHd->tenan_name,
                        'coa_year' => date('Y',strtotime($postingdate)),
                        'coa_code' => $coa_fitting,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id, //hardcode utk finance
                        'dept_id' => 3, //hardcode utk finance
                        'modulname' => 'FittingOut',
                        'refnumber' =>$id
                    ];

            //KREDIT
            $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
            $journal[] = [
                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => $postingdate,
                        'ledg_refno' => $paymentHd->out_number,
                        'ledg_debit' => 0,
                        'ledg_credit' => $paymentHd->out_amount,
                        'ledg_description' => 'Pengembalian Fitting Out '.$paymentHd->unit_code.' - '.$paymentHd->tenan_name,
                        'coa_year' => date('Y',strtotime($postingdate)),
                        'coa_code' => $paymentHd->coa_code,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id, //hardcode utk finance
                        'dept_id' => 3, //hardcode utk finance
                        'modulname' => 'FittingOut',
                        'refnumber' =>$id
                    ];
        }
        $successIds[] = $id;
        
        // INSERT DATABASE
        DB::beginTransaction();
        try{
            // insert journal
            TrLedger::insert($journal);
            foreach ($ids as $id) {
                FittingOut::where('id', $id)->update(['out_post'=>1, 'posting_at'=>$postingdate]);
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error'=>1, 'message'=> $e->getMessage()]);
        }
        $message = count($successIds).' Pengembalian Fitting Terposting';
        return response()->json(['success'=>1, 'message'=> $message]);
    }

    public function unposting_fittingout(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

        $ids = FittingOut::where('out_post',1)->whereIn('id', $ids)->pluck('id');
        if(count($ids) < 1) return response()->json(['error'=>1, 'message'=> "0 Fitting Out Ter-unposting"]);
        $sc = 0;
        foreach ($ids as $id) {
            TrLedger::where('refnumber', $id)->where('modulname','FittingOut')->delete();
            $pay = FittingOut::find($id);
            $pay->update(['out_post'=>0,'posting_at'=>NULL]);
            $sc++;   
        }
        $message = $sc.' Pengembalian Fitting Unposting';
        return response()->json(['success'=>1, 'message'=> $message]);
    }

    public function fittingout_void(Request $request){
        $id = $request->id;
        $paymHeader = FittingOut::find($id);
        $fit_in = FittingIn::where('id',$paymHeader->fit_id);
        $fit_in->update(['flag_selesai'=>0]);

        // default result
        $result = array(
            'status'=>0,
            'message'=> 'Data not found'
        );
        if(!empty($paymHeader)){
            if($paymHeader->out_post == 't'){
                $result['message'] = 'You can\'t void posted fitting out';
                return response()->json($result);
            }
            
            FittingOut::where('id',$id)->delete();

            $result = array(
                'status'=>1,
                'message'=> 'Success void fitting Out'
            );
        }else{
            return response()->json($result);
        }
        return response()->json($result);
    }

    public function print_bpv(Request $request){
        try{

            $ap_id = $request->id;
            if(!is_array($ap_id)) $ap_id = [$ap_id];
            $type = $request->type;

            $ap_data = FittingOut::whereIn('id',$ap_id)->get()->toArray();
            $listcoa = MsMasterCoa::get();
            $lsc = array();
            foreach($listcoa as $row){
                $lsc[trim($row->coa_code)] = $row->coa_name;
            }
            foreach ($ap_data as $key => $payable) {
                $result = TrLedger::where('refnumber',$payable['id'])->where('modulname','FittingOut')->get()->toArray();
                $ap_data[$key]['details'] = $result;
                $terbilang = $this->terbilang($payable['out_amount']);
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
                return view('print_fitting_bpv', $set_data);
            }
         }catch(\Exception $e){
             return $e->getMessage();
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