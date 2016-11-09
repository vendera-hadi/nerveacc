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
            $fetch = TrInvoice::select('tr_invoice.id','tr_invoice.inv_number','tr_invoice.inv_date','tr_invoice.inv_duedate','tr_invoice.inv_amount','tr_invoice.inv_ppn','tr_invoice.inv_ppn_amount','tr_invoice.inv_post','ms_invoice_type.invtp_name','tr_contract.contr_id','ms_tenant.tenan_name')
                    ->join('ms_invoice_type','ms_invoice_type.invtp_code',"=",'tr_invoice.invtp_code')
                    ->join('tr_contract',\DB::raw('tr_contract.id::integer'),"=",\DB::raw('tr_invoice.contr_id::integer'))
                    ->join('ms_tenant',\DB::raw('ms_tenant.id::integer'),"=",\DB::raw('tr_invoice.tenan_id::integer'));
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
            $nilai = TrInvoiceDetail::select('costd_is')->where('inv_id',$inv_id)->get();
            $cost_id = $nilai[0]->costd_is;
            $result = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.invdt_amount','tr_invoice_detail.invdt_note','tr_period_meter.prdmet_id','tr_period_meter.prd_billing_date','tr_meter.meter_start','tr_meter.meter_end','tr_meter.meter_used','tr_meter.meter_cost')
                ->join('tr_invoice',\DB::raw('tr_invoice.id::integer'),"=",\DB::raw('tr_invoice_detail.inv_id::integer'))
                ->leftJoin('tr_meter','tr_meter.id',"=",'tr_invoice_detail.meter_id')
                ->leftJoin('tr_period_meter','tr_period_meter.id',"=",'tr_meter.prdmet_id')
                ->where('tr_invoice_detail.inv_id',$inv_id)
                ->get();
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function generateInvoice(Request $request){
        return view('generateinvoice');
    }

    public function postGenerateInvoice(Request $request){
        $month = $request->input('month');
        $month = str_pad($month, 2, 0, STR_PAD_LEFT);
        $year = $request->input('year');
        $tempTimeStart = implode('-', [$year,$month,'01']);
        $tempTimeEnd = date("Y-m-t", strtotime($tempTimeStart));

        // cari di contract where date between start & end date
        // $availableContract = TrContract::whereNull('contr_terminate_date')->where(DB::raw("'".$tempTimeStart."'"),'>=','contr_startdate')->where(DB::raw("'".$tempTimeEnd."'"),'<=','contr_enddate')->get();
        $availableContract = DB::select('select * from "tr_contract" where "contr_terminate_date" is null and \''.$tempTimeStart.'\' >= "contr_startdate" and \''.$tempTimeEnd.'\' <= "contr_enddate" ');
        
        $totalavContract = count($availableContract);
        if($totalavContract == 0) return '<h4><strong>There is no contract available</strong></h4>';
        // dari semua yg available check invoice yg sudah exist
        // $invoiceExists = TrInvoice::select('tr_contract.id')->join('tr_contract','tr_invoice.contr_id','=','tr_contract.id')->whereNull('tr_contract.contr_terminate_date')->where('tr_contract.contr_startdate','>=',$tempTimeStart)->where('tr_contract.contr_enddate','<=',$tempTimeEnd)->toSql();
        $invoiceExists = DB::select('select "tr_contract"."id" from "tr_invoice" inner join "tr_contract" on "tr_invoice"."contr_id" = "tr_contract"."id" where "tr_contract"."contr_terminate_date" is null and \''.$tempTimeStart.'\' >= "tr_contract"."contr_startdate" and \''.$tempTimeEnd.'\' <= "tr_contract"."contr_enddate" group by "tr_contract"."id" ');
        
        // echo 'total available : '.$totalavContract.' , total exist : '.$invoiceExists->count(); die();
        if(count($invoiceExists) >= $totalavContract){
            return '<h4><strong>All of Invoices this month is already exist in Invoice List</strong></h4>';
        }

        // contract pengecualian
        $invExceptions = [];
        if(count($invoiceExists) > 0){
            foreach ($invoiceExists as $key => $val) {
                array_push($invExceptions, $val->id);
            }
        }

        // looping contract yg bs digenerate PER CONTRACT
        foreach ($availableContract as $key => $contract) {
            if(!in_array($contract->id, $invExceptions)){
                // generate invoice, tentuin berapa invoice yg digenerate PER CONTRACT group by Invoice type
                $totalInv = TrContractInvoice::select('tr_contract_invoice.contr_id','tr_contract_invoice.invtp_code')->join('ms_cost_detail','tr_contract_invoice.costd_is','=','ms_cost_detail.id')
                            ->where('tr_contract_invoice.contr_id',$contract->id)->groupBy('tr_contract_invoice.invtp_code','tr_contract_invoice.contr_id')->get();          
                
                foreach ($totalInv as $key => $ctrInv) {
                    $details = TrContractInvoice::select('tr_contract_invoice.*','ms_cost_detail.*','ms_cost_detail.id as costd_id','tr_contract.tenan_id','ms_invoice_type.invtp_prefix','ms_invoice_type.invtp_code')
                            ->join('ms_cost_detail','tr_contract_invoice.costd_is','=','ms_cost_detail.id')
                            ->join('tr_contract',DB::raw('tr_contract_invoice.contr_id::integer'),'=','tr_contract.id')
                            ->join('ms_invoice_type','tr_contract_invoice.invtp_code','=','ms_invoice_type.invtp_code')
                            ->where('tr_contract_invoice.contr_id',$ctrInv->contr_id)->where('tr_contract_invoice.invtp_code',$ctrInv->invtp_code)->get();
                    
                    $invDetail = [];
                    // Looping per Invoice yg sdh di grouping
                    foreach ($details as $key2 => $value) {
                        // echo "Invoice ".$key." , detail ".$key2."<br><br>";
                        // KALAU is meter true, hitung cost meteran 
                        if(!empty($value->costd_ismeter)){
                            $totalPay = 0;
                            // get harga meteran selama periode bulan ini
                            $meter = TrMeter::select('tr_meter.id as tr_meter_id','tr_meter.*','tr_period_meter.*','ms_cost_detail.costd_name','ms_cost_detail.id as costd_is')
                                ->join('tr_period_meter','tr_meter.prdmet_id','=','tr_period_meter.id')
                                ->join('ms_cost_detail','tr_meter.costd_is','=','ms_cost_detail.id')
                                ->where('tr_meter.contr_id', $contract->id)->where('tr_meter.costd_is',$value->costd_id)
                                ->where('tr_period_meter.prdmet_start_date','>=',$tempTimeStart)->where('tr_period_meter.prdmet_end_date','<=',$tempTimeEnd)->first();
                            
                            if(empty($meter)){ 
                                echo "<strong>Contract ID #".$ctrInv->contr_id." Meter ID is not inputed</strong><br>";
                            }else{
                                // note masi minus rumus
                                $note = $meter->costd_name." Per ".date('d/m/Y',strtotime($meter->prdmet_start_date))." - ".date('d/m/Y',strtotime($meter->prdmet_end_date));   
                                // rumus masih standar, rate * meter used + burden + admin
                                // $amount = ($meter->meter_used * $meter->meter_cost) + $meter->meter_burden + $meter->meter_admin;
                                $amount = $meter->meter_cost;
                                $invDetail[] = [
                                    'invdt_amount' => $amount,
                                    'invdt_note' => $note,
                                    'costd_is' => $meter->costd_is,
                                    'meter_id' => $meter->tr_meter_id
                                ];
                                $totalPay+=$amount;
                            }  
                        }else{
                            // KALAU NOT USING METER
                            $totalPay = 0;
                            $note = $value->costd_name." Per ".date('F',strtotime($tempTimeStart))." ".$year;   
                            // rumus cost + burden + admin
                            $amount = $value->costd_rate + $value->costd_burden + $value->costd_admin;
                            $invDetail[] = [
                                'invdt_amount' => $amount,
                                'invdt_note' => $note,
                                'costd_is' => $value->costd_id
                            ];
                            $totalPay+=$amount;
                        }

                    }

                    DB::transaction(function () use($year, $month, $value, $totalPay, $contract, $invDetail){
                        // insert invoice
                        // get last prefix
                        $lastInvoiceofMonth = TrInvoice::select('inv_number')->where('inv_number','like',$value->invtp_prefix.'-'.substr($year, -2).$month.'-%')->orderBy('id','desc')->first();
                        if($lastInvoiceofMonth){
                            $lastPrefix = explode('-', $lastInvoiceofMonth->inv_number);
                            $lastPrefix = (int) $lastPrefix[2];               
                        }else{
                            $lastPrefix = 0;
                        }
                        $newPrefix = $lastPrefix + 1;
                        $newPrefix = str_pad($newPrefix, 4, 0, STR_PAD_LEFT);

                        $now = date('Y-m-d');
                        $duedate = date('Y-m-d', strtotime('+1 month'));
                        $inv = [
                            'tenan_id' => $value->tenan_id,
                            'inv_number' => $value->invtp_prefix."-".substr($year, -2).$month."-".$newPrefix,
                            'inv_date' => $now,
                            'inv_duedate' => $duedate,
                            'inv_amount' => $totalPay,
                            'inv_ppn' => 0.1,
                            'inv_ppn_amount' => $totalPay * 0.1,
                            'inv_post' => 0,
                            'invtp_code' => $value->invtp_code,
                            'contr_id' => $contract->id
                        ];
                        $insertInvoice = TrInvoice::create($inv);

                        // insert detail
                        foreach($invDetail as $indt){
                            $indt['inv_id'] = $insertInvoice->id;
                            TrInvoiceDetail::create($indt);
                        }
                    });
                }
                                        
                        
            }
        }

        return '<h3>Generate success, Please Check Invoice List <a href="'.url('invoice').'">Here</a></h3>';
    }

}
