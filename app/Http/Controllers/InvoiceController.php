<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load library
use App\Libs\Invoice;
use App\Libs\Contract;
use App\Libs\CostCreator;
use App\Jobs\SendMail;

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
use App\Models\Autoreminder;
use App\Models\MsEmailTemplate;
use App\Models\InvoiceScheduler;
use App\Models\AkasaOutstanding;
use App\Models\EmailQueue;
use App\Models\ReminderH;
use App\Models\ReminderD;
use App\Models\MsCashBank;
use App\Models\MsPaymentType;
use App\Models\ExcessPayment;
use App\Models\LogExcessPayment;
use App\Models\LogPaymentUsed;
use App\Models\CreditNoteH;
use App\Models\CreditNoteD;
use App\Models\AkrualInv;
use App\Models\KwitansiCounter;
use App\Models\Numcounter;
use DB;
use PDF;
use Validator;

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

    public function sendInvoice(Request $request){
        // $invoice = TrInvoice::find($id);
        $invoice = TrInvoice::orderBy('created_at','desc')->limit(1)->first();
        // $queue = new EmailQueue;
        // $queue->status = 'new';
        // $queue->mailclass = '\App\Mail\InvoiceMail';
        // $queue->ref_id = $invoice->id;
        // $queue->to = 'vendera.hadi@gmail.com';
        // $queue->cc = 'rahmat.setiawan90@gmail.com';
        // $queue->save();
        \Mail::to('rahmat.setiawan90@gmail.com')->send(new \App\Mail\InvoiceMail($invoice->id));

        echo "email sent to queue";
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
            $fetch = TrInvoice::select('tr_invoice.id','tr_invoice.inv_iscancel','tr_invoice.inv_number','tr_invoice.inv_date','tr_invoice.inv_duedate','tr_invoice.inv_amount','tr_invoice.inv_outstanding','tr_invoice.inv_ppn','tr_invoice.inv_ppn_amount','tr_invoice.inv_post','tr_invoice.unit_id','ms_invoice_type.invtp_name','ms_tenant.tenan_name','tr_contract.contr_no', 'ms_unit.unit_name','ms_floor.floor_name','ms_unit.unit_code','tr_invoice.tenan_id')
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
<<<<<<< Updated upstream
                                        $query->where(\DB::raw('lower(trim("contr_no"::varchar))'),'ilike','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("inv_number"::varchar))'),'ilike','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("unit_code"::varchar))'),'ilike','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("tenan_name"::varchar))'),'ilike','%'.$keyword.'%');
=======
                                        $query->where(\DB::raw('lower(trim("inv_amount"::varchar))'),'ilike','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("inv_number"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("unit_code"::varchar))'),'ilike','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("tenan_name"::varchar))'),'ilike','%'.$keyword.'%');
