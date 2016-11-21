<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\TrInvoice;
use App\Models\TrInvoiceDetail;
use App\Models\MsCostDetail;
use App\Models\MsInvoiceType;
use App\Models\MsTenant;
use App\Models\TrContract;
use App\Models\TrContractInvoice;
use App\Models\TrMeter;
use DB;

class AgingController extends Controller
{
    public function index(){
        return view('aging_piutang');
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
            $fetch = TrContract::select('tr_contract.id','tr_contract.contr_no','tr_contract.contr_startdate','tr_contract.contr_enddate','tr_contract.contr_status','ms_tenant.tenan_code')
            ->leftJoin('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id');
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
                    // special condition
                    if($filter->field == 'inv_post'){
                        if(strtolower($filter->value) == "yes") $filter->value = "true";
                        else $filter->value = "false";
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
                $temp['contr_no'] = $value->contr_no;
                $temp['contr_startdate'] = $value->contr_startdate;
                $temp['contr_enddate'] = $value->contr_enddate;
                $temp['contr_status'] = $value->contr_status;
                $temp['tenan_code'] = $value->tenan_code;
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getdetail(Request $request){
        try{
            $id = $request->id;
            $result = TrInvoice::select('tr_invoice.*','ms_tenant.tenan_code','ms_invoice_type.invtp_name',DB::raw("current_date::date - inv_duedate::date AS ag"),DB::raw("current_date::date - inv_duedate::date AS ag1"),DB::raw("current_date::date - inv_duedate::date AS ag2"),DB::raw("current_date::date - inv_duedate::date AS ag3"),DB::raw("current_date::date - inv_duedate::date AS ag4"))
                ->leftJoin('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->leftJoin('ms_invoice_type','ms_invoice_type.invtp_code',"=",'tr_invoice.invtp_code')
                ->where('tr_invoice.contr_id',$id)
                ->get();
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function tes(Request $request){
        $result = TrContract::select('tr_contract.id','tr_contract.contr_no','tr_contract.contr_startdate','tr_contract.contr_enddate','tr_contract.contr_status','ms_tenant.tenan_code')
            ->leftJoin('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id')
            ->get();
        echo $result;
    }

    public function tes2(Request $request){
        $id=52;
        $result = TrInvoice::select('tr_invoice.*','ms_tenant.tenan_code','ms_invoice_type.invtp_name',DB::raw("current_date::date - inv_duedate::date AS interval"))
            ->leftJoin('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
            ->leftJoin('ms_invoice_type','ms_invoice_type.invtp_code',"=",'tr_invoice.invtp_code')
            ->where('tr_invoice.contr_id',$id)
            ->get();
        echo $result;
    }

}
