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
use App\Models\MsMasterCoa;
use App\Models\MsJournalType;
use App\Models\MsConfig;
use App\Models\TrLedger;
use App\Models\MsUnit;
use App\Models\TrInvoiceJournal;
use App\Models\TrInvoicePaymhdr;
use App\Models\TrInvoicePaymdtl;
use DB;
use PDF;

class InvoiceController extends Controller
{
    public function index(){
        $data['cost_items'] = MsCostDetail::select('ms_cost_detail.id','ms_cost_item.cost_name','ms_cost_item.cost_code','ms_cost_detail.costd_name')
                                ->join('ms_cost_item','ms_cost_detail.cost_id','=','ms_cost_item.id')
                                ->where('ms_cost_detail.costd_ismeter',0)->where('is_service_charge',0)
                                ->where('is_sinking_fund',0)->where('is_insurance',0)->get();
        $data['inv_type'] = MsInvoiceType::all();
        $data['coa'] = MsMasterCoa::where('coa_year',date('Y'))->where('coa_isparent',FALSE)->get();
        return view('invoice',$data);
    }

    public function get(Request $request){
        try{
            // params
            $keyword = @$request->q;
            $invtype = @$request->invtype;
            $datefrom = @$request->datefrom;
            $dateto = @$request->dateto;
            $outstanding = @$request->outstanding;  

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
            $fetch = TrInvoice::select('tr_invoice.id','tr_invoice.inv_iscancel','tr_invoice.inv_number','tr_invoice.inv_date','tr_invoice.inv_duedate','tr_invoice.inv_amount','tr_invoice.inv_outstanding','tr_invoice.inv_ppn','tr_invoice.inv_ppn_amount','tr_invoice.inv_post','ms_invoice_type.invtp_name','ms_tenant.tenan_name','tr_contract.contr_no', 'ms_unit.unit_name','ms_floor.floor_name','ms_unit.unit_code')
                    ->join('ms_invoice_type','ms_invoice_type.id',"=",'tr_invoice.invtp_id')
                    ->leftJoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                    ->leftJoin('ms_unit','tr_contract.unit_id',"=",'ms_unit.id')
                    ->leftJoin('ms_floor','ms_unit.floor_id',"=",'ms_floor.id')
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
                                        $query->where(\DB::raw('lower(trim("contr_no"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("inv_number"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("unit_code"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("tenan_name"::varchar))'),'like','%'.$keyword.'%');
                                    });
            // jika ada inv type
            if(!empty($invtype)) $fetch = $fetch->where('tr_invoice.invtp_id',$invtype);
            // jika ada date from
            if(!empty($datefrom)) $fetch = $fetch->where('tr_invoice.inv_faktur_date','>=',$datefrom);
            // jika ada date to
            if(!empty($dateto)) $fetch = $fetch->where('tr_invoice.inv_faktur_date','<=',$dateto);
            // outstanding
            if(!empty($outstanding)){
                if($outstanding == 1) $fetch = $fetch->where('tr_invoice.inv_outstanding','>',0);
                else $fetch = $fetch->where('tr_invoice.inv_outstanding',0);
            }

            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['contr_no'] = $value->contr_no;
                $temp['unit'] = $value->unit_code;
                $temp['inv_number'] = $value->inv_number;
                $temp['inv_date'] = date('d-m-y',strtotime($value->inv_date));
                $temp['inv_duedate'] = date('d-m-Y',strtotime($value->inv_duedate));
                $temp['inv_amount'] = "Rp. ".number_format($value->inv_amount);
                $temp['inv_ppn'] = $value->inv_ppn * 100;
                $temp['inv_ppn'] = $temp['inv_ppn']."%";
                $temp['inv_ppn_amount'] = "Rp. ".$value->inv_ppn_amount;
                $temp['inv_outstanding'] = !empty((int)number_format($value->inv_outstanding)) ? "Rp. ".number_format($value->inv_outstanding) : "Lunas";
                $temp['invtp_name'] = $value->invtp_name;
                $temp['contr_id'] = $value->contr_id;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['inv_post'] = !empty($value->inv_post) ? 'yes' : 'no';
                $temp['checkbox'] = '<input type="checkbox" name="check" data-posting="'.$value->inv_post.'" value="'.$value->id.'">';
                $temp['action_button'] = '<a href="'.url('invoice/print_faktur?id='.$value->id).'" class="print-window" data-width="640" data-height="660">Print</a> | <a href="'.url('invoice/print_faktur?id='.$value->id.'&type=pdf').'">PDF</a> | <a href="'.url('invoice/receipt?id='.$value->id).'" class="print-window" data-width="640" data-height="660">Receipt</a>';
                $temp['inv_iscancel'] = $value->inv_iscancel;
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
                if(empty($result[$key]->costd_name)) $result[$key]->costd_name = 'Lain-lain';
                $result[$key]->invdt_amount = "Rp. ".number_format($value->invdt_amount);
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
    /*
    //ORIGINAL
    public function postGenerateInvoice(Request $request){
        try{
        $include_outstanding = @MsConfig::where('name','inv_outstanding_active')->first()->value;
        $month = $request->input('month');
        // bulan dikurang 1 karna generate invoice utk bulan kemarin
        $year = $request->input('year');
        if($month == 1) $year = $year-1;

        if($month == 1) $month = 12;
        else $month = $month - 1;
        $month = str_pad($month, 2, 0, STR_PAD_LEFT);
        // BUAT DATE LIMITATION PERIOD METER
        $tempTimeStart = implode('-', [$year,$month,'01']);
        $tempTimeEnd = date("Y-m-t", strtotime($tempTimeStart));
        $companyData = MsCompany::first();
        $stampData = MsCostItem::where('cost_code','STAMP')->first();
        if(!empty($stampData)) $stampCoa = $stampData->cost_coa_code;
        else $stampCoa = 21400;

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
        //edited rahmat, gw ganti monthnya jadi bulan berjalan soalnya aneh kok dia ngecek malah bulan yg lalu harusnya ngecek bulan berjalan
        $invoiceExists = DB::select('select "tr_contract"."id" from "tr_invoice" inner join "tr_contract" on "tr_invoice"."contr_id" = "tr_contract"."id" where "tr_contract"."contr_iscancel" = false and "tr_contract"."contr_status" != \'closed\' and \''.$tempTimeStart.'\' >= "tr_contract"."contr_startdate" and \''.$tempTimeStart.'\' <= "tr_contract"."contr_enddate" and EXTRACT(MONTH FROM "tr_invoice"."inv_date") = '.$month.' group by "tr_contract"."id" ');
        // echo 'total available : '.$totalavContract.' , total exist : '.$invoiceExists->count(); die();
        if(count($invoiceExists) >= $totalavContract){
            return '<h4><strong>All of Invoices this month is already exist in Invoice List</strong></h4> ';
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
                    $details = TrContractInvoice::select('tr_contract_invoice.*','tr_contract_invoice.id as tcinv_id','ms_cost_detail.*','ms_cost_item.is_service_charge','ms_cost_item.is_sinking_fund','ms_cost_item.is_insurance','ms_unit.unit_sqrt','ms_cost_detail.id as costd_id','tr_contract.tenan_id','tr_contract.unit_id','tr_contract.contr_code','tr_contract.contr_enddate','tr_contract.contr_terminate_date','ms_invoice_type.invtp_prefix','ms_invoice_type.id as invtp_id','ms_cost_item.id as cost_item_id')
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
                    $totalPay = 0;
                    foreach ($details as $key2 => $value) {
                        //echo "Invoice ".$key." , detail ".$key2."<br><br>";
                        // LAST INV DATE
                        //echo $value->continv_next_inv;
                        if(!empty($value->continv_next_inv)) $last_inv_date = $value->continv_next_inv;
                        else $last_inv_date = $tempTimeStart;

                        //echo $tempTimeStart." dan ".$last_inv_date;
                        // GENERATE KALAU PERIODE LAST INV UDA LEWAT
                        if($tempTimeStart >= $last_inv_date){
                            // KALAU is meter true, hitung cost meteran 
                            if(!empty($value->costd_ismeter)){
                                // echo 'meter<br>';
                                // $totalPay = 0;
                                // get harga meteran selama periode bulan ini
                                $lastPeriodMeterofMonth = TrPeriodMeter::where(\DB::raw('Extract(month from prd_billing_date)'),'=',$month)->where('status',1)->orderBy('id','desc')->first();
                                // echo $tempTimeStart." dan ".$tempTimeEnd; die();
                                if($lastPeriodMeterofMonth){
                                    $meter = TrMeter::select('tr_meter.id as tr_meter_id','tr_meter.*','tr_period_meter.*','ms_cost_detail.costd_name','ms_cost_detail.costd_rate','ms_cost_detail.costd_unit','ms_cost_detail.id as costd_id')
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
                                        $amount = $meter->total;
                                        // note masi minus rumus     
                                        // KALAU ELECTRICITY
                                        if($value->cost_item_id == 1){
                                            // echo 'listrik '.$amount."<br>";
                                            $note = $meter->costd_name." : ".date('d/m/Y',strtotime($meter->prdmet_start_date))." - ".date('d/m/Y',strtotime($meter->prdmet_end_date))."<br>Meter Awal : ".number_format($meter->meter_start,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Meter Akhir : ".number_format($meter->meter_end,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Pemakaian : ".number_format($meter->meter_used,2)." x ".$meter->costd_rate." : ".number_format($meter->meter_cost,2)."<br>BPJU 3% :".$meter->other_cost;
                                        }else if($value->cost_item_id == 2){
                                            // KALAU AIR
                                            // echo 'air '.$amount."<br>";
                                            $note = $meter->costd_name." : ".date('d/m/Y',strtotime($meter->prdmet_start_date))." - ".date('d/m/Y',strtotime($meter->prdmet_end_date))."<br>Meter Awal : ".number_format($meter->meter_start,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Meter Akhir : ".number_format($meter->meter_end,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Pemakaian : ".number_format($meter->meter_used,0)."<br>Biaya Pemakaian : ".number_format($meter->meter_used,0)." x ".number_format($meter->costd_rate,0)."<br>Biaya Beban Tetap Air : ".number_format($meter->meter_burden,0)."<br>Biaya Pemeliharaan Meter : ".number_format($meter->meter_admin,0); 
                                        }else{
                                            // echo 'other '.$amount."<br>";
                                            $note = $meter->costd_name."<br>Konsumsi : ".number_format($meter->meter_used,0)." ".$meter->costd_unit." Per ".date('d/m/Y',strtotime($meter->prdmet_start_date))." - ".date('d/m/Y',strtotime($meter->prdmet_end_date));
                                        }


                                        // rumus masih standar, rate * meter used + burden + admin
                                        // $amount = ($meter->meter_used * $meter->meter_cost) + $meter->meter_burden + $meter->meter_admin;
                                        // $amount = $meter->meter_cost;
                                        $invDetail[] = [
                                            'invdt_amount' => $amount,
                                            'invdt_note' => $note,
                                            'costd_id' => $meter->costd_id,
                                            'meter_id' => $meter->tr_meter_id
                                        ];
                                        $updateCtrInv[$value->tcinv_id] = [
                                            'continv_start_inv' => $meter->prd_billing_date,
                                            'continv_next_inv' => date('Y-m-d',strtotime($meter->prd_billing_date." +".$value->continv_period." months"))
                                        ];
                                        $totalPay+=$amount;
                                        // echo 'totalpay : '.$totalPay."<br>";
                                    }
                                }else{
                                    echo "<br><b>Contract #".$contract->contr_no."</b><br> Meter Input for ".date('F Y',strtotime($tempTimeStart)).' was not inputed yet. Go to <a href="'.url('period_meter').'">Meter Input</a> and create Period then Input Meter of this particular month<br>';
                                    $insertFlag = false;
                                }  

                            }
                            else{
                                // echo 'non meter<br>';
                                // YG NOT USING METER, GENERATE FULLRATE AJA
                                // $totalPay = 0;
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
                                        $currUnit = MsUnit::find($value->unit_id);
                                        $alias = @MsConfig::where('name','service_charge_alias')->first()->value;
                                        $note = $alias." ".date('d-m-Y',strtotime($tempTimeStart))." s/d ".date('d-m-Y',strtotime($tempTimeStart." +".$value->continv_period." months"))."<br>".number_format($currUnit->unit_sqrt,2)."M2 x Rp. ".number_format($value->costd_rate);
                                        $amount = ($value->unit_sqrt * $value->costd_rate) + $value->costd_burden + $value->costd_admin;
                                        $amount = round($amount,2);
                                    }else if($value->is_sinking_fund){
                                        // SINKING FUND (DUMMY)
                                        $currUnit = MsUnit::find($value->unit_id);
                                        $note = $value->costd_name." (SF)  ".date('d-m-Y',strtotime($tempTimeStart))." s/d ".date('d-m-Y',strtotime($tempTimeStart." +".$value->continv_period." months"))."<br>".number_format($currUnit->unit_sqrt,2)."M2 x Rp. ".number_format($value->costd_rate);   
                                        $amount = ($value->unit_sqrt * $value->costd_rate) + $value->costd_burden + $value->costd_admin;
                                        $amount = round($amount,2);
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
                                        'costd_id' => $value->costd_id
                                    ];
                                    $updateCtrInv[$value->tcinv_id] = [
                                        'continv_start_inv' => $tempTimeStart,
                                        'continv_next_inv' => date('Y-m-d',strtotime($tempTimeStart." +".$value->continv_period." months"))
                                    ];
                                    // echo 'non meter cost '.$amount."<br>";
                                    $totalPay+=$amount;
                                    // echo 'totalpay : '.$totalPay."<br>";
                                }
                                // ends
                            }
                            // end cek meter not meter
                        }else{
                            $insertFlag = false;
                        }
                        // end cek periode dan rangkai detail

                    }

                    //echo var_dump($invDetail)."<br><br>"; 

                    // $insertFlag = false;
                    // INSERT DB
                    if($insertFlag){
                        // HABIS JABARIN DETAIL, INSERT INVOICE 
                        
                        // INCLUDE OUTSTANDING JK ADA
                        if(!empty($include_outstanding)){
                            $totalOutstanding = TrInvoice::where('contr_id',$contract->id)->sum('inv_outstanding'); 
                            $invDetail[] = ['invdt_amount' => $totalOutstanding, 'invdt_note'=> 'Tagihan Belum Terbayar', 'costd_id' => 0];
                        }
                        // KARNA DENDA ITU KAN UTK PAYMENT TELAT, GENERATOR DIGENERATE SEBELUM WKT BAYAR JD BLUM TENTU ADA DISINI

                        // PPN
                        $tenan = MsTenant::find($value->tenan_id);
                        $usePPN = !empty($tenan) ? $tenan->tenan_isppn : 0;
                        if(!empty($usePPN)){
                            $totalPPN = 0.1 * $totalPay;
                            $invDetail[] = ['invdt_amount' => $totalPPN, 'invdt_note' => 'PPH 10%', 'costd_id'=> 0, 'coa_code' => '40900'];
                            $totalPay += $totalPPN;
                        }

                        // TAMBAHIN STAMP DUTY
                        if($totalPay <= $companyData->comp_materai1_amount){ 
                            $invDetail[] = ['invdt_amount' => $companyData->comp_materai1, 'invdt_note' => 'MATERAI', 'costd_id'=> 0, 'coa_code' => $stampCoa];
                            $totalStamp = $companyData->comp_materai1;
                        }else{ 
                            $invDetail[] = ['invdt_amount' => $companyData->comp_materai2, 'invdt_note' => 'MATERAI', 'costd_id'=> 0, 'coa_code' => $stampCoa];
                            $totalStamp = $companyData->comp_materai2;
                        }

                        DB::transaction(function () use($year, $month, $value, $totalPay, $contract, $invDetail, $totalStamp, $updateCtrInv){
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
                            // $duedate = date('Y-m-d', strtotime('+'.$value->continv_period.' month'));
                            $duedate_interval = @MsConfig::where('name','duedate_interval')->first()->value;
                            $duedate = date('Y-m-d', strtotime('+'.$duedate_interval.' days'));

                            // $totalWithTaxStamp = ($totalPay * 1.1) + $totalStamp;
                            $totalWithStamp = $totalPay + $totalStamp;

                            $footer = @MsConfig::where('name','footer_invoice')->first()->value;
                            $label = @MsConfig::where('name','footer_label_inv')->first()->value;
                            $sendEmail = @MsConfig::where('name','send_inv_email')->first()->value;
                            $ccEmail = @MsConfig::where('name','cc_email')->first()->value;
                            $inv = [
                                'tenan_id' => $value->tenan_id,
                                'inv_number' => $value->invtp_prefix."-".substr($year, -2).$month."-".$newPrefix,
                                'inv_faktur_no' => $value->invtp_prefix."-".substr($year, -2).$month."-".$newPrefix,
                                'inv_faktur_date' => $now,
                                'inv_date' => $now,
                                'inv_duedate' => $duedate,
                                'inv_amount' => $totalWithStamp,
                                'inv_ppn' => 0.1,
                                'inv_outstanding' => $totalWithStamp,
                                'inv_ppn_amount' => $totalWithStamp, // sementara begini dulu, ikutin cara di foto invoice
                                'inv_post' => 0,
                                'invtp_id' => $value->invtp_id,
                                'contr_id' => $contract->id,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'footer' => $footer,
                                'label' => $label
                            ];
                            $insertInvoice = TrInvoice::create($inv);

                            // insert detail
                            foreach($invDetail as $indt){
                                $indt['inv_id'] = $insertInvoice->id;
                                TrInvoiceDetail::create($indt);
                            }

                            // update periode di tr contract inv
                            foreach ($updateCtrInv as $key => $contractInvoice) {
                                TrContractInvoice::where('id',$key)->update($contractInvoice);
                            }

                            // email
                            if(!empty($sendEmail)){
                                // BTH SMTP BUAT TESTING, UNSTABLE
                                try{
                                    // $txtMessage = 'Yth Customer kami di tempat, Berikut adalah Invoice yang harus dibayarkan. Detail bisa di download di attachment';
                                    $txtMessage = @MsConfig::where('name','inv_body_email')->first()->value;
                                    \Mail::send('email-template',['message', $txtMessage], function($message) use($inv, $companyData, $ccEmail, $insertInvoice){
                                        $tenan = MsTenant::find($inv['inv_id']);
                                        $data = file_get_contents(url('invoice/print_faktur').'?id='.$insertInvoice->id.'&type=pdf');
                                        $message->attachData($data, 'Invoice-'.$inv['inv_number'].'.pdf');
                                        $message->from('admin@ptjlm.com', 'Admin Unit');
                                        if(!empty($ccEmail)) $message->cc($ccEmail);
                                        $message->to($tenan->tenan_email)->subject('Tagihan '.$inv['inv_number'].' - '.$companyData->comp_name);
                                    });
                                }catch(\Exception $e){

                                }
                            }
                        });
                        $invoiceGenerated++;
                    }
                    //end insert db 
                }
                                        
                        
            }
        }

        return '<h3>'.$invoiceGenerated.' of '.$totalInvoice.' Invoices Generated, Please Check Invoice List <a href="'.url('invoice').'">Here</a></h3>';
        }catch(\Exception $e){
            return response()->json(['errorMsg'=>$e->getMessage()]);
        }
    }
    */

