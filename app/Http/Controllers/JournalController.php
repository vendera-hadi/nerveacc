<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\MsMasterCoa;
use App\Models\MsDepartment;
use App\Models\MsJournalType;
use App\Models\TrLedger;
use DB;

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
        $checkRefno = TrLedger::where('ledg_refno', $request->ledg_refno)->first();
        if($checkRefno) return response()->json(['errorMsg' => 'Refno is already exist']);

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
        $journalPrefix = $journalType->jour_type_prefix;
        
        // cari last prefix, order by journal type

        $lastJournal = TrLedger::where('jour_type_id',$request->jour_type_id)->latest()->first();
        if($lastJournal){
            $lastJournalNumber = explode(" ", $lastJournal->ledg_number);
            $lastJournalNumber = (int) end($lastJournalNumber);
            $nextJournalNumber = $lastJournalNumber + 1;
        }else{
            $nextJournalNumber = 1;
        }
        $nextJournalNumber = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);

        if(count($coa_code) > 0){
            DB::transaction(function () use($request, $coa_code, $debit, $credit, $description, $department, $journalPrefix, $nextJournalNumber){
                $month = str_pad(date('m'), 2, 0, STR_PAD_LEFT);
                $year = date('y');
                $journalNumber = $journalPrefix." ".$year.$month." ".$nextJournalNumber;
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
            session()->flash('success','success');
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
                            ->where('ms_master_coa.coa_year',$year)->where('tr_ledger.coa_year',$year)
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
            TrLedger::where('ledg_refno',$refno)->delete();
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
                    ->where('tr_ledger.coa_year',$coayear)->where('ms_master_coa.coa_year',$coayear)
                    ->where('tr_ledger.ledg_refno',$refno)
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
                    ->where('tr_ledger.coa_year',$coayear)->where('ms_master_coa.coa_year',$coayear)->where('tr_ledger.ledg_refno',$refno)
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
                    TrLedger::where('ledg_refno',$refno)->delete();

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
            return ['status' => 1, 'message' => 'Update Journal Success'];
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
                            ->select('tr_ledger.coa_code','tr_ledger.closed_at','coa_name','ledg_date','ledg_description','ledg_debit','ledg_credit','jour_type_prefix','ledg_refno','tenan_id','tenan_name')
                            ->where('ms_master_coa.coa_year',$year)->where('tr_ledger.coa_year',$year);
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
                $temp['ledg_refno'] = $value->ledg_refno;
                $temp['ledg_description'] = $value->ledg_description;
                $temp['debit'] = "Rp. ".number_format($value->ledg_debit,2);
                $temp['credit'] = "Rp. ".number_format($value->ledg_credit,2);
                $temp['coa_code'] = $value->coa_code;
                $temp['coa_name'] = $value->coa_name;
                $temp['jour_type_prefix'] = $value->jour_type_prefix;
                $temp['tenan_name'] = $value->tenan_name;

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

    public function clEntryUpdate(Request $request){
        $closingType = @$request->closing_type;
        $month = @$request->month;
        $year = @$request->year;

        if($closingType == 'monthly'){
            $startdate = date('Y-m-01',strtotime($year."-".$month."-01"));
            $enddate = date('Y-m-t', strtotime($startdate));
            $checkClosed = TrLedger::whereBetween('ledg_date',[$startdate, $enddate])->whereNotNull('closed_at')->count();
            $checkNotClosed = TrLedger::whereBetween('ledg_date',[$startdate, $enddate])->whereNull('closed_at')->count();

            if($checkNotClosed == 0 && $checkClosed == 0){
                return response()->json(['errorMsg' => 'There is no entries at this month']);                
            }else if($checkNotClosed == 0 && $checkClosed > 0){
                return response()->json(['errorMsg' => 'All entries this month was already closed']);                
            }else{
                TrLedger::whereBetween('ledg_date',[$startdate, $enddate])->whereNull('closed_at')->update(['closed_at' => date('Y-m-d')]);
                // update saldo
                $totalDebit = TrLedger::select(\DB::raw('SUM(ledg_debit) as total'))->whereBetween('ledg_date',[$startdate, $enddate])->first();
                $totalCredit = TrLedger::select(\DB::raw('SUM(ledg_credit) as total'))->whereBetween('ledg_date',[$startdate, $enddate])->first();
                if(empty($totalDebit->total)) $totalDebit->total = 0;
                if(empty($totalCredit->total)) $totalCredit->total = 0; 
                $balance = $totalCredit->total - $totalDebit->total;
                \DB::table('gl_balance_log')->insert(['month'=>(int)$month, 'year'=>(int)$year, 'balance'=>(float)$balance]);
                return response()->json(['success' => 'Closing Success']);
            }
        }else if($closingType == 'yearly'){
            $startdate = date('Y-m-d',strtotime($year."-01-01"));
            $enddate = date('Y-m-t', strtotime($year."-12-01"));
            $checkClosed = TrLedger::whereBetween('ledg_date',[$startdate, $enddate])->whereNotNull('closed_at')->count();
            $checkNotClosed = TrLedger::whereBetween('ledg_date',[$startdate, $enddate])->whereNull('closed_at')->count();

            if($checkNotClosed == 0 && $checkClosed == 0){
                return response()->json(['errorMsg' => 'There is no entries at this year']);                
            }else if($checkNotClosed == 0 && $checkClosed > 0){
                return response()->json(['errorMsg' => 'All entries this year was already closed']);                
            }else{
                TrLedger::whereBetween('ledg_date',[$startdate, $enddate])->whereNull('closed_at')->update(['closed_at' => date('Y-m-d')]);
                for($i=1; $i<=12; $i++) {
                    // update saldo
                    $startdate = date('Y-m-d',strtotime($year."-".$i."-01"));
                    $enddate = date('Y-m-t', strtotime($year."-".$i."-01"));
                    $totalDebit = TrLedger::select(\DB::raw('SUM(ledg_debit) as total'))->whereBetween('ledg_date',[$startdate, $enddate])->first();
                    $totalCredit = TrLedger::select(\DB::raw('SUM(ledg_credit) as total'))->whereBetween('ledg_date',[$startdate, $enddate])->first();
                    if(empty($totalDebit->total)) $totalDebit->total = 0;
                    if(empty($totalCredit->total)) $totalCredit->total = 0; 
                    $balance = $totalCredit->total - $totalDebit->total;
                    \DB::table('gl_balance_log')->insert(['month'=>(int)$i, 'year'=>(int)$year, 'balance'=>(float)$balance]);
                }
                return response()->json(['success' => 'Closing Success']);
            }
        }

        // return response()->json();
    }

}
