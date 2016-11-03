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

class InvoiceController extends Controller
{
    public function index(){
        return view('invoice');
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
            $count = TrInvoice::count();
            $fetch = TrInvoice::select('tr_invoice.id','tr_invoice.inv_number','tr_invoice.inv_data','tr_invoice.inv_duedate','tr_invoice.inv_amount','tr_invoice.inv_ppn','tr_invoice.inv_ppn_amount','tr_invoice.inv_post','ms_invoice_type.invtp_name','tr_contract.contr_id','ms_tenant.tenan_name')->join('ms_invoice_type',\DB::raw('ms_invoice_type.id::integer'),"=",\DB::raw('tr_invoice.invtp_code::integer'))->join('tr_contract',\DB::raw('tr_contract.id::integer'),"=",\DB::raw('tr_invoice.contr_id::integer'))->join('ms_tenant',\DB::raw('ms_tenant.id::integer'),"=",\DB::raw('tr_invoice.tenan_id::integer'));
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
                $temp['inv_number'] = $value->inv_number;
                $temp['inv_data'] = $value->inv_data;
                $temp['inv_duedate'] = $value->inv_duedate;
                $temp['inv_amount'] = $value->inv_amount;
                $temp['inv_ppn'] = $value->inv_ppn;
                $temp['inv_ppn_amount'] = $value->inv_ppn_amount;
                $temp['invtp_name'] = $value->invtp_name;
                $temp['contr_id'] = $value->contr_id;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['inv_post'] = !empty($value->costd_ismeter) ? 'yes' : 'no';
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getdetail(Request $request){
        try{
            $inv_id = $request->id;
            $nilai = TrInvoiceDetail::select('costd_is')
                    ->where('inv_id',$inv_id)
                    ->get();
            $cost_id = $nilai[0]->costd_is;
            $result = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.invdt_amount','tr_invoice_detail.invdt_note','tr_period_meter.prdmet_id','tr_period_meter.prd_billing_date','tr_meter.meter_start','tr_meter.meter_end','tr_meter.meter_used','tr_meter.meter_cost')
                ->join('tr_period_meter',\DB::raw('tr_period_meter.id::integer'),"=",\DB::raw('tr_invoice_detail.prdmet_id::integer'))
                ->join('tr_meter',\DB::raw('tr_meter.prdmet_id::integer'),"=",\DB::raw('tr_period_meter.id::integer'))
                ->join('tr_invoice',\DB::raw('tr_invoice.id::integer'),"=",\DB::raw('tr_invoice_detail.inv_id::integer'))
                ->where('tr_invoice_detail.inv_id',$inv_id)
                ->where('tr_meter.cosid_is',$cost_id)
                ->get();
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }
}
