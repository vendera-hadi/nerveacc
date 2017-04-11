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
use Excel;

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
            $count = TrInvoice::select('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name',
                    DB::raw("CONCAT(ms_tenant.tenan_name,' - ',ms_unit.unit_code) AS gabung"),
                    DB::raw("SUM(inv_outstanding) AS total"),
                    DB::raw("SUM((CASE WHEN tr_invoice.inv_date::date = current_date::date THEN tr_invoice.inv_outstanding ELSE 0 END)) AS current"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > 0 AND (current_date::date - inv_date::date)<=30 THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > 30 AND (current_date::date - inv_date::date)<=60 THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >60 AND (current_date::date - inv_date::date)<=90 THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > 90 THEN tr_invoice.inv_outstanding ELSE 0 END)) AS agl180"))
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                ->where('tr_invoice.inv_outstanding','>',0)
                ->groupBy('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name')
                ->count();
            $fetch = TrInvoice::select('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name',
                    DB::raw("CONCAT(ms_tenant.tenan_name,' - ',ms_unit.unit_code) AS gabung"),
                    DB::raw("SUM(inv_outstanding) AS total"),
                    DB::raw("SUM((CASE WHEN tr_invoice.inv_date::date = current_date::date THEN tr_invoice.inv_outstanding ELSE 0 END)) AS currents"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > 0 AND (current_date::date - inv_date::date)<=30 THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > 30 AND (current_date::date - inv_date::date)<=60 THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >60 AND (current_date::date - inv_date::date)<=90 THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > 90 THEN tr_invoice.inv_outstanding ELSE 0 END)) AS agl180"))
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                ->where('tr_invoice.inv_outstanding','>',0)
                ->groupBy('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name');
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
                    if($filter->field == 'unit_isactive'){
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
                $temp['id'] = $value->tenan_id;
                $temp['gabung'] = $value->gabung;
                $temp['unit_code'] = $value->unit_code;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['total'] ="Rp. ".number_format($value->total);
                $temp['currents'] = "Rp. ".number_format($value->currents);
                $temp['ag30'] = "Rp. ".number_format($value->ag30);
                $temp['ag60'] = "Rp. ".number_format($value->ag60);
                $temp['ag90'] = "Rp. ".number_format($value->ag90);
                // $temp['ag180'] = "Rp. ".number_format($value->ag180);
                $temp['agl180'] = "Rp. ".number_format($value->agl180);
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
            $result = TrInvoice::select('tr_invoice.*','ms_invoice_type.invtp_name')
                ->leftJoin('ms_invoice_type','ms_invoice_type.id',"=",'tr_invoice.invtp_id')
                ->where('tr_invoice.tenan_id',$id)
                ->where('inv_outstanding','>',0)
                ->get();
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function downloadAgingExcel()
    {
        $data_ori = TrInvoice::select('ms_unit.unit_code AS Unit','ms_tenant.tenan_name AS Tenant',
                    DB::raw("SUM(inv_outstanding) AS Total"),
                    DB::raw("SUM((CASE WHEN tr_invoice.inv_date::date = current_date::date THEN tr_invoice.inv_outstanding ELSE 0 END)) AS Current"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > 0 AND (current_date::date - inv_date::date)<=30 THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > 30 AND (current_date::date - inv_date::date)<=60 THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >60 AND (current_date::date - inv_date::date)<=90 THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > 90 THEN tr_invoice.inv_outstanding ELSE 0 END)) AS agl180"))
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                ->where('tr_invoice.inv_outstanding','>',0)
                ->groupBy('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name')
                ->get()->toArray();
        $data = array();
        for($i=0; $i<count($data_ori); $i++){
            $data[$i]=array(
                'Unit Code'=>$data_ori[$i]['Unit'],
                'Nama Tenant'=>$data_ori[$i]['Tenant'],
                'Total'=>number_format($data_ori[$i]['total']),
                'Current'=>number_format($data_ori[$i]['current']),
                '1-30 Days'=>number_format($data_ori[$i]['ag30']),
                '31-60 Days'=>number_format($data_ori[$i]['ag60']),
                '61-90 Days'=>number_format($data_ori[$i]['ag90']),
                '90 Days'=>number_format($data_ori[$i]['agl180']));
        }
        $tp = 'xls';
        return Excel::create('report_aging', function($excel) use ($data) {
            $excel->sheet('mySheet', function($sheet) use ($data)
            {
                $sheet->fromArray($data);
            });
        })->download($tp);
    }
    public function test()
    {
        $test = TrInvoice::select('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name','tr_invoice.inv_date',
                    DB::raw("CONCAT(ms_tenant.tenan_name,' - ',ms_unit.unit_code) AS gabung"),
                    DB::raw("SUM(inv_outstanding) AS total"),
                    DB::raw("(CASE WHEN tr_invoice.inv_date::date = current_date::date THEN tr_invoice.inv_outstanding ELSE 0 END) AS current"),
                    DB::raw("(CASE WHEN (current_date::date - inv_date::date) > 0 AND (current_date::date - inv_date::date)<=30 THEN tr_invoice.inv_outstanding ELSE 0 END) AS ag30"),
                    DB::raw("(CASE WHEN (current_date::date - inv_date::date) > 30 AND (current_date::date - inv_date::date)<=60 THEN tr_invoice.inv_outstanding ELSE 0 END) AS ag60"),
                    DB::raw("(CASE WHEN (current_date::date - inv_date::date) >60 AND (current_date::date - inv_date::date)<=90 THEN tr_invoice.inv_outstanding ELSE 0 END) AS ag90"),
                    DB::raw("(CASE WHEN (current_date::date - inv_date::date) > 90 AND (current_date::date - inv_date::date)<=180 THEN tr_invoice.inv_outstanding ELSE 0 END) AS ag190"),
                    DB::raw("(CASE WHEN (current_date::date - inv_date::date) > 180 THEN tr_invoice.inv_outstanding ELSE 0 END) AS agl180"))
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                ->where('tr_invoice.inv_outstanding','>',0)
                ->groupBy('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name','tr_invoice.inv_date','tr_invoice.inv_outstanding')
                ->toSql();
        echo $test;
    }
}
