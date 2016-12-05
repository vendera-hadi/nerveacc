<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TrInvoice;
use App\Models\TrInvoiceDetail;
use App\Models\MsCompany;
use DB;

class FakturController extends Controller
{
    public function index(){
        return view('faktur');
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
            // $fetch = TrInvoice::select('tr_invoice.*','ms_tenant.tenan_name')->join('ms_tenant',\DB::raw('tr_invoice.tenan_id::int4'),"=",\DB::raw('ms_tenant.id::int4'))->where('tr_invoice.inv_post', true);
            $fetch = TrInvoice::select('tr_invoice.*','ms_tenant.tenan_name')->join('ms_tenant',\DB::raw('tr_invoice.tenan_id::int4'),"=",\DB::raw('ms_tenant.id::int4'))->where('tr_invoice.inv_post', false);

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
                $temp['inv_number'] = $value->inv_number;
                $temp['inv_date'] = date('d/m/Y',strtotime($value->inv_date));
                $temp['inv_duedate'] = date('d/m/Y',strtotime($value->inv_duedate));
                $temp['inv_amount'] = "Rp. ".$value->inv_amount;
                $temp['inv_ppn_amount'] = "Rp. ".$value->inv_ppn_amount;
                $temp['invtp_name'] = $value->invtp_name;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['action_button'] = '<a href="/faktur/print_faktur?id='.$value->id.'" class="print-window" data-width="640" data-height="660">Print</a>';
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
            $nilai = TrInvoiceDetail::select('costd_id')->where('inv_id',$inv_id)->get();
            $cost_id = $nilai[0]->costd_is;
            $result = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.invdt_amount','tr_invoice_detail.invdt_note','tr_period_meter.prdmet_id','tr_period_meter.prd_billing_date','tr_meter.meter_start','tr_meter.meter_end','tr_meter.meter_used','tr_meter.meter_cost','ms_cost_detail.costd_name')
                ->join('tr_invoice','tr_invoice.id',"=",'tr_invoice_detail.inv_id')
                ->leftJoin('ms_cost_detail','tr_invoice_detail.costd_id',"=",'ms_cost_detail.id')
                ->leftJoin('tr_meter','tr_meter.id',"=",'tr_invoice_detail.meter_id')
                ->leftJoin('tr_period_meter','tr_period_meter.id',"=",'tr_meter.prdmet_id')
                ->where('tr_invoice_detail.inv_id',$inv_id)
                ->get();
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function print_faktur(Request $request){
        try{

            $inv_id = $request->id;
            $result = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.invdt_amount','tr_invoice_detail.invdt_note','tr_period_meter.prdmet_id','tr_period_meter.prd_billing_date','tr_meter.meter_start','tr_meter.meter_end','tr_meter.meter_used','tr_meter.meter_cost','ms_cost_detail.costd_name')
                ->join('tr_invoice','tr_invoice.id',"=",'tr_invoice_detail.inv_id')
                ->leftJoin('ms_cost_detail','tr_invoice_detail.costd_id',"=",'ms_cost_detail.id')
                ->leftJoin('tr_meter','tr_meter.id',"=",'tr_invoice_detail.meter_id')
                ->leftJoin('tr_period_meter','tr_period_meter.id',"=",'tr_meter.prdmet_id')
                ->where('tr_invoice_detail.inv_id',$inv_id)
                ->get()->toArray();

            $invoice_data = TrInvoice::find($inv_id)->with('MsTenant')->get()->first()->toArray();

            $company = MsCompany::with('MsCashbank')->first()->toArray();
            
            return view('print_faktur', array(
                'invoice_data' => $invoice_data,
                'result' => $result,
                'company' => $company
            ));
        }catch(\Exception $e){
            return view('print_faktur', array('errorMsg' => $e->getMessage()));
        } 
    }
}
