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
use App\Models\MsCompany;
use App\Models\MsCostItem;
use DB;
use PDF;

class InvoiceController extends Controller
{
    public function index(){
        $data['inv_type'] = MsInvoiceType::all();
        return view('invoice',$data);
    }

    public function get(Request $request){
        try{
            // params
            $keyword = @$request->q;
            $invtype = @$request->invtype;
            $datefrom = @$request->datefrom;
            $dateto = @$request->dateto;  

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
            $fetch = TrInvoice::select('tr_invoice.id','tr_invoice.inv_number','tr_invoice.inv_date','tr_invoice.inv_duedate','tr_invoice.inv_amount','tr_invoice.inv_outstanding','tr_invoice.inv_ppn','tr_invoice.inv_ppn_amount','tr_invoice.inv_post','ms_invoice_type.invtp_name','ms_tenant.tenan_name','tr_contract.contr_no', 'ms_unit.unit_name','ms_floor.floor_name')
                    ->join('ms_invoice_type','ms_invoice_type.id',"=",'tr_invoice.invtp_id')
                    ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                    ->join('ms_unit','tr_contract.unit_id',"=",'ms_unit.id')
                    ->join('ms_floor','ms_unit.floor_id',"=",'ms_floor.id')
                    ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id');
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
            // jika ada keyword
            if(!empty($keyword)) $fetch = $fetch->where(function($query) use($keyword){
                                        $query->where(\DB::raw('lower(trim("contr_no"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("inv_number"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("tenan_name"::varchar))'),'like','%'.$keyword.'%');
                                    });
            // jika ada inv type
            if(!empty($invtype)) $fetch = $fetch->where('tr_invoice.invtp_id',$invtype);
            // jika ada date from
            if(!empty($datefrom)) $fetch = $fetch->where('tr_invoice.inv_faktur_date','>=',$datefrom);
            // jika ada date to
            if(!empty($dateto)) $fetch = $fetch->where('tr_invoice.inv_faktur_date','<=',$dateto);

            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['contr_no'] = $value->contr_no;
                $temp['unit'] = $value->unit_name." (".$value->floor_name.")";
                $temp['inv_number'] = $value->inv_number;
                $temp['inv_date'] = date('d/m/Y',strtotime($value->inv_date));
                $temp['inv_duedate'] = date('d/m/Y',strtotime($value->inv_duedate));
                $temp['inv_amount'] = "Rp. ".$value->inv_amount;
                $temp['inv_ppn'] = $value->inv_ppn * 100;
                $temp['inv_ppn'] = $temp['inv_ppn']."%";
                $temp['inv_ppn_amount'] = "Rp. ".$value->inv_ppn_amount;
                $temp['inv_outstanding'] = !empty((int)$value->inv_outstanding) ? "Rp. ".$value->inv_outstanding : "Lunas";
                $temp['invtp_name'] = $value->invtp_name;
                $temp['contr_id'] = $value->contr_id;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['inv_post'] = !empty($value->costd_ismeter) ? 'yes' : 'no';
                $temp['action_button'] = '<a href="/invoice/print_faktur?id='.$value->id.'" class="print-window" data-width="640" data-height="660">Print</a> | <a href="/invoice/print_faktur?id='.$value->id.'&type=pdf">PDF</a>';
                // $temp['daysLeft']
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
            $result = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.invdt_amount','tr_invoice_detail.invdt_note','tr_period_meter.prdmet_id','tr_period_meter.prd_billing_date','tr_meter.meter_start','tr_meter.meter_end','tr_meter.meter_used','tr_meter.meter_cost','ms_cost_detail.costd_name','ms_cost_detail.costd_unit')
                ->join('tr_invoice','tr_invoice.id',"=",'tr_invoice_detail.inv_id')
                ->leftJoin('ms_cost_detail','tr_invoice_detail.costd_id',"=",'ms_cost_detail.id')
                ->leftJoin('tr_meter','tr_meter.id',"=",'tr_invoice_detail.meter_id')
                ->leftJoin('tr_period_meter','tr_period_meter.id',"=",'tr_meter.prdmet_id')
                ->where('tr_invoice_detail.inv_id',$inv_id)
                ->get();
            foreach ($result as $key => $value) {
                $result[$key]->invdt_amount = "Rp. ".$value->invdt_amount;
                $result[$key]->meter_start = (int)$value->meter_start;
                $result[$key]->meter_end = (int)$value->meter_end;
                $result[$key]->meter_used = !empty($value->meter_used) ? (int)$value->meter_used." ".$value->costd_unit : (int)$value->meter_used;
            }
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
        $companyData = MsCompany::first();
        $stampData = MsCostItem::where('cost_code','STAMP')->first();
        // if(!empty($stampData)) $stampCoa = $stampData->cost_coa_code;
        // else $stampCoa = 21400;

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
                $totalInv = TrContractInvoice::select('tr_contract_invoice.contr_id','tr_contract_invoice.invtp_id')->join('ms_cost_detail','tr_contract_invoice.costd_id','=','ms_cost_detail.id')
                            ->where('tr_contract_invoice.contr_id',$contract->id)->groupBy('tr_contract_invoice.invtp_id','tr_contract_invoice.contr_id')->get();          
                $totalInvoice+= count($totalInv);
                foreach ($totalInv as $key => $ctrInv) {
                    // echo "Contract #".$ctrInv->contr_id."<br>";
                    $countInvoice+=1;
                    // AMBIL CONTRACT INVOICE PER INVOICE TYPE
                    $details = TrContractInvoice::select('tr_contract_invoice.*','ms_cost_detail.*','ms_cost_item.is_service_charge','ms_cost_item.is_sinking_fund','ms_cost_item.is_insurance','ms_unit.unit_sqrt','ms_cost_detail.id as costd_id','tr_contract.tenan_id','tr_contract.unit_id','tr_contract.contr_code','tr_contract.contr_enddate','tr_contract.contr_terminate_date','ms_invoice_type.invtp_prefix','ms_invoice_type.id as invtp_id')
                            ->join('ms_cost_detail','tr_contract_invoice.costd_id','=','ms_cost_detail.id')
                            ->join('ms_cost_item','ms_cost_detail.cost_id','=','ms_cost_item.id')
                            ->join('tr_contract',DB::raw('tr_contract_invoice.contr_id::integer'),'=','tr_contract.id')
                            ->join('ms_unit','ms_unit.id','=','tr_contract.unit_id')
                            ->join('ms_invoice_type','tr_contract_invoice.invtp_id','=','ms_invoice_type.id')
                            ->where('tr_contract_invoice.contr_id',$ctrInv->contr_id)->where('tr_contract_invoice.invtp_id',$ctrInv->invtp_id)->get();
                    
                    $invDetail = [];
                    $insertFlag = true;
                    // echo "<br>Invoice #".$countInvoice."<br>";
                    // Looping per Invoice yg sdh di grouping (CONTRACT INVOICE PER INV TYPE)
                    foreach ($details as $key2 => $value) {
                        // echo "Invoice ".$key." , detail ".$key2."<br><br>";
                        // LAST INV DATE
                        if(!empty($value->continv_next_inv)) $last_inv_date = $value->continv_next_inv;
                        else $last_inv_date = $tempTimeStart;
                        // GENERATE KALAU PERIODE LAST INV UDA LEWAT
                        if($tempTimeStart >= $last_inv_date){
                            // KALAU is meter true, hitung cost meteran 
                            if(!empty($value->costd_ismeter)){
                                // echo 'meter<br>';
                                $totalPay = 0;
                                // get harga meteran selama periode bulan ini
                                $lastPeriodMeterofMonth = TrPeriodMeter::where('prdmet_start_date','>=',$tempTimeStart)->where('prdmet_end_date','<=',$tempTimeEnd)->where('status',1)->orderBy('id','desc')->first();
                                if($lastPeriodMeterofMonth){
                                    $meter = TrMeter::select('tr_meter.id as tr_meter_id','tr_meter.*','tr_period_meter.*','ms_cost_detail.costd_name','ms_cost_detail.costd_unit','ms_cost_detail.id as costd_id')
                                        ->join('tr_period_meter','tr_meter.prdmet_id','=','tr_period_meter.id')
                                        ->join('ms_cost_detail','tr_meter.costd_id','=','ms_cost_detail.id')
                                        ->where('tr_meter.contr_id', $contract->id)->where('tr_meter.costd_id',$value->costd_id)
                                        ->where('tr_period_meter.id',$lastPeriodMeterofMonth->id)->first();
                                    // echo "<br>Last Prd ".$lastPeriodMeterofMonth->id."<br>";
                                    // echo "<br>Meter<br>".$meter."<br>";
                                    if(empty($meter)){ 
                                        echo "<br><b>Contract #".$contract->contr_no."</b><br>Contract Code <strong>".$value->contr_code."</strong> Cost Item <strong>".$value->costd_name."</strong>, Meter ID is not inputed yet<br>";
                                        $insertFlag = false;
                                    }else{
                                        // note masi minus rumus
                                        $note = $meter->costd_name." Consumption : ".(int)$meter->meter_used." ".$meter->costd_unit." Per ".date('d/m/Y',strtotime($meter->prdmet_start_date))." - ".date('d/m/Y',strtotime($meter->prdmet_end_date));   
                                        // rumus masih standar, rate * meter used + burden + admin
                                        // $amount = ($meter->meter_used * $meter->meter_cost) + $meter->meter_burden + $meter->meter_admin;
                                        $amount = $meter->meter_cost;
                                        $invDetail[] = [
                                            'invdt_amount' => $amount,
                                            'invdt_note' => $note,
                                            'continv_start_inv' => $tempTimeStart,
                                            'continv_next_inv' => date('Y-m-d',strtotime($tempTimeStart." +".$value->continv_period." months")),
                                            'costd_id' => $meter->costd_id,
                                            'meter_id' => $meter->tr_meter_id
                                        ];
                                        $totalPay+=$amount;
                                    }
                                }else{
                                    echo "<br><b>Contract #".$contract->contr_no."</b><br> Meter Input for ".date('F Y',strtotime($tempTimeStart)).' was not inputed yet. Go to <a href="'.url('period_meter').'">Meter Input</a> and create Period then Input Meter of this particular month<br>';
                                    $insertFlag = false;
                                }  

                            }
                            else{
                                // echo 'non meter<br>';
                                // YG NOT USING METER, GENERATE FULLRATE AJA
                                $totalPay = 0;
                                if(!empty($value->contr_terminate_date) && ($tempTimeEnd > $value->contr_terminate_date)){
                                    // JIKA CONTRACT TERMINATE DATE BERAKHIR BULAN INI
                                    echo "<br><b>Contract #".$contract->contr_no."</b><br> terminated at ".date('d/m/Y',strtotime($value->contr_terminate_date)).", Please CLOSE this Contract <a href=\"".route('contract.unclosed')."\">Here";
                                    $insertFlag = false;
                                }else if($tempTimeEnd > $value->contr_enddate){
                                    // JIKA CONTRACT SUDAH BERAKHIR
                                    echo "<br><b>Contract #".$contract->contr_no."</b><br> expired at ".date('d/m/Y',strtotime($value->contr_enddate)).", Please CLOSE this Contract <a href=\"".route('contract.unclosed')."\">Here";
                                    $insertFlag = false;
                                }else{
                                    // JENIS COST ITEM
                                    if($value->is_service_charge){
                                        // SERVICE CHARGE
                                        $note = $value->costd_name." (Rp.".number_format($value->costd_rate,2)." x ".(int)$value->unit_sqrt." sqrt x ".$value->continv_period." bulan) Periode ".date('d-m-Y',strtotime($tempTimeStart))." s/d ".date('d-m-Y',strtotime($tempTimeStart." +".$value->continv_period." months"));
                                        $amount = ((int)$value->unit_sqrt * $value->costd_rate) + $value->costd_burden + $value->costd_admin;
                                    }else if($value->is_sinking_fund){
                                        // SINKING FUND (DUMMY)
                                        $note = $value->costd_name." Periode ".date('d-m-Y',strtotime($tempTimeStart))." s/d ".date('d-m-Y',strtotime($tempTimeStart." +".$value->continv_period." months"));   
                                        $amount = $value->costd_rate + $value->costd_burden + $value->costd_admin;
                                    }else if($value->is_insurance){
                                        // INSURANCE
                                        // find unit utk ngambil luas unit
                                        $currUnit = MsUnit::find($value->unit_id);
                                        $npp_building = $companyData->comp_npp_insurance;
                                        // npp unit  = lust unit per luas total unit
                                        $npp_unit =  $currUnit->unit_sqrt / $companyData->comp_sqrt;
                                        $note = $value->costd_name." (Rp. ".number_format($value->costd_rate,2)."/".number_format($npp_building,2)." x ".$npp_unit.") Periode ".date('d-m-Y',strtotime($tempTimeStart))." s/d ".date('d-m-Y',strtotime($tempTimeStart." +".$value->continv_period." months"));   
                                        // rumus cost + burden + admin
                                        $smount = $value->costd_rate / $npp_building * $npp_unit;
                                    }else{
                                        // ELSE
                                        $note = $value->costd_name." Periode ".date('d-m-Y',strtotime($tempTimeStart))." s/d ".date('d-m-Y',strtotime($tempTimeStart." +".$value->continv_period." months"));   
                                        // rumus cost + burden + admin
                                        $amount = $value->costd_rate + $value->costd_burden + $value->costd_admin;
                                    }
                                    $invDetail[] = [
                                        'invdt_amount' => $amount,
                                        'invdt_note' => $note,
                                        'continv_start_inv' => $tempTimeStart,
                                        'continv_next_inv' => date('Y-m-d',strtotime($tempTimeStart." +".$value->continv_period." months")),
                                        'costd_id' => $value->costd_id
                                    ];
                                    $totalPay+=$amount;
                                }
                                // ends
                            }
                            // end cek meter not meter
                        }
                        // end cek periode dan rangkai detail

                    }
                    
                    // HABIS JABARIN DETAIL, INSERT INVOICE 
                    // TAMBAHIN STAMP DUTY
                    if($totalPay <= $companyData->comp_materai1_amount){ 
                        $invDetail[] = ['invdt_amount' => $companyData->comp_materai1, 'invdt_note' => 'STAMP DUTY', 'costd_id'=> 0];
                        $totalStamp = $companyData->comp_materai1;
                    }else{ 
                        $invDetail[] = ['invdt_amount' => $companyData->comp_materai2, 'invdt_note' => 'STAMP DUTY', 'costd_id'=> 0];
                        $totalStamp = $companyData->comp_materai2;
                    }

                    // echo var_dump($invDetail)."<br><br>"; 

                    // $insertFlag = false;
                    // INSERT DB
                    if($insertFlag){
                        DB::transaction(function () use($year, $month, $value, $totalPay, $contract, $invDetail, $totalStamp){
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
                            $duedate = date('Y-m-d', strtotime('+'.$value->continv_period.' month'));
                            // $totalWithTaxStamp = ($totalPay * 1.1) + $totalStamp;
                            $totalWithStamp = $totalPay + $totalStamp;
                            $inv = [
                                'tenan_id' => $value->tenan_id,
                                'inv_number' => $value->invtp_prefix."-".substr($year, -2).$month."-".$newPrefix,
                                'inv_faktur_no' => $value->invtp_prefix."-".substr($year, -2).$month."-".$newPrefix,
                                'inv_faktur_date' => $now,
                                'inv_date' => $now,
                                'inv_duedate' => $duedate,
                                'inv_amount' => $totalWithStamp,
                                'inv_ppn' => 0.1,
                                'inv_outstanding' => 0,
                                'inv_ppn_amount' => $totalWithStamp, // sementara begini dulu, ikutin cara di foto invoice
                                'inv_post' => 0,
                                'invtp_id' => $value->invtp_id,
                                'contr_id' => $contract->id,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id()
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
                    //end insert db 
                }
                                        
                        
            }
        }

        return '<h3>'.$invoiceGenerated.' of '.$totalInvoice.' Invoices Generated, Please Check Invoice List <a href="'.url('invoice').'">Here</a></h3>';
    }

    public function print_faktur(Request $request){
        try{

            $inv_id = $request->id;
            $type = $request->type;
            $result = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.invdt_amount','tr_invoice_detail.invdt_note','tr_period_meter.prdmet_id','tr_period_meter.prd_billing_date','tr_meter.meter_start','tr_meter.meter_end','tr_meter.meter_used','tr_meter.meter_cost','ms_cost_detail.costd_name')
                ->join('tr_invoice','tr_invoice.id',"=",'tr_invoice_detail.inv_id')
                ->leftJoin('ms_cost_detail','tr_invoice_detail.costd_id',"=",'ms_cost_detail.id')
                ->leftJoin('tr_meter','tr_meter.id',"=",'tr_invoice_detail.meter_id')
                ->leftJoin('tr_period_meter','tr_period_meter.id',"=",'tr_meter.prdmet_id')
                ->where('tr_invoice_detail.inv_id',$inv_id)
                ->get()->toArray();

            $invoice_data = TrInvoice::find($inv_id)->with('MsTenant')->get()->first()->toArray();
            
            $company = MsCompany::with('MsCashbank')->first()->toArray();

            $set_data = array(
                'invoice_data' => $invoice_data,
                'result' => $result,
                'company' => $company,
                'type' => $type
            );
            
            if($type == 'pdf'){
                $pdf = PDF::loadView('print_faktur', $set_data);

                return $pdf->download('FAKTUR-'.$invoice_data['inv_number'].'.pdf');
            }else{
                return view('print_faktur', $set_data);
            }
        }catch(\Exception $e){
            return view('print_faktur', array('errorMsg' => $e->getMessage()));
        } 
    }
}
