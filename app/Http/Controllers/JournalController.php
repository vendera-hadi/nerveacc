<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\MsMasterCoa;
use App\Models\MsAsset;
use App\Models\MsDepartment;
use App\Models\MsJournalType;
use App\Models\TrLedger;
use App\Models\TrInvoice;
use App\Models\MsConfig;
use App\Models\AkrualInv;
use App\Models\Numcounter;
use App\Models\LogAkrual;
use Carbon\Carbon;
use DB, Auth;

class JournalController extends Controller
{
    public function index(){
        $coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        $data['departments'] = MsDepartment::where('dept_isactive',1)->get();
        $data['journal_types'] = MsJournalType::where('jour_type_isactive',1)->get();
        return view('journal_list', $data);
    }

    public function insert(Request $request){
        // check refno
        if(!isset($request->ledg_refno)) return response()->json(['errorMsg' => 'Refno is required']);
        $form_secret = !empty($request->input('form_secret')) ? $request->input('form_secret') : '' ;
        if(!empty($request->session()->get('FORM_SECRET'))) {
            if(strcasecmp($form_secret, $request->session()->get('FORM_SECRET')) === 0) {
                $coa_code = $request->input('coa_code');
                $typeVal = $request->input('typeVal');
                $type = $request->input('type');

                $debit = [];
                $credit = [];
                foreach ($type as $key => $val) {
                    if($val == 'debit'){
                        $debit[] = $typeVal[$key];
                        $credit[] = 0;
                    }else{
                        $debit[] = 0;
                        $credit[] = $typeVal[$key];
                    }
                }

                $description = $request->input('ledg_description');
                $department = $request->input('dept_code');
                $journalType = MsJournalType::find($request->jour_type_id);
                $journalPrefix = trim($journalType->jour_type_prefix);

                $coayear = date('Y',strtotime($request->ledg_date));
                $month = date('m',strtotime($request->ledg_date));

                if($journalPrefix == 'AR'){
        	       $journalPrefix = 'JG';
                }
                // cari last prefix, order by journal type

                $lastJournal = Numcounter::where('numtype',$journalPrefix)->where('tahun',$coayear)->where('bulan',$month)->first();
                if(count($lastJournal) > 0){
                    $lst = $lastJournal->last_counter;
                    $nextJournalNumber = $lst + 1;
                    $lastJournal->update(['last_counter'=>$nextJournalNumber]);
                }else{
                    $nextJournalNumber = 1;
                    $lastcounter = new Numcounter;
                    $lastcounter->numtype = $journalPrefix;
                    $lastcounter->tahun = $coayear;
                    $lastcounter->bulan = $month;
                    $lastcounter->last_counter = 1;
                    $lastcounter->save();
                }
                $nextJournalNumber = str_pad($nextJournalNumber, 6, 0, STR_PAD_LEFT);
                $journalNumber = $journalPrefix."/".$coayear."/".$this->Romawi($month)."/".$nextJournalNumber;

                if(count($coa_code) > 0){
                    DB::transaction(function () use($request, $coa_code, $debit, $credit, $description, $department, $journalNumber){
                        foreach ($coa_code as $key => $value) {
                            $input = [
                                    'ledg_id' => "JRNL".str_replace(".", "", str_replace(" ", "",microtime())),
                                    'ledge_fisyear' => date('Y'),
                                    'ledg_number' => $journalNumber, //ini sementara coa code dulu
                                    'ledg_date' => $request->ledg_date,
                                    'ledg_refno' => $request->ledg_refno,
                                    'ledg_debit' => $debit[$key],
                                    'ledg_credit' => $credit[$key],
                                    'ledg_description' => $description[$key],
                                    'coa_year' => date('Y'),
                                    'coa_code' => $coa_code[$key],
                                    'dept_id' => $department[$key],
                                    'created_by' => \Auth::id(),
                                    'updated_by' => \Auth::id(),
                                    'jour_type_id' => $request->jour_type_id
                                ];
                            TrLedger::create($input);
                        }
                    });
                    $request->session()->forget('FORM_SECRET');
                    session()->flash('success','success');
                    return ['status' => 1, 'message' => 'Insert Journal Success'];
                }
            }
        }else{
            return ['status' => 1, 'message' => 'Insert Journal Success'];
        }
    }

