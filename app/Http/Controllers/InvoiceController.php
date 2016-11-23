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
use App\Models\TrPeriodMeter;
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
            $fetch = TrInvoice::select('tr_invoice.id','tr_invoice.inv_number','tr_invoice.inv_date','tr_invoice.inv_duedate','tr_invoice.inv_amount','tr_invoice.inv_ppn','tr_invoice.inv_ppn_amount','tr_invoice.inv_post','ms_invoice_type.invtp_name','ms_tenant.tenan_name')
                    ->join('ms_invoice_type','ms_invoice_type.invtp_code',"=",'tr_invoice.invtp_code')
                    ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
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
        // bulan dikurang 1 karna generate invoice utk bulan kemarin
        if($month == 1) $month = 12;
        else $month = $month - 1;
        $month = str_pad($month, 2, 0, STR_PAD_LEFT);
        $year = $request->input('year');
        $tempTimeStart = implode('-', [$year,$month,'01']);
        $tempTimeEnd = date("Y-m-t", strtotime($tempTimeStart));
        $maxTime = date('Y-m-d',strtotime("first day of previous month"));
        // invoice dpt di generate paling lama bulan sekarang, generate utk bulan kmaren
        if($tempTimeStart > $maxTime) return response()->json(['errorMsg' => 'Invoice can\'t be generated more than this month']);
        
        // cari di contract where date between start & end date
        // $availableContract = TrContract::whereNull('contr_terminate_date')->where(DB::raw("'".$tempTimeStart."'"),'>=','contr_startdate')->where(DB::raw("'".$tempTimeEnd."'"),'<=','contr_enddate')->get();
        $availableContract = DB::select('select * from "tr_contract" where "contr_iscancel" = false and "contr_status" != \'closed\' and \''.$tempTimeStart.'\' >= "contr_startdate" and \''.$tempTimeStart.'\' <= "contr_enddate" and "contr_status" = \'confirmed\' ');

        $totalavContract = count($availableContract);
        if($totalavContract == 0) return '<h4><strong>There is no contract available</strong></h4>';
        
        // dari semua yg available check invoice yg sudah exist di BULAN YG DIFILTER 
        // $invoiceExists = TrInvoice::select('tr_contract.id')->join('tr_contract','tr_invoice.contr_id','=','tr_contract.id')->whereNull('tr_contract.contr_terminate_date')->where('tr_contract.contr_startdate','>=',$tempTimeStart)->where('tr_contract.contr_enddate','<=',$tempTimeEnd)->toSql();
        $invoiceExists = DB::select('select "tr_contract"."id" from "tr_invoice" inner join "tr_contract" on "tr_invoice"."contr_id" = "tr_contract"."id" where "tr_contract"."contr_iscancel" = false and "tr_contract"."contr_status" != \'closed\' and \''.$tempTimeStart.'\' >= "tr_contract"."contr_startdate" and \''.$tempTimeStart.'\' <= "tr_contract"."contr_enddate" and EXTRACT(MONTH FROM "tr_invoice"."inv_date") = '.$month.' group by "tr_contract"."id" ');
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

        $invoiceGenerated = 0;
        $totalInvoice = 0;
        $countInvoice = 0;
        // looping contract yg bs digenerate PER CONTRACT
        foreach ($availableContract as $key => $contract) {
            if(!in_array($contract->id, $invExceptions)){
                // generate invoice, tentuin berapa invoice yg digenerate PER CONTRACT group by Invoice type
                $totalInv = TrContractInvoice::select('tr_contract_invoice.contr_id','tr_contract_invoice.invtp_code')->join('ms_cost_detail','tr_contract_invoice.costd_is','=','ms_cost_detail.id')
                            ->where('tr_contract_invoice.contr_id',$contract->id)->groupBy('tr_contract_invoice.invtp_code','tr_contract_invoice.contr_id')->get();          
                $totalInvoice+= count($totalInv);
                foreach ($totalInv as $key => $ctrInv) {
                    // echo "Contract #".$ctrInv->contr_id."<br>";
                    $countInvoice+=1;
                    $details = TrContractInvoice::select('tr_contract_invoice.*','ms_cost_detail.*','ms_cost_item.is_service_charge','ms_unit.unit_sqrt','ms_cost_detail.id as costd_id','tr_contract.tenan_id','tr_contract.contr_code','tr_contract.contr_enddate','tr_contract.contr_terminate_date','ms_invoice_type.invtp_prefix','ms_invoice_type.invtp_code')
                            ->join('ms_cost_detail','tr_contract_invoice.costd_is','=','ms_cost_detail.id')
                            ->join('ms_cost_item','ms_cost_detail.cost_id','=','ms_cost_item.id')
                            ->join('tr_contract',DB::raw('tr_contract_invoice.contr_id::integer'),'=','tr_contract.id')
                            ->join('ms_unit','ms_unit.id','=','tr_contract.unit_id')
                            ->join('ms_invoice_type','tr_contract_invoice.invtp_code','=','ms_invoice_type.invtp_code')
                            ->where('tr_contract_invoice.contr_id',$ctrInv->contr_id)->where('tr_contract_invoice.invtp_code',$ctrInv->invtp_code)->get();
                    
                    $invDetail = [];
                    $insertFlag = true;
                    // echo "<br>Invoice #".$countInvoice."<br>";
                    // Looping per Invoice yg sdh di grouping
                    foreach ($details as $key2 => $value) {
                        // echo "Invoice ".$key." , detail ".$key2."<br><br>";
                        // KALAU is meter true, hitung cost meteran 
                        if(!empty($value->costd_ismeter)){
                            $totalPay = 0;
                            // get harga meteran selama periode bulan ini
                            $lastPeriodMeterofMonth = TrPeriodMeter::where('prdmet_start_date','>=',$tempTimeStart)->where('prdmet_end_date','<=',$tempTimeEnd)->where('status',1)->orderBy('id','desc')->first();
                            if($lastPeriodMeterofMonth){
                                $meter = TrMeter::select('tr_meter.id as tr_meter_id','tr_meter.*','tr_period_meter.*','ms_cost_detail.costd_name','ms_cost_detail.id as costd_is')
                                    ->join('tr_period_meter','tr_meter.prdmet_id','=','tr_period_meter.id')
                                    ->join('ms_cost_detail','tr_meter.costd_is','=','ms_cost_detail.id')
                                    ->where('tr_meter.contr_id', $contract->id)->where('tr_meter.costd_is',$value->costd_id)
                                    ->where('tr_period_meter.id',$lastPeriodMeterofMonth->id)->first();
                                // echo "<br>Last Prd ".$lastPeriodMeterofMonth->id."<br>";
                                // echo "<br>Meter<br>".$meter."<br>";
                                if(empty($meter)){ 
                                    echo "Contract Code <strong>".$value->contr_code."</strong> Cost Item <strong>".$value->costd_name."</strong>, Meter ID is not inputed yet<br>";
                                    $insertFlag = false;
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
                                echo "<br>Meter Input for ".date('F Y',strtotime($tempTimeStart)).' was not inputed yet. Go to <a href="'.url('period_meter').'">Meter Input</a> and create Period then Input Meter of this particular month<br>';
                            }  

                        }else{
                            // YG NOT USING METER (AGK TRICKY), MUSTI CEK STATUS KONTRAK, KALO END GA SAMPE AKHIR BULAN MUSTI PRO RATE
                            // echo "<br>not using meter<br>";
                            $totalPay = 0;
                            if($value->is_service_charge){
                                // pake rumus service charge
                                $note = $value->costd_name." (".(int)$value->unit_sqrt." m2) Per ".date('F',strtotime($tempTimeStart))." ".$year;
                                // cek akhir period dari kontrak dia
                                $totalDayCertainMonth = date('t',strtotime($tempTimeEnd));
                                if(!empty($value->contr_terminate_date) && ($tempTimeEnd > $value->contr_terminate_date)){
                                    $dayUsed = date('d',strtotime($value->contr_terminate_date));
                                    // LOGIKA PRO RATE
                                    $amount = ($dayUsed / $totalDayCertainMonth * $value->costd_rate * $value->unit_sqrt) + $value->costd_burden + $value->costd_admin;
                                    // echo "<br>PRO RATE BY TERMINATE DATE<br>";
                                }else if($tempTimeEnd > $value->contr_enddate){
                                    $dayUsed = date('d',strtotime($value->contr_enddate));
                                    // LOGIKA PRO RATE
                                    $amount = ($dayUsed / $totalDayCertainMonth * $value->costd_rate * $value->unit_sqrt) + $value->costd_burden + $value->costd_admin;
                                    // echo "<br>PRO RATE END CONTRACT<br>";
                                }else{
                                    // HITUNG FULL
                                    // echo "<br>FULL RATE<br>";
                                    $amount = ($value->costd_rate * $value->unit_sqrt) + $value->costd_burden + $value->costd_admin;    
                                }

                            }else{
                                $note = $value->costd_name." Per ".date('F',strtotime($tempTimeStart))." ".$year;   
                                // rumus cost + burden + admin
                                $amount = $value->costd_rate + $value->costd_burden + $value->costd_admin;
                            }
                            $invDetail[] = [
                                'invdt_amount' => $amount,
                                'invdt_note' => $note,
                                'costd_is' => $value->costd_id
                            ];
                            $totalPay+=$amount;
                        }

                    }
                    // $insertFlag = false;
                    if($insertFlag){
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
                        $invoiceGenerated++;
                    }  
                }
                                        
                        
            }
        }

        return '<h3>'.$invoiceGenerated.' of '.$totalInvoice.' Invoices Generated, Please Check Invoice List <a href="'.url('invoice').'">Here</a></h3>';
    }

}
