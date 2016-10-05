<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\TrContract;

class ContractController extends Controller
{
    public function index(){
        return view('contract');
    }

    public function get(Request $request){
    	try{
            // params
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
            $count = TrContract::count();
            $fetch = TrContract::select('tr_contract.*','ms_tenant.tenan_name')
            		->join('ms_tenant',\DB::raw('ms_tenant.id::integer'),"=",\DB::raw('tr_contract.tenan_id::integer'));
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
                $temp['contr_code'] = $value->contr_code;
                $temp['contr_no'] = $value->contr_no;
                $temp['contr_startdate'] = $value->contr_startdate;
                $temp['contr_enddate'] = $value->contr_enddate;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['contr_status'] = $value->contr_status;
                $temp['contr_terminate_date'] = $value->contr_terminate_date;
                $temp['action'] = '<a href="#" data-toggle="modal" data-target="#detailModal" data-id="'.$value->id.'" class="getDetail">Detail</a>';
                
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getdetail(Request $request){
        $contractId = $request->id;
        $fetch = TrContract::select('tr_contract.*','ms_tenant.tenan_code','ms_tenant.tenan_name','ms_tenant.tenan_idno','ms_marketing_agent.mark_code','ms_marketing_agent.mark_name','ms_rental_period.renprd_name','ms_rental_period.renprd_day','ms_virtual_account.viracc_no','ms_virtual_account.viracc_name','ms_virtual_account.viracc_isactive','ms_contract_status.const_code','ms_contract_status.const_name','ms_unit.unit_code','ms_unit.unit_name','ms_unit.unit_isactive')
        ->join('ms_tenant',\DB::raw('ms_tenant.id::integer'),"=",\DB::raw('tr_contract.tenan_id::integer'))
        ->join('ms_marketing_agent',\DB::raw('ms_marketing_agent.id::integer'),"=",\DB::raw('tr_contract.mark_id::integer'))
        ->join('ms_rental_period',\DB::raw('ms_rental_period.id::integer'),"=",\DB::raw('tr_contract.renprd_id::integer'))
        ->join('ms_virtual_account',\DB::raw('ms_virtual_account.id::integer'),"=",\DB::raw('tr_contract.viracc_id::integer'))
        ->join('ms_contract_status',\DB::raw('ms_contract_status.id::integer'),"=",\DB::raw('tr_contract.const_id::integer'))
        ->join('ms_unit',\DB::raw('ms_unit.id::integer'),"=",\DB::raw('tr_contract.unit_id::integer'))->first();
        return view('modal.contract', ['fetch' => $fetch]);

    }
}