    public function get(Request $request){
        try{
            $keyword = $request->input('q');
            $coa = $request->input('coa');
            $deptParam = $request->input('dept');
            // $jourTypeParam = $request->input('jour_type_id');

            // params
            $date = $request->input('date');
            if($date){
                $date = explode(' - ',$date);
                $startdate = date('Y-m-d',strtotime($date[0]));
                $enddate = date('Y-m-d',strtotime($date[1]));
            }

            $page = $request->page;
            $perPage = $request->rows;
            $page-=1;
            $offset = $page * $perPage;
            // @ -> isset(var) ? var : null
            $sort = @$request->sort;
            $order = @$request->order;
            $filters = @$request->filterRules;
            if(!empty($filters)) $filters = json_decode($filters);
            if($date) $year = date('Y',strtotime($enddate));
            else $year = date('Y');

            // olah data
            // beda sama GL, ini cm get Jurnal Umum aja
            $fetch = TrLedger::join('ms_master_coa','ms_master_coa.coa_code','=','tr_ledger.coa_code')
                            ->join('ms_journal_type','ms_journal_type.id','=','tr_ledger.jour_type_id')
                            ->leftJoin('tr_invoice','tr_invoice.inv_number','=','tr_ledger.ledg_refno')
                            ->leftJoin('ms_tenant','ms_tenant.id','=','tr_invoice.tenan_id')
                            ->select('tr_ledger.coa_code','tr_ledger.closed_at','coa_name','ledg_date','ledg_description','ledg_debit','ledg_credit','jour_type_prefix','ledg_refno','tenan_id','tenan_name')
                            ->where('ms_master_coa.coa_year',$year)
                            ->where('jour_type_id',1);
            if(!empty($date)) $fetch = $fetch->where('ledg_date','>=',$startdate)->where('ledg_date','<=',$enddate);
            if(!empty($deptParam)) $fetch = $fetch->where('dept_id',$deptParam);
            // if(!empty($jourTypeParam)) $fetch = $fetch->where('jour_type_id',$jourTypeParam);
            if(!empty($coa)) $fetch = $fetch->where('tr_ledger.coa_code',$coa);
            if(!empty($keyword)){
                $fetch = $fetch->where(function($query) use($keyword){
                        $query->where(DB::raw("LOWER(ledg_description)"),'like','%'.$keyword.'%')->orWhere(DB::raw("LOWER(tenan_name)"),'like','%'.$keyword.'%');
                    });
            }

            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            // echo $fetch; die();

            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                // $temp['id'] = $value->id;
                $temp['ledg_date'] = date('d/m/Y',strtotime($value->ledg_date));
                $temp['ledg_refno'] = $value->ledg_refno;
                $temp['ledg_description'] = $value->ledg_description;
                $temp['debit'] = number_format($value->ledg_debit,2);
                $temp['credit'] = number_format($value->ledg_credit,2);
                $temp['coa_code'] = $value->coa_code;
                $temp['coa_name'] = $value->coa_name;
                $temp['jour_type_prefix'] = $value->jour_type_prefix;

                $temp['action'] = '<a href="#" data-toggle="modal" data-target="#detailModal" data-id="'.$value->ledg_refno.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a> ';
                if(empty($value->closed_at)){
                    if(\Session::get('role')==1 || in_array(66,\Session::get('permissions'))){
                        $temp['action'] .= ' <a href="#" data-id="'.$value->ledg_refno.'" class="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a> ';
                    }
                    if(\Session::get('role')==1 || in_array(67,\Session::get('permissions'))){
                        $temp['action'] .=  '<a href="#" data-id="'.$value->ledg_refno.'" class="remove"><i class="fa fa-times" aria-hidden="true"></i></a>';
                    }
                }
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function delete(Request $request){
        try{
            $refno = $request->id;
            TrLedger::where('ledg_number',$refno)->delete();
            return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function getdetail(Request $request){
        try{
            $refno = $request->id;
            $coayear = date('Y');
            $fetch = TrLedger::select('ledg_number','ledg_date','ledg_refno','ledg_debit','ledg_credit','ledg_description','tr_ledger.coa_code','ms_master_coa.coa_name','tr_ledger.dept_id','ms_department.dept_name','ms_journal_type.jour_type_name')
                    ->join('ms_master_coa','tr_ledger.coa_code','=','ms_master_coa.coa_code')
                    ->join('ms_department','tr_ledger.dept_id','=','ms_department.id')
                    ->join('ms_journal_type',\DB::raw('tr_ledger.jour_type_id::integer'),'=','ms_journal_type.id')
                    ->where('ms_master_coa.coa_year',$coayear)
                    ->where('tr_ledger.ledg_number',$refno)
                    ->orderBy('ledg_number','asc')
                    ->orderBy('tr_ledger.id','asc')
                    ->get();
            return view('modal.detailjournal', ['fetch' => $fetch]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function accountSelect2(Request $request){
        $key = $request->q;
        $coaYear = date('Y');
        $fetch = MsMasterCoa::select('coa_code','coa_name','coa_type')->where(function($query) use($key){
            $query->where(\DB::raw('LOWER(coa_code)'),'like','%'.$key.'%')->orWhere(\DB::raw('LOWER(coa_name)'),'like','%'.$key.'%');
        })->where('coa_year',$coaYear)->get();

        $result['results'] = [];
        foreach ($fetch as $key => $value) {
            $temp = ['id'=>$value->id, 'text'=>$value->coa_code." ".$value->coa_name];
            array_push($result['results'], $temp);
        }
        return json_encode($result);
    }

    public function edit(Request $request){
        try{
            $refno = $request->id;
            $page = @$request->page;
            $coayear = date('Y');
            $data['id'] = $refno;
            $data['fetch'] = TrLedger::select('ledg_number','tr_ledger.coa_year','ledg_date','ledg_refno','ledg_debit','ledg_credit','ledg_description','tr_ledger.jour_type_id','tr_ledger.coa_code','ms_master_coa.coa_name','tr_ledger.dept_id','ms_department.dept_name','ms_journal_type.jour_type_name')
                    ->join('ms_master_coa','tr_ledger.coa_code','=','ms_master_coa.coa_code')
                    ->join('ms_department','tr_ledger.dept_id','=','ms_department.id')
                    ->join('ms_journal_type',\DB::raw('tr_ledger.jour_type_id::integer'),'=','ms_journal_type.id')
                    ->where('ms_master_coa.coa_year',$coayear)->where('tr_ledger.ledg_number',$refno)
                    ->get();

            $data['accounts'] = MsMasterCoa::where('coa_year',$coayear)->where('coa_isparent',0)->orderBy('coa_type')->get();
            $data['departments'] = MsDepartment::where('dept_isactive',1)->get();
            if($page == 'je') $data['journal_types'] = MsJournalType::where('jour_type_isactive',1)->where('id',1)->get();
            else $data['journal_types'] = MsJournalType::where('jour_type_isactive',1)->get();
            return view('editjournal', $data);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function update(Request $request){
        try{
            $form_secret = !empty($request->input('form_secret')) ? $request->input('form_secret') : '' ;
            if(!empty($request->session()->get('FORM_SECRET'))) {
                if(strcasecmp($form_secret, $request->session()->get('FORM_SECRET')) === 0) {
                    // delete journal details
                    $refno = $request->id;

                    // reinsert all
                    $coa_code = $request->input('coa_code');
                    $typeVal = $request->input('typeVal');
                    $type = $request->input('type');

                    $debit = [];
                    $credit = [];
                    foreach ($type as $key => $val) {
                        if($val == 'debit'){
                            $debit[] = $typeVal[$key];
                            $credit[] = 0;
                        }else{
                            $debit[] = 0;
                            $credit[] = $typeVal[$key];
                        }
                    }

                    $description = $request->input('ledg_description');
                    $department = $request->input('dept_code');
                    $journalNumber = $request->input('ledg_number');

                    if(count($coa_code) > 0){
                        DB::transaction(function () use($request, $coa_code, $debit, $credit, $description, $department, $journalNumber, $refno){
                            TrLedger::where('ledg_number',$refno)->delete();

                            foreach ($coa_code as $key => $value) {
                                $input = [
                                        'ledg_id' => "JRNL".str_replace(".", "", str_replace(" ", "",microtime())),
                                        'ledge_fisyear' => date('Y'),
                                        'ledg_number' => $journalNumber, //ini sementara coa code dulu
                                        'ledg_date' => $request->ledg_date,
                                        'ledg_refno' => $request->ledg_refno,
                                        'ledg_debit' => $debit[$key],
                                        'ledg_credit' => $credit[$key],
                                        'ledg_description' => $description[$key],
                                        'coa_year' => date('Y'),
                                        'coa_code' => $coa_code[$key],
                                        'dept_id' => $department[$key],
                                        'created_by' => \Auth::id(),
                                        'updated_by' => \Auth::id(),
                                        'jour_type_id' => $request->jour_type_id
                                    ];
                                TrLedger::create($input);
                            }
                        });
                    }
                    $request->session()->forget('FORM_SECRET');
                    return ['status' => 1, 'message' => 'Update Journal Success'];
                }
            }else{
                return ['status' => 1, 'message' => 'Update Journal Success !!'];
            }
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function generalLedger(){
        $coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        $data['departments'] = MsDepartment::where('dept_isactive',1)->get();
        $data['journal_types'] = MsJournalType::where('jour_type_isactive',1)->get();
        return view('generalledger', $data);
    }

    public function glGet(Request $request){
        try{
            $keyword = $request->input('q');
            if(!empty($keyword)) $keyword = strtolower($keyword);
            $coa = $request->input('coa');
            $tocoa = $request->input('tocoa');
            $deptParam = $request->input('dept');
            $jourTypeParam = $request->input('jour_type_id');

            // params
            $date = $request->input('date');
            if($date){
                $date = explode(' - ',$date);
                $startdate = date('Y-m-d',strtotime($date[0]));
                $enddate = date('Y-m-d',strtotime($date[1]));
            }

            $page = $request->page;
            $perPage = $request->rows;
            $page-=1;
            $offset = $page * $perPage;
            // @ -> isset(var) ? var : null
            $sort = @$request->sort;
            $order = @$request->order;
            $filters = @$request->filterRules;
            if(!empty($filters)) $filters = json_decode($filters);
            if($date) $year = date('Y',strtotime($enddate));
            else $year = date('Y');

            // olah data

            $fetch = TrLedger::join('ms_master_coa','ms_master_coa.coa_code','=','tr_ledger.coa_code')
                            ->join('ms_journal_type','ms_journal_type.id','=','tr_ledger.jour_type_id')
                            ->leftJoin('tr_invoice','tr_invoice.inv_number','=','tr_ledger.ledg_refno')
                            ->leftJoin('ms_tenant','ms_tenant.id','=','tr_invoice.tenan_id')
                            ->select('tr_ledger.coa_code','tr_ledger.closed_at','coa_name','ledg_date','ledg_description','ledg_debit','ledg_credit','jour_type_prefix','ledg_refno','tenan_id','tenan_name','ledg_number')
                            ->where('ms_master_coa.coa_year',$year);
            if(!empty($date)) $fetch = $fetch->where('ledg_date','>=',$startdate)->where('ledg_date','<=',$enddate);
            if(!empty($deptParam)) $fetch = $fetch->where('dept_id',$deptParam);
            if(!empty($jourTypeParam)) $fetch = $fetch->where('jour_type_id',$jourTypeParam);

            if(!empty($coa) && empty($tocoa)) $fetch = $fetch->where('tr_ledger.coa_code',$coa);
            else if(empty($coa) && !empty($tocoa)) $fetch = $fetch->where('tr_ledger.coa_code',$tocoa);
            else if(!empty($coa) && !empty($tocoa) && $coa == $tocoa) $fetch = $fetch->where('tr_ledger.coa_code',$coa);
            else if(!empty($coa) && !empty($tocoa) && $coa > $tocoa) $fetch = $fetch->whereBetween('tr_ledger.coa_code',[$tocoa,$coa]);
            else if(!empty($coa) && !empty($tocoa) && $coa < $tocoa) $fetch = $fetch->whereBetween('tr_ledger.coa_code',[$coa,$tocoa]);

            if(!empty($keyword)){
                $fetch = $fetch->where(function($query) use($keyword){
                        $query->where(DB::raw("LOWER(ledg_description)"),'like','%'.$keyword.'%')->orWhere(DB::raw("LOWER(tenan_name)"),'like','%'.$keyword.'%')->orWhere(DB::raw("LOWER(ledg_refno)"),'like','%'.$keyword.'%');
                    });
            }

            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            // echo $fetch; die();

            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                // $temp['id'] = $value->id;
                $temp['ledg_date'] = date('d/m/Y',strtotime($value->ledg_date));
                $temp['ledg_refno'] = $value->ledg_number;
                $temp['ledg_description'] = $value->ledg_description;
                $temp['debit'] = "Rp. ".number_format($value->ledg_debit,2);
                $temp['credit'] = "Rp. ".number_format($value->ledg_credit,2);
                $temp['coa_code'] = $value->coa_code;
                $temp['coa_name'] = $value->coa_name;
                $temp['jour_type_prefix'] = $value->jour_type_prefix;
                $temp['tenan_name'] = $value->tenan_name;

                $temp['action'] = '<a href="#" data-toggle="modal" data-target="#detailModal" data-id="'.$value->ledg_number.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a> ';
                if(empty($value->closed_at)){
                    if(\Session::get('role')==1 || in_array(66,\Session::get('permissions'))){
                        $temp['action'] .= ' <a href="#" data-id="'.$value->ledg_number.'" class="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a> ';
                    }
                    if(\Session::get('role')==1 || in_array(67,\Session::get('permissions'))){
                        $temp['action'] .=  '<a href="#" data-id="'.$value->ledg_number.'" class="remove"><i class="fa fa-times" aria-hidden="true"></i></a>';
                    }
                }
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function trEntry(){
        $coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        $data['departments'] = MsDepartment::where('dept_isactive',1)->get();
        $data['journal_types'] = MsJournalType::where('jour_type_isactive',1)->get();
        return view('transaction_entry', $data);
    }

    public function clEntry(){
        return view('close_entry', []);
    }

    public function opEntry(){
        return view('open_entry', []);
    }

    public function opEntryUpdate(Request $request){
        $month = @$request->month;
        $year = @$request->year;

        $startdate = date('Y-m-01',strtotime($year."-".$month."-01"));
        $enddate = date('Y-m-t', strtotime($startdate));
        $startyear = date('Y-m-01',strtotime($year."-01-01"));

        if($month - 1 == 0){
            $lastmonth = 12;
            $lastyear = $year -1;
        }else{
            $lastmonth = $month -1;
            $lastyear = $year;
        }

        //ACRUD balikin jurnal dari pendapatan di terima dimuka ke arahan piutangnya
        $info = AkrualInv::whereRaw('log_potong < total_potong')
                ->whereBetween('inv_date',[$startyear, $enddate])
                ->where('last_status','end')
                ->where(DB::raw('EXTRACT(MONTH FROM last_process)'),$lastmonth)
                ->where(DB::raw('EXTRACT(YEAR FROM last_process)'),$lastyear)
                ->get();
        $pmuka = @MsConfig::where('name','coa_uangmuka')->first()->value;
        if(count($info) > 0){
            $jourType = MsJournalType::where('jour_type_prefix','AR')->first();
            if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "AR" first before posting an invoice']);
                
            $journaldata = [];
            $logdata = [];
            foreach ($info as $inf) {
                if($inf->log_potong != $inf->total_potong){
                    $lastJournal = Numcounter::where('numtype','JG')->where('tahun',$year)->where('bulan',$month)->first();
                    if(count($lastJournal) > 0){
                        $lst = $lastJournal->last_counter;
                        $nextJournalNumber = $lst + 1;
                        $lastJournal->update(['last_counter'=>$nextJournalNumber]);
                    }else{
                        $nextJournalNumber = 1;
                        $lastcounter = new Numcounter;
                        $lastcounter->numtype = 'JG';
                        $lastcounter->tahun = $year;
                        $lastcounter->bulan = $month;
                        $lastcounter->last_counter = 1;
                        $lastcounter->save();
                    }
                    $nextJournalNumber = str_pad($nextJournalNumber, 6, 0, STR_PAD_LEFT);
                    $journalNumber = "JG/".$year."/".$this->Romawi($month)."/".$nextJournalNumber;

                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    $last = $inf->log_potong;
                    //DEBET
                    if($inf->prorate_amount > 0 && $inf->log_potong == 0){
                        $nl = $inf->prorate_amount;
                    }else{
                        $nl = $inf->potong_perbulan;
                    }
                    $journaldata[] = [
                                    'ledg_id' => "JRNL".substr($microtime,-10).str_random(5),
                                    'ledge_fisyear' => $year,
                                    'ledg_number' => $journalNumber,
                                    'ledg_date' => $enddate,
                                    'ledg_refno' => $inf->inv_number,
                                    'ledg_debit' => $nl,
                                    'ledg_credit' => 0,
                                    'ledg_description' => 'Piutang dibayar dimuka Ke '.($last+1).' - '.$inf->inv_number,
                                    'coa_year' => $year,
                                    'coa_code' => $pmuka,
                                    'created_by' => Auth::id(),
                                    'updated_by' => Auth::id(),
                                    'jour_type_id' => $jourType->id,
                                    'dept_id' => 3
                                ];
                    //CREDIT
                    $coa_names = MsMasterCoa::where('coa_code',$inf->coa_code)->get()->first();            
                    $journaldata[] = [
                                    'ledg_id' => "JRNL".substr($microtime,-10).str_random(5),
                                    'ledge_fisyear' => $year,
                                    'ledg_number' => $journalNumber,
                                    'ledg_date' => $enddate,
                                    'ledg_refno' => $inf->inv_number,
                                    'ledg_debit' => 0,
                                    'ledg_credit' => $nl,
                                    'ledg_description' => $coa_names->coa_name.' Ke '.($last+1).' - '.$inf->inv_number,
                                    'coa_year' => $year,
                                    'coa_code' => $inf->coa_code,
                                    'created_by' => Auth::id(),
                                    'updated_by' => Auth::id(),
                                    'jour_type_id' => $jourType->id,
                                    'dept_id' => 3
                                ];
                    //LOG
                    $logdata[] = [
                                    'inv_id' => $inf->inv_id,
                                    'inv_number' => $inf->inv_number,
                                    'inv_date' => $inf->inv_date,
                                    'inv_amount' => $nl,
                                    'process_date' => $enddate
                                ];
                    //UPDATE LOG POTONG
                    AkrualInv::where('id',$inf->id)->update(['log_potong' => ($last+1), 'last_process'=>$enddate]);
                    $nextJournalNumber++;
                }
            }
            if(count($journaldata) > 0){
                try {
                    DB::transaction(function () use($journaldata,$logdata){
                        // insert journal
                        TrLedger::insert($journaldata);
                        LogAkrual::insert($logdata);
                    });
                } catch (\Exception $e) {

                }
            }
        }
        
        return response()->json(['success' => 'Open Entry Success']);
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

    public function clEntryUpdate(Request $request){
        $closingType = @$request->closing_type;
        $month = @$request->month;
        $year = @$request->year;

        if($closingType == 'monthly'){
            $startdate = date('Y-m-01',strtotime($year."-".$month."-01"));
            $enddate = date('Y-m-t', strtotime($startdate));
            $startyear = date('Y-m-01',strtotime($year."-01-01"));
            $checkClosed = TrLedger::whereBetween('ledg_date',[$startdate, $enddate])->whereNotNull('closed_at')->count();
            $checkNotClosed = TrLedger::whereBetween('ledg_date',[$startdate, $enddate])->whereNull('closed_at')->count();

            if($checkNotClosed == 0 && $checkClosed == 0){
                return response()->json(['errorMsg' => 'There is no entries at this month']);
            }else if($checkNotClosed == 0 && $checkClosed > 0){
                return response()->json(['errorMsg' => 'All entries this month was already closed']);
            }else{
                //ACRUD balikin jurnal dari pendapatan di terima dimuka ke arahan piutangnya
                $info = AkrualInv::whereRaw('log_potong < total_potong')->whereBetween('inv_date',[$startyear, $enddate])->where('last_status',NULL)->get();
                $pmuka = @MsConfig::where('name','coa_uangmuka')->first()->value;
                if(count($info) > 0){
                    $jourType = MsJournalType::where('jour_type_prefix','AR')->first();
                    if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "AR" first before posting an invoice']);
                    
                    $journaldata = [];
                    $logdata = [];
                    foreach ($info as $inf) {
                        if($inf->log_potong != $inf->total_potong){

                            $lastJournal = Numcounter::where('numtype','JG')->where('tahun',$year)->where('bulan',$month)->first();
                            if(count($lastJournal) > 0){
                                $lst = $lastJournal->last_counter;
                                $nextJournalNumber = $lst + 1;
                                $lastJournal->update(['last_counter'=>$nextJournalNumber]);
                            }else{
                                $nextJournalNumber = 1;
                                $lastcounter = new Numcounter;
                                $lastcounter->numtype = 'JG';
                                $lastcounter->tahun = $year;
                                $lastcounter->bulan = $month;
                                $lastcounter->last_counter = 1;
                                $lastcounter->save();
                            }
                            $nextJournalNumber = str_pad($nextJournalNumber, 6, 0, STR_PAD_LEFT);
                            $journalNumber = "JG/".$year."/".$this->Romawi($month)."/".$nextJournalNumber;

                            $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                            $last = $inf->log_potong;
                            //DEBET
                            if($inf->prorate_amount > 0 && $inf->log_potong == 0){
                                $nl = $inf->prorate_amount;
                            }else{
                                $nl = $inf->potong_perbulan;
                            }
                            $journaldata[] = [
                                            'ledg_id' => "JRNL".substr($microtime,-10).str_random(5),
                                            'ledge_fisyear' => $year,
                                            'ledg_number' => $journalNumber,
                                            'ledg_date' => $enddate,
                                            'ledg_refno' => $inf->inv_number,
                                            'ledg_debit' => $nl,
                                            'ledg_credit' => 0,
                                            'ledg_description' => 'Piutang dibayar dimuka Ke '.($last+1).' - '.$inf->inv_number,
                                            'coa_year' => $year,
                                            'coa_code' => $pmuka,
                                            'created_by' => Auth::id(),
                                            'updated_by' => Auth::id(),
                                            'jour_type_id' => $jourType->id,
                                            'dept_id' => 3
                                        ];
                            //CREDIT
                            $coa_names = MsMasterCoa::where('coa_code',$inf->coa_code)->get()->first();            
                            $journaldata[] = [
                                            'ledg_id' => "JRNL".substr($microtime,-10).str_random(5),
                                            'ledge_fisyear' => $year,
                                            'ledg_number' => $journalNumber,
                                            'ledg_date' => $enddate,
                                            'ledg_refno' => $inf->inv_number,
                                            'ledg_debit' => 0,
                                            'ledg_credit' => $nl,
                                            'ledg_description' => $coa_names->coa_name.' Ke '.($last+1).' - '.$inf->inv_number,
                                            'coa_year' => $year,
                                            'coa_code' => $inf->coa_code,
                                            'created_by' => Auth::id(),
                                            'updated_by' => Auth::id(),
                                            'jour_type_id' => $jourType->id,
                                            'dept_id' => 3
                                        ];
                            //LOG
                            $logdata[] = [
                                    'inv_id' => $inf->inv_id,
                                    'inv_number' => $inf->inv_number,
                                    'inv_date' => $inf->inv_date,
                                    'inv_amount' => $nl,
                                    'process_date' => $enddate
                                ];
                            //UPDATE LOG POTONG
                            AkrualInv::where('id',$inf->id)->update(['log_potong' => ($last+1),'last_status'=>'end','last_process'=>$enddate]);

                            $nextJournalNumber++;
                        }
                    }
                    if(count($journaldata) > 0){
                        try {
                            DB::transaction(function () use($journaldata,$logdata){
                                // insert journal
                                TrLedger::insert($journaldata);
                                LogAkrual::insert($logdata);
                            });
                        } catch (\Exception $e) {

                        }
                    }
                }
                //gk di update dlu closed_atnya
                //TrLedger::whereBetween('ledg_date',[$startdate, $enddate])->whereNull('closed_at')->update(['closed_at' => date('Y-m-d')]);
                // update saldo
                $totalDebit = TrLedger::select(\DB::raw('SUM(ledg_debit) as total'))->whereBetween('ledg_date',[$startdate, $enddate])->first();
                $totalCredit = TrLedger::select(\DB::raw('SUM(ledg_credit) as total'))->whereBetween('ledg_date',[$startdate, $enddate])->first();
                if(empty($totalDebit->total)) $totalDebit->total = 0;
                if(empty($totalCredit->total)) $totalCredit->total = 0;
                $balance = $totalCredit->total - $totalDebit->total;

                \DB::table('gl_balance_log')->insert(['month'=>(int)$month, 'year'=>(int)$year, 'balance'=>(float)$balance]);
                // jalanin posting penyusutan jg
                $this->postingPenyusutan($month,$year);
                return response()->json(['success' => 'Closing Success']);
            }
        }else if($closingType == 'yearly'){
            $startdate = date('Y-m-d',strtotime($year."-01-01"));
            $enddate = date('Y-m-d', strtotime($year."-12-31"));
            $checkClosed = TrLedger::whereBetween('ledg_date',[$startdate, $enddate])->whereNotNull('closed_at')->count();
            $checkNotClosed = TrLedger::whereBetween('ledg_date',[$startdate, $enddate])->whereNull('closed_at')->count();
            /*
            if($checkNotClosed == 0 && $checkClosed == 0){
                return response()->json(['errorMsg' => 'There is no entries at this year']);
            }else if($checkNotClosed == 0 && $checkClosed > 0){
                return response()->json(['errorMsg' => 'All entries this year was already closed']);
            }else{
            */
                TrLedger::whereBetween('ledg_date',[$startdate, $enddate])->whereNull('closed_at')->update(['closed_at' => date('Y-m-d',strtotime($year."-12-31"))]);
                for($i=1; $i<=12; $i++) {
                    // update saldo
                    $startdate = date('Y-m-d',strtotime($year."-".$i."-01"));
                    $enddate = date('Y-m-t', strtotime($year."-".$i."-31"));
                    $totalDebit = TrLedger::select(\DB::raw('SUM(ledg_debit) as total'))->where(DB::raw('EXTRACT(MONTH FROM ledg_date)'),$i)->where(DB::raw('EXTRACT(YEAR FROM ledg_date)'),$year)->first();
                    $totalCredit = TrLedger::select(\DB::raw('SUM(ledg_credit) as total'))->where(DB::raw('EXTRACT(MONTH FROM ledg_date)'),$i)->where(DB::raw('EXTRACT(YEAR FROM ledg_date)'),$year)->first();
                    if(empty($totalDebit->total)) $totalDebit->total = 0;
                    if(empty($totalCredit->total)) $totalCredit->total = 0;
                    $balance = (float)$totalDebit->total - (float)$totalCredit->total;
                    \DB::table('gl_balance_log')->insert(['month'=>(int)$i, 'year'=>(int)$year, 'balance'=>(float)$balance]);
                }

                // catat saldo akhir, insert ke next coa beginning
                $this->calculateAndCloneCoaNextYear($year);
                return response()->json(['success' => 'Closing Success']);
            //}
        }

        // return response()->json();
    }

    public function calculateAndCloneCoaNextYear($year)
    {
        $coa_laba_rugi = @MsConfig::where('name','coa_laba_rugi')->first()->value;
        $allcoa = MsMasterCoa::where('coa_year', $year)->get();
        foreach ($allcoa as $coa) {
            if($coa->coa_code == $coa_laba_rugi) $result = $this->labarugiBerjalan($coa->coa_code, $year);
            else $result = $this->getTotalFromLedger($coa->coa_code, $year);
            //echo "COA code = ".$coa->coa_code.", Debit : ".$result['debit'].", Credit : ".$result['credit'].", Total : ".$result['total']."<br><br>";
            $coa->coa_debit = $result['debit'];
            $coa->coa_credit = $result['credit'];
            $coa->coa_ending = $result['total'];
            $coa->save();

            $newCoa = $coa->replicate();
            $newCoa->coa_year = $year + 1;
            $newCoa->coa_beginning = $result['total'];
            $newCoa->coa_debit = 0;
            $newCoa->coa_credit = 0;
            $newCoa->coa_ending = 0;
            $newCoa->save();
        }
    }

    private function postingPenyusutan($month,$year)
    {
        // list semua harta
        $harta = MsAsset::all();
        $lastJournal = TrLedger::where('jour_type_id',1)->where('ledg_number','like','%'.$year.$month.'%')->latest()->first();
        if($lastJournal){
            $lastJournalNumber = explode(" ", $lastJournal->ledg_number);
            $lastJournalNumber = (int) end($lastJournalNumber);
            $nextJournalNumber = $lastJournalNumber + 1;
        }else{
            $nextJournalNumber = 1;
        }
        $journal = [];
        foreach ($harta as $key => $hrt) {
            // $nilaiSisaTahunan = $hrt->nilaiSisaTahunan($year, date('Y-m-t',strtotime($year."-".$month."-01")));
            $startTime = Carbon::parse($hrt->date);
            $finishTime = Carbon::parse(date('Y-m-t',strtotime($year."-".$month."-01")));
            $jedaTahunan = $finishTime->diffInYears($startTime);
            $jedaBulanan = $finishTime->diffInMonths($startTime);
            $bulanLebih = $jedaBulanan % ($jedaTahunan * 12);

            if($bulanLebih > 0){
                $tempyear = $year + 1;
                $value = $hrt->depreciationPerMonth($tempyear, date('Y-m-t',strtotime($tempyear."-".$month."-01")));
            }else{
                $value = $hrt->depreciationPerMonth($year, date('Y-m-t',strtotime($year."-".$month."-01")));
            }

            if($value > 0){
                $nextJournalNumber = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                $journalNumber = "JU ".$year.$month." ".$nextJournalNumber;
                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                // Debet
                $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $year,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => date('Y-m-d'),
                                'ledg_refno' => $hrt->id,
                                'ledg_debit' => $value,
                                'ledg_credit' => 0,
                                'ledg_description' => 'Penyusutan Harta : '.$hrt->name,
                                'coa_year' => $year,
                                'coa_code' => $hrt->assetType->debit_coa_code,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => 1,
                                'dept_id' => 3 //hardcode utk finance
                            ];
                $nextJournalNumber += 1;
                $nextJournalNumber = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                $journalNumber = "JU ".$year.$month." ".$nextJournalNumber;
                $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $year,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => date('Y-m-d'),
                                'ledg_refno' => $hrt->id,
                                'ledg_debit' => 0,
                                'ledg_credit' => $value,
                                'ledg_description' => 'Akumulasi Penyusutan Harta : '.$hrt->name,
                                'coa_year' => $year,
                                'coa_code' => $hrt->assetType->debit_coa_code,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => 1,
                                'dept_id' => 3 //hardcode utk finance
                            ];
                $nextJournalNumber += 1;
            }
            // end if
        }
        // end foreach
        if(count($journal) > 0){
            try {
                DB::transaction(function () use($journal){
                    // insert journal
                    TrLedger::insert($journal);
                });
            } catch (\Exception $e) {

            }
        }
    }

    private function getTotalFromLedger($coacode, $year)
    {
        // get semua ledger ditotal dr awal sampai akhir tahun
        $coa = MsMasterCoa::where('coa_code','like',$coacode."%")->where('coa_year', $year)->first();
        $total = $debit = $credit = 0;
        if($coa){
            $ledger = TrLedger::where('coa_code','like',$coacode."%")
                ->where('ledg_date','>=',date($year.'-01-01'))->where('ledg_date','<=',date($year.'-12-31'))
                ->select(\DB::raw('SUM(ledg_debit) as debit'), \DB::raw('SUM(ledg_credit) as credit'))->first();
            if(strpos($coa->coa_type, 'DEBET') !== false) $total = $coa->coa_beginning + $ledger->debit - $ledger->credit;
            else $total = $coa->coa_beginning + $ledger->credit - $ledger->debit;
            $debit = $ledger->debit ?: 0;
            $credit = $ledger->credit ?: 0;
        }
        return ['total' => $total, 'debit' => $debit, 'credit' => $credit];
    }

    private function labarugiBerjalan($coacode, $year)
    {
        $coa = MsMasterCoa::where('coa_code','like',$coacode."%")->where('coa_year',$year)->first();
        // rekap pendapatan (debet)
        $ledgerProfit = TrLedger::where(function($query){
                        $query->where('coa_code','like',"4%")->orWhere('coa_code','like',"6%");
                })->where('ledg_date','>=',date($year.'-01-01'))->where('ledg_date','<=',date($year.'-12-31'))
                ->select(\DB::raw('SUM(ledg_debit) as debit'), \DB::raw('SUM(ledg_credit) as credit'))->first();
        $profit = abs($ledgerProfit->credit - $ledgerProfit->debit);
        //  rekap loss (credit)
        $ledgerLoss = TrLedger::where(function($query){
                        $query->where('coa_code','like',"5%")->orWhere('coa_code','like',"7%");
                })->where('ledg_date','>=',date($year.'-01-01'))->where('ledg_date','<=', date($year.'-12-31'))
                ->select(\DB::raw('SUM(ledg_debit) as debit'), \DB::raw('SUM(ledg_credit) as credit'))->first();
        $loss = abs($ledgerLoss->debit - $ledgerLoss->credit);
        $total = $coa->coa_beginning + $profit - $loss;
        return ['total' => $total, 'debit' => $profit, 'credit' => $loss];
    }

}
