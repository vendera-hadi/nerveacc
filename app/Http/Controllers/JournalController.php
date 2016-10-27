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
        $debit = $request->input('debit');
        $credit = $request->input('credit');
        if(count($coa_code) > 0){
            DB::transaction(function () use($request, $coa_code, $debit, $credit){
                foreach ($coa_code as $key => $value) {
                    $input = [
                            'ledg_id' => 'JRNL'.str_replace(".", "", str_replace(" ", "",microtime())),
                            'ledge_fisyear' => date('Y'),
                            'ledg_number' => $coa_code[$key], //ini sementara coa code dulu
                            'ledg_date' => $request->ledg_date,
                            'ledg_refno' => $request->ledg_refno,
                            'ledg_debit' => $debit[$key],
                            'ledg_credit' => $credit[$key],
                            'ledg_description' => $request->ledg_description,
                            'coa_year' => date('Y'),
                            'coa_code' => $coa_code[$key],
                            'dept_code' => $request->dept_code,
                            'created_by' => \Auth::id(),
                            'updated_by' => \Auth::id(),
                            'jour_type_id' => $request->jour_type_id
                        ];
                    TrLedger::create($input);
                }
            });
            return ['status' => 1, 'message' => 'Update Success'];
        }
    }

    public function get(Request $request){
        try{
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
            $count = TrLedger::select('ledg_date','ledg_refno','ledg_description',\DB::raw('sum(ledg_debit) as debit'),\DB::raw('sum(ledg_credit) as credit'))->groupBy('ledg_refno','ledg_date','ledg_description');
            if($date) $count = $count->where('ledg_date','>=',$startdate)->where('ledg_date','<=',$enddate);
            $count = $count->count();

            $fetch = TrLedger::select('ledg_date','ledg_refno','ledg_description',\DB::raw('sum(ledg_debit) as debit'),\DB::raw('sum(ledg_credit) as credit'))->groupBy('ledg_refno','ledg_date','ledg_description');
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
                }
            }
            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['ledg_date'] = $value->ledg_date;
                $temp['ledg_refno'] = $value->ledg_refno;
                $temp['ledg_description'] = $value->ledg_description;
                $temp['debit'] = $value->debit;
                $temp['credit'] = $value->credit;
                
                $temp['action'] = '<a href="#" data-toggle="modal" data-target="#detailModal" data-id="'.$value->ledg_refno.'" class="getDetail">Detail</a> <a href="#" data-id="'.$value->ledg_refno.'" class="remove">Remove</a>';
                
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
            $fetch = TrLedger::select('ledg_date','ledg_refno','ledg_debit','ledg_credit','ledg_description','tr_ledger.coa_code','ms_master_coa.coa_name','tr_ledger.dept_code','ms_department.dept_name','ms_journal_type.jour_type_name')
                    ->join('ms_master_coa','tr_ledger.coa_code','=','ms_master_coa.coa_code')
                    ->join('ms_department','tr_ledger.dept_code','=','ms_department.dept_code')
                    ->join('ms_journal_type',\DB::raw('tr_ledger.jour_type_id::integer'),'=','ms_journal_type.id')
                    ->where('tr_ledger.coa_year',$coayear)
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
}