>>>>>>> Stashed changes
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
                $temp['unit'] = !empty($value->unit) ? @$value->unit->unit_code : @$value->unit_code;
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

                if(!$value->inv_iscancel){
                    $temp['action_button'] = '<a href="'.url('invoice/print_faktur?id='.$value->id).'" class="print-window" data-width="640" data-height="660">Print</a> | <a href="'.url('invoice/print_faktur?id='.$value->id.'&type=pdf').'">PDF</a> | <a href="'.url('invoice/receipt?id='.$value->id).'" class="print-window" data-width="640" data-height="660">Receipt</a>';

                    if($value->invtp_name == 'INVOICE UTILITIES'){
                        $temp['action_button'] .= ' | <a href="'.route('invoice.editinv',$value->id).'" >Edit</a>';
                    }

                    // if(!empty((int)number_format($value->inv_outstanding))) $temp['action_button'] .= ' | <a href="'.url('invoice/sendreminder?id='.$value->tenan_id).'" class="print-window" data-width="640" data-height="660">Send Reminder</a>';
                }

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
            $invoiceHd = TrInvoice::find($inv_id);
            $nilai = TrInvoiceDetail::select('costd_id')->where('inv_id',$inv_id)->get();
            $cost_id = $nilai[0]->costd_is;
            $result = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.costd_id','tr_invoice_detail.invdt_amount','tr_invoice_detail.invdt_note','tr_period_meter.prdmet_id','tr_period_meter.prd_billing_date','tr_meter.meter_start','tr_meter.meter_end','tr_meter.meter_used','tr_meter.meter_cost','ms_cost_detail.costd_name','ms_cost_detail.costd_unit')
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
                // get order
                $ctrInv = TrContractInvoice::where('contr_id',$invoiceHd->contr_id)->where('costd_id',$value->costd_id)->first();
                $result[$key]->order = !empty(@$ctrInv->order) ? $ctrInv->order : $value->id;
            }
            $result = $result->toArray();
            usort($result, function($a, $b) {
                    return $a['order'] - $b['order'];
                });
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function generateInvoice(Request $request){
        $data['invtypes'] = MsInvoiceType::all();
        return view('generateinvoice', $data);
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

    public function generateInvSchedule($total, $periodStart, $periodEnd, $invoice_type_id)
    {
        // get sejumlah total task yang akan dikerjakan
        $schedules = InvoiceScheduler::where('status','new')->where('period_start', $periodStart)->where('period_end', $periodEnd)
                        ->where('invtp_id', $invoice_type_id)
                        ->orderBy('id');
        if(!empty($total)) $schedules = $schedules->limit($total);
        $schedules = $schedules->get();
        $total = 0;
        foreach ($schedules as $sch) {
            // call class invoice
            $invoice = new Invoice;
            $invoice->setInvoiceType($sch->invtp_id);
            $invoice->setContract($sch->contract_id);
            $invoice->setPeriod(date('m',strtotime($sch->period_start)), date('Y',strtotime($sch->period_end)));

            $contract = $invoice->getContract();
            if(!$contract) continue;
            echo "<br><b>Contract # ".$contract->contr_no."</b> ";
            if(!$invoice->exists()){
                // generating cost details
                $contract = new Contract($periodStart, $periodEnd);
                $contract->setContract($sch->contract_id);
                $cost_details = $contract->getCostItems($sch->invtp_id);
                $tenant = $contract->getTenant();
                $countCost = 0;
                $tempDetails = [];
                foreach ($cost_details as $costdt) {
                     //echo $costdt.",";
                    $cost = new CostCreator;
                    $cost->setCostItem($costdt);
                    $cost->setInvType($sch->invtp_id);
                    $cost->setContract($sch->contract_id);
                    $cost->setInvStartDate($invoice->getInvStartDate());
                    $cost->setPeriod($sch->period_start, $sch->period_end);
                    $detail = $cost->generateDetail();
                     //print_r($detail);
                     //die();
                    if(!empty($detail)){
                        $countCost++;
                        $tempDetails[] = $detail;
                    }
                }
                // jika semua cost lengkap dan bisa digenerate, masukin child
                //echo $countCost." dan ".count($cost_details)."<br>";
                 if($countCost == count($cost_details)){
                     //echo "ADD CHILD";
                    foreach ($tempDetails as $dt) {
                        $invoice->addChild($dt);
                    }
                    $use_denda = @MsConfig::where('name','denda_active')->first()->value;
                    if(!empty($use_denda)) $invoice->addDenda();
                    // tenan_isppn kalau aktif tambah PPN
                    if(@$tenant->tenan_isppn) $invoice->addPPN();
                    // kalau config materai aktif add materai
                    $use_materai = @MsConfig::where('name','use_materai')->first()->value;
                    if(!empty($use_materai)) $invoice->addMaterai();
                 }
                 //echo "SKIPPED";

                 //echo $invoice->create();
                if($invoice->create()){
                    echo "Generated<br>--------------<br>";
                    $total++;
                }else{
                    echo "<br>Not Generated<br>--------------<br>";
                }
            }else{
                echo "Already Generated<br>--------------<br>";
            }

        }
        echo "<h3>$total Invoice(s) Generated</h3><br><br>";
    }

    public function postGenerateInvoice(Request $request){
        try{
            $month = $request->input('month');
            $year = $request->input('year');
            $invoice_type_id = $request->input('invtp_id');

            // call class invoice
            $invoice = new Invoice;
            $invoice->setPeriod($month, $year);
            $periodStart = $invoice->getPeriodStart();
            $periodEnd = $invoice->getPeriodEnd();

            // call class contract
            $contract = new Contract($periodStart, $periodEnd);
            // cari contract availabel, yg bakal dipakai utk generate
            $contract->setInvType($invoice_type_id);
            $countAvailableContract = $contract->countAvailable();
            // validasi jika contract availabel kosong
            if(empty($countAvailableContract)) return '<h4><strong>There is no contract available</strong></h4>';

            // validasi jika generate lebih dari bulan ini
            // if($periodStart > date('Y-m-d',strtotime("first day of this month"))) return response()->json(['errorMsg' => 'Invoice can\'t be generated more than this month']);

            $availableContract = $contract->getAvailable();
            // loop sejumlah contract = sejumlah invoice
            $generated = 0;
            foreach ($availableContract as $contract) {
                // check contract is generated or not
                $check = InvoiceScheduler::where('period_start',$periodStart)->where('period_end', $periodEnd)->where('invtp_id', $invoice_type_id)->where('contract_id', $contract->id)->first();
                if(!$check){
                    $scheduler = new InvoiceScheduler;
                    $scheduler->period_start = $periodStart;
                    $scheduler->period_end = $periodEnd;
                    $scheduler->invtp_id = $invoice_type_id;
                    $scheduler->contract_id = $contract->id;
                    $scheduler->status = 'new';
                    $scheduler->save();
                }
                $generated += 1;
            }
            // echo "generated $generated";
            // generate 100 task pertama
            $this->generateInvSchedule(null, $periodStart, $periodEnd, $invoice_type_id);
        }catch(\Exception $e){
            return response()->json(['errorMsg'=>$e->getMessage()]);
        }
    }

    public function print_faktur(Request $request){
        try{

            $inv_id = $request->id;
            if(!is_array($inv_id)) $inv_id = [$inv_id];
            $type = $request->type;

            $invoice_data = TrInvoice::select('tr_invoice.*', 'ms_unit.unit_code', 'ms_unit.va_utilities', 'ms_unit.va_maintenance')
                                    ->leftJoin('tr_contract','tr_contract.id','=','tr_invoice.contr_id')
                                    ->leftJoin('ms_unit','tr_invoice.unit_id','=','ms_unit.id')
                                    ->where('inv_iscancel','!=',1)
                                    ->whereIn('tr_invoice.id',$inv_id)->with('MsTenant','InvoiceType')->get()->toArray();
            foreach ($invoice_data as $key => $inv) {
                $result = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.invdt_amount','tr_invoice_detail.invdt_note','tr_invoice_detail.costd_id','tr_period_meter.prdmet_id','tr_period_meter.prd_billing_date','tr_meter.meter_start','tr_meter.meter_end','tr_meter.meter_used','tr_meter.meter_cost','ms_cost_detail.costd_name')
                ->join('tr_invoice','tr_invoice.id',"=",'tr_invoice_detail.inv_id')
                ->leftJoin('ms_cost_detail','tr_invoice_detail.costd_id',"=",'ms_cost_detail.id')
                ->leftJoin('tr_meter','tr_meter.id',"=",'tr_invoice_detail.meter_id')
                ->leftJoin('tr_period_meter','tr_period_meter.id',"=",'tr_meter.prdmet_id')
                ->where('tr_invoice_detail.inv_id',$inv['id'])
                ->get()->toArray();
                // jabarin detail dan sisipkan order
                foreach ($result as $key2 => $detail) {
                    $ctrInv = TrContractInvoice::where('contr_id',$inv['contr_id'])->where('costd_id',$detail['costd_id'])->first();
                    $result[$key2]['order'] = !empty(@$ctrInv->order) ? $ctrInv->order : $detail['id'];
                }
                usort($result, function($a, $b) {
                    return $a['order'] - $b['order'];
                });

                $invoice_data[$key]['details'] = $result;
                $terbilang = $this->terbilang(($inv['inv_amount']-$inv['total_excess_payment']));
                if(($inv['inv_amount']-$inv['total_excess_payment']) == 0){
                    $invoice_data[$key]['terbilang'] = '## LUNAS ##';
                }else{
                    $invoice_data[$key]['terbilang'] = '## '.$terbilang.' Rupiah ##';
                }
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

    // public function sendInvoice(Request $request, $id){
    //     $invoice = TrInvoice::find($id);
    //     $mailClass = new \App\Mail\InvoiceMail($invoice);
    //     $cc = @MsConfig::where('name','cc_email')->first()->value;
    //     if(empty($cc)) $cc = [];
    //     dispatch(new SendMail($mailClass, 'vendera.hadi@gmail.com', []));
    //     echo "email sent";
    // }

    public function print_kwitansi(Request $request){
        $sendKwitansi = @$request->send;
        $company = MsCompany::with('MsCashbank')->first()->toArray();
        // $signature = @MsConfig::where('name','digital_signature')->first()->value;
        $paymentHeader = TrInvoicePaymhdr::find($request->id);
        
        $paymentDetails = TrInvoicePaymdtl::select('tr_invoice.id','tr_invoice.inv_number','tr_invoice_paymdtl.invpayd_amount','tr_invoice.inv_amount','tr_creditnote_dtl.credit_amount','tr_invoice.unit_id')
                                ->join('tr_invoice','tr_invoice_paymdtl.inv_id','=','tr_invoice.id')
                                ->leftJoin('tr_creditnote_dtl','tr_creditnote_dtl.inv_id','=','tr_invoice.id')
                                ->leftJoin('tr_creditnote_hdr','tr_creditnote_hdr.id','=','tr_creditnote_dtl.creditnote_hdr_id')
                                ->where('tr_invoice_paymdtl.invpayh_id',$request->id)->get();

        $unit_k = $paymentDetails[0]->unit_id;
        $contract = TrContract::where('tr_contract.tenan_id',$paymentHeader->tenan_id)->where('tr_contract.unit_id',$unit_k)->first();
        $total = 0;
        $crd = 0;
        if(count($paymentDetails) > 0){
            foreach ($paymentDetails as $key => $value) {
                $total += $value->inv_amount;
                // get detail invoice
                $temp = [];
                $invHd = TrInvoice::find($value->id);
                $inv_details = TrInvoiceDetail::where('inv_id',$value->id)->get();
                $crd_note = CreditNoteD::join('tr_creditnote_hdr','tr_creditnote_dtl.creditnote_hdr_id','=','tr_creditnote_hdr.id')
                                ->where('tr_creditnote_dtl.inv_id',$value->id)
                                ->where('creditnote_post','t')
                                ->get();
                // jabarin detail dan sisipkan order
                if(count($inv_details) > 0){
                    foreach ($inv_details as $key2 => $value2) {
                        $ctrInv = TrContractInvoice::where('contr_id',$invHd->contr_id)->where('costd_id', $value2->costd_id)->first();
                        $inv_details[$key2]['order'] = !empty(@$ctrInv->order) ? $ctrInv->order : 255;
                    }
                    $inv_details = $inv_details->toArray();
                    usort($inv_details, function($a, $b) {
                        return $a['order'] - $b['order'];
                    });
                    foreach ($inv_details as $key2 => $value2) {
                        $note = explode('<br>', $value2['invdt_note']);
                        if(count($note) > 1) $temp[] = @$note[0];
                        else $temp[] = $value2['invdt_note'];
                    }
                }
                if(count($crd_note) > 0){
                    foreach ($crd_note as $key3 => $value3) {
                        $crd += $value3->credit_amount;
                        $temp[] = $value3['creditnote_keterangan'];
                    }
                }
                $paymentDetails[$key]->details = $temp;
            }
        }
        $terbilang = $this->terbilang($total - $crd);

        $set_data = array(
                'company' => $company,
                // 'signature' => $signature,
                'header' => $paymentHeader,
                'details' => $paymentDetails,
                'terbilang' => $terbilang.' Rupiah',
                'tenan' => @$contract->MsTenant->tenan_name,
                'unit' => @$contract->MsUnit->unit_code,
                'type' => null
            );

        if(!empty($sendKwitansi)){
<<<<<<< Updated upstream
            \Mail::to($paymentHeader->tenant->tenan_email)->send(new \App\Mail\Kwitansi($paymentHeader));
=======
            $cc = @MsConfig::where('name','cc_email')->first()->value;
            $queue = new EmailQueue;
            $queue->status = 'new';
            $queue->mailclass = '\App\Mail\KwitansiMail';
            $queue->ref_id = $paymentHeader;
            $queue->to = $paymentHeader->tenant->tenan_email;
            if(!empty($cc)) $queue->cc = $cc;
            $queue->save();
            if($buktifaktur->tenant->cc_email != NULL || $buktifaktur->tenant->cc_email != ''){
                $cc_tenant = explode('|', $buktifaktur->tenant->cc_email);
                if(count($cc_tenant) > 0){
                    for($i=0; $i<count($cc_tenant); $i++){
                        $tnt2 = explode('~', $cc_tenant[$i]);
                        if(count($tnt2) > 0){
                            $unit_dt = $tnt2[0];
                            $units = MsUnit::where('unit_code', $unit_dt)->first();
                            $kirim = explode(';', $tnt2[1]);
                            if(count($units) > 0){
                                //if($units->id == $invoice->unit_id){ 
                                    if(count($kirim) > 0){
                                        for($j=0; $j<count($kirim); $j++){
                                            $queue = new EmailQueue;
                                            $queue->status = 'new';
                                            $queue->mailclass = '\App\Mail\KwitansiMail';
                                            $queue->ref_id = $buktifaktur->id;
                                            $queue->to = $kirim[$j];
                                            if(!empty($cc)) $queue->cc = $cc;
                                            $queue->save();
                                        }
                                    }
                                //}
                            } 
                        }
                    }
                }
            } 
>>>>>>> Stashed changes
            return 'Success! Email sent to '.$paymentHeader->tenant->tenan_email;
        }
        return view('print_payment', $set_data);
    }

    public function posting(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

        $coayear = date('Y');
        $month = date('m');
        $journal = [];
        $invJournal = [];
        $invAkrual = [];
        $coatitipan = @MsConfig::where('name','coa_hutang_titipan')->first()->value;

        $successPosting = 0;
        $successIds = [];

        // cek backdate dr closing bulanan/tahunan
        $lastclose = TrLedger::whereNotNull('closed_at')->orderBy('closed_at','desc')->first();
        $limitMinPostingDate = null;
        if($lastclose) $limitMinPostingDate = date('Y-m-t', strtotime($lastclose->closing_at));

        foreach ($ids as $id) {

            // cari last prefix, order by journal type
            $jourType = MsJournalType::where('jour_type_prefix','AR')->first();
            if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "AR" first before posting an invoice']);
            $lastJournal = Numcounter::where('numtype','JG')->where('tahun',$coayear)->where('bulan',$month)->first();
            if(count($lastJournal) > 0){
                $lst = $lastJournal->last_counter;
                $nextJournalNumber = $lst + 1;
                $lastJournal->update(['last_counter'=>$nextJournalNumber]);
            }else{
                $nextJournalNumber = 1;
                $lastcounter = new Numcounter;
                $lastcounter->numtype = 'JG';
                $lastcounter->tahun = date('Y');
                $lastcounter->bulan = date('m');
                $lastcounter->last_counter = 1;
                $lastcounter->save();
            }

            // get coa code dari invoice type
            $invoiceHd = TrInvoice::with('MsTenant')->find($id);
            if($invoiceHd->inv_iscancel) break;

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

            // validasi backdate posting
            if(!empty($limitMinPostingDate) && $invoiceHd->inv_date < $limitMinPostingDate){
                return response()->json(['error'=>1, 'message'=> "You can't posting if one of these invoice date is before last close date"]);
            }

            $nextJournalNumber = str_pad($nextJournalNumber, 6, 0, STR_PAD_LEFT);
            $journalNumber = "JG/".$coayear."/".$this->Romawi($month)."/".$nextJournalNumber;

            // create journal DEBET utk piutang
            foreach($debetCoaAmount as $key => $value){
                $coaDebet = MsMasterCoa::where('coa_code',$key)->first();
                if(empty($coaDebet)) return response()->json(['error'=>1, 'message'=>'COA Code: '.$key.' is not found on this year list. Please ReInsert this COA Code']);

                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                // Debet
                $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => date('Y-m-d'),
                                //'ledg_date' => '2019-01-03',
                                'ledg_refno' => $invoiceHd->inv_faktur_no,
                                'ledg_debit' => $value,
                                'ledg_credit' => 0,
                                'ledg_description' => $debetCoaName[$key],
                                'coa_year' => $coayear,
                                'coa_code' => $coaDebet->coa_code,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => $jourType->id,
                                'dept_id' => 3, //hardcode utk finance,
                                'modulname' => 'AR',
                                'refnumber' =>$id
                            ];
                
                if($invoiceHd->total_excess_payment > 0){
                    if($invoiceHd->total_excess_payment < $invoiceHd->inv_amount){
                        $sf = 10/100 * $invoiceHd->total_excess_payment;
                        $ipl = $invoiceHd->total_excess_payment - $sf;
                        $air = $invoiceHd->total_excess_payment;

                        $lastJournal_titipan = Numcounter::where('numtype','JG')->where('tahun',$coayear)->where('bulan',$month)->first();
                        if(count($lastJournal_titipan) > 0){
                            $lst_titipan = $lastJournal_titipan->last_counter;
                            $nextJournalNumber_titipan = $lst_titipan + 1;
                            $lastJournal_titipan->update(['last_counter'=>$nextJournalNumber_titipan]);
                        }else{
                            $nextJournalNumber_titipan = 1;
                            $lastcounter_titipan = new Numcounter;
                            $lastcounter_titipan->numtype = 'BRV';
                            $lastcounter_titipan->tahun = date('Y');
                            $lastcounter_titipan->bulan = date('m');
                            $lastcounter_titipan->last_counter = 1;
                            $lastcounter_titipan->save();
                        }
                        $nextJournalNumber_titipan = str_pad($nextJournalNumber_titipan, 6, 0, STR_PAD_LEFT);
                        $journalNumber_titipan = "JG/".$coayear."/".$this->Romawi($month)."/".$nextJournalNumber_titipan;

                        if($invoiceHd->invtp_id == 2){
                            if(trim($coaDebet->coa_code) == '10310'){
                                $journal[] = [
                                    'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                    'ledge_fisyear' => $coayear,
                                    'ledg_number' => $journalNumber_titipan,
                                    'ledg_date' => date('Y-m-d'),
                                    //'ledg_date' => '2019-01-03',
                                    'ledg_refno' => $invoiceHd->inv_faktur_no,
                                    'ledg_debit' => $air,
                                    'ledg_credit' => 0,
                                    'ledg_description' => 'Hutang Titipan Pemotong Invoice '.$debetCoaName[$key],
                                    'coa_year' => $coayear,
                                    'coa_code' => $coatitipan,
                                    'created_by' => Auth::id(),
                                    'updated_by' => Auth::id(),
                                    'jour_type_id' => $jourType->id,
                                    'dept_id' => 3, //hardcode utk finance
                                    'modulname' => 'AR',
                                    'refnumber' =>$id
                                ];
                                $journal[] = [
                                    'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                    'ledge_fisyear' => $coayear,
                                    'ledg_number' => $journalNumber_titipan,
                                    'ledg_date' => date('Y-m-d'),
                                    //'ledg_date' => '2019-01-03',
                                    'ledg_refno' => $invoiceHd->inv_faktur_no,
                                    'ledg_debit' => 0,
                                    'ledg_credit' => $ipl,
                                    'ledg_description' => 'Hutang Titipan Pemotong Invoice '.$debetCoaName[$key],
                                    'coa_year' => $coayear,
                                    'coa_code' => $coaDebet->coa_code,
                                    'created_by' => Auth::id(),
                                    'updated_by' => Auth::id(),
                                    'jour_type_id' => $jourType->id,
                                    'dept_id' => 3, //hardcode utk finance
                                    'modulname' => 'AR',
                                    'refnumber' =>$id
                                ];
                            }else{
                                $journal[] =[
                                    'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                    'ledge_fisyear' => $coayear,
                                    'ledg_number' => $journalNumber_titipan,
                                    'ledg_date' => date('Y-m-d'),
                                    //'ledg_date' => '2019-01-03',
                                    'ledg_refno' => $invoiceHd->inv_faktur_no,
                                    'ledg_debit' => 0,
                                    'ledg_credit' => $sf,
                                    'ledg_description' => 'Hutang Titipan Pemotong Invoice '.$debetCoaName[$key],
                                    'coa_year' => $coayear,
                                    'coa_code' => $coaDebet->coa_code,
                                    'created_by' => Auth::id(),
                                    'updated_by' => Auth::id(),
                                    'jour_type_id' => $jourType->id,
                                    'dept_id' => 3, //hardcode utk finance
                                    'modulname' => 'AR',
                                    'refnumber' =>$id
                                ];
                            }
                        }else{
                            $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber_titipan,
                                'ledg_date' => date('Y-m-d'),
                                //'ledg_date' => '2019-01-03',
                                'ledg_refno' => $invoiceHd->inv_faktur_no,
                                'ledg_debit' => $air,
                                'ledg_credit' => 0,
                                'ledg_description' => 'Hutang Titipan Pemotong Invoice '.$debetCoaName[$key],
                                'coa_year' => $coayear,
                                'coa_code' => $coatitipan,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => $jourType->id,
                                'dept_id' => 3, //hardcode utk finance
                                'modulname' => 'AR',
                                'refnumber' =>$id
                            ];
                            $journal[] =[
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber_titipan,
                                'ledg_date' => date('Y-m-d'),
                                //'ledg_date' => '2019-01-03',
                                'ledg_refno' => $invoiceHd->inv_faktur_no,
                                'ledg_debit' => 0,
                                'ledg_credit' => $air,
                                'ledg_description' => 'Hutang Titipan Pemotong Invoice '.$debetCoaName[$key],
                                'coa_year' => $coayear,
                                'coa_code' => $coaDebet->coa_code,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => $jourType->id,
                                'dept_id' => 3, //hardcode utk finance
                                'modulname' => 'AR',
                                'refnumber' =>$id
                            ];
                        }
                    }
                }
                
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
                //ACRUD jadi pas pertama kali akui piutang kreditnya ke COA pendapatan di terima di Muka
                $pmuka = @MsConfig::where('name','coa_uangmuka')->first()->value;
                if(!empty($detail->coa_code)){
                    $costItem = "";
                    $coaCredit = MsMasterCoa::where('coa_code',$detail->coa_code)->first();
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
                    $coaCredit = MsMasterCoa::where('coa_code',$cost_coa_code)->first();
                }
                //INPUT KE LOG AKRUAL
                $totalp = 1;
                if($invoiceHd->invtp_id == 2){
                    $bulan = date('n',strtotime($invoiceHd->inv_date));
                    switch ($bulan) {
                        case '1':
                            $totalp = 3;
                            break;
                        case '2':
                            $totalp = 2;
                            break;
                        case '3':
                            $totalp = 1;
                            break;
                        case '4':
                            $totalp = 3;
                            break;
                        case '5':
                            $totalp = 2;
                            break;
                        case '6':
                            $totalp = 1;
                            break;
                        case '7':
                            $totalp = 3;
                            break;
                        case '8':
                            $totalp = 2;
                            break;
                        case '9':
                            $totalp = 1;
                            break;
                        case '10':
                            $totalp = 3;
                            break;
                        case '11':
                            $totalp = 2;
                            break;
                        case '12':
                            $totalp = 1;
                            break;
                        default:
                            $totalp = 1;
                            break;
                    }

                    $ptgbulan = $detail->invdt_amount - $detail->prorate;
                    if($detail->prorate > 0){
                        if($ptgbulan > 0 && $totalp > 1 ){
                            $ptg = $ptgbulan/($totalp - 1);
                        }else{
                            $ptg = $detail->invdt_amount/$totalp;
                        }
                    }else{
                        $ptg = $detail->invdt_amount/$totalp;
                    }

                    if(trim($coaCredit->coa_code) == '40100'){
                        $invAkrual[] = [
                                    'inv_id' => $invoiceHd->id,
                                    'inv_number' => $invoiceHd->inv_faktur_no,
                                    'inv_date' => $invoiceHd->inv_date,
                                    'inv_amount' => $detail->invdt_amount,
                                    'potong_perbulan' => $ptg,
                                    'prorate_amount' => $detail->prorate,
                                    'coa_code' => $coaCredit->coa_code,
                                    'total_potong' => $totalp,
                                    'log_potong' => 0,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ];

                        $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                        $ledgNote = !empty($costItem) ? $costItem->cost_name : $detail->invdt_note;
                        $journal[] = [
                                    'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                    'ledge_fisyear' => $coayear,
                                    'ledg_number' => $journalNumber,
                                    'ledg_date' => date('Y-m-d'),
                                    //'ledg_date' => '2019-01-03',
                                    'ledg_refno' => $invoiceHd->inv_faktur_no,
                                    'ledg_debit' => 0,
                                    'ledg_credit' => $detail->invdt_amount,
                                    'ledg_description' => $invoiceHd->MsTenant->tenan_name." : ".$ledgNote,
                                    'coa_year' => $coayear,
                                    //'coa_code' => $coaCredit->coa_code,
                                    'coa_code' => $pmuka,
                                    'created_by' => Auth::id(),
                                    'updated_by' => Auth::id(),
                                    'jour_type_id' => $jourType->id,
                                    'dept_id' => 3, //hardcode utk finance
                                    'modulname' => 'AR',
                                    'refnumber' =>$id
                                ];

                        $invJournal[] = [
                                    'inv_id' => $id,
                                    'invjour_voucher' => $journalNumber,
                                    'invjour_date' => date('Y-m-d'),
                                    'invjour_note' => 'Posting Invoice '.$invoiceHd->inv_faktur_no,
                                    'coa_code' => $pmuka,
                                    'invjour_debit' => 0,
                                    'invjour_credit' => $detail->invdt_amount
                                ];
                    }else{
                        $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                        $ledgNote = !empty($costItem) ? $costItem->cost_name : $detail->invdt_note;
                        $journal[] = [
                                    'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                    'ledge_fisyear' => $coayear,
                                    'ledg_number' => $journalNumber,
                                    'ledg_date' => date('Y-m-d'),
                                    //'ledg_date' => '2019-01-03',
                                    'ledg_refno' => $invoiceHd->inv_faktur_no,
                                    'ledg_debit' => 0,
                                    'ledg_credit' => $detail->invdt_amount,
                                    'ledg_description' => $invoiceHd->MsTenant->tenan_name." : ".$ledgNote,
                                    'coa_year' => $coayear,
                                    'coa_code' => $coaCredit->coa_code,
                                    'created_by' => Auth::id(),
                                    'updated_by' => Auth::id(),
                                    'jour_type_id' => $jourType->id,
                                    'dept_id' => 3,
                                    'modulname' => 'AR',
                                    'refnumber' =>$id
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
                }else{
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    $ledgNote = !empty($costItem) ? $costItem->cost_name : $detail->invdt_note;
                    $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => date('Y-m-d'),
                                //'ledg_date' => '2019-01-03',
                                'ledg_refno' => $invoiceHd->inv_faktur_no,
                                'ledg_debit' => 0,
                                'ledg_credit' => $detail->invdt_amount,
                                'ledg_description' => $invoiceHd->MsTenant->tenan_name." : ".$ledgNote,
                                'coa_year' => $coayear,
                                'coa_code' => $coaCredit->coa_code,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => $jourType->id,
                                'dept_id' => 3,
                                'modulname' => 'AR',
                                'refnumber' =>$id
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
            }
            $successIds[] = $id;
            $nextJournalNumber++;
            $successPosting++;
        }

        // INSERT DATABASE
        try{
            DB::transaction(function () use($successIds, $invJournal, $journal, $invAkrual){
                $sendMailFlag = @MsConfig::where('name','send_inv_email')->first()->value;
                // insert journal
                TrLedger::insert($journal);
                // insert invoice journal
                TrInvoiceJournal::insert($invJournal);
                // insert akrual
                AkrualInv::insert($invAkrual);
                // update posting to yes
                if(count($successIds) > 0){
                    foreach ($successIds as $id) {
                        $invoice = TrInvoice::find($id);
                        $invoice->update(['inv_post'=>1]);
                        if($invoice->inv_amount == $invoice->total_excess_payment){
                            //belom tambahin biar langsung buat kwitansi
                            $lastPayment = KwitansiCounter::where(\DB::raw('tahun'),'=',date('Y'))
                                ->where(\DB::raw('bulan'),'=',date('m'))->first();
                            $indexNumber = null;
                            
                            if($lastPayment){
                                $index = $lastPayment->last_counter;
                                $index+= 1;
                                $indexNumber = $index;
                                $index = str_pad($index, 3, "0", STR_PAD_LEFT);
                                $lastPayment->update(['last_counter'=>$index]);
                            }else{
                                $index = "001";
                                $indexNumber = 1;
                                $lastcounter = new KwitansiCounter;
                                $lastcounter->tahun = date('Y');
                                $lastcounter->bulan = date('m');
                                $lastcounter->last_counter = 1;
                                $lastcounter->save();
                            }

                            $payVal = (int)$invoice->inv_amount;
                            $total = $payVal;
                            $detail_payment = array(
                                'invpayd_amount' => $payVal,
                                'inv_id' => $invoice->id
                            );

                            $action = new TrInvoicePaymhdr;
                            $prefixKuitansi = @MsConfig::where('name','prefix_kuitansi')->first()->value;
                            $banktitipan = @MsConfig::where('name','bank_titipan')->first()->value;
                            $action->no_kwitansi = $prefixKuitansi.'-'.date('Y-m').'.'.$index;
                            $action->invpayh_date = date('Y-m-d');
                            //$action->invpayh_date = '2019-03-05';
                            $action->invpayh_checkno = '';
                            $action->invpayh_giro = NULL;
                            $action->invpayh_note = 'PEMBAYARAN AUTO LUNAS';
                            $action->invpayh_post = FALSE;
                            $action->paymtp_code = 2;
                            $action->cashbk_id = (int)$banktitipan;
                            $action->tenan_id = $invoice->tenan_id;
                            $action->invpayh_settlamt = 1;
                            $action->invpayh_adjustamt = 1;
                            $action->invpayh_amount = $total;
                            $action->updated_by = $action->created_by = Auth::id();
                            $action->status_void = FALSE;

                            if($action->save()){
                                $payment_id = $action->id;
                                $payment_ids[] = $payment_id;

                                $action_detail = new TrInvoicePaymdtl;
                                $invoice_data = $invoice->get()->first();

                                if(!empty($invoice_data)){
                                    $invoice_data = $invoice_data->toArray();
                                    $inv_amount = $invoice_data['inv_amount'];

                                    $invoice_has_paid = TrInvoicePaymdtl::select('tr_invoice_paymhdr.*', 'tr_invoice_paymdtl.*')
                                        ->join('tr_invoice_paymhdr','tr_invoice_paymdtl.invpayh_id','=','tr_invoice_paymhdr.id')
                                        ->where('status_void', '=', false)
                                        ->where('inv_id', '=', $detail_payment['inv_id'])
                                        ->get()->first();

                                    if(!empty($invoice_has_paid)){
                                        $invoice_has_paid = $invoice_has_paid->sum('invpayd_amount');
                                    }else{
                                        $invoice_has_paid = 0;
                                    }

                                    $total_has_paid = $invoice_has_paid + $detail_payment['invpayd_amount'];
                                    $outstand = $inv_amount - $total_has_paid;

                                    if($outstand <= 0){
                                        $outstand = 0;
                                    }

                                    $action_detail->invpayd_amount = $detail_payment['invpayd_amount'];
                                    $action_detail->inv_id = $detail_payment['inv_id'];
                                    $action_detail->invpayh_id = $payment_id;
                                    $action_detail->last_outstanding = $detail_payment['invpayd_amount'];
                                    $action_detail->save();
                                }

                                if(isset($invoice->inv_outstanding)){
                                    $currentOutstanding = $invoice->inv_outstanding;
                                    $tempAmount = $invoice->inv_outstanding - $payVal;
                                    // update
                                    if((int)$tempAmount < 0){
                                        $lebih = $lebih + ($tempAmount * -1);  
                                        $tempAmount = 0;
                                    }
                                    $invoice->inv_outstanding = (int)$tempAmount;
                                    $invoice->save();
                                }
                            }
                        }else{
                            if($invoice->total_excess_payment > 0){
                                $invoice->inv_outstanding = (int)$invoice->inv_outstanding - $invoice->total_excess_payment;
                                $invoice->save();
                            }
                        }
                        // send email if flag send mail is active
                        
                        if(!empty($sendMailFlag)){
                            $cc = @MsConfig::where('name','cc_email')->first()->value;
                            if(empty($cc)) $cc = [];

                            $queue = new EmailQueue;
                            $queue->status = 'new';
                            $queue->mailclass = '\App\Mail\InvoiceMail';
                            $queue->ref_id = $invoice->id;
                            $queue->to = $invoice->MsTenant->tenan_email;
                            if(!empty($cc)) $queue->cc = $cc;
                            $queue->save();
                            // $mailClass = new \App\Mail\InvoiceMail($invoice);
                            // dispatch(new SendMail($mailClass, $invoice->MsTenant->tenan_email, $cc));
                        }
                        
                    }
                }
            });
        }catch(\Exception $e){
            return response()->json(['error'=>1, 'message'=> $e->getMessage()]);
        }

        return response()->json(['success'=>1, 'message'=>$successPosting.' Invoice posted Successfully']);
    }

    public function insert(Request $request){
        $tenanId = @$request->tenan_id;
        if(empty($tenanId)) return response()->json(['error' => 1, 'message' => 'Tenant id is required']);
        $form_secret = !empty($request->input('form_secret')) ? $request->input('form_secret') : '' ;
        $msg = 'Insert Success!!!';
        if(!empty($request->session()->get('FORM_SECRET'))) {
            if(strcasecmp($form_secret, $request->session()->get('FORM_SECRET')) === 0) {

                $inv_date = explode('-',$request->inv_date);
                $invtp = MsInvoiceType::find($request->invtp_id);
                $lastInvoiceofMonth = TrInvoice::select('inv_number')->where('inv_number','like',$invtp->invtp_prefix.'-'.substr($inv_date[0], -2).$inv_date[1].'-%')->orderBy('id','desc')->first();
                if($lastInvoiceofMonth){
                    $lastPrefix = explode('-', $lastInvoiceofMonth->inv_number);
                    $lastPrefix = (int) @$lastPrefix[2];
                }else{
                    $lastPrefix = 0;
                }
                $newPrefix = $lastPrefix + 1;
                $newPrefix = str_pad($newPrefix, 4, 0, STR_PAD_LEFT);

                $tenant = MsTenant::find($tenanId);
                $contractId = 0;
                // $contract = TrContract::find($request->contr_id);
                // $contractId = $contract->id;
                // $contract = TrContract::where('tenan_id',$tenanId)->where('contr_status','confirmed')->first();
                // if($contract) $contractId = $contract->id;
                // else $contractId = 0;

                $invHeader = [
                    'tenan_id' => $tenanId,
                    'inv_number' => $invtp->invtp_prefix."-".substr(@$inv_date[0], -2).@$inv_date[1]."-".$newPrefix,
                    'inv_faktur_no' => $invtp->invtp_prefix."-".substr(@$inv_date[0], -2).@$inv_date[1]."-".$newPrefix,
                    'inv_faktur_date' => $request->inv_date,
                    'inv_date' => $request->inv_date,
                    'inv_duedate' => $request->inv_duedate,
                    'inv_amount' => $request->amount,
                    'inv_ppn' => 0,
                    'inv_outstanding' => $request->amount,
                    'inv_ppn_amount' => $request->amount, // sementara begini dulu, ikutin cara di foto invoice
                    'inv_post' => 0,
                    'invtp_id' => $request->invtp_id,
                    // 'contr_id' => $request->contr_id,
                    'contr_id' => $contractId,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'footer' => @MsConfig::where('name','footer_invoice')->first()->value,
                    'label' => @MsConfig::where('name','footer_label_inv')->first()->value
                ];
                if(!empty(@$request->unit_id)) $invHeader['unit_id'] = $request->unit_id;

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

                        // TrContractInvoice::where('invtp_id',$request->invtp_id)->where('contr_id',$request->contr_id)->where('costd_id',$indt['costd_id'])->update($updateCtrInv[$key]);
                    }
                    // });
                }catch(\Exception $e){
                    return response()->json(['error' => 1, 'message' => 'Error Occured']);
                }

                $request->session()->forget('FORM_SECRET');
                $msg = 'Insert Invoice Success';
            }
            $msg = 'Insert Success!!';
        }   
        return response()->json(['success' => 1, 'message' => $msg]);
    }

    public function cancel(Request $request){
        try{
            $ids = $request->id;
            if(!is_array($ids)) $ids = [$ids];

            $invoices = TrInvoice::whereIn('id', $ids)->where('inv_post',0)->get();
            foreach($invoices as $invoice){
                TrContractInvoice::where('invtp_id',$invoice->invtp_id)->where('contr_id',$invoice->contr_id)->update(['continv_next_inv'=>null]);
                // TrInvoice::where('id',$id)->delete();
                TrInvoice::where('id',$invoice->id)->update(['inv_iscancel'=>1]);
                if($invoice->total_excess_payment > 0){
                    $lebih = ExcessPayment::where('unit_id',$invoice->unit_id)->get()->first();
                    $current = ($lebih->total_amount + $invoice->total_excess_payment);
                    $lebih->total_amount = $current;
                    $lebih->save();

                    LogPaymentUsed::where('inv_id',$invoice->id)->delete();

                }
            }
            return response()->json(['success' => 1, 'message' => 'Cancel '.@$invoices->count().' Invoice Success']);
        }catch(\Exception $e){
            return response()->json(['error' => 1, 'message' => 'Error Occured']);
        }
    }

    public function kuitansi(Request $request){
            $inv_id = $request->id;
            if(!is_array($inv_id)) $inv_id = [$inv_id];
            $type = $request->type;

            $invoice_data = TrInvoice::select('tr_invoice.*', 'ms_unit.unit_code')
                                    ->leftJoin('tr_contract','tr_contract.id','=','tr_invoice.contr_id')
                                    ->leftJoin('ms_unit','tr_contract.unit_id','=','ms_unit.id')
                                    ->whereIn('tr_invoice.id',$inv_id)->with('MsTenant')->get()->toArray();
            foreach ($invoice_data as $key => $inv) {
                $result = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.costd_id','tr_invoice_detail.invdt_amount','tr_invoice_detail.invdt_note','tr_period_meter.prdmet_id','tr_period_meter.prd_billing_date','tr_meter.meter_start','tr_meter.meter_end','tr_meter.meter_used','tr_meter.meter_cost','ms_cost_detail.costd_name')
                ->join('tr_invoice','tr_invoice.id',"=",'tr_invoice_detail.inv_id')
                ->leftJoin('ms_cost_detail','tr_invoice_detail.costd_id',"=",'ms_cost_detail.id')
                ->leftJoin('tr_meter','tr_meter.id',"=",'tr_invoice_detail.meter_id')
                ->leftJoin('tr_period_meter','tr_period_meter.id',"=",'tr_meter.prdmet_id')
                ->where('tr_invoice_detail.inv_id',$inv['id'])
                ->get()->toArray();
                // jabarin detail dan sisipkan order
                foreach ($result as $key2 => $detail) {
                    $ctrInv = TrContractInvoice::where('contr_id',$inv['contr_id'])->where('costd_id',$detail['costd_id'])->first();
                    $result[$key2]['order'] = !empty(@$ctrInv->order) ? $ctrInv->order : 255;
                }
                usort($result, function($a, $b) {
                    return $a['order'] - $b['order'];
                });
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

    public function reminder(Request $request)
    {
        $now = date('Y-m-d 00:00:00');
        $list = TrInvoice::select('tenan_id','tenan_name',\DB::raw('COUNT(tr_invoice.id) as totalinv'))
                ->join('ms_tenant','ms_tenant.id','=','tr_invoice.tenan_id')
                ->where('inv_outstanding', '>', 0)
                ->where('inv_iscancel', 0)
                ->where('inv_post',1)
                ->where('inv_duedate', '<=', $now);
        if(!empty($request->q))
            $list = $list->where('ms_tenant.tenan_name','ilike','%'.$request->q.'%');
        if(!empty($request->start) && !empty($request->end))
            $list = $list->where('inv_date','>=',$request->start." 00:00:00")->where('inv_date','<=',$request->end.' 23:59:59');
        $list = $list->groupBy('tenan_id','tenan_name')
                ->orderBy('tenan_name')
                ->paginate(20);
        foreach ($list as $key => $val) {
            $temp = TrInvoice::select('inv_number','inv_outstanding', 'inv_duedate')->where('tenan_id',$val->tenan_id)->where('inv_outstanding', '>', 0)->where('inv_duedate', '<=', $now)->where('inv_iscancel',0);
            if(!empty($request->start) && !empty($request->end))
                $temp = $temp->where('inv_date','>=',$request->start." 00:00:00")->where('inv_date','<=',$request->end.' 23:59:59');
            $list[$key]->invoices = $temp->get();
        }
        $data['list'] = $list;

        $data['sp1'] = MsEmailTemplate::where('name','SP1')->first();
        $data['sp2'] = MsEmailTemplate::where('name','SP2')->first();
        $data['sp3'] = MsEmailTemplate::where('name','SP3')->first();

        return view('reminder_list',$data);
    }

    public function updateReminder(Request $request)
    {
        $sp1 = MsEmailTemplate::where('name','SP1')->first();
        $sp1->title = $request->sp1_title;
        $sp1->content = $request->sp1_content;
        $sp1->save();

        $sp2 = MsEmailTemplate::where('name','SP2')->first();
        $sp2->title = $request->sp2_title;
        $sp2->content = $request->sp2_content;
        $sp2->save();

        $sp3 = MsEmailTemplate::where('name','SP3')->first();
        $sp3->title = $request->sp3_title;
        $sp3->content = $request->sp3_content;
        $sp3->save();

        $request->session()->flash('success', 'Update email template success');
        return redirect()->back();
    }

    public function reminderPrintout(Request $request){
        $id = $request->id;
        $company = MsCompany::with('MsCashbank')->first()->toArray();
        $signature = @MsConfig::where('name','digital_signature')->first()->value;
        $signatureFlag = @MsConfig::where('name','invoice_signature_flag')->first()->value;
        $invoice_data = TrInvoice::where('tenan_id', $id)->orderBy('created_at','desc')->get();

        $set_data = array(
            'id' => $id,
            'invoice_data' => $invoice_data,
            'company' => $company,
            'signature' => $signature,
            'signatureFlag' => $signatureFlag
        );
        // \Mail::to($invoice_data[0]->MsTenant->tenan_email)->send(new \App\Mail\ReminderMailDefault($set_data));
        return view('print_reminder', $set_data);
    }

    public function reminderPrintout2(Request $request){
        $id = $request->id;
        $company = MsCompany::with('MsCashbank')->first()->toArray();
        $signature = @MsConfig::where('name','digital_signature')->first()->value;
        $signatureFlag = @MsConfig::where('name','invoice_signature_flag')->first()->value;
        $emailPengelola = @MsConfig::where('name','email_pengelola')->first()->value;
        $invoice_data = TrInvoice::where('tenan_id', $id)->orderBy('created_at','desc')->get();

        $set_data = array(
            'id' => $id,
            'email' => $emailPengelola,
            'invoice_data' => $invoice_data,
            'title' => $request->title,
            'content' => $request->content,
            'company' => $company,
            'signature' => $signature,
            'signatureFlag' => $signatureFlag
        );
        // trigger mail
        \Mail::to($invoice_data[0]->MsTenant->tenan_email)->send(new \App\Mail\CustomReminderMail($set_data));
        return view('print_reminder2', $set_data);
    }

    public function sendSP(Request $request)
    {
        $tenan_id = @$request->id;
        $sp = @$request->sp;
        $invoice = TrInvoice::where('inv_outstanding','>',0)->where('inv_post',1)->where('inv_iscancel',0)->where('tenan_id',$tenan_id)->orderBy('inv_date','desc')->first();
        if($invoice){
            try{
                $email = @MsCompany::first()->email;
                \Mail::to(@$invoice->MsTenant->tenan_email)
                        // ->cc([$email])
                        ->send(new \App\Mail\SuratPeringatan('sp'.$sp, $invoice));
                return response()->json(['success' => 1]);
            }catch(\Exception $e){
                return response()->json(['success' => 0]);
            }
        }
    }

    public function printSP3(Request $request)
    {
        $tenan_id = @$request->id;
        $data['invoice'] = TrInvoice::where('inv_outstanding','>',0)->where('inv_post',1)->where('inv_iscancel',0)->where('tenan_id',$tenan_id)->orderBy('inv_date','desc')->first();
        $data['company'] = MsCompany::with('MsCashbank')->first();
        $data['emailtpl'] = MsEmailTemplate::where('name','SP3')->first();
        $data['print'] = 1;
        return view('emails.sp3', $data);
    }

    public function test()
    {
        // test function

    }

    public function unposting(Request $request)
    {
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

        $success = 0;
        foreach ($ids as $id) {
            $invoiceHd = TrInvoice::find($id);
            if($invoiceHd->inv_iscancel) break;

            // check apa suda ada payment?
            $payment = TrInvoicePaymhdr::whereHas('TrInvoicePaymdtl', function($query) use($id){
                $query->where('inv_id',$id);
            })->where('status_void',0)->first();
            if($payment) return response()->json(['error'=>1, 'message'=> 'Invoice tidak dapat diunpost jika sudah ada pembayaran terhadap invoice ini']);
            // cari di trledger
            $check = TrLedger::where('ledg_refno', $invoiceHd->inv_number)->orderBy('id')->first();
            if($check){
                $date = $check->ledg_date;
                TrLedger::where('ledg_refno', $invoiceHd->inv_number)->where('ledg_date', $date)->delete();
            }
            $invoiceHd->inv_post = false;
            $invoiceHd->save();
            $success++;
        }
        return response()->json(['success'=>1, 'message'=>$success.' Invoice unposted Successfully']);
    }

    public function reminderPreview(Request $request, $type)
    {
        $type = strtolower($type);
        $data['company'] = MsCompany::with('MsCashbank')->first();
        switch ($type) {
            case 'sp1':
                $data['emailtpl'] = MsEmailTemplate::where('name','SP1')->first();
                $view = 'emails.preview_sp1';
                break;
            case 'sp2':
                $data['emailtpl'] = MsEmailTemplate::where('name','SP2')->first();
                $view = 'emails.preview_sp2';
                break;

            default:
                $data['emailtpl'] = MsEmailTemplate::where('name','SP1')->first();
                $view = 'emails.sp1';
                break;
        }
        return view($view, $data);
    }

    public function sendmail(Request $request)
    {
        $queue = EmailQueue::where('status','new')->orderBy('created_at')->limit(1)->first();
        if(!empty($queue)){
            try{
                $mail = \Mail::to($queue->to);
                if(!empty($queue->cc)) $mail->cc($queue->cc);
                $mail->send(new $queue->mailclass($queue->ref_id));

                $queue->status = 'success';
                $queue->sent_at = date('Y-m-d H:i:s');
                $queue->save();
                //$this->info('Email sukses terkirim');
                echo "sukses";
            }catch(\Exception $e){
                $queue->note = $e->getMessage();
                $queue->status = 'failed';
                $queue->save();
                //$this->info('Terjadi error saat mengirim email');
                echo "error";
            }
        }else{
            //$this->info('Email tidak ada dalam antrean');
                echo "not available";
        }
    }

<<<<<<< Updated upstream
=======
    public function resendinvoice(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

        $successPosting = 0;
        $successIds = [];

        foreach ($ids as $id) {     
            $successIds[] = $id;
            $successPosting++;
        }

        try{
            DB::transaction(function () use($successIds){
                $sendMailFlag = @MsConfig::where('name','send_inv_email')->first()->value;
                
                if(count($successIds) > 0){
                    foreach ($successIds as $id) {
                        $invoice = TrInvoice::find($id);
                        if(!empty($sendMailFlag)){
                            $cc = @MsConfig::where('name','cc_email')->first()->value;
                            if(empty($cc)) $cc = [];

                            $queue = new EmailQueue;
                            $queue->status = 'new';
                            $queue->mailclass = '\App\Mail\InvoiceMail';
                            $queue->ref_id = $invoice->id;
                            $queue->to = $invoice->MsTenant->tenan_email;
                            if(!empty($cc)) $queue->cc = $cc;
                            $queue->save();
                        }
                    }
                }
            });
        }catch(\Exception $e){
            return response()->json(['error'=>1, 'message'=> 'Error occured when posting invoice']);
        }

        return response()->json(['success'=>1, 'message'=>$successPosting.' Invoice send Successfully']);
    }

    public function reminderm(){

        $manual = @MsEmailTemplate::where('name','MANUAL')->first();
        $manual2 = @MsEmailTemplate::where('name','MANUAL2')->first();
        $manual3 = @MsEmailTemplate::where('name','MANUAL3')->first();
        $body_email_reminder = @MsConfig::where('name','rm_body_email')->first();
        $unit= MsUnit::select('id','unit_code')->orderBy('unit_code')->get();

        return view('reminderm', array(

            'bodyemail' => $body_email_reminder->value,
            'attach' => $manual->title,
            'hrulesFlag' =>(empty($manual->title) == true ? 0 : 1),
            'unit' => $unit,
            'manual2' => $manual2,
            'manual3' => $manual3,
            'manual' => $manual
        ));
    }

    public function getreminder(Request $request){
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
            $count = ReminderH::count();
            $fetch = ReminderH::select('reminder_header.*', 'ms_unit.unit_code' ,'ms_tenant.tenan_name','ms_tenant.tenan_phone')
            		->leftjoin('ms_unit_owner','ms_unit_owner.unit_id','=','reminder_header.unit_id')
                    ->leftJoin('ms_unit','reminder_header.unit_id','=','ms_unit.id')
                    ->leftjoin('ms_tenant','ms_tenant.id','=','ms_unit_owner.tenan_id')
                    ->where('ms_unit_owner.deleted_at',NULL);

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
                    if($op == 'like'){
                        $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                    }else{
                        $fetch = $fetch->where($filter->field, $op, $filter->value);
                    }

                }
            }
            if(!empty($keyword)) $fetch = $fetch->where(function($query) use($keyword){
                                        $query->where(\DB::raw('lower(trim("contr_no"::varchar))'),'ilike','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("inv_number"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("unit_code"::varchar))'),'ilike','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("tenan_name"::varchar))'),'ilike','%'.$keyword.'%');
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
                $temp['reminder_no'] = $value->reminder_no;
                $temp['unit_code'] = !empty($value->unit) ? @$value->unit->unit_code : @$value->unit_code;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['tenan_phone'] = $value->tenan_phone;
                $temp['reminder_date'] = date('Y-m-d',strtotime($value->reminder_date));
                $temp['pokok_amount'] = "Rp. ".number_format($value->pokok_amount);
                $temp['denda_total'] = "Rp. ".number_format($value->denda_total);
                $temp['denda_outstanding'] = "Rp. ".number_format($value->denda_outstanding);
                $temp['lastsent_date'] = !empty($value->lastsent_date) ? date('Y-m-d',strtotime($value->lastsent_date)) : '';
                $temp['sent_counter'] = $value->sent_counter;
                $temp['sp_type'] = ($value->sp_type == 4 ? 'SP 1' : ($value->sp_type ==  5 ? 'SP 2' : 'SP 3'));
                $temp['posting'] = $value->posting == 1 ? 'yes' : 'no';
                $temp['checkbox'] = '<input type="checkbox" name="check" data-posting="'.$value->sent_counter.'" value="'.$value->id.'">';

                $temp['action_button'] = '<center><a href="'.url('invoice/print_manualreminder?id='.$value->id).'" class="print-window" data-width="640" data-height="660">Print</a> | <a href="'.url('invoice/print_manualreminder?id='.$value->id.'&type=pdf').'">PDF</a></center>';
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function manualreminderUpdate(Request $request){
        $subject2 = $request->judul;
        $content = $request->spcontent;
        $content_email = $request->spemailcontent;
        $upd = MsEmailTemplate::where('name','MANUAL')->first();
        $upd->update(['subject'=>$subject2,'content'=>$content]);
       
        $request->session()->flash('success', 'Update template SP1 data success');
        return redirect()->back();
    }

    public function manualreminderUpdate2(Request $request){
        $subject2 = $request->judul;
        $content = $request->spcontent;
        $content_email = $request->spemailcontent;
        $upd = MsEmailTemplate::where('name','MANUAL2')->first();
        $upd->update(['subject'=>$subject2,'content'=>$content]);
       
        $request->session()->flash('success', 'Update template SP2 data success');
        return redirect()->back();
    }

    public function manualreminderUpdate3(Request $request){
        $subject2 = $request->judul;
        $content = $request->spcontent;
        $content_email = $request->spemailcontent;
        $upd = MsEmailTemplate::where('name','MANUAL3')->first();
        $upd->update(['subject'=>$subject2,'content'=>$content]);

        $request->session()->flash('success', 'Update template SP3 data success');
        return redirect()->back();
    }

    public function manualbodyemail(Request $request){
        $content_email = $request->spemailcontent;
        $upd2 = MsConfig::where('name','rm_body_email')->first();
        $upd2->update(['value'=>$content_email]);
       
        $request->session()->flash('success', 'Update template Body Email data success');
        return redirect()->back();
    }

    public function newreminder(Request $request){
        try{
            $rmd_date = date('Y-m-d',strtotime($request->reminder_date));
            $unit = $request->unit_id;
            $sp_type = $request->sp_type;
            $tahun = date('y',strtotime($rmd_date));
            $bulan = date('n',strtotime($rmd_date));
            $variable_denda = @MsConfig::where('name','denda_variable')->first()->value;

            if(!empty($unit)){
                $outstanding = TrInvoice::select('unit_id')->where('inv_outstanding','>',0)->where('inv_iscancel','f')->where('inv_post','t')->whereRaw('((current_date::date - inv_duedate::date) > 7)')->where('unit_id',$unit)->groupBy('unit_id')->get();
            }else{
                 $outstanding = TrInvoice::select('unit_id')->where('inv_outstanding','>',0)->where('inv_iscancel','f')->where('inv_post','t')->whereRaw('((current_date::date - inv_duedate::date) > 7)')->groupBy('unit_id')->get();
            }
            $counter = 0;
            foreach($outstanding as $ost){
                //CHECK APAKAH SUDAH PERNAH DIBUAT
                $cek = ReminderH::where('reminder_date','=',$rmd_date)->where('unit_id','=',$ost->unit_id)->first();
                switch ($sp_type) {
                    case '4':
                        $ctn = @MsEmailTemplate::where('name','MANUAL')->first();
                        break;
                    case '5':
                        $ctn = @MsEmailTemplate::where('name','MANUAL2')->first();
                        break;
                    case '6':
                        $ctn = @MsEmailTemplate::where('name','MANUAL3')->first();
                        break;
                    default:
                        $ctn = @MsEmailTemplate::where('name','MANUAL')->first();
                        break;
                }
                $cek_last_sp3 = ReminderH::where('unit_id','=',$ost->unit_id)->where('sp_type',6)->where('active_tagih',1)->where('posting',1)->first();
                if(count($cek_last_sp3) == 0){
                    if(count($cek) == 0){
                        $lastnum = ReminderH::select('reminder_no')->where('reminder_no','like','RM-'.$tahun.$bulan.'-%')->orderBy('id','desc')->first();
                        if($lastnum){
                            $lastPrefix = explode('-', $lastnum->reminder_no);
                            $lastPrefix = (int) $lastPrefix[2];
                        }else{
                            $lastPrefix = 0;
                        }
                        $newPrefix = $lastPrefix + 1;
                        $newPrefix = str_pad($newPrefix, 4, 0, STR_PAD_LEFT);

                        $rmHeader = [
                            'reminder_no' => "RM-".$tahun.$bulan."-".$newPrefix,
                            'unit_id' => $ost->unit_id,
                            'reminder_date' => $rmd_date,
                            'sent_counter' => 0,
                            'isi_content' => $ctn->content,
                            'sp_type' => $sp_type
                        ];
                        $newreminder =  ReminderH::create($rmHeader);
                        //CHECK INVOICE OUTSTANDING
                        $dtl_outstanding = TrInvoice::select('id','inv_amount','inv_outstanding',DB::raw('(current_date::date - inv_duedate::date) AS hari'))->where('unit_id','=',$ost->unit_id)->where('inv_outstanding','>',0)->where('inv_iscancel','f')->where('inv_post','t')->get();
                        $total_denda = 0;
                        $total_pokok = 0;
                        foreach($dtl_outstanding as $dtl){
                            $hari_dnd = ($dtl->hari < 0 ? 0 : $dtl->hari);
                            $denda_amt = ROUND($variable_denda*$dtl->inv_outstanding*$hari_dnd,0);

                            $rmDetails = [
                                'reminderh_id' => $newreminder->id,
                                'inv_id' => $dtl->id,
                                'denda_days' => $hari_dnd,
                                'denda_amount' =>$denda_amt
                            ];
                            $total_denda = $total_denda + $denda_amt;
                            $total_pokok = $total_pokok + $dtl->inv_outstanding;
                            $newdtl = ReminderD::create($rmDetails);
                        }
                        ReminderH::where('id',$newreminder->id)->update(['pokok_amount'=>$total_pokok,'denda_total'=>$total_denda,'denda_outstanding'=>($total_denda+$total_pokok),'active_tagih'=>0,'posting'=>0]);
                        $counter++;
                    }
                }else{
                    //skip klo sp 3 udh pernah di buat dan masih aktif di tagih
                }
            }
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
        return ['status' => 1, 'message' => 'Successfully create '.$counter.' Reminder'];
    }

    public function deletereminder(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];
        $success = 0; 
        for($i=0; $i<count($ids); $i++){
            //CEK APABILA SUDAH DIKIRIM GK BISA DI DELETE
            $check = ReminderH::where('id',$ids[$i])->first();
            if($check->sent_counter == 0){
                ReminderD::where('reminderh_id',$ids[$i])->delete();
                ReminderH::where('id',$ids[$i])->delete();
                $success++;
            }
        }

        $message = $success.' Reminder Ter-delete';
        return response()->json(['success'=>1, 'message'=> $message]);
    }

    public function postingreminder(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];
        $success = 0; 
        for($i=0; $i<count($ids); $i++){
            $check = ReminderH::where('id',$ids[$i])->first();
            //cek semua sp yang masih aktif dibuat non aktif dan di aktifin cuma 1
            ReminderH::where('unit_id',$check->unit_id)->update(['active_tagih'=>0]);
        }

        for($k=0; $k<count($ids); $k++){
        	ReminderH::where('id',$ids[$k])->update(['posting'=>1,'active_tagih'=>1]);
        	$success++;
        }

        $message = $success.' Reminder Ter-posting';
        return response()->json(['success'=>1, 'message'=> $message]);
    }

    public function unpostingreminder(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];
        $success = 0; 

        for($k=0; $k<count($ids); $k++){
            ReminderH::where('id',$ids[$k])->update(['posting'=>0,'active_tagih'=>0]);
            $success++;
        }

        $message = $success.' Reminder Ter-Unposting';
        return response()->json(['success'=>1, 'message'=> $message]);
    }

    public function sendreminder(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];
        $success_send = 0;
        $cc = @MsConfig::where('name','cc_email')->first()->value;
        for($k=0; $k<count($ids); $k++){
            $reminder_inv = ReminderH::select('reminder_header.id','reminder_header.unit_id','tenan_email','cc_email','ms_email_templates.subject')
            ->join('ms_unit_owner','ms_unit_owner.unit_id','=','reminder_header.unit_id')
            ->join('ms_email_templates','ms_email_templates.id','=','reminder_header.sp_type')
            ->join('ms_tenant','ms_tenant.id','=','ms_unit_owner.tenan_id')
            ->where('ms_unit_owner.deleted_at','=',NULL)->where('ms_tenant.deleted_at','=',NULL)->where('reminder_header.id',$ids[$k])->get();
            //UPDATE REMINDERH
            $upd3 = ReminderH::where('id','=',$ids[$k])->first();
            $ct = (int)($upd3->sent_counter)+1;
            $upd3->update(['lastsent_date'=>date('Y-m-d'),'sent_counter'=>$ct]);
            $success_send++;
            foreach($reminder_inv as $invoice_rmd){
                $queue = new EmailQueue;
                $queue->status = 'new';
                $queue->mailclass = '\App\Mail\ManualReminderMail';
                $queue->ref_id = $ids[$k];
                $queue->to = $invoice_rmd->tenan_email;
                if(!empty($cc)) $queue->cc = $cc;
                $queue->save();
                
                if($invoice_rmd->cc_email != NULL || $invoice_rmd->cc_email != ''){
                    $cc_tenant = explode('|', $invoice_rmd->cc_email);
                    if(count($cc_tenant) > 0){
                        for($i=0; $i<count($cc_tenant); $i++){
                            $tnt2 = explode('~', $cc_tenant[$i]);
                            if(count($tnt2) > 0){
                                $unit_dt = $tnt2[0];
                                $units = MsUnit::where('unit_code', $unit_dt)->first();
                                $kirim = explode(';', $tnt2[1]);
                                if(count($units) > 0){
                                    if($units->id == $invoice_rmd->unit_id){ 
                                        if(count($kirim) > 0){
                                            for($j=0; $j<count($kirim); $j++){
                                                $queue = new EmailQueue;
                                                $queue->status = 'new';
                                                $queue->mailclass = '\App\Mail\ManualReminderMail';
                                                $queue->ref_id = $ids[$k];
                                                $queue->to = $kirim[$j];
                                                if(!empty($cc)) $queue->cc = $cc;
                                                $queue->save();
                                            }
                                        }
                                    }
                                } 
                            }
                        }
                    }
                }         
            }
        }
        return response()->json(['success'=>1, 'message'=>$success_send.' reminder send Successfully']);
    }

    public function print_manualreminder(Request $request){
        try{
            $inv_id = $request->id;
            if(!is_array($inv_id)) $inv_id = [$inv_id];
            $type = $request->type;

            $invoice_data = ReminderH::select('reminder_header.*', 'ms_unit.unit_code','ms_email_templates.subject','ms_tenant.tenan_name')
            						->leftjoin('ms_unit_owner','ms_unit_owner.unit_id','=','reminder_header.unit_id')
                                    ->leftJoin('ms_unit','reminder_header.unit_id','=','ms_unit.id')
                                    ->leftjoin('ms_tenant','ms_tenant.id','=','ms_unit_owner.tenan_id')
                                    ->leftjoin('ms_email_templates','ms_email_templates.id','=','reminder_header.sp_type')
                                    ->where('ms_unit_owner.deleted_at',NULL)
                                    ->whereIn('reminder_header.id',$inv_id)->get()->toArray();
            foreach ($invoice_data as $key => $inv) {
                $result = ReminderD::select('tr_invoice.inv_number','tr_invoice.inv_outstanding','inv_amount','ms_invoice_type.invtp_name','denda_days','denda_amount')
                ->join('tr_invoice','reminder_details.inv_id',"=",'tr_invoice.id')
                ->leftJoin('ms_invoice_type','ms_invoice_type.id',"=",'tr_invoice.invtp_id')
                ->where('reminder_details.reminderh_id',$inv['id'])
                ->orderBy('inv_date','asc')
                ->get()->toArray();

                $lastreminder = ReminderH::select('reminder_date')->where('unit_id',$inv['unit_id'])->where('sp_type',4)->orderBy('reminder_date','DESC')->first();

                $invoice_data[$key]['details'] = $result;
                if(count($lastreminder) > 0){
                    $invoice_data[$key]['rdate'] = $lastreminder->reminder_date;
                }else{
                    $invoice_data[$key]['rdate'] = '';
                }
            }

            $company = MsCompany::with('MsCashbank')->first()->toArray();
            $signature = @MsConfig::where('name','digital_signature')->first()->value;
            $signatureFlag = @MsConfig::where('name','invoice_signature_flag')->first()->value;
            $content = MsEmailTemplate::where('name','MANUAL')->first();

            $set_data = array(
                'invoice_data' => $invoice_data,
                'result' => $result,
                'company' => $company,
                'signature' => $signature,
                'signatureFlag' => $signatureFlag,
                'title' => $content->subject,
                'content' => $content->content,
                'type' => $type
            );

            if($type == 'pdf'){
                $pdf = PDF::loadView('print_manualr', $set_data)->setPaper('a4');

                return $pdf->download('REMINDER-OUTSTANDING-INV.pdf');
            }else{
                return view('print_manualr', $set_data);
            }
         }catch(\Exception $e){
             return $e->getMessage();
         }
    }

    public function ajaxGetManualInv(Request $request){
        $rmd_id = $request->id;
        $invoice = ReminderH::select('id','manual_inv')->find($rmd_id)->toArray();
        if(!$invoice) return response()->json(['errMsg' => 'Reminder not found']);

        return response()->json(['status' => 1, 'result' => $invoice]);
    }

    public function ajaxStoreManualInv(Request $request){
        if(!empty(@$request->id)){
            try{
                $reminder = ReminderH::find($request->id);
                $reminder->manual_inv = $request->manual_invoice;
                $reminder->save();
                return response()->json(['success' => 1]);
            }catch(\Exception $e){
                return response()->json(['errMsg' => $e->getMessage()]);
            }
        }else{
            return response()->json(['errMsg' => 'Reminder ID not found']);
        }
    }

    public function creditnote(){

        $contract_data = TrInvoice::select('ms_tenant.tenan_name', 'tr_contract.contr_code', 'tr_contract.id', 'tr_invoice.contr_id')
        ->join('ms_tenant','tr_invoice.tenan_id','=','ms_tenant.id')
        ->join('tr_contract','tr_invoice.contr_id','=','tr_contract.id')
        ->orderBy('ms_tenant.tenan_name', 'ASC')
        ->groupBy('tr_invoice.contr_id', 'ms_tenant.tenan_name', 'tr_contract.contr_code', 'tr_contract.id')
        ->where('tr_invoice.inv_outstanding', '>', 0)
        ->get()
        ->toArray();

        $coa = MsMasterCoa::where('coa_year',date('Y'))->where('coa_isparent',FALSE)->get();

        if(!empty($contract_data)){
            $temp = array();
            foreach ($contract_data as $key => $value) {
                $temp[] = array(
                    'id' => $value['id'],
                    'tenan_name' => sprintf('%s | %s', $value['tenan_name'], $value['contr_code'])
                );
            }

            $contract_data = $temp;
        }

        return view('creditnote', array(
            'contract_data' => $contract_data,
            'coa' => $coa
        ));
    }

    public function creditnote_get(Request $request){
        try{
            $keyword = @$request->q;
            $datefrom = @$request->datefrom;
            $dateto = @$request->dateto;

            $page = $request->page;
            $perPage = $request->rows;
            $page-=1;
            $offset = $page * $perPage;
            $sort = @$request->sort;
            $order = @$request->order;
            $filters = @$request->filterRules;
            if(!empty($filters)) $filters = json_decode($filters);

            $count = CreditNoteH::count();
            $fetch = CreditNoteH::select('tr_creditnote_hdr.*','tr_invoice.inv_number','ms_unit.unit_code')->join('ms_unit','ms_unit.id',"=",'tr_creditnote_hdr.unit_id')->join('tr_invoice','tr_invoice.id',"=",'tr_creditnote_hdr.inv_id');

            if(!empty($filters) && count($filters) > 0){
                foreach($filters as $filter){
                    $op = "like";
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
                    if($op == 'like'){
                        $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                    }else{
                        $fetch = $fetch->where($filter->field, $op, $filter->value);
                    }

                }
            }

            if(!empty($keyword)) $fetch = $fetch->where(function($query) use($keyword){
                                        $query->where(\DB::raw('lower(trim("creditnote_number"::varchar))'),'like','%'.$keyword.'%')->orWhere(\DB::raw('lower(trim("tenan_name"::varchar))'),'like','%'.$keyword.'%');
                                    });
            if(!empty($datefrom)) $fetch = $fetch->where('tr_invoice_paymhdr.creditnote_date','>=',$datefrom);
            if(!empty($dateto)) $fetch = $fetch->where('tr_invoice_paymhdr.creditnote_date','<=',$dateto);
            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);

            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['creditnote_number'] = $value->creditnote_number;
                $temp['creditnote_keterangan'] = $value->creditnote_keterangan;
                $temp['creditnote_date'] = date('d/m/Y',strtotime($value->creditnote_date));
                $temp['posting_at'] = ($value->posting_at == NULL ? '' : date('d/m/Y',strtotime($value->posting_at)));
                $temp['inv_number'] = $value->inv_number;
                $temp['unit_code'] = $value->unit_code;
                $temp['total_amt'] = "Rp. ".number_format($value->total_amt);
                $temp['checkbox'] = '<input type="checkbox" name="check" value="'.$value->id.'">';
                $creditnote_post = $temp['creditnote_post'] = !empty($value->creditnote_post) ? 'yes' : 'no';

                $action_button = '<a href="#" title="View Detail" data-toggle="modal" data-target="#detailModal" data-id="'.$value->id.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a>';

                if($creditnote_post == 'no'){
                    if(\Session::get('role')==1 || in_array(78,\Session::get('permissions'))){
                        $action_button .= ' | <a href="#" data-id="'.$value->id.'" title="Posting Payment" class="posting-confirm"><i class="fa fa-arrow-circle-o-up"></i></a>';
                    }
                    if(\Session::get('role')==1 || in_array(78,\Session::get('permissions'))){
                        $action_button .= ' | <a href="creditnote/creditnote_void?id='.$value->id.'" title="Void" class="void-confirm"><i class="fa fa-ban"></i></a>';
                    }
                }
                $temp['action_button'] = $action_button;

                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function insertcreditnote(Request $request){
        $messages = [
            'creditnote_date.required' => 'Payment Date is required',
        ];

        $validator = Validator::make($request->all(), [
            'creditnote_date' => 'required:tr_creditnote_hdr'
        ], $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ['status' => 0, 'message' => $errors];
        }

        $data_payment = $request->input('data_payment');
        $detail_payment = array();
        $payment_ids = [];
        if(!empty($data_payment) && count($data_payment['invpayd_amount']) > 0){
            $lastPayment = CreditNoteH::where(\DB::raw('EXTRACT(YEAR FROM created_at)'),'=',date('Y'))
                                ->where(\DB::raw('EXTRACT(MONTH FROM created_at)'),'=',date('m'))
                                ->orderBy('created_at','desc')->first();
            $indexNumber = null;
            if($lastPayment){
                $index = explode('.',$lastPayment->creditnote_number);
                $index = (int) end($index);
                $index+= 1;
                $indexNumber = $index;
                $index = str_pad($index, 3, "0", STR_PAD_LEFT);
            }else{
                $index = "001";
                $indexNumber = 1;
            }

            $lebih = 0;
            foreach ($data_payment['invpayd_amount'] as $key => $value) {
                $tempAmount = $currentOutstanding = 0;
                $invoice = TrInvoice::find($key);
                if(!empty($value)){
                    $cek_pay = true;

                    $payVal = (int)$data_payment['totalpay'][$key];
                    $total = $payVal;

                    $action = new CreditNoteH;
                    $action->creditnote_number = 'CR'.'-'.date('Y-m').'.'.$index;
                    $action->creditnote_date = $request->input('creditnote_date');
                    $action->creditnote_keterangan = $request->input('creditnote_keterangan');
                    $action->creditnote_post = !empty($request->input('creditnote_post')) ? true : false;
                    $action->unit_id = $request->input('tenan_id');
                    $action->total_amt = $payVal;
                    $action->inv_id = $key;

                    if($action->save()){
                        $payment_id = $action->id;
                        $payment_ids[] = $payment_id;

                        $action_detail = new CreditNoteD;
                        $action_detail->inv_amount = $invoice->inv_outstanding;
                        $action_detail->credit_amount = $payVal;
                        $action_detail->inv_id = $key;
                        $action_detail->jurnal_type = $request->input('coatype');
                        $action_detail->coa_code = $request->input('coa_code');
                        $action_detail->creditnote_hdr_id = $payment_id;
                       
                        $action_detail->save();
                        $currentOutstanding = $invoice->inv_outstanding;
                        $tempAmount = $invoice->inv_outstanding - $payVal;
                        $invoice->inv_outstanding = (int)$tempAmount;
                        $invoice->save();
                    }

                    $indexNumber++;
                    $index = str_pad($indexNumber, 3, "0", STR_PAD_LEFT);
                }
            }
        }else{
            return ['status' => 0, 'message' => 'Please Check at least one of Invoice for payment'];
        }
        return ['status' => 1, 'message' => 'Insert Success', 'paym_id' => $payment_ids];
    }

    public function creditnote_void(Request $request){
        $id = $request->id;
        $paymHeader = CreditNoteH::find($id);
        // default result
        $result = array(
            'status'=>0,
            'message'=> 'Data not found'
        );
        if(!empty($paymHeader)){
            if($paymHeader->creditnote_post == 't'){
                $result['message'] = 'You can\'t void posted payment';
                return response()->json($result);
            }
            foreach ($paymHeader->CreditNoteD as $payDtl) {
                $invoice_id = $payDtl->inv_id;
                $invoice = TrInvoice::find($invoice_id);

                $invoice->inv_outstanding += $payDtl->credit_amount;
                $invoice->save();
            }
            CreditNoteH::where('id',$id)->delete();
            CreditNoteD::where('creditnote_hdr_id',$id)->delete();

            $result = array(
                'status'=>1,
                'message'=> 'Success void credit note'
            );
        }else{
            return response()->json($result);
        }
        return response()->json($result);
    }


    public function posting_creditnote(Request $request){
        $ids = $request->id;
        $postingdate = date('Y-m-d',strtotime($request->posting_date));
        if(!is_array($ids)) $ids = explode(',',$ids);
        $ids = CreditNoteH::where('creditnote_post',0)->whereIn('id', $ids)->pluck('id');
        if(count($ids) < 1) return response()->json(['error'=>1, 'message'=> "0 Credit Note Terposting"]);

        $coayear = date('Y');
        $month = date('m');
        $journal = [];

        $jourType = MsJournalType::where('jour_type_prefix','AR')->first();
        if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "AR" first before posting an credit note']);
        $successPosting = 0;
        $successIds = [];
        $piutangIds = [];

        // cek backdate dr closing bulanan/tahunan
        $lastclose = TrLedger::whereNotNull('closed_at')->orderBy('closed_at','desc')->first();
        $limitMinPostingDate = null;
        if($lastclose) $limitMinPostingDate = date('Y-m-t', strtotime($lastclose->closed_at));

        foreach ($ids as $id) {
            $lastJournal = Numcounter::where('numtype','JG')->where('tahun',$coayear)->where('bulan',$month)->first();
            if(count($lastJournal) > 0){
                $lst = $lastJournal->last_counter;
                $nextJournalNumber = $lst + 1;
                $lastJournal->update(['last_counter'=>$nextJournalNumber]);
            }else{
                $nextJournalNumber = 1;
                $lastcounter = new Numcounter;
                $lastcounter->numtype = 'JG';
                $lastcounter->tahun = date('Y');
                $lastcounter->bulan = date('m');
                $lastcounter->last_counter = 1;
                $lastcounter->save();
            }

            $paymentHd = CreditNoteH::find($id);
            $paymentDtl = CreditNoteD::select('tr_creditnote_dtl.coa_code','credit_amount','tr_invoice_detail.coa_code as coa_ar','ms_cost_item.ar_coa_code','ms_cost_detail.costd_name','inv_number')
                                        ->join('tr_invoice','tr_creditnote_dtl.inv_id','=','tr_invoice.id')
                                        ->join('tr_invoice_detail','tr_invoice.id','=','tr_invoice_detail.inv_id')
                                        ->leftjoin('ms_cost_detail','ms_cost_detail.id','=','tr_invoice_detail.costd_id')
                                        ->leftjoin('ms_cost_item','ms_cost_item.id','=','ms_cost_detail.cost_id')
                                        ->where('creditnote_hdr_id',$id)->get();
            $refno = $paymentHd->creditnote_number;
            $nextJournalNumber = str_pad($nextJournalNumber, 6, 0, STR_PAD_LEFT);
            $journalNumber = "JG/".$coayear."/".$this->Romawi($month)."/".$nextJournalNumber;
            //DEBET
            $cek = 0;
            foreach ($paymentDtl as $value) {
                if($cek == 0){
                $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                $journal[] = [
                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => $postingdate,
                        'ledg_refno' => $refno,
                        'ledg_debit' => $value->credit_amount,
                        'ledg_credit' => 0,
                        'ledg_description' => $paymentHd->creditnote_keterangan,
                        'coa_year' => date('Y',strtotime($postingdate)),
                        'coa_code' => $value->coa_code,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id, //hardcode utk finance
                        'dept_id' => 3, //hardcode utk finance
                        'modulname' => 'Credit Note',
                        'refnumber' =>$id
                    ];
                    $cek++;
                }
            }   
            
            //KREDIT
            foreach ($paymentDtl as $value) {
                $service = ROUND($value->credit_amount/1.1,0);
                $sinkinfund = ROUND(10/100 * $service,0);
                if(trim($value->ar_coa_code) == '10310'){
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => $postingdate,
                                'ledg_refno' => $value->inv_number,
                                'ledg_debit' => 0,
                                'ledg_credit' => $sinkinfund,
                                'ledg_description' => $paymentHd->creditnote_keterangan,
                                'coa_year' => date('Y',strtotime($postingdate)),
                                'coa_code' => ($value->ar_coa_code == NULL ? $value->coa_ar : $value->ar_coa_code),
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => $jourType->id, //hardcode utk finance
                                'dept_id' => 3, //hardcode utk finance
                                'modulname' => 'Credit Note',
                                'refnumber' =>$id
                            ];
                }else if(trim($value->ar_coa_code) == '10320'){
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => $postingdate,
                                'ledg_refno' => $value->inv_number,
                                'ledg_debit' => 0,
                                'ledg_credit' => $service,
                                'ledg_description' => $paymentHd->creditnote_keterangan,
                                'coa_year' => date('Y',strtotime($postingdate)),
                                'coa_code' => ($value->ar_coa_code == NULL ? $value->coa_ar : $value->ar_coa_code),
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => $jourType->id, //hardcode utk finance
                                'dept_id' => 3, //hardcode utk finance
                                'modulname' => 'Credit Note',
                                'refnumber' =>$id
                            ];
                }else{
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    $journal[] = [
                                'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                'ledge_fisyear' => $coayear,
                                'ledg_number' => $journalNumber,
                                'ledg_date' => $postingdate,
                                'ledg_refno' => $value->inv_number,
                                'ledg_debit' => 0,
                                'ledg_credit' => $value->credit_amount,
                                'ledg_description' => $paymentHd->creditnote_keterangan,
                                'coa_year' => date('Y',strtotime($postingdate)),
                                'coa_code' => ($value->ar_coa_code == NULL ? $value->coa_ar : $value->ar_coa_code),
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                                'jour_type_id' => $jourType->id, //hardcode utk finance
                                'dept_id' => 3, //hardcode utk finance
                                'modulname' => 'Credit Note',
                                'refnumber' =>$id
                            ];
                }
            }
            $successIds[] = $id;
        }
        // INSERT DATABASE
        DB::beginTransaction();
        try{
            // insert journal
            TrLedger::insert($journal);

            if(count($successIds) > 0){
                foreach ($successIds as $id) {
                    CreditNoteH::where('id', $id)->update(['creditnote_post'=>1, 'posting_at'=>$postingdate, 'posting_by'=>Auth::id()]);
                }
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['error'=>1, 'message'=> $e->getMessage()]);
        }
        $message = count($successIds).' Credit Note Terposting';
        return response()->json(['success'=>1, 'message'=> $message]);
    }

    public function unposting_creditnote(Request $request){
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

        $ids = CreditNoteH::where('creditnote_post',1)->whereIn('id', $ids)->pluck('id');
        if(count($ids) < 1) return response()->json(['error'=>1, 'message'=> "0 Credit Note Ter-unposting"]);
        $sc = 0;
        foreach ($ids as $id) {
            TrLedger::where('refnumber', $id)->where('modulname','Credit Note')->delete();
            $pay = CreditNoteH::find($id);
            $pay->update(['creditnote_post'=>0,'posting_by'=>NULL,'posting_at'=>NULL]);
            $sc++;   
        }
        $message = $sc.' Credit Note Unposting';
        return response()->json(['success'=>1, 'message'=> $message]);
    }

    public function getdetail_creditnote(Request $request){
        $id = $request->id;
        $note_hdr = CreditNoteH::find($id);
        $note_dtl = CreditNoteD::select('tr_creditnote_dtl.*','tr_invoice.inv_number','ms_master_coa.coa_name')
                    ->join('tr_invoice','tr_invoice.id','=','tr_creditnote_dtl.inv_id')
                    ->join('ms_master_coa','ms_master_coa.coa_code','=','tr_creditnote_dtl.coa_code')
                    ->where('tr_creditnote_dtl.creditnote_hdr_id','=',$id)
                    ->where('ms_master_coa.coa_year','=',date('Y'))
                    ->get();

        return view('modal.creditnote', ['header' => $note_hdr, 'detail' => $note_dtl]);
    }

    public function Romawi($n){
        $hasil = "";
        $iromawi = array("","I","II","III","IV","V","VI","VII","VIII","IX","X",20=>"XX",30=>"XXX",40=>"XL",50=>"L",60=>"LX",70=>"LXX",80=>"LXXX",90=>"XC",100=>"C",200=>"CC",300=>"CCC",400=>"CD",500=>"D",600=>"DC",700=>"DCC",800=>"DCCC",900=>"CM",1000=>"M",2000=>"MM",3000=>"MMM");
        if(array_key_exists($n,$iromawi)){
            $hasil = $iromawi[$n];
        }elseif($n >= 11 && $n <= 99){
            $i = $n % 10;
            $hasil = $iromawi[$n-$i].$this->Romawi($n % 10);
        }elseif($n >= 101 && $n <= 999){
            $i = $n % 100;
            $hasil = $iromawi[$n-$i].$this->Romawi($n % 100);
        }else{
            $i = $n % 1000;
            $hasil = $iromawi[$n-$i].$this->Romawi($n % 1000);
        }
        return $hasil;
    }

    public function invEdit(Request $request, $id)
    {
        $data['detail'] = TrInvoiceDetail::select(
            'invdt_amount','invdt_note','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin','costd_admin_type','meter_start','meter_end','tr_invoice_detail.costd_id')
        ->join('ms_cost_detail','ms_cost_detail.id','=','tr_invoice_detail.costd_id')
        ->leftjoin('tr_meter','tr_meter.id','=','tr_invoice_detail.meter_id')->where('inv_id',$id)->get();
        $data['ids'] = $id;
        return view('invoice_edit',$data);
    }

    public function updateInv(Request $request){
        $inv_id = $request->id;
        $st_data = $request->start;
        $end_data = $request->end;
        $cost_data = $request->costd_id;
        $start_note = $request->note;
        $bpju = @MsConfig::where('name','ppju')->first()->value;
        
        $subtotal_data = 0;
        $ids = array();
        for($i=0; $i<count($cost_data); $i++){
            $cost_detail = MsCostDetail::find($cost_data[$i]);
            $last_detail = TrInvoiceDetail::where('inv_id',$inv_id)->where('costd_id',$cost_data[$i])->first();
            $meter_start = $st_data[$i];
            $meter_end = $end_data[$i];
            $used = $meter_end - $meter_start;

            if($cost_data[$i] != '4'){
                //LISTRIK
                $min = (40 * $cost_detail->daya * $cost_detail->costd_rate) + $cost_detail->costd_burden;
                $elec_cost = ($used *  $cost_detail->costd_rate) + $cost_detail->costd_burden;
                if($elec_cost > $min){
                    $meter_cost = $elec_cost;
                }else{
                    $meter_cost = $min;
                }
                $bpju_s = round($bpju/100 * $meter_cost);
                $subtotal = $meter_cost + $bpju_s;

                if($cost_detail->value_type == 'percent'){
                    $public_area = round($cost_detail->percentage / 100 * $subtotal);
                }else{
                    $public_area = $cost_detail->percentage;
                    if(empty($public_area)) $public_area = 0;
                }

                if(!empty($cost_detail->costd_admin_type) && $cost_detail->costd_admin_type == 'percent'){
                    $admincost = round($cost_detail->costd_admin / 100 * $subtotal);
                }else{
                    $admincost = $cost_detail->costd_admin;
                }

                $total = $subtotal + $admincost + $public_area;

                if(!empty($cost_detail->grossup_pph)){
                    $grossup_total = round($total / 0.9 * 0.1);
                    // echo "Grossup $grossup_total<br>";
                    $total += $grossup_total;
                }

                $last_note = "<br>Awal : ".number_format($meter_start,2)."&nbsp;&nbsp;&nbsp; Akhir : ".number_format($meter_end,2)."&nbsp;&nbsp;&nbsp; Pakai : ".number_format($used,2)."&nbsp;&nbsp;&nbsp;Abodemen : ".number_format($cost_detail->costd_burden,2)."&nbsp;&nbsp;&nbsp;Tarif (/kWh): ".number_format($cost_detail->costd_rate,2)."&nbsp;&nbsp;&nbsp;PPJU : ".$bpju."% &nbsp;&nbsp;&nbsp;Beban Bersama : ".$public_area."&nbsp;&nbsp;&nbsp;Biaya Admin : ".$admincost;
                $notes = $start_note[$i].$last_note;

            }else{
                //AIR
                $admin = $cost_detail->costd_admin/100 * (($used * $cost_detail->costd_rate) + $cost_detail->costd_burden);
                $total = ($used * $cost_detail->costd_rate) + ($cost_detail->costd_burden + $admin);
                $last_note = "<br>Awal : ".number_format($meter_start,2)."&nbsp;&nbsp;&nbsp; Akhir : ".number_format($meter_end,2)."&nbsp;&nbsp;&nbsp; Pakai : ".number_format($used,2)."&nbsp;&nbsp;&nbsp; Tarif (per M3) : ".number_format($cost_detail->costd_rate,2)."&nbsp;&nbsp;&nbsp;Abodemen : ".number_format($cost_detail->costd_burden,2)."&nbsp;&nbsp;&nbsp;Adm : ".number_format($admin,2);
                $notes = $start_note[$i].$last_note;

            }

            $invdet = [
                        'invdt_amount' => $total,
                        'invdt_note' => $notes,
                        'costd_id' => $cost_data[$i],
                        'inv_id' => $inv_id,
                        'meter_id' => $last_detail->meter_id,
                        'coa_code' => NULL
                    ];
            $last_detail->update($invdet);
            $subtotal_data = $subtotal_data + $total;

            //UPDATE TRMETER
            $upd_meter = TrMeter::find($last_detail->meter_id);
            $upd_meter->update(['meter_start' => $meter_start,'meter_end' => $meter_end,'meter_used' => $used]);

        }
        //DELETE PPN DAN MATERAI
        TrInvoiceDetail::where('inv_id',$inv_id)->where('invdt_note','PPN')->delete();
        TrInvoiceDetail::where('inv_id',$inv_id)->where('invdt_note','MATERAI')->delete();        
        $all = $subtotal_data;

        //UPDATE HEADER
        $upd_header = TrInvoice::find($inv_id);
        $upd_header->update(['inv_amount' => $all,'inv_outstanding' => $all]);

        $request->session()->flash('success', 'Update edit Invoice');
        return redirect()->back();
    }

>>>>>>> Stashed changes
}
