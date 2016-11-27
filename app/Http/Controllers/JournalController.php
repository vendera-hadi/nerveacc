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
    	$data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->orderBy('coa_type')->get();
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
        
        // cari last prefix
        $lastJournal = TrLedger::latest()->first();
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
            return ['status' => 1, 'message' => 'Insert Journal Success'];
        }
    }

    public function get(Request $request){
        try{
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

            // olah data
            $count = TrLedger::from(DB::raw('(select distinct on("ledg_refno") "id", "ledg_number", "ledg_date", "ledg_refno", "ledg_description", "dept_id", "jour_type_id", sum(ledg_debit) as debit, sum(ledg_credit) as credit from "tr_ledger" group by "id", "ledg_number", "ledg_refno", "ledg_date", "ledg_description", "dept_id","jour_type_id") as test'));
            if($date) $count = $count->where('ledg_date','>=',$startdate)->where('ledg_date','<=',$enddate);
            $count = $count->count();

            $fetch = TrLedger::from(DB::raw('(select distinct on("ledg_refno") "id", "ledg_number", "ledg_date", "ledg_refno", "ledg_description", "dept_id", "jour_type_id", sum(ledg_debit) as debit, sum(ledg_credit) as credit from "tr_ledger" group by "id", "ledg_number", "ledg_refno", "ledg_date", "ledg_description", "dept_id", "jour_type_id") as test'));
            if($date) $fetch = $fetch->where('ledg_date','>=',$startdate)->where('ledg_date','<=',$enddate);
            if($deptParam) $fetch = $fetch->where('dept_id',$deptParam);
            if($jourTypeParam) $fetch = $fetch->where('jour_type_id',$jourTypeParam);
            if(!empty($filters) && count($filters) > 0){
                foreach($filters as $filter){
                    $op = "like";
                    // tentuin operator
                    switch ($filter->op) {
                        case 'contains':
                            $op = 'like';
                            $fetch = $fetch->where(DB::raw("LOWER(".$filter->field.")"),$op,'%'.$filter->value.'%');
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
                }
            }
            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            // echo $fetch; die();

            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                // $temp['id'] = $value->id;
                $temp['ledg_number'] = $value->ledg_number;
                $temp['ledg_date'] = date('d/m/Y',strtotime($value->ledg_date));
                $temp['ledg_refno'] = $value->ledg_refno;
                $temp['ledg_description'] = $value->ledg_description;
                $temp['debit'] = ($value->debit > 0) ? $value->debit : $value->credit;
                // $temp['credit'] = $value->credit;
                
                $temp['action'] = '<a href="#" data-toggle="modal" data-target="#detailModal" data-id="'.$value->ledg_refno.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a> <a href="#" data-id="'.$value->ledg_refno.'" class="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a> <a href="#" data-id="'.$value->ledg_refno.'" class="remove"><i class="fa fa-times" aria-hidden="true"></i></a>';
                
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
                    ->where('tr_ledger.coa_year',$coayear)->where('tr_ledger.ledg_refno',$refno)
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
            $coayear = date('Y');
            $data['id'] = $refno;
            $data['fetch'] = TrLedger::select('ledg_number','ledg_date','ledg_refno','ledg_debit','ledg_credit','ledg_description','tr_ledger.jour_type_id','tr_ledger.coa_code','ms_master_coa.coa_name','tr_ledger.dept_id','ms_department.dept_name','ms_journal_type.jour_type_name')
                    ->join('ms_master_coa','tr_ledger.coa_code','=','ms_master_coa.coa_code')
                    ->join('ms_department','tr_ledger.dept_id','=','ms_department.id')
                    ->join('ms_journal_type',\DB::raw('tr_ledger.jour_type_id::integer'),'=','ms_journal_type.id')
                    ->where('tr_ledger.coa_year',$coayear)->where('tr_ledger.ledg_refno',$refno)
                    ->get();
            $data['accounts'] = MsMasterCoa::where('coa_year',$coayear)->orderBy('coa_type')->get();
            $data['departments'] = MsDepartment::where('dept_isactive',1)->get();
            $data['journal_types'] = MsJournalType::where('jour_type_isactive',1)->get();
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



}