    public function postGenerateInvoice(Request $request){
        try{
        $include_outstanding = @MsConfig::where('name','inv_outstanding_active')->first()->value;
        $month = $request->input('month');
        // bulan dikurang 1 karna generate invoice utk bulan kemarin
        $year = $request->input('year');
        if($month == 1) $year = $year-1;

        if($month == 1) $month = 12;
        else $month = $month - 1;
        $month = str_pad($month, 2, 0, STR_PAD_LEFT);
        // BUAT DATE LIMITATION PERIOD METER
        $tempTimeStart = implode('-', [$year,$month,'01']);
        $tempTimeEnd = date("Y-m-t", strtotime($tempTimeStart));
        $companyData = MsCompany::first();
        $stampData = MsCostItem::where('cost_code','STAMP')->first();
        if(!empty($stampData)) $stampCoa = $stampData->cost_coa_code;
        else $stampCoa = 21400;

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
        //edited rahmat, gw ganti monthnya jadi bulan berjalan soalnya aneh kok dia ngecek malah bulan yg lalu harusnya ngecek bulan berjalan
        $invoiceExists = DB::select('select "tr_contract"."id" from "tr_invoice" inner join "tr_contract" on "tr_invoice"."contr_id" = "tr_contract"."id" where "tr_contract"."contr_iscancel" = false and "tr_contract"."contr_status" != \'closed\' and \''.$tempTimeStart.'\' >= "tr_contract"."contr_startdate" and \''.$tempTimeStart.'\' <= "tr_contract"."contr_enddate" and EXTRACT(MONTH FROM "tr_invoice"."inv_date") = '.$month.' group by "tr_contract"."id" ');
        // echo 'total available : '.$totalavContract.' , total exist : '.$invoiceExists->count(); die();
        if(count($invoiceExists) >= $totalavContract){
            return '<h4><strong>All of Invoices this month is already exist in Invoice List</strong></h4> ';
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
                    $details = TrContractInvoice::select('tr_contract_invoice.*','tr_contract_invoice.id as tcinv_id','ms_cost_detail.*','ms_cost_item.is_service_charge','ms_cost_item.is_sinking_fund','ms_cost_item.is_insurance','ms_unit.unit_sqrt','ms_cost_detail.id as costd_id','tr_contract.tenan_id','tr_contract.unit_id','tr_contract.contr_code','tr_contract.contr_enddate','tr_contract.contr_terminate_date','ms_invoice_type.invtp_prefix','ms_invoice_type.id as invtp_id','ms_cost_item.id as cost_item_id')
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
                    $totalPay = 0;
                    foreach ($details as $key2 => $value) {
                        //echo "Invoice ".$key." , detail ".$key2."<br><br>";
                        // LAST INV DATE
                        //echo $value->continv_next_inv;
                        if(!empty($value->continv_next_inv)) $last_inv_date = $value->continv_next_inv;
                        else $last_inv_date = $tempTimeStart;

                        //echo $tempTimeStart." dan ".$last_inv_date;
                        // GENERATE KALAU PERIODE LAST INV UDA LEWAT
                        if($tempTimeStart >= $last_inv_date){
                            // KALAU is meter true, hitung cost meteran 
                            if(!empty($value->costd_ismeter)){
                                // echo 'meter<br>';
                                // $totalPay = 0;
                                // get harga meteran selama periode bulan ini
                                $lastPeriodMeterofMonth = TrPeriodMeter::where(\DB::raw('Extract(month from prd_billing_date)'),'=',$month)->where('status',1)->orderBy('id','desc')->first();
                                // echo $tempTimeStart." dan ".$tempTimeEnd; die();
                                if($lastPeriodMeterofMonth){
                                    $meter = TrMeter::select('tr_meter.id as tr_meter_id','tr_meter.*','tr_period_meter.*','ms_cost_detail.costd_name','ms_cost_detail.costd_rate','ms_cost_detail.costd_unit','ms_cost_detail.id as costd_id')
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
                                        $amount = $meter->total;
                                        // note masi minus rumus     
                                        // KALAU ELECTRICITY
                                        if($value->cost_item_id == 1){
                                            // echo 'listrik '.$amount."<br>";
                                            $note = $meter->costd_name." : ".date('d/m/Y',strtotime($meter->prdmet_start_date))." - ".date('d/m/Y',strtotime($meter->prdmet_end_date))."<br>Meter Awal : ".number_format($meter->meter_start,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Meter Akhir : ".number_format($meter->meter_end,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Pemakaian : ".number_format($meter->meter_used,2);
                                        }else if($value->cost_item_id == 2){
                                            // KALAU AIR
                                            // echo 'air '.$amount."<br>";
                                            $note = $meter->costd_name." : ".date('d/m/Y',strtotime($meter->prdmet_start_date))." - ".date('d/m/Y',strtotime($meter->prdmet_end_date))."<br>Meter Awal : ".number_format($meter->meter_start,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Meter Akhir : ".number_format($meter->meter_end,0)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Pemakaian : ".number_format($meter->meter_used,0)."<br>Biaya Pemakaian : ".number_format($meter->meter_used,0); 
                                        }else{
                                            // echo 'other '.$amount."<br>";
                                            $note = $meter->costd_name."<br>Konsumsi : ".number_format($meter->meter_used,0)." ".$meter->costd_unit." Per ".date('d/m/Y',strtotime($meter->prdmet_start_date))." - ".date('d/m/Y',strtotime($meter->prdmet_end_date));
                                        }


                                        // rumus masih standar, rate * meter used + burden + admin
                                        // $amount = ($meter->meter_used * $meter->meter_cost) + $meter->meter_burden + $meter->meter_admin;
                                        // $amount = $meter->meter_cost;
                                        $invDetail[] = [
                                            'invdt_amount' => $amount,
                                            'invdt_note' => $note,
                                            'costd_id' => $meter->costd_id,
                                            'meter_id' => $meter->tr_meter_id
                                        ];
                                        $updateCtrInv[$value->tcinv_id] = [
                                            'continv_start_inv' => $meter->prd_billing_date,
                                            'continv_next_inv' => date('Y-m-d',strtotime($meter->prd_billing_date." +".$value->continv_period." months"))
                                        ];
                                        $totalPay+=$amount;
                                        // echo 'totalpay : '.$totalPay."<br>";
                                    }
                                }else{
                                    echo "<br><b>Contract #".$contract->contr_no."</b><br> Meter Input for ".date('F Y',strtotime($tempTimeStart)).' was not inputed yet. Go to <a href="'.url('period_meter').'">Meter Input</a> and create Period then Input Meter of this particular month<br>';
                                    $insertFlag = false;
                                }  

                            }
                            else{
                                // echo 'non meter<br>';
                                // YG NOT USING METER, GENERATE FULLRATE AJA
                                // $totalPay = 0;
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
                                        $currUnit = MsUnit::find($value->unit_id);
                                        $alias = @MsConfig::where('name','service_charge_alias')->first()->value;
                                        $note = $alias." ".date('d-m-Y',strtotime($tempTimeStart))." s/d ".date('d-m-Y',strtotime($tempTimeStart." +".$value->continv_period." months"))."<br>".number_format($currUnit->unit_sqrt,2)."M2 x Rp. ".number_format($value->costd_rate);
                                        $amount = ($value->unit_sqrt * $value->costd_rate) + $value->costd_burden + $value->costd_admin;
                                        $amount = round($amount,2);
                                    }else if($value->is_sinking_fund){
                                        // SINKING FUND (DUMMY)
                                        $currUnit = MsUnit::find($value->unit_id);
                                        $note = $value->costd_name." (SF)  ".date('d-m-Y',strtotime($tempTimeStart))." s/d ".date('d-m-Y',strtotime($tempTimeStart." +".$value->continv_period." months"))."<br>".number_format($currUnit->unit_sqrt,2)."M2 x Rp. ".number_format($value->costd_rate);   
                                        $amount = ($value->unit_sqrt * $value->costd_rate) + $value->costd_burden + $value->costd_admin;
                                        $amount = round($amount,2);
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
                                        'costd_id' => $value->costd_id
                                    ];
                                    $updateCtrInv[$value->tcinv_id] = [
                                        'continv_start_inv' => $tempTimeStart,
                                        'continv_next_inv' => date('Y-m-d',strtotime($tempTimeStart." +".$value->continv_period." months"))
                                    ];
                                    // echo 'non meter cost '.$amount."<br>";
                                    $totalPay+=$amount;
                                    // echo 'totalpay : '.$totalPay."<br>";
                                }
                                // ends
                            }
                            // end cek meter not meter
                        }else{
                            $insertFlag = false;
                        }
                        // end cek periode dan rangkai detail

                    }

                    //echo var_dump($invDetail)."<br><br>"; 

                    // $insertFlag = false;
                    // INSERT DB
                    if($insertFlag){
                        // HABIS JABARIN DETAIL, INSERT INVOICE 
                        
                        // INCLUDE OUTSTANDING JK ADA
                        if(!empty($include_outstanding)){
                            $totalOutstanding = TrInvoice::where('contr_id',$contract->id)->sum('inv_outstanding'); 
                            $invDetail[] = ['invdt_amount' => $totalOutstanding, 'invdt_note'=> 'Tagihan Belum Terbayar', 'costd_id' => 0];
                        }
                        // KARNA DENDA ITU KAN UTK PAYMENT TELAT, GENERATOR DIGENERATE SEBELUM WKT BAYAR JD BLUM TENTU ADA DISINI

                        // PPN
                        $tenan = MsTenant::find($value->tenan_id);
                        $usePPN = !empty($tenan) ? $tenan->tenan_isppn : 0;
                        if(!empty($usePPN)){
                            $totalPPN = 0.1 * $totalPay;
                            $invDetail[] = ['invdt_amount' => $totalPPN, 'invdt_note' => 'PPH 10%', 'costd_id'=> 0, 'coa_code' => '40900'];
                            $totalPay += $totalPPN;
                        }

                        // TAMBAHIN STAMP DUTY
                        if($totalPay <= $companyData->comp_materai1_amount){ 
                            $invDetail[] = ['invdt_amount' => $companyData->comp_materai1, 'invdt_note' => 'MATERAI', 'costd_id'=> 0, 'coa_code' => $stampCoa];
                            $totalStamp = $companyData->comp_materai1;
                        }else{ 
                            $invDetail[] = ['invdt_amount' => $companyData->comp_materai2, 'invdt_note' => 'MATERAI', 'costd_id'=> 0, 'coa_code' => $stampCoa];
                            $totalStamp = $companyData->comp_materai2;
                        }

                        DB::transaction(function () use($year, $month, $value, $totalPay, $contract, $invDetail, $totalStamp, $updateCtrInv){
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
                            // $duedate = date('Y-m-d', strtotime('+'.$value->continv_period.' month'));
                            $duedate_interval = @MsConfig::where('name','duedate_interval')->first()->value;
                            $duedate = date('Y-m-d', strtotime('+'.$duedate_interval.' days'));

                            // $totalWithTaxStamp = ($totalPay * 1.1) + $totalStamp;
                            $totalWithStamp = $totalPay + $totalStamp;

                            $footer = @MsConfig::where('name','footer_invoice')->first()->value;
                            $label = @MsConfig::where('name','footer_label_inv')->first()->value;
                            $sendEmail = @MsConfig::where('name','send_inv_email')->first()->value;
                            $ccEmail = @MsConfig::where('name','cc_email')->first()->value;
                            $inv = [
                                'tenan_id' => $value->tenan_id,
                                'inv_number' => $value->invtp_prefix."-".substr($year, -2).$month."-".$newPrefix,
                                'inv_faktur_no' => $value->invtp_prefix."-".substr($year, -2).$month."-".$newPrefix,
                                'inv_faktur_date' => $now,
                                'inv_date' => $now,
                                'inv_duedate' => $duedate,
                                'inv_amount' => $totalWithStamp,
                                'inv_ppn' => 0.1,
                                'inv_outstanding' => $totalWithStamp,
                                'inv_ppn_amount' => $totalWithStamp, // sementara begini dulu, ikutin cara di foto invoice
                                'inv_post' => 0,
                                'invtp_id' => $value->invtp_id,
                                'contr_id' => $contract->id,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'footer' => $footer,
                                'label' => $label
                            ];
                            $insertInvoice = TrInvoice::create($inv);

                            // insert detail
                            foreach($invDetail as $indt){
                                $indt['inv_id'] = $insertInvoice->id;
                                TrInvoiceDetail::create($indt);
                            }

                            // update periode di tr contract inv
                            foreach ($updateCtrInv as $key => $contractInvoice) {
                                TrContractInvoice::where('id',$key)->update($contractInvoice);
                            }

                            // email
                            if(!empty($sendEmail)){
                                // BTH SMTP BUAT TESTING, UNSTABLE
                                try{
                                    // $txtMessage = 'Yth Customer kami di tempat, Berikut adalah Invoice yang harus dibayarkan. Detail bisa di download di attachment';
                                    $txtMessage = @MsConfig::where('name','inv_body_email')->first()->value;
                                    \Mail::send('email-template',['message', $txtMessage], function($message) use($inv, $companyData, $ccEmail, $insertInvoice){
                                        $tenan = MsTenant::find($inv['inv_id']);
                                        $data = file_get_contents(url('invoice/print_faktur').'?id='.$insertInvoice->id.'&type=pdf');
                                        $message->attachData($data, 'Invoice-'.$inv['inv_number'].'.pdf');
                                        $message->from('admin@ptjlm.com', 'Admin Unit');
                                        if(!empty($ccEmail)) $message->cc($ccEmail);
                                        $message->to($tenan->tenan_email)->subject('Tagihan '.$inv['inv_number'].' - '.$companyData->comp_name);
                                    });
                                }catch(\Exception $e){

                                }
                            }
                        });
                        $invoiceGenerated++;
                    }
                    //end insert db 
                }
                                        
                        
            }
        }

        return '<h3>'.$invoiceGenerated.' of '.$totalInvoice.' Invoices Generated, Please Check Invoice List <a href="'.url('invoice').'">Here</a></h3>';
        }catch(\Exception $e){
            return response()->json(['errorMsg'=>$e->getMessage()]);
        }
    }

    public function print_faktur(Request $request){
        try{

            $inv_id = $request->id;
            if(!is_array($inv_id)) $inv_id = [$inv_id];
            $type = $request->type;

            $invoice_data = TrInvoice::select('tr_invoice.*', 'ms_unit.unit_code', 'ms_unit.virtual_account')
                                    ->join('tr_contract','tr_contract.id','=','tr_invoice.contr_id')
                                    ->join('ms_unit','tr_contract.unit_id','=','ms_unit.id')
                                    ->whereIn('tr_invoice.id',$inv_id)->with('MsTenant')->get()->toArray();
            foreach ($invoice_data as $key => $inv) {
                $result = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.invdt_amount','tr_invoice_detail.invdt_note','tr_period_meter.prdmet_id','tr_period_meter.prd_billing_date','tr_meter.meter_start','tr_meter.meter_end','tr_meter.meter_used','tr_meter.meter_cost','ms_cost_detail.costd_name')
                ->join('tr_invoice','tr_invoice.id',"=",'tr_invoice_detail.inv_id')
                ->leftJoin('ms_cost_detail','tr_invoice_detail.costd_id',"=",'ms_cost_detail.id')
                ->leftJoin('tr_meter','tr_meter.id',"=",'tr_invoice_detail.meter_id')
                ->leftJoin('tr_period_meter','tr_period_meter.id',"=",'tr_meter.prdmet_id')
                ->where('tr_invoice_detail.inv_id',$inv['id'])
                ->get()->toArray();
                $invoice_data[$key]['details'] = $result;
                $terbilang = $this->terbilang($inv['inv_amount']);
                $invoice_data[$key]['terbilang'] = '## '.$terbilang.' Rupiah ##';
            }
            $total = $invoice_data[0]['inv_outstanding'];
            
            
            $company = MsCompany::with('MsCashbank')->first()->toArray();
            $signature = @MsConfig::where('name','digital_signature')->first()->value;
            $signatureFlag = @MsConfig::where('name','invoice_signature_flag')->first()->value;

            $set_data = array(
                'invoice_data' => $invoice_data,
                'result' => $result,
                'company' => $company,
                'type' => $type,
                'signature' => $signature,
                'signatureFlag' => $signatureFlag
            );
            
            if($type == 'pdf'){
                $pdf = PDF::loadView('print_faktur', $set_data)->setPaper('a4');

                return $pdf->download('FAKTUR-INV.pdf');
            }else{
                return view('print_faktur', $set_data);
            }
         }catch(\Exception $e){
             return $e->getMessage();
         } 
    }

    public function print_kwitansi(Request $request){
        $company = MsCompany::with('MsCashbank')->first()->toArray();
        // $signature = @MsConfig::where('name','digital_signature')->first()->value;
        $paymentHeader = TrInvoicePaymhdr::find($request->id);
        $contract = TrContract::select('ms_tenant.tenan_name','ms_unit.unit_code')->join('ms_unit','tr_contract.unit_id','=','ms_unit.id')
                            ->join('ms_tenant','tr_contract.tenan_id','=','ms_tenant.id')
                            ->where('tr_contract.id',$paymentHeader->contr_id)->first();
        $paymentDetails = TrInvoicePaymdtl::select('tr_invoice.id','tr_invoice.inv_number','tr_invoice_paymdtl.invpayd_amount')
                                ->join('tr_invoice','tr_invoice_paymdtl.inv_id','=','tr_invoice.id')
                                ->where('tr_invoice_paymdtl.invpayh_id',$request->id)->get();
        $total = 0;
        if(count($paymentDetails) > 0){
            foreach ($paymentDetails as $key => $value) {
                $total += $value->invpayd_amount;
                // get detail invoice
                $temp = [];
                $inv_details = TrInvoiceDetail::where('inv_id',$value->id)->get();
                if(count($inv_details) > 0){
                    foreach ($inv_details as $value2) {
                        $note = explode('<br>', $value2->invdt_note);
                        if(count($note) > 1) $temp[] = @$note[0];
                        else $temp[] = $value2->invdt_note;
                    }
                }
                $paymentDetails[$key]->details = $temp;
            }
        }
        $terbilang = $this->terbilang($total);

        $set_data = array(
                'company' => $company,
                // 'signature' => $signature,
                'header' => $paymentHeader,
                'details' => $paymentDetails,
                'terbilang' => $terbilang,
                'tenan' => @$contract->tenan_name,
                'unit' => @$contract->unit_code
            );

        return view('print_payment', $set_data);
    }

    public function posting(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

        $coayear = date('Y');
        $month = date('m');
        $journal = [];
        $invJournal = [];

        // cari last prefix, order by journal type
        $jourType = MsJournalType::where('jour_type_prefix','AR')->first();
        if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "AR" first before posting an invoice']);
        $lastJournal = TrLedger::where('jour_type_id',$jourType->id)->latest()->first();
        if($lastJournal){
            $lastJournalNumber = explode(" ", $lastJournal->ledg_number);
            $lastJournalNumber = (int) end($lastJournalNumber);
            $nextJournalNumber = $lastJournalNumber + 1;
        }else{
            $nextJournalNumber = 1;
        }
        $successPosting = 0;
        $successIds = [];

        // cek backdate dr closing bulanan/tahunan
        $lastclose = TrLedger::whereNotNull('closed_at')->orderBy('closed_at','desc')->first();
        $limitMinPostingDate = null;
        if($lastclose) $limitMinPostingDate = date('Y-m-t', strtotime($lastclose->closing_at));
        
        foreach ($ids as $id) {
            // coa ambil dari grouping cost detail
            $invDetails = TrInvoiceDetail::select('coa_code','ar_coa_code','cost_name','invdt_amount','invdt_note')->leftJoin('ms_cost_detail','ms_cost_detail.id','=','tr_invoice_detail.costd_id')
                                        ->leftJoin('ms_cost_item','ms_cost_item.id','=','ms_cost_detail.cost_id')
                                        ->where('inv_id',$id)->get();
            $debetCoa = [];
            $debetCoaAmount = [];
            $debetCoaName = [];
            foreach ($invDetails as $key => $value) {
                if(empty($value->coa_code) && !in_array($value->ar_coa_code, $debetCoa)){
                    if(empty($value->ar_coa_code)) $value->ar_coa_code = 10390;
                    $debetCoa[] = (int)$value->ar_coa_code;
                    $debetCoaAmount[(int)$value->ar_coa_code] = $value->invdt_amount;
                    $debetCoaName[(int)$value->ar_coa_code] = !empty($value->cost_name) ? $value->cost_name : $value->invdt_note;
                }else if(!empty($value->coa_code) && !in_array($value->coa_code, $debetCoa)){
                    $debetCoa[] = (int)$value->coa_code;
                    $debetCoaAmount[(int)$value->coa_code] = $value->invdt_amount;
                    $debetCoaName[(int)$value->coa_code] = !empty($value->cost_name) ? $value->cost_name : $value->invdt_note;
                }else if(empty($value->coa_code) && in_array($value->ar_coa_code, $debetCoa)){
                    $debetCoaAmount[(int)$value->ar_coa_code] += $value->invdt_amount;
                }else if(!empty($value->coa_code) && in_array($value->coa_code, $debetCoa)){
                    $debetCoaAmount[(int)$value->coa_code] += $value->invdt_amount;
                }
            }

            // get coa code dari invoice type
            $invoiceHd = TrInvoice::with('MsTenant')->find($id);
            // validasi backdate posting
            if(!empty($limitMinPostingDate) && $invoiceHd->inv_date < $limitMinPostingDate){
                return response()->json(['error'=>1, 'message'=> "You can't posting if one of these invoice date is before last close date"]);
            }
            // if(!isset($invoiceHd->InvoiceType->invtp_coa_ar)) return response()->json(['error'=>1, 'message'=> 'Invoice Type Name: '.$invoiceHd->InvoiceType->invtp_name.' need to be set with COA code']);
            // create journal DEBET utk piutang
            foreach($debetCoaAmount as $key => $value){
                $coaDebet = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$key)->first();
                if(empty($coaDebet)) return response()->json(['error'=>1, 'message'=>'COA Code: '.$key.' is not found on this year list. Please ReInsert this COA Code']);
                
                $nextJournalNumber = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumber;
                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                // Debet
                $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => date('Y-m-d'),
                                'ledg_refno' => $invoiceHd->inv_faktur_no,
                                'ledg_debit' => $value,
                                'ledg_credit' => 0,
                                'ledg_description' => $debetCoaName[$key],
                                'coa_year' => $coaDebet->coa_year,
                                'coa_code' => $coaDebet->coa_code,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => $jourType->id,
                                'dept_id' => 3 //hardcode utk finance
                            ];

                $invJournal[] = [
                                'inv_id' => $id,
                                'invjour_voucher' => $journalNumber,
                                'invjour_date' => date('Y-m-d'),
                                'invjour_note' => 'Posting Invoice '.$invoiceHd->inv_faktur_no,
                                'coa_code' => $coaDebet->coa_code,
                                'invjour_debit' => $value,
                                'invjour_credit' => 0
                            ];
                $nextJournalNumber++;
            }
            // End DEBET

            // Create CREDIT
            // jabarin invoice detail
            $invDetails = TrInvoiceDetail::where('inv_id',$id)->get();
            foreach ($invDetails as $detail) {
                // coa credit diambil dari cost item
                if(!empty($detail->coa_code)){
                    $costItem = "";
                    $coaCredit = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$detail->coa_code)->first();
                }else{
                    if($detail->costd_id != 0){ 
                        $costItem = MsCostDetail::join('ms_cost_item','ms_cost_item.id','=','ms_cost_detail.cost_id')->where('ms_cost_detail.id',$detail->costd_id)->first();
                        $cost_coa_code = $costItem->cost_coa_code;
                    }else{ 
                        // $costItem = MsCostItem::where('cost_code','STAMP')->first();
                        $costItem = "";
                        if(empty($detail->coa_code)) $detail->coa_code = 40900;
                        $cost_coa_code = $detail->coa_code;
                    }
                    $coaCredit = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$cost_coa_code)->first();
                }
                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                $ledgNote = !empty($costItem) ? $costItem->cost_name : $detail->invdt_note;
                $journal[] = [
                            'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                            'ledge_fisyear' => $coayear,
                            'ledg_number' => $journalNumber,
                            'ledg_date' => date('Y-m-d'),
                            'ledg_refno' => $invoiceHd->inv_faktur_no,
                            'ledg_debit' => 0,
                            'ledg_credit' => $detail->invdt_amount,
                            'ledg_description' => $invoiceHd->MsTenant->tenan_name." : ".$ledgNote,
                            'coa_year' => $coaCredit->coa_year,
                            'coa_code' => $coaCredit->coa_code,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'jour_type_id' => $jourType->id,
                            'dept_id' => 3 //hardcode utk finance
                        ];

                $invJournal[] = [
                            'inv_id' => $id,
                            'invjour_voucher' => $journalNumber,
                            'invjour_date' => date('Y-m-d'),
                            'invjour_note' => 'Posting Invoice '.$invoiceHd->inv_faktur_no,
                            'coa_code' => $coaCredit->coa_code,
                            'invjour_debit' => 0,
                            'invjour_credit' => $detail->invdt_amount
                        ];
            }
            $successIds[] = $id;
            $nextJournalNumber++;
            $successPosting++;
        }
        // var_dump($journal);
        // var_dump($invJournal);
        
        // INSERT DATABASE
        try{
            DB::transaction(function () use($successIds, $invJournal, $journal){
                // insert journal
                TrLedger::insert($journal);
                // insert invoice journal
                TrInvoiceJournal::insert($invJournal);
                // update posting to yes
                if(count($successIds) > 0){
                    foreach ($successIds as $id) {
                        TrInvoice::where('id', $id)->update(['inv_post'=>1]);
                    }
                }
            });
        }catch(\Exception $e){
            return response()->json(['error'=>1, 'message'=> 'Error occured when posting invoice']);
        }

        return response()->json(['success'=>1, 'message'=>$successPosting.' Invoice posted Successfully']);
    }

    public function insert(Request $request){
        $inv_date = explode('-',$request->inv_date);
        $invtp = MsInvoiceType::find($request->invtp_id);
        $lastInvoiceofMonth = TrInvoice::select('inv_number')->where('inv_number','like',$invtp->invtp_prefix.'-'.substr($inv_date[0], -2).$inv_date[1].'-%')->orderBy('id','desc')->first();
        if($lastInvoiceofMonth){
            $lastPrefix = explode('-', $lastInvoiceofMonth->inv_number);
            $lastPrefix = (int) $lastPrefix[2];               
        }else{
            $lastPrefix = 0;
        }
        $newPrefix = $lastPrefix + 1;
        $newPrefix = str_pad($newPrefix, 4, 0, STR_PAD_LEFT);
        // $tenanId = $request->tenan_id;
        $contract = TrContract::find($request->contr_id);
        $contractId = $contract->id;
        // $contract = TrContract::where('tenan_id',$tenanId)->where('contr_status','confirmed')->first();
        // if($contract) $contractId = $contract->id;
        // else $contractId = 0;

        $invHeader = [
            'tenan_id' => $contract->tenan_id,
            'inv_number' => $invtp->invtp_prefix."-".substr($inv_date[0], -2).$inv_date[1]."-".$newPrefix,
            'inv_faktur_no' => $invtp->invtp_prefix."-".substr($inv_date[0], -2).$inv_date[1]."-".$newPrefix,
            'inv_faktur_date' => $request->inv_date,
            'inv_date' => $request->inv_date,
            'inv_duedate' => $request->inv_duedate,
            'inv_amount' => $request->amount,
            'inv_ppn' => 0.1,
            'inv_outstanding' => $request->amount,
            'inv_ppn_amount' => $request->amount, // sementara begini dulu, ikutin cara di foto invoice
            'inv_post' => 0,
            'invtp_id' => $request->invtp_id,
            // 'contr_id' => $request->contr_id,
            'contr_id' => $contractId,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id()
        ];

        $coa_codes = $request->coa_code;
        $invdt_notes = $request->invdt_note;
        $invdt_amounts = $request->invdt_amount;
        foreach ($coa_codes as $key => $code) {
            $invDtl[] = [
                'invdt_amount' => $invdt_amounts[$key],
                'invdt_note' => $invdt_notes[$key],
                'costd_id' => 0,
                'coa_code' => $code
            ];
            $updateCtrInv[] = [
                'continv_start_inv' => $request->inv_date,
                'continv_next_inv' => date('Y-m-d',strtotime($request->inv_date." +1 months"))
            ];
        }

        try{
            // DB::transaction(function () use($invHeader, $invDtl, $request, $updateCtrInv){
                $insertInvoice = TrInvoice::create($invHeader);

                // insert detail
                foreach($invDtl as $key => $indt){
                    $indt['inv_id'] = $insertInvoice->id;
                    TrInvoiceDetail::create($indt);

                    TrContractInvoice::where('invtp_id',$request->invtp_id)->where('contr_id',$request->contr_id)->where('costd_id',$indt['costd_id'])->update($updateCtrInv[$key]);
                }
            // });
        }catch(\Exception $e){
            return response()->json(['error' => 1, 'message' => 'Error Occured']);
        }
        return response()->json(['success' => 1, 'inv_id' => $insertInvoice->id, 'message' => 'Insert Invoice Success']);
    }

    public function cancel(Request $request){
        try{
            $id = $request->id;
            // TrInvoice::where('id',$id)->update(['inv_iscancel'=>1]);
            $invoice = TrInvoice::find($id);
            TrContractInvoice::where('invtp_id',$invoice->invtp_id)->where('contr_id',$invoice->contr_id)->update(['continv_next_inv'=>null]);
            TrInvoice::where('id',$id)->delete();
            return response()->json(['success' => 1, 'message' => 'Cancel Invoice Success']);
        }catch(\Exception $e){
            return response()->json(['error' => 1, 'message' => 'Error Occured']);
        } 
    }

    public function kuitansi(Request $request){
            $inv_id = $request->id;
            if(!is_array($inv_id)) $inv_id = [$inv_id];
            $type = $request->type;

            $invoice_data = TrInvoice::select('tr_invoice.*', 'ms_unit.unit_code')
                                    ->join('tr_contract','tr_contract.id','=','tr_invoice.contr_id')
                                    ->join('ms_unit','tr_contract.unit_id','=','ms_unit.id')
                                    ->whereIn('tr_invoice.id',$inv_id)->with('MsTenant')->get()->toArray();
            foreach ($invoice_data as $key => $inv) {
                $result = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.invdt_amount','tr_invoice_detail.invdt_note','tr_period_meter.prdmet_id','tr_period_meter.prd_billing_date','tr_meter.meter_start','tr_meter.meter_end','tr_meter.meter_used','tr_meter.meter_cost','ms_cost_detail.costd_name')
                ->join('tr_invoice','tr_invoice.id',"=",'tr_invoice_detail.inv_id')
                ->leftJoin('ms_cost_detail','tr_invoice_detail.costd_id',"=",'ms_cost_detail.id')
                ->leftJoin('tr_meter','tr_meter.id',"=",'tr_invoice_detail.meter_id')
                ->leftJoin('tr_period_meter','tr_period_meter.id',"=",'tr_meter.prdmet_id')
                ->where('tr_invoice_detail.inv_id',$inv['id'])
                ->get()->toArray();
                $invoice_data[$key]['details'] = $result;
            }
            $total = $invoice_data[0]['inv_outstanding'];
            $terbilang = $this->terbilang($total);
            $invoice_data[$key]['terbilang'] = '## '.$terbilang.' Rupiah ##';
            
            $company = MsCompany::with('MsCashbank')->first()->toArray();
            $signature = @MsConfig::where('name','digital_signature')->first()->value;

            $set_data = array(
                'invoice_data' => $invoice_data,
                'result' => $result,
                'company' => $company,
                'type' => $type,
                'signature' => $signature
            );
            return view('print_kuitansi', $set_data);
    }

    public function ajaxGetFooter(Request $request){
        $inv_id = $request->id;
        $invoice = TrInvoice::select('id','footer','label')->find($inv_id)->toArray();
        if(!$invoice) return response()->json(['errMsg' => 'Invoice not found']);

        return response()->json(['status' => 1, 'result' => $invoice]); 
    }

    public function ajaxStoreFooter(Request $request){
        if(!empty(@$request->id)){
            try{
                $invoice = TrInvoice::find($request->id);
                $invoice->footer = $request->footer_invoice;
                $invoice->label = $request->footer_label_inv;
                $invoice->save();
                return response()->json(['success' => 1]);
            }catch(\Exception $e){
                return response()->json(['errMsg' => $e->getMessage()]);
            }
        }else{
            return response()->json(['errMsg' => 'Invoice ID not found']);
        }
    }

    public function terbilang ($angka) {
        $angka = (float)$angka;
        $bilangan = array('','Satu','Dua','Tiga','Empat','Lima','Enam','Tujuh','Delapan','Sembilan','Sepuluh','Sebelas');
        if ($angka < 12) {
            return $bilangan[$angka];
        } else if ($angka < 20) {
            return $bilangan[$angka - 10] . ' Belas';
        } else if ($angka < 100) {
            $hasil_bagi = (int)($angka / 10);
            $hasil_mod = $angka % 10;
            return trim(sprintf('%s Puluh %s', $bilangan[$hasil_bagi], $bilangan[$hasil_mod]));
        } else if ($angka < 200) { return sprintf('Seratus %s', $this->terbilang($angka - 100));
        } else if ($angka < 1000) { $hasil_bagi = (int)($angka / 100); $hasil_mod = $angka % 100; return trim(sprintf('%s Ratus %s', $bilangan[$hasil_bagi], $this->terbilang($hasil_mod)));
        } else if ($angka < 2000) { return trim(sprintf('Seribu %s', $this->terbilang($angka - 1000)));
        } else if ($angka < 1000000) { $hasil_bagi = (int)($angka / 1000); $hasil_mod = $angka % 1000; return sprintf('%s Ribu %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod));
        } else if ($angka < 1000000000) { $hasil_bagi = (int)($angka / 1000000); $hasil_mod = $angka % 1000000; return trim(sprintf('%s Juta %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
        } else if ($angka < 1000000000000) { $hasil_bagi = (int)($angka / 1000000000); $hasil_mod = fmod($angka, 1000000000); return trim(sprintf('%s Milyar %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
        } else if ($angka < 1000000000000000) { $hasil_bagi = $angka / 1000000000000; $hasil_mod = fmod($angka, 1000000000000); return trim(sprintf('%s Triliun %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
        } else {
            return 'Data Salah';
        }
    }

    public function progressGenerate(Request $request){
        $month = $request->input('month');
        $year = $request->input('year');
        if($month == 1) $year = $year-1;

        if($month == 1) $month = 12;
        else $month = $month - 1;
        $month = str_pad($month, 2, 0, STR_PAD_LEFT);
        $year = substr($year, -2);
        
        $generated = TrInvoice::where('inv_faktur_no', 'like', '%-'.$year.$month.'-%')->count();
        $totalInvoice = $request->session()->get('totalInv');
        return response()->json(['generated' => $generated, 'total' => $totalInvoice]);
    }

}
