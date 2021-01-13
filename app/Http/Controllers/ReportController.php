<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrInvoice;
use App\Models\TrInvoiceDetail;
use App\Models\TrInvoicePaymhdr;
use App\Models\TrInvoicePaymdtl;
use App\Models\MsCompany;
use App\Models\TrContract;
use App\Models\TrMeter;
use App\Models\TrLedger;
use App\Models\MsUnit;
use App\Models\MsTenant;
use App\Models\MsInvoiceType;
use App\Models\MsMasterCoa;
use App\Models\MsJournalType;
use App\Models\MsDepartment;
use App\Models\MsCashBank;
use App\Models\MsPaymentType;
use App\Models\MsHeaderFormat;
use App\Models\MsDetailFormat;
use App\Models\MsSupplier;
use App\Models\TrApHeader;
use App\Models\TrApDetail;
use App\Models\TrApPaymentHeader;
use App\Models\TrApPaymentDetail;
use App\Models\TrPOHeader;
use App\Models\ViewInv;
use App\Models\TrBudgetHdr;
use App\Models\TrBudgetDtl;
use App\Models\TrBudgetDetail;
use App\Models\Cashflow;
use App\Models\Realisasi;
use App\Models\FittingIn;
use App\Models\FittingOut;
use App\Models\ManualHdr;
use App\Models\MsConfig;
use App\Models\ReminderH;
use App\Models\ReminderD;
use App\Models\TrDendaPayment;
use App\Models\TrVaOther;
use App\Models\ExcessPayment;
use App\Models\LogExcessPayment;
use App\Models\LogPaymentUsed;
use PDF;
use DB;
use Excel;
use DateTime;

class ReportController extends Controller
{
	public function arview(){
        $data['invtypes'] = MsInvoiceType::all();
        $data['banks'] = MsCashBank::all();
		$data['payment_types'] = MsPaymentType::all();
        return view('report_ar',$data);
	}

    public function arbyInvoice(Request $request){
        $from = @$request->from;
        $to = @$request->to;
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $unit = @$request->unit_2;
        $data['tahun'] = '';
        if(!empty($from) && !empty($to)) $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to));
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "AR Invoices Report";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_ar_invoice';
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        $fetch = TrInvoice::select('ms_unit.unit_code','ms_tenant.tenan_name','tr_invoice.id AS inv_id','tr_invoice.inv_number','tr_invoice.inv_date','tr_invoice.inv_duedate','tr_invoice.inv_amount','tr_contract.contr_no','ms_unit.unit_sqrt')
                    ->join('ms_unit','tr_invoice.unit_id',"=",'ms_unit.id')
                    ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                    ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                    ->where('tr_invoice.inv_iscancel',0)
                    ->where('tr_invoice.inv_post','=',TRUE);
        if($from) $fetch = $fetch->where('tr_invoice.inv_date','>=',$from);
        if($to) $fetch = $fetch->where('tr_invoice.inv_date','<=',$to);
        if($unit) $fetch = $fetch->where('ms_unit.unit_code','LIKE',  '%'.$unit.'%');
        $fetch = $fetch->orderBy('ms_unit.unit_code','desc');
        $fetch = $fetch->get();

        $data['invoices'] = [];
        $dtinv = array();
        $total = 0;
        foreach ($fetch as $inv) {
            $tempInv = [
                'unit_code' => $inv->unit_code,
                'unit_sqrt' => $inv->unit_sqrt,
                'tenan_name' => $inv->tenan_name,
                'inv_number' => $inv->inv_number,
                'inv_date' => date('d/m/Y',strtotime($inv->inv_date)),
                'inv_duedate' => date('d/m/Y',strtotime($inv->inv_duedate)),
                'inv_amount' => $inv->inv_amount
            ];
            $total = $total + $inv->inv_amount;
            $tempInv['details'] = [];
            $result = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.invdt_amount','tr_invoice_detail.invdt_note','tr_period_meter.prdmet_id','tr_period_meter.prd_billing_date','tr_meter.meter_start','tr_meter.meter_end','tr_meter.meter_used','tr_meter.meter_cost','ms_cost_detail.costd_name','ms_cost_detail.costd_unit')
                ->join('tr_invoice','tr_invoice.id',"=",'tr_invoice_detail.inv_id')
                ->leftJoin('ms_cost_detail','tr_invoice_detail.costd_id',"=",'ms_cost_detail.id')
                ->leftJoin('tr_meter','tr_meter.id',"=",'tr_invoice_detail.meter_id')
                ->leftJoin('tr_period_meter','tr_period_meter.id',"=",'tr_meter.prdmet_id')
                ->where('tr_invoice_detail.inv_id',$inv->inv_id)
                ->get();
            foreach ($result as $key => $value) {
                $tempInv['details'][] = [
                    'invdt_note' => $value->invdt_note,
                    'invdt_amount' => (float)$value->invdt_amount,
                    'meter_start' => (int)$value->meter_start,
                    'meter_end' => (int)$value->meter_end,
                    'meter_used' => !empty($value->meter_used) ? (int)$value->meter_used." ".$value->costd_unit : (int)$value->meter_used
                    ];
            }
            $data['invoices'][] = $tempInv;
            $dtinv[] = $tempInv;
        }
        $summary = [
            'unit_code' => '',
            'unit_sqrt' => '',
            'tenan_name' => '',
            'inv_number' => '',
            'inv_date' => '',
            'inv_duedate' => 'TOTAL INVOICE',
            'inv_amount' => $total,
            'details'=>array(array('invdt_note'=>'','invdt_amount'=>'','meter_start'=>'','meter_end'=>'','meter_used'=>''))
        ];
        $data['invoices'][] = $summary;
        $dtinv[] = $summary;

        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('AR_Invoice_'.$from.'_to_'.$to.'.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $data_ori = $dtinv;
            $data = array();
            $k=0;
            for($i=0; $i<count($data_ori); $i++){
                $data[$k]=array(
                    'No.Unit' =>$data_ori[$i]['unit_code'],
                    'Luas' =>$data_ori[$i]['unit_sqrt'],
                    'Nama Tenant' =>$data_ori[$i]['tenan_name'],
                    'No.Invoice' =>$data_ori[$i]['inv_number'],
                    'Tgl' =>$data_ori[$i]['inv_date'],
                    'Tempo' =>$data_ori[$i]['inv_duedate'],
                    'Amount' =>(float)$data_ori[$i]['inv_amount']
                );
                $k++;
                for($j=0; $j<count($data_ori[$i]['details']); $j++){
                    $text = strip_tags($data_ori[$i]['details'][$j]['invdt_note']);
                    $content = preg_replace("/&#?[a-z0-9]{2,8};/i","",$text );
                    $data[$k]=array(
                        'No.Unit' =>'',
                        'Luas' =>'',
                        'Nama Tenant' =>'',
                        'No.Invoice' =>'',
                        'Tgl' =>'',
                        'Tempo' =>$content,
                        'Amount' =>$data_ori[$i]['details'][$j]['invdt_amount']
                    );
                    $k++;
                }
            }
            $border = 'A1:G';
            $tp = 'xls';
            return Excel::create('Invoice Report', function($excel) use ($data,$border) {
                $excel->sheet('Invoice Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function arbyInvoiceCancel(Request $request){
    	$from = @$request->from;
    	$to = @$request->to;
    	$pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $data['tahun'] = '';
        if(!empty($from) && !empty($to)) $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to));
        $data['name'] = MsCompany::first()->comp_name;
    	$data['title'] = "AR Cancelled Invoices";
    	$data['logo'] = MsCompany::first()->comp_image;
    	$data['template'] = 'report_ar_invoice';
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
    	$fetch = TrInvoice::select('tr_invoice.id','tr_invoice.inv_number','tr_invoice.inv_date','tr_invoice.inv_duedate','tr_invoice.inv_amount','tr_invoice.inv_outstanding','tr_invoice.inv_ppn','tr_invoice.inv_ppn_amount','tr_invoice.inv_post','ms_invoice_type.invtp_name','ms_tenant.tenan_name','tr_contract.contr_no', 'ms_unit.unit_name','ms_floor.floor_name')
                    ->join('ms_invoice_type','ms_invoice_type.id',"=",'tr_invoice.invtp_id')
                    ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                    ->join('ms_unit','tr_contract.unit_id',"=",'ms_unit.id')
                    ->join('ms_floor','ms_unit.floor_id',"=",'ms_floor.id')
                    ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                    ->where('inv_iscancel',1);
        if($from) $fetch = $fetch->where('inv_date','>=',$from);
        if($to) $fetch = $fetch->where('inv_date','<=',$to);
        $fetch = $fetch->get();

        $data['invoices'] = [];
        foreach ($fetch as $inv) {
        	$tempInv = [
        		'inv_number' => $inv->inv_number,
        		'contr_no' => $inv->contr_no,
        		'tenan_name' => $inv->tenan_name,
        		'unit_name' => $inv->unit_name,
        		'inv_date' => date('d/m/Y',strtotime($inv->inv_date)),
        		'inv_duedate' => date('d/m/Y',strtotime($inv->inv_duedate)),
        		'inv_amount' => 'Rp. '.number_format($inv->inv_amount),
        		'invtp_name' => $inv->invtp_name
        	];
        	$tempInv['details'] = [];
        	$result = TrInvoiceDetail::select('tr_invoice_detail.id','tr_invoice_detail.invdt_amount','tr_invoice_detail.invdt_note','tr_period_meter.prdmet_id','tr_period_meter.prd_billing_date','tr_meter.meter_start','tr_meter.meter_end','tr_meter.meter_used','tr_meter.meter_cost','ms_cost_detail.costd_name','ms_cost_detail.costd_unit')
                ->join('tr_invoice','tr_invoice.id',"=",'tr_invoice_detail.inv_id')
                ->leftJoin('ms_cost_detail','tr_invoice_detail.costd_id',"=",'ms_cost_detail.id')
                ->leftJoin('tr_meter','tr_meter.id',"=",'tr_invoice_detail.meter_id')
                ->leftJoin('tr_period_meter','tr_period_meter.id',"=",'tr_meter.prdmet_id')
                ->where('tr_invoice_detail.inv_id',$inv->id)
                ->get();
            foreach ($result as $key => $value) {
                $tempInv['details'][] = [
                	'invdt_note' => $value->invdt_note,
                	'invdt_amount' => "Rp. ".$value->invdt_amount,
                	'meter_start' => (int)$value->meter_start,
                	'meter_end' => (int)$value->meter_end,
                	'meter_used' => !empty($value->meter_used) ? (int)$value->meter_used." ".$value->costd_unit : (int)$value->meter_used
                	];
            }
            $data['invoices'][] = $tempInv;
        }
    	if($pdf){
            $data['type'] = 'pdf';
    		$pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
        	return $pdf->download('AR_Invoice_Cancel_'.$from.'_to_'.$to.'.pdf');
    	}else if($excel){
            $data['type'] = 'excel';
            $data_ori = $fetch->toArray();
            $data = array();
            for($i=0; $i<count($data_ori); $i++){
                $data[$i]=array(
                    'No Invoice' =>$data_ori[$i]['inv_number'],
                    'No Billing' =>$data_ori[$i]['contr_no'],
                    'Tenan' =>$data_ori[$i]['tenan_name'],
                    'Unit' =>$data_ori[$i]['unit_name'],
                    'Tgl Invoice' =>$data_ori[$i]['inv_date'],
                    'Jatuh Tempo' =>$data_ori[$i]['inv_duedate'],
                    'Jenis Invoice' =>$data_ori[$i]['invtp_name'],
                    'Amount' =>number_format($data_ori[$i]['inv_amount']));
            }
            $border = 'A1:H';
            $tp = 'xls';
            return Excel::create('Invoice Report', function($excel) use ($data,$border) {
                $excel->sheet('Invoice Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
    		return view('layouts.report_template2', $data);
    	}
    }

    public function arAging(Request $request){
        $ty = @$request->jenis;
        $ag30 = @$request->ag30;
        $ag60 = @$request->ag60;
        $ag90 = @$request->ag90;
        $ag180 = @$request->ag180;
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $tyt = @$request->jenist;
        $cutoff = @$request->cutoff;
        $unit_id = @$request->unit3;

        $data['tahun'] = 'Periode Sampai : '.date('M Y');
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Aged Receivables Report By Customer Key";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['unit'] = MsUnit::where('id',$unit_id)->get();
        $data['tyt'] = $tyt;
        $data['ty'] = $ty;
        if($tyt == 1){
            $data['template'] = 'report_ar_aging';
        }else{
            $data['template'] = 'report_ar_aging_detail';
        }

        if($ty == 1 && $tyt == 1){
            $data['title_r'] = 'Summary Outstanding Invoice';
        }else if($ty == 1 && $tyt == 2){
            $data['title_r'] = 'Detail Outstanding Invoice';
        }else if($ty == 2 && $tyt == 1){
            $data['title_r'] = 'Summary Paid Invoice';
        }else if($ty == 2 && $tyt == 2){
            $data['title_r'] = 'Detail Paid Invoice';
        }else if($ty == 3 && $tyt == 1){
            $data['title_r'] = 'Summary All Invoice';
        }else if($ty == 3 && $tyt == 2){
            $data['title_r'] = 'Detail All Invoice';
        }else{
            $data['title_r'] = '';
        }
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        $data['label'] = explode('~', '1 - '.$ag30.'~'.$ag30.' - '.$ag60.'~'.$ag60.' - '.$ag90.'~'.'OVER '.$ag180);
        if($ty == 1){
            $fetch = TrInvoice::select('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name','tr_contract.contr_bast_date',
                    DB::raw("SUM(inv_outstanding) AS total"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >= -1 AND (current_date::date - inv_date::date) <=".$ag30." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > ".$ag30." AND (current_date::date - inv_date::date)<=".$ag60." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >".$ag60." AND (current_date::date - inv_date::date)<=".$ag90." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > ".$ag90." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS agl180"))
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_invoice.unit_id')
                ->leftjoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->where('tr_contract.contr_status','=','confirmed')
                ->where('tr_invoice.inv_post','=',TRUE)
                ->where('tr_invoice.inv_outstanding','>',0)
                ->where('tr_invoice.inv_date','<=',$cutoff)
                ->where('tr_invoice.invtp_id','=',2)
                ->groupBy('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name','tr_contract.contr_bast_date')
                ->orderBy('unit_code', 'asc');
        }else if ($ty == 2){
            $fetch = TrInvoicePaymhdr::select('tr_invoice_paymhdr.tenan_id','ms_tenant.tenan_name','ms_unit.unit_code','tr_contract.contr_bast_date',
                    DB::raw("SUM(invpayh_amount) AS total"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) >= -1 AND (current_date::date - invpayh_date::date) <=".$ag30." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) > ".$ag30." AND (current_date::date - invpayh_date::date)<=".$ag60." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) >".$ag60." AND (current_date::date - invpayh_date::date)<=".$ag90." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) > ".$ag90." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS agl180"))
                ->join('tr_invoice_paymdtl','tr_invoice_paymhdr.id',"=",'tr_invoice_paymdtl.invpayh_id')
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice_paymhdr.tenan_id')
                ->join('tr_invoice','tr_invoice.id','=','tr_invoice_paymdtl.inv_id')
                ->leftjoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_invoice.unit_id')
                ->where('tr_contract.contr_status','=','confirmed')
                ->where('invpayh_post','=',TRUE)
                ->where('tr_invoice_paymhdr.invpayh_date','<=',$cutoff)
                ->where('tr_invoice.invtp_id','=',2)
                ->groupBy('tr_invoice_paymhdr.tenan_id','ms_tenant.tenan_name','ms_unit.unit_code','tr_contract.contr_bast_date')
                ->orderBy('ms_unit.unit_code', 'asc');
        }else{
            if($tyt == 2){
                $fetch = TrContract::select('tr_contract.id AS contr_id','ms_unit.unit_code','ms_tenant.tenan_name')
                    ->join('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id')
                    ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                    ->where('contr_status','=','confirmed')
                    ->where('contr_terminate_date','=',NULL)
                    ->orderBy('unit_code', 'asc');
            }else{
                //sama kyk not paid
               $fetch = TrInvoice::select('tr_invoice.tenan_id','tr_contract.contr_bast_date','ms_unit.unit_code','ms_tenant.tenan_name','tr_contract.contr_bast_date',
                    DB::raw("SUM(inv_outstanding) AS total"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >= -1 AND (current_date::date - inv_date::date) <=".$ag30." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > ".$ag30." AND (current_date::date - inv_date::date)<=".$ag60." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >".$ag60." AND (current_date::date - inv_date::date)<=".$ag90." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > ".$ag90." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS agl180"))
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_invoice.unit_id')
                ->leftjoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->where('tr_contract.contr_status','=','confirmed')
                ->where('tr_invoice.inv_post','=',TRUE)
                ->where('tr_invoice.inv_outstanding','>',0)
                ->where('tr_invoice.inv_date','<=',$cutoff)
                ->where('tr_invoice.invtp_id','=',2)
                ->groupBy('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name','tr_contract.contr_bast_date')
                ->orderBy('unit_code', 'asc');
            }
        }

        if($unit_id) $fetch = $fetch->where('ms_unit.id','=',$unit_id);
        $fetch = $fetch->get();
        $data['invoices'] = $fetch;

        if($tyt == 2){
            $data['invoices'] = [];
            if($ty == 1){
                foreach ($fetch as $inv) {
                    $tempInv = [
                        'unit_code' => $inv->unit_code,
                        'contr_bast_date' => $inv->contr_bast_date,
                        'tenan_name' => $inv->tenan_name,
                        'total' => $inv->total,
                        'ag30' => $inv->ag30,
                        'ag60' => $inv->ag60,
                        'ag90' => $inv->ag90,
                        'agl180' => $inv->agl180
                    ];
                    $tempInv['details'] = [];
                    $result = TrInvoice::select('tr_invoice.*','tr_contract.contr_bast_date',
                                DB::raw("to_char(inv_date, 'DD/MM/YYYY') AS tanggal"),
                                DB::raw("to_char(inv_duedate, 'DD/MM/YYYY') AS tanggaldue"))
                            ->join('ms_unit','ms_unit.id',"=",'tr_invoice.unit_id')
                            ->leftjoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                			->where('tr_contract.contr_status','=','confirmed')
                            ->where('ms_unit.unit_code',$inv->unit_code)
                            ->where('inv_outstanding','>',0)
                            ->where('tr_invoice.inv_date','<=',$cutoff)
                            ->where('tr_invoice.invtp_id','=',2)
                            ->where('inv_post',TRUE)
                        ->get();
                    foreach ($result as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->inv_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = abs((int)$dif->format('%R%a'));

                        $tempInv['details'][] = [
                            'inv_number' => $value->inv_number,
                            'contr_bast_date' => $value->contr_bast_date,
                            'tanggal' => $value->tanggal,
                            'tanggaldue' => $value->tanggaldue,
                            'inv_amount' => $value->inv_outstanding,
                            'inv_outstanding' => $value->inv_outstanding,
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? $value->inv_outstanding : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? $value->inv_outstanding : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? $value->inv_outstanding : 0),
                            'ags180' => ($difference > $ag90 ? $value->inv_outstanding : 0)
                            ];
                    }
                    $data['invoices'][] = $tempInv;
                }
            }else if($ty == 2){
                foreach ($fetch as $inv) {
                    $tempInv = [
                        'unit_code' => $inv->unit_code,
                        'contr_bast_date' => $inv->contr_bast_date,
                        'tenan_name' => $inv->tenan_name,
                        'total' => $inv->total,
                        'ag30' => $inv->ag30,
                        'ag60' => $inv->ag60,
                        'ag90' => $inv->ag90,
                        'agl180' => $inv->agl180
                    ];
                    $tempInv['details'] = [];
                    $result = TrInvoicePaymdtl::select('tr_invoice_paymdtl.invpayd_amount','tr_invoice.inv_number',
                                DB::raw("to_char(tr_invoice_paymhdr.invpayh_date, 'DD/MM/YYYY') AS tanggal"))
                            ->join('tr_invoice_paymhdr','tr_invoice_paymhdr.id',"=",'tr_invoice_paymdtl.invpayh_id')
                            ->join('tr_invoice','tr_invoice_paymdtl.inv_id',"=",'tr_invoice.id')
                            ->leftjoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                			->where('tr_contract.contr_status','=','confirmed')
                            ->where('tr_invoice.tenan_id',$inv->tenan_id)
                            ->where('tr_invoice_paymhdr.tenan_id',$inv->tenan_id)
                            ->where('tr_invoice.invtp_id','=',2)
                            ->where('invpayh_post',TRUE)
                        ->get();
                    foreach ($result as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->invpayh_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = $dif->d;

                        $tempInv['details'][] = [
                            'inv_number' => $value->inv_number,
                            'contr_bast_date' => $value->contr_bast_date,
                            'tanggal' => $value->tanggal,
                            'inv_amount' => $value->invpayd_amount,
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? $value->invpayd_amount : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? $value->invpayd_amount : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? $value->invpayd_amount : 0),
                            'ags180' => ($difference > $ag90 ? $value->invpayd_amount : 0)
                            ];
                    }
                    $data['invoices'][] = $tempInv;
                }
            }else{
                foreach ($fetch as $inv) {
                    $tempInv = [
                        'unit_code' => $inv->unit_code,
                        'tenan_name' => $inv->tenan_name,
                        'contr_bast_date' => $inv->contr_bast_date
                    ];
                    $tempInv['details'] = [];
                    $result = TrInvoice::select('tr_invoice.*','tr_contract.contr_bast_date',
                                DB::raw("to_char(inv_date, 'DD/MM/YYYY') AS tanggal"))
                    		->leftjoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                			->where('tr_contract.contr_status','=','confirmed')
                            ->where('tr_invoice.contr_id',$inv->contr_id)
                            ->where('tr_invoice.invtp_id','=',2)
                            ->where('inv_post',TRUE)
                        ->get();
                    foreach ($result as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->inv_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = $dif->d;

                        $tempInv['details'][] = [
                            'inv_number' => $value->inv_number,
                            'contr_bast_date' => $value->contr_bast_date,
                            'tanggal' => $value->tanggal,
                            'inv_amount' => $value->inv_amount,
                            'inv_tp' => 'INVOICE',
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? $value->inv_amount : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? $value->inv_amount : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? $value->inv_amount : 0),
                            'ags180' => ($difference > $ag90 ? $value->inv_amount : 0)
                            ];
                    }
                    $result2 = TrInvoicePaymdtl::select('tr_invoice_paymdtl.invpayd_amount','tr_invoice.inv_number','tr_contract.contr_bast_date',
                                DB::raw("to_char(tr_invoice_paymhdr.invpayh_date, 'DD/MM/YYYY') AS tanggal"))
                            ->join('tr_invoice_paymhdr','tr_invoice_paymhdr.id',"=",'tr_invoice_paymdtl.invpayh_id')
                            ->join('tr_invoice','tr_invoice_paymdtl.inv_id',"=",'tr_invoice.id')
                            ->leftjoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                			->where('tr_contract.contr_status','=','confirmed')
                            ->where('tr_invoice_paymhdr.tenan_id',$inv->tenan_id)
                            ->where('tr_invoice_paymhdr.invpayh_date','<=',$cutoff)
                            ->where('tr_invoice.invtp_id','=',2)
                            ->where('invpayh_post',TRUE)
                        ->get();
                    foreach ($result2 as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->invpayh_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = $dif->d;

                        $tempInv['details'][] = [
                            'inv_number' => $value->inv_number,
                            'contr_bast_date' => $value->contr_bast_date,
                            'tanggal' => $value->tanggal,
                            'inv_amount' => ($value->invpayd_amount * -1),
                            'inv_tp' => 'PAYMENT',
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? ($value->invpayd_amount * -1) : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? ($value->invpayd_amount * -1) : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? ($value->invpayd_amount * -1) : 0),
                            'ags180' => ($difference > $ag90 ? ($value->invpayd_amount * -1) : 0)
                            ];
                    }
                    $data['invoices'][] = $tempInv;;
                }
            }
        }
        //print_r($data['invoices']);
        //die();
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('AR_Aging_periode.pdf');
        }else if($excel){
            $data['type'] = 'excel';

            $data_ori = $data['invoices'];
            $data = array();
            $total_semua = 0;
            $total_a30 = 0;
            $total_a60 = 0;
            $total_a90 = 0;
            $total_a180 = 0; 
            for($i=0; $i<count($data_ori); $i++){
                $name1 = '1-'.$ag30.' Days';
                $name2 = $ag30.'-'.$ag60.' Days';
                $name3 = $ag60.'-'.$ag90.' Days';
                $name4 = 'OVER '.$ag180.' Days';
                if(count($data_ori[$i]['details']) > 0){
                    $data[]=array(
                        'Unit Code' =>@$data_ori[$i]['unit_code'],
                        'Tgl Serah Terima' =>$data_ori[$i]['contr_bast_date'],
                        'Nama Tenant' =>$data_ori[$i]['tenan_name'],
                        'Tgl Invoice' =>'',
                        'Total' =>'',
                        $name1 =>'',
                        $name2 =>'',
                        $name3 =>'',
                        $name4 =>'',
                        'Summary'=>(float)$data_ori[$i]['total']
                    );
                }else{
                    $data[]=array(
                        'Unit Code' =>@$data_ori[$i]['unit_code'],
                        'Tgl Serah Terima' =>$data_ori[$i]['contr_bast_date'],
                        'Nama Tenant' =>$data_ori[$i]['tenan_name'],
                        'Tgl Invoice' =>'',
                        'Total' =>(float)$data_ori[$i]['total'],
                        $name1 =>(float)$data_ori[$i]['ag30'],
                        $name2 =>(float)$data_ori[$i]['ag60'],
                        $name3 =>(float)$data_ori[$i]['ag90'],
                        $name4 =>(float)$data_ori[$i]['agl180'],
                        'Summary'=>''
                    );
                }
                if(count($data_ori[$i]['details']) > 0){
                    for($k=0; $k<count($data_ori[$i]['details']); $k++){
                        $data[]=array(
                            'Unit Code' =>$data_ori[$i]['details'][$k]['inv_number'],
                            'Tgl Serah Terima' =>$data_ori[$i]['details'][$k]['contr_bast_date'],
                            'Nama Tenant' =>@$data_ori[$i]['unit_code'].' / '.$data_ori[$i]['tenan_name'],
                            'Tgl Invoice' =>$data_ori[$i]['details'][$k]['tanggal'],
                            'Total' =>(float)$data_ori[$i]['details'][$k]['inv_outstanding'],
                            $name1 =>(float)$data_ori[$i]['details'][$k]['ags30'],
                            $name2 =>(float)$data_ori[$i]['details'][$k]['ags60'],
                            $name3 =>(float)$data_ori[$i]['details'][$k]['ags90'],
                            $name4 =>(float)$data_ori[$i]['details'][$k]['ags180'],
                            'Summary'=>''
                        );
                    }
                }
                $total_semua = $total_semua + (float)$data_ori[$i]['total'];
                $total_a30 = $total_a30 + (float)$data_ori[$i]['ag30'];
                $total_a60 = $total_a60 + (float)$data_ori[$i]['ag60'];
                $total_a90 = $total_a90 + (float)$data_ori[$i]['ag90'];
                $total_a180 = $total_a180 + (float)$data_ori[$i]['agl180'];
            }
            $data[]=array(
                    'Unit Code' =>'TOTAL',
                    'Tgl Serah Terima' =>'',
                    'Nama Tenant' =>'',
                    'Tgl Invoice' =>'',
                    'Total' =>(float)$total_semua,
                    $name1 =>(float)$total_a30,
                    $name2 =>(float)$total_a60,
                    $name3 =>(float)$total_a90,
                    $name4 =>(float)$total_a180,
                    'Summary'=>(float)$total_semua
                );

            $border = 'A1:J';
            $tp = 'xls';
            return Excel::create('Aging Report', function($excel) use ($data,$border) {
                $excel->sheet('Aging Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function outContr(Request $request){
        $from = @$request->from;
        $to = @$request->to;
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;

        $data['tahun'] = '';
        if(!empty($from) && !empty($to)) $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to));
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Outstanding By Contract";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_out_contr';
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        $fetch = TrInvoice::select('tr_contract.contr_code','ms_unit.unit_name','ms_tenant.tenan_name',
                    DB::raw("SUM(inv_outstanding) AS outstanding"))
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                ->where('tr_invoice.inv_outstanding','>',0)
                ->where('tr_invoice.inv_post','=',TRUE)
                ->groupBy('ms_unit.unit_name','ms_tenant.tenan_name','tr_contract.contr_code');
        if($from) $fetch = $fetch->where('inv_date','>=',$from);
        if($to) $fetch = $fetch->where('inv_date','<=',$to);
        $fetch = $fetch->get();
        $data['invoices'] = $fetch;
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Outstanding_By_Contract_'.$from.'_to_'.$to.'.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $data_ori = $fetch->toArray();
            $data = array();
            for($i=0; $i<count($data_ori); $i++){
                $data[$i]=array(
                    'Billing Code' =>$data_ori[$i]['contr_code'],
                    'Nama Tenant' =>$data_ori[$i]['tenan_name'],
                    'Unit' =>$data_ori[$i]['unit_name'],
                    'Total Outstanding' =>number_format($data_ori[$i]['outstanding']));
            }
            $border = 'A1:D';
            $tp = 'xls';
            return Excel::create('Outstanding By Billing Report', function($excel) use ($data,$border) {
                $excel->sheet('Outstanding By Billing Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function outInv(Request $request){
        $from = @$request->from;
        $to = @$request->to;
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        // tambahan
        $unit_id = @$request->unit;
        $inv_type_id = @$request->inv_type;

        $data['tahun'] = '';
        if(!empty($from) && !empty($to)) $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to));
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Outstanding By Unit";
        if(!empty($unit_id)){
            $data['title'] .= " - Unit ".@MsUnit::find($unit_id)->unit_code;
        }
        if(!empty($inv_type_id)){
            $data['title'] .= " - ".@MsInvoiceType::find($inv_type_id)->invtp_name;
        }
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_out_inv';
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        $fetch = TrInvoice::select('tr_invoice.inv_number','tr_invoice.inv_date','tr_invoice.inv_duedate','ms_unit.unit_name','ms_tenant.tenan_name','tr_invoice.inv_outstanding')
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->leftJoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->leftJoin('ms_unit','ms_unit.id',"=",'tr_invoice.unit_id')
                ->where('tr_invoice.inv_outstanding','>',0)
                ->where('tr_invoice.inv_post','=',TRUE)
                ->groupBy('tr_invoice.inv_number','ms_unit.unit_name','ms_tenant.tenan_name','tr_invoice.inv_date','tr_invoice.inv_duedate','tr_invoice.inv_outstanding')
                ->orderBy('tr_invoice.inv_date','ms_unit.unit_name');
        if($from) $fetch = $fetch->where('inv_date','>=',$from);
        if($to) $fetch = $fetch->where('inv_date','<=',$to);
        if(!empty($unit_id)) $fetch = $fetch->where('ms_unit.id',$unit_id);
        if(!empty($inv_type_id)) $fetch = $fetch->where('tr_invoice.invtp_id',$inv_type_id);

        $fetch = $fetch->get();
        $data['invoices'] = $fetch;
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Outstanding_By_Invoice_'.$from.'_to_'.$to.'.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $data_ori = $fetch->toArray();
            $data = array();
            $total = 0;
            for($i=0; $i<count($data_ori); $i++){
                $data[$i]=array(
                    'No Invoice' =>$data_ori[$i]['inv_number'],
                    'Tgl Invoice' =>$data_ori[$i]['inv_date'],
                    'Jatuh Tempo' =>$data_ori[$i]['inv_duedate'],
                    'Tenan' =>$data_ori[$i]['tenan_name'],
                    'Unit' =>$data_ori[$i]['unit_name'],
                    'Total Outstanding' =>(float)$data_ori[$i]['inv_outstanding']);
                $total = $total + $data_ori[$i]['inv_outstanding'];
            }
            $data[$i]=array(
                    'No Invoice' =>'',
                    'Tgl Invoice' =>'',
                    'Jatuh Tempo' =>'',
                    'Tenan' =>'',
                    'Unit' =>'',
                    'Total Outstanding' =>(float)$total);
            $border = 'A1:F';
            $tp = 'xls';
            return Excel::create('Outstanding By Unit Report', function($excel) use ($data,$border) {
                $excel->sheet('Outstanding By Unit Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function paymHistory(Request $request){
        $from = @$request->from;
        $to = @$request->to;
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;

        $unit_id = @$request->unit2;
        $bank_id = @$request->bank_id;
        $inv_number = @$request->inv_number;
        if(!empty($inv_number)) $inv_number = strtolower($inv_number);
        $paym_type_id = @$request->payment_id;
        $post_status = @$request->post_status;
        $date_type = @$request->date_type;
        if(!empty($post_status)){
            if($post_status == 1) $post_flag = 1;
            else $post_flag = 0;
        }

        $data['tahun'] = '';
        if(!empty($from) && !empty($to)) $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to))."<br>";
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Payment History";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_payment';

        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        $fetch = TrInvoicePaymhdr::select('tr_invoice_paymhdr.no_kwitansi','tr_invoice_paymhdr.invpayh_date','ms_payment_type.paymtp_name','ms_cash_bank.cashbk_name','tr_invoice_paymhdr.invpayh_checkno','tr_invoice.inv_number','tr_invoice_paymdtl.invpayd_amount','ms_tenant.tenan_name','tr_invoice.inv_post','ms_unit.unit_name','tr_invoice_paymhdr.posting_at')
                    ->join('tr_invoice_paymdtl','tr_invoice_paymhdr.id','=','tr_invoice_paymdtl.invpayh_id')
                    ->join('tr_invoice','tr_invoice_paymdtl.inv_id','=','tr_invoice.id')
                    ->join('ms_tenant','ms_tenant.id','=','tr_invoice.tenan_id')
                    ->join('ms_unit','ms_unit.id','=','tr_invoice.unit_id')
                    ->join('ms_cash_bank','tr_invoice_paymhdr.cashbk_id','=','ms_cash_bank.id')
                    ->join('ms_payment_type','tr_invoice_paymhdr.paymtp_code','=','ms_payment_type.id');
        $fetch = $fetch->where('tr_invoice_paymhdr.status_void','f');

        if($date_type == 1){
            if($from) $fetch = $fetch->where('tr_invoice_paymhdr.posting_at','>=',$from);
            if($to) $fetch = $fetch->where('tr_invoice_paymhdr.posting_at','<=',$to);
        }else{
            if($from) $fetch = $fetch->where('tr_invoice_paymhdr.invpayh_date','>=',$from);
            if($to) $fetch = $fetch->where('tr_invoice_paymhdr.invpayh_date','<=',$to);
        }

        if(!empty($unit_id)){
            $fetch = $fetch->where('tr_invoice.unit_id',$unit_id);
            $unit = MsUnit::find($unit_id);
            $data['tahun'] .= "<br>Unit : ".$unit->unit_code."<br>";
        }
        if(!empty($inv_number)) $fetch = $fetch->where(DB::raw("LOWER(inv_number)"),'like','%'.$inv_number.'%');
        if(!empty($post_status)) $fetch = $fetch->where('tr_invoice_paymhdr.invpayh_post',$post_flag);
        if(!empty($paym_type_id)){
            $fetch = $fetch->where('paymtp_code',$paym_type_id);
            $paymtype = MsPaymentType::find($paym_type_id);
            $data['tahun'] .= "Payment Type : ".$paymtype->paymtp_name."<br>";
        }
        if(!empty($bank_id)){
            $fetch = $fetch->where('cashbk_id',$bank_id);
            $bank = MsCashBank::find($bank_id);
            $data['tahun'] .= "Bank : ".$bank->cashbk_name."<br>";
        }

        $fetch = $fetch->get();
        $data['payments'] = $fetch;

        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Payment_'.$from.'_to_'.$to.'.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $data_ori = $fetch->toArray();
            $data = array();
            $total_debet = 0;
            $total_kredit =0;
            for($i=0; $i<count($data_ori); $i++){
                $data[$i]=array(
                    'Payment' =>$data_ori[$i]['invpayh_date'],
                    'Posting At' =>$data_ori[$i]['posting_at'],
                    'No Kwitansi' =>$data_ori[$i]['no_kwitansi'],
                    'No Invoice' =>$data_ori[$i]['inv_number'],
                    'Unit' =>$data_ori[$i]['unit_name'],
                    'Nama' =>$data_ori[$i]['tenan_name'],
                    'Bank' =>$data_ori[$i]['cashbk_name'],
                    'No Giro' =>$data_ori[$i]['invpayh_checkno'],
                    'Amount' =>(float)$data_ori[$i]['invpayd_amount']
                    );
                $total_debet = $total_debet + $data_ori[$i]['invpayd_amount'];
            }
            $data[$i]=array(
                    'Payment' =>'',
                    'Posting At' =>'',
                    'No Kwitansi' =>'',
                    'No Invoice' =>'',
                    'Unit' =>'',
                    'Nama' =>'',
                    'Bank' =>'',
                    'No Giro' =>'TOTAL',
                    'Amount' => (float)$total_debet
                    );
            $border = 'A1:H';
            $tp = 'xls';
            return Excel::create('Payment History Report', function($excel) use ($data,$border) {
                $excel->sheet('Payment History Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function tenancyview(){
        return view('report_tenancy');
    }

    public function HistoryMeter(Request $request){
        $year = @$request->year;
        $cost = @$request->cost;
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;

        if($cost == 1){
            $ctname = 'Electricity';
        }else{
            $ctname = 'Water';
        }
        $data['title'] = "History Reading Meter ". $ctname;
        $data['tahun'] = 'Year : '.$year;
        $data['logo'] = MsCompany::first()->comp_image;
        $data['name'] = MsCompany::first()->comp_name;
        $data['template'] = 'report_history_meter';
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        $fetch = TrMeter::select('ms_unit.unit_code',
                DB::raw("SUM((CASE WHEN DATE_PART('MONTH', prd_billing_date) = 1 THEN tr_meter.meter_used ELSE 0 END)) AS jan"),
                DB::raw("SUM((CASE WHEN DATE_PART('MONTH', prd_billing_date) = 2 THEN tr_meter.meter_used ELSE 0 END)) AS feb"),
                DB::raw("SUM((CASE WHEN DATE_PART('MONTH', prd_billing_date) = 3 THEN tr_meter.meter_used ELSE 0 END)) AS mar"),
                DB::raw("SUM((CASE WHEN DATE_PART('MONTH', prd_billing_date) = 4 THEN tr_meter.meter_used ELSE 0 END)) AS apr"),
                DB::raw("SUM((CASE WHEN DATE_PART('MONTH', prd_billing_date) = 5 THEN tr_meter.meter_used ELSE 0 END)) AS may"),
                DB::raw("SUM((CASE WHEN DATE_PART('MONTH', prd_billing_date) = 6 THEN tr_meter.meter_used ELSE 0 END)) AS jun"),
                DB::raw("SUM((CASE WHEN DATE_PART('MONTH', prd_billing_date) = 7 THEN tr_meter.meter_used ELSE 0 END)) AS jul"),
                DB::raw("SUM((CASE WHEN DATE_PART('MONTH', prd_billing_date) = 8 THEN tr_meter.meter_used ELSE 0 END)) AS aug"),
                DB::raw("SUM((CASE WHEN DATE_PART('MONTH', prd_billing_date) = 9 THEN tr_meter.meter_used ELSE 0 END)) AS sep"),
                DB::raw("SUM((CASE WHEN DATE_PART('MONTH', prd_billing_date) = 10 THEN tr_meter.meter_used ELSE 0 END)) AS okt"),
                DB::raw("SUM((CASE WHEN DATE_PART('MONTH', prd_billing_date) = 11 THEN tr_meter.meter_used ELSE 0 END)) AS nov"),
                DB::raw("SUM((CASE WHEN DATE_PART('MONTH', prd_billing_date) = 12 THEN tr_meter.meter_used ELSE 0 END)) AS des"),
                DB::raw("SUM(meter_used) AS total")
                )
                ->join('ms_cost_detail','tr_meter.costd_id',"=",'ms_cost_detail.id')
                ->join('ms_cost_item','ms_cost_detail.cost_id',"=",'ms_cost_item.id')
                ->join('ms_unit','tr_meter.unit_id',"=",'ms_unit.id')
                ->join('tr_period_meter','tr_meter.prdmet_id',"=",'tr_period_meter.id')
                ->where('ms_cost_item.id',$cost)
                ->whereYear('prd_billing_date','=',$year)
                ->groupBy('ms_unit.unit_code')
                ->orderBy('ms_unit.unit_code','asc');
        $fetch = $fetch->get();
        $data['invoices'] = $fetch;
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Report_ReadingMeter_'.$year.'_'.$ctname.'.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $data_ori = $fetch->toArray();
            $data = array();
            for($i=0; $i<count($data_ori); $i++){
                $data[$i]=array(
                    'No Unit' =>$data_ori[$i]['unit_code'],
                    'JANUARI' =>(float)$data_ori[$i]['jan'],
                    'FEBRUARI' =>(float)$data_ori[$i]['feb'],
                    'MARET' =>(float)$data_ori[$i]['mar'],
                    'APRIL' =>(float)$data_ori[$i]['apr'],
                    'MEI' =>(float)$data_ori[$i]['may'],
                    'JUNI' =>(float)$data_ori[$i]['jun'],
                    'JULI' =>(float)$data_ori[$i]['jul'],
                    'AGUSTUS' =>(float)$data_ori[$i]['aug'],
                    'SEPTEMBER' =>(float)$data_ori[$i]['sep'],
                    'OKTOBER' =>(float)$data_ori[$i]['okt'],
                    'NOVEMBER' =>(float)$data_ori[$i]['nov'],
                    'DESEMBER' =>(float)$data_ori[$i]['des'],
                    'TOTAL KONSUMSI' =>(float)$data_ori[$i]['total']
                    );
            }
            $border = 'A1:N';
            $tp = 'xls';
            return Excel::create('Reading Meter Report', function($excel) use ($data,$border) {
                $excel->sheet('Reading Meter Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function ReportUnit(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;

        $data['title'] = "Report Unit";
        $data['tahun'] = '';
        $data['logo'] = MsCompany::first()->comp_image;
        $data['name'] = MsCompany::first()->comp_name;
        $data['template'] = 'report_unit';
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        $units = MsUnit::select('ms_unit.unit_code','ms_unit.unit_sqrt','ms_unit.va_utilities','ms_unit.va_maintenance','ms_floor.floor_name','ms_unit.meter_listrik','ms_unit.meter_air','ms_tenant.tenan_name','ms_tenant.tenan_idno','ms_tenant.tenan_phone','ms_tenant.tenan_fax','ms_tenant.tenan_email','ms_tenant.tenan_npwp','ms_tenant.tenan_address','tr_contract.contr_bast_date','tr_contract.contr_bast_by')
                ->join('ms_floor','ms_unit.floor_id',"=",'ms_floor.id')
                ->leftjoin('ms_unit_owner','ms_unit.id',"=",'ms_unit_owner.unit_id')
                ->leftjoin('ms_tenant','ms_tenant.id',"=",'ms_unit_owner.tenan_id')
                ->leftjoin('tr_contract','tr_contract.unit_id',"=",'ms_unit.id')
                ->whereNull('ms_unit_owner.deleted_at')
                ->where('tr_contract.contr_iscancel',FALSE)
                ->where('tr_contract.contr_status','confirmed')
                ->where('ms_tenant.tent_id',1)
                ->orderBy('ms_unit.unit_code');
        $fetch = $units->get();
        $data['invoices'] = $fetch;
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Report_Unit.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $data = $units->get()->toArray();
            $border = 'A1:P';
            $tp = 'xls';
            return Excel::create('Unit Report', function($excel) use ($data,$border) {
                $excel->sheet('Unit Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function ReportTenant(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;

        $data['title'] = "Report Tenant";
        $data['tahun'] = '';
        $data['logo'] = MsCompany::first()->comp_image;
        $data['name'] = MsCompany::first()->comp_name;
        $data['template'] = 'report_tenant';
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        $fetch = MsTenant::select('ms_tenant.*')
                ->orWhereNull('deleted_at');
        $fetch = $fetch->get();
        $data['invoices'] = $fetch;
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Report_Tenant.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $data = MsTenant::select('tenan_name AS Name','tenan_idno AS NIK','tenan_phone AS Phone','tenan_fax AS Fax','tenan_email AS Email','tenan_address AS Address','tenan_npwp AS NPWP','tenan_taxname AS Taxname','tenan_tax_address AS TaxAddress')
                ->orWhereNull('deleted_at')->get()->toArray();
            $border = 'A1:I';
            $tp = 'xls';
            return Excel::create('Tenant Report', function($excel) use ($data,$border) {
                $excel->sheet('Tenant Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function glview(){
        $coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        $data['departments'] = MsDepartment::where('dept_isactive',1)->get();
        $data['journal_types'] = MsJournalType::where('jour_type_isactive',1)->get();
        return view('report_gl',$data);
    }

    public function glreport(Request $request){
        $from = @$request->from;
        $to = @$request->to;
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        // tambahan
        $keyword = $request->input('q');
        if(!empty($keyword)) $keyword = strtolower($keyword);
        $coa = $request->input('coa');
        $tocoa = $request->input('tocoa');
        $deptParam = $request->input('dept');
        $jourTypeParam = $request->input('jour_type_id');

        if(!empty($from) && !empty($to)){
            $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to));
        }else{
            $data['tahun'] = 'All Periode';
        }
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "General Ledger Report";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_gl_template';
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        if(!empty($to)) $year = date('Y',strtotime($to));
        else $year = date('Y');

        $fetch = TrLedger::select('tr_ledger.coa_code','tr_ledger.closed_at','coa_name','ledg_date','ledg_description','ledg_debit','ledg_credit','jour_type_prefix','ledg_refno','ledg_number')
                            ->join('ms_master_coa','ms_master_coa.coa_code','=','tr_ledger.coa_code')
                            ->join('ms_journal_type','ms_journal_type.id','=','tr_ledger.jour_type_id')
                            ->where('ms_master_coa.coa_year',$year)->where('tr_ledger.coa_year',$year);
        if(!empty($from) && !empty($to)) $fetch = $fetch->where('ledg_date','>=',$from)->where('ledg_date','<=',$to);
        if(!empty($deptParam)) $fetch = $fetch->where('dept_id',$deptParam);
        if(!empty($jourTypeParam)) $fetch = $fetch->where('jour_type_id',$jourTypeParam);

        if(!empty($coa) && empty($tocoa)) $fetch = $fetch->where('tr_ledger.coa_code',$coa);
        else if(empty($coa) && !empty($tocoa)) $fetch = $fetch->where('tr_ledger.coa_code',$tocoa);
        else if(!empty($coa) && !empty($tocoa) && $coa == $tocoa) $fetch = $fetch->where('tr_ledger.coa_code',$coa);
        else if(!empty($coa) && !empty($tocoa) && $coa > $tocoa) $fetch = $fetch->whereBetween('tr_ledger.coa_code',[$tocoa,$coa]);
        else if(!empty($coa) && !empty($tocoa) && $coa < $tocoa) $fetch = $fetch->whereBetween('tr_ledger.coa_code',[$coa,$tocoa]);

        if(!empty($keyword)){
            $fetch = $fetch->where(function($query) use($keyword){
                    $query->where(DB::raw("LOWER(ledg_description)"),'like','%'.$keyword.'%')->orWhere(DB::raw("LOWER(ledg_refno)"),'like','%'.$keyword.'%');
                });
        }
        $fetch = $fetch->orderBy('tr_ledger.ledg_number','asc');
        $fetch = $fetch->orderBy('tr_ledger.id','asc');
        //dd($fetch->toSql(), $fetch->getBindings());
        //$fetch = $fetch->orderBy('tr_ledger.ledg_date','asc');
        //memory exhause/keperluan demo aja makanya dilimit
        // $fetch = $fetch->limit(100)->get();
        $fetch = $fetch->get();
        $data['ledger'] = $fetch;
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('GL_Report_'.$from.'_to_'.$to.'.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $data_ori = $fetch->toArray();
            $data = array();
            for($i=0; $i<count($data_ori); $i++){
                $data[$i]=array(
                	'RefNumber' =>$data_ori[$i]['ledg_number'],
                    'Account No' =>$data_ori[$i]['coa_code'],
                    'Account Name' =>$data_ori[$i]['coa_name'],
                    'Date' =>$data_ori[$i]['ledg_date'],
                    'No Invoice/Payment' =>$data_ori[$i]['ledg_refno'],
                    'Deskripsi'=>$data_ori[$i]['ledg_description'],
                    'Debet' =>(float)$data_ori[$i]['ledg_debit'],
                    'Kredit' =>(float)$data_ori[$i]['ledg_credit'],
                    'Type' =>$data_ori[$i]['jour_type_prefix']
                    );
            }
            $border = 'A1:I';
            $tp = 'xls';
            return Excel::create('General Ledger Report', function($excel) use ($data,$border) {
                $excel->sheet('General Ledger Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function ytd(){
        $coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        $data['departments'] = MsDepartment::where('dept_isactive',1)->get();
        $data['journal_types'] = MsJournalType::where('jour_type_isactive',1)->get();
        return view('report_ytd',$data);
    }

    public function ytdreport(Request $request){
        $monthfrom = @$request->monthfrom;
        $yearfrom = @$request->yearfrom;
        $monthto = @$request->monthto;
        $yearto = @$request->yearto;

        $from = date($yearfrom.'-'.$monthfrom.'-01');
        $to = date($yearto.'-'.$monthto.'-t');

        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        if(!empty($monthfrom) && !empty($monthto) && !empty($yearfrom) && !empty($yearto)){
            $data['tahun'] = 'Periode : '.date('M Y',strtotime($from)).' s/d '.date('M Y',strtotime($to));
        }else{
            $data['tahun'] = 'All Periode';
        }
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Year to Date General Ledger Report";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_ytd_gl_template';


        // $data['ledger'] = $fetch;

        $from = strtotime($from);
        $to = strtotime($to);
        $balances = [];
        $ledger = [];
        $array_excel = [];
        while($from < $to){
            $fetch = TrLedger::join('ms_master_coa','ms_master_coa.coa_code','=','tr_ledger.coa_code')
                            ->join('ms_journal_type','ms_journal_type.id','=','tr_ledger.jour_type_id')
                            ->leftJoin('tr_invoice','tr_invoice.inv_number','=','tr_ledger.ledg_refno')
                            ->leftJoin('ms_tenant','ms_tenant.id','=','tr_invoice.tenan_id')
                            ->select('tr_ledger.coa_code','tr_ledger.closed_at','coa_name','ledg_date','ledg_description','ledg_debit','ledg_credit','jour_type_prefix','ledg_refno','tenan_id','tenan_name');
            if(!empty($from) && !empty($to)) $fetch = $fetch->where(\DB::raw('ledg_date::text'),'like',date('Y-m',$from).'-%');
            $fetch = $fetch->orderBy('ledg_date')->get();
            $ledger[date('Y-m',$from)] = $fetch;

            $log_gl = \DB::table('gl_balance_log')->where('month',(int)date('m',$from))->where('month',(int)date('Y',$from))->first();
            $balances[date('Y-m',$from)] = !empty($log_gl->total) ? $log_gl->total : 0;
            $from = strtotime("+1 month", $from);
        }

        $data['ledger'] = $ledger;
        $data['balances'] = $balances;

        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('YTD_Report_'.date('d-m-y', $from).'_to_'.date('d-m-y', $to).'.pdf');
        }else if($excel){
            foreach($ledger as $key => $ledg_per_month){
                $totalDebit = 0;
                $totalCredit = 0;
                foreach($ledg_per_month as $ledg){
                    $totalDebit += $ledg->ledg_debit;
                    $totalCredit += $ledg->ledg_credit;
                    $array_excel[] = ['Date'=>date('d/m/Y',strtotime($ledg->ledg_date)),'Acc No'=>$ledg->coa_code,'Description'=>$ledg->ledg_description,'DEBET'=>"Rp. ".number_format($ledg->ledg_debit,2),'KREDIT'=>"Rp. ".number_format($ledg->ledg_credit,2),'Jurnal Type'=>$ledg->jour_type_prefix,'Balance'=>''];
                }
                $array_excel[] = ['Date'=>"<< ".date('F Y',strtotime(date($key."-01") ))." >>",'Acc No'=>'','Description'=>'','DEBET'=>"Rp. ".number_format($totalDebit,2),'KREDIT'=>"Rp. ".number_format($totalCredit,2),'Jurnal Type'=>'','Balance'=> $balances[$key] < 0 ? "(Rp. ".number_format($balances[$key],2).")" : "Rp. ".number_format($balances[$key],2)];
            }

            $data['type'] = 'excel';
            $data = $array_excel;
            $border = 'A1:K';
            $tp = 'xls';
            return Excel::create('YTD Report', function($excel) use ($data,$border) {
                $excel->sheet('YTD Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function ledger_view(){
        $coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_code')->get();
        return view('report_ledger',$data);
    }

    public function rledger(Request $request){
        $from = @$request->from;
        $to = @$request->to;
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        // tambahan
        $keyword = $request->input('q');
        if(!empty($keyword)) $keyword = strtolower($keyword);
        $coa = $request->input('coa');
        $tocoa = $request->input('tocoa');

        if(!empty($from) && !empty($to)){
            $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to));
        }else{
            $data['tahun'] = 'All Periode';
        }
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "General Ledger Report";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_ledger_template';
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        if(!empty($from)){ $year = date('Y',strtotime($from)); }else{ $year = date('Y'); }

        $coa_code = MsMasterCoa::select('coa_code','coa_name','coa_beginning','coa_type')->where('coa_code','>=',$coa)->where('coa_code','<=',$tocoa)->where('coa_year','=',$year)->orderBy('coa_code','ASC')->get();
        $last_date = date('Y-m-d',(strtotime('-1 day',strtotime($from))));
        $first_date = $year.'-01-01';
        $data['invoices'] = [];
        $array_excel = [];
        foreach ($coa_code as $inv) {
            $mutasi = TrLedger::select(DB::raw("SUM(ledg_debit) AS total_debet"),DB::raw("SUM(ledg_credit) AS total_credit"))->where('coa_code',$inv->coa_code)->where('ledg_date','>=',$first_date)->where('ledg_date','<=',$last_date)->get();
            $mutasi_history = TrLedger::select('ledg_date','ledg_description','ledg_refno','ledg_debit','ledg_credit','dept_name','jour_type_prefix','coa_code')
                            ->join('ms_department','tr_ledger.dept_id',"=",'ms_department.id')
                            ->join('ms_journal_type','tr_ledger.jour_type_id',"=",'ms_journal_type.id')
                            ->where('coa_code',$inv->coa_code)
                            ->where('ledg_date','>=',$from)
                            ->where('ledg_date','<=',$to)
                            ->orderBy('ledg_date','ASC')
                            ->orderBy('tr_ledger.id','ASC')->get();
            $tempInv = ['coa'=>trim($inv->coa_code).' - '.$inv->coa_name];
            $array_excel[] = ['Tanggal'=>trim($inv->coa_code),'Kel.Journal'=>'','COA'=>'','COA Name'=>$inv->coa_name,'Ref'=>'','Deskripsi'=>'','DEBET'=>'','KREDIT'=>'','Saldo Akhir'=>''];
            $tempInv['details'] = [];
            if(trim($inv->coa_type) == 'DEBET'){
                $saldo_awal = $inv->coa_beginning + $mutasi[0]->total_debet - $mutasi[0]->total_credit;
                $tempInv['details'][] = [
                    'ledg_date' => '',
                    'jour_type_prefix'=> '',
                    'coa_code'=> '',
                    'ledg_refno'=> '',
                    'ledg_description' =>'Saldo Akhir Per '.date('t M Y',(strtotime('-1 day',strtotime($from)))),
                    'ledg_debit' => $saldo_awal,
                    'ledg_credit' => 0,
                    'saldo_akhir' => $saldo_awal,      
                ];
                $array_excel[] = ['Tanggal'=>'','Kel.Journal'=>'','COA'=>'','COA Name'=>'Saldo Akhir Per '.date('t M Y',(strtotime('-1 day',strtotime($from)))),'Ref'=>'','Deskripsi'=>'','DEBET'=>ROUND($saldo_awal,0),'KREDIT'=>0,'Saldo Akhir'=>ROUND($saldo_awal,0)];
            }else{
                $saldo_awal = $inv->coa_beginning - $mutasi[0]->total_debet + $mutasi[0]->total_credit;
                $tempInv['details'][] = [
                    'ledg_date' => '',
                    'jour_type_prefix'=> '',
                    'coa_code'=> '',
                    'ledg_refno'=> '',
                    'ledg_description' =>'Saldo Akhir Per '.date('t M Y',(strtotime('-1 day',strtotime($from)))),
                    'ledg_debit' => 0,
                    'ledg_credit' => ROUND($saldo_awal,0),
                    'saldo_akhir' => ROUND($saldo_awal,0),         
                ];
                $array_excel[] = ['Tanggal'=>'','Kel.Journal'=>'','COA'=>'','COA Name'=>'Saldo Akhir Per '.date('t M Y',(strtotime('-1 day',strtotime($from)))),'Ref'=>'','Deskripsi'=>'','DEBET'=>0,'KREDIT'=>ROUND($saldo_awal,0),'Saldo Akhir'=>ROUND($saldo_awal,0)];
            }
            $tot_mutasi = $saldo_awal;
            foreach ($mutasi_history as $key => $value) {
                if(trim($inv->coa_type) == 'DEBET'){
                    $tot_mutasi =  $tot_mutasi  + (float)$value->ledg_debit - (float)$value->ledg_credit;
                }else{
                    $tot_mutasi =  $tot_mutasi  + (float)$value->ledg_credit - (float)$value->ledg_debit;
                }
                $tempInv['details'][] = [
                    'ledg_date' => ($value->ledg_date != '' ? date('d-M-y',strtotime($value->ledg_date)) : ''),
                    'jour_type_prefix' => $value->jour_type_prefix,
                    'coa_code' => $value->coa_code,
                    'ledg_refno' => $value->ledg_refno,
                    'ledg_description' => $value->ledg_description,
                    'ledg_debit' => ROUND($value->ledg_debit,0),
                    'ledg_credit' => ROUND($value->ledg_credit,0),
                    'saldo_akhir' => ROUND($tot_mutasi,0),
                    ];
                $array_excel[] = ['Tanggal'=>($value->ledg_date != '' ? date('d-M-y',strtotime($value->ledg_date)) : ''),'Kel.Journal'=>$value->jour_type_prefix,'COA'=>$value->coa_code,'COA Name'=>'','Ref'=>$value->ledg_refno,'Uraian'=>$value->ledg_description,'DEBET'=>ROUND($value->ledg_debit,0),'KREDIT'=>ROUND($value->ledg_credit,0),'Saldo Akhir'=>ROUND($tot_mutasi,0)];
            }
            $data['invoices'][] = $tempInv;
        }

        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('GL_Report_'.$from.'_to_'.$to.'.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $data = $array_excel;
            $border = 'A1:H';
            $tp = 'xls';
            return Excel::create('General Ledger Report', function($excel) use ($data,$border) {
                $excel->sheet('General Ledger Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function tb_view(){
        $coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_code')->get();
        return view('report_trial',$data);
    }

    public function dotrial(Request $request){
        $from = @$request->from;
        $to = @$request->to;
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        // tambahan
        $keyword = $request->input('q');
        if(!empty($keyword)) $keyword = strtolower($keyword);
        $coa = $request->input('coa');
        $tocoa = $request->input('tocoa');

        if(!empty($from) && !empty($to)){
            $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to));
        }else{
            $data['tahun'] = 'All Periode';
        }
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Working Trial Balance Report";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_trial_template';
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        if(!empty($to)){ $year = date('Y',strtotime($to)); }else{ $year = date('Y'); }

        $coa_code = MsMasterCoa::select('coa_code','coa_name','coa_beginning','coa_type')->where('coa_code','>=',$coa)->where('coa_code','<=',$tocoa)->where('coa_year','=',$year)->orderBy('coa_code','ASC')->get();
        $last_date = date('Y-m-d',(strtotime('-1 day',strtotime($from))));
        $first_date = $year.'-01-01';
        $data['invoices'] = [];
        foreach ($coa_code as $inv) {
            $mutasi = TrLedger::select(DB::raw("SUM(ledg_debit) AS total_debet"),DB::raw("SUM(ledg_credit) AS total_credit"))->where('coa_code',$inv->coa_code)->where('ledg_date','>=',$first_date)->where('ledg_date','<=',$last_date)->get();
            $mutasi_history = TrLedger::select(DB::raw("SUM(ledg_debit) AS total_mutasi_debet"),DB::raw("SUM(ledg_credit) AS total_mutasi_credit"))->where('coa_code',$inv->coa_code)->where('ledg_date','>=',$from)->where('ledg_date','<=',$to)->get();

            if(trim($inv->coa_type) == 'DEBET'){
                $saldo_awal = $inv->coa_beginning + $mutasi[0]->total_debet - $mutasi[0]->total_credit;
                $saldo_akhir = $saldo_awal + $mutasi_history[0]->total_mutasi_debet - $mutasi_history[0]->total_mutasi_credit;

            }else{
                $saldo_awal = $inv->coa_beginning - $mutasi[0]->total_debet + $mutasi[0]->total_credit;
                $saldo_akhir = $saldo_awal - $mutasi_history[0]->total_mutasi_debet + $mutasi_history[0]->total_mutasi_credit;
            }
            $tempInv = [
                        'coa_code'=>trim($inv->coa_code),
                        'coa_name'=>$inv->coa_name,
                        'saldo_awal'=>$saldo_awal,
                        'debet'=>$mutasi_history[0]->total_mutasi_debet,
                        'credit'=>$mutasi_history[0]->total_mutasi_credit,
                        'saldo_akhir'=>$saldo_akhir
                       ];
            $data['invoices'][] = $tempInv;
        }

        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('GL_Report_'.$from.'_to_'.$to.'.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $data_ori = $data['invoices'];
            $data = array();
            $total_saldo_awal = 0;
            $total_debet = 0;
            $total_kredit = 0;
            $total_saldo_akhir = 0;
            for($i=0; $i<count($data_ori); $i++){
                $data[$i]=array(
                    'Account Code' =>$data_ori[$i]['coa_code'],
                    'Account Name' =>$data_ori[$i]['coa_name'],
                    'Saldo Awal' =>$data_ori[$i]['saldo_awal'],
                    'Debet'=>number_format($data_ori[$i]['debet'],2),
                    'Kredit' =>number_format($data_ori[$i]['credit'],2),
                    'Saldo Akhir' =>number_format($data_ori[$i]['saldo_akhir'],2)
                    );
                $total_saldo_awal = $total_saldo_awal + $data_ori[$i]['saldo_awal'];
                $total_debet = $total_debet + $data_ori[$i]['debet'];
                $total_kredit = $total_kredit + $data_ori[$i]['credit'];
                $total_saldo_akhir = $total_saldo_akhir + $data_ori[$i]['saldo_akhir'];
            }
            $data[$i] = array(NULL,'TOTAL',$total_saldo_awal,$total_debet,$total_kredit,$total_saldo_akhir);
            $border = 'A1:F';
            $tp = 'xls';
            return Excel::create('Trial Balance Report', function($excel) use ($data,$border) {
                $excel->sheet('Trial Balance Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    // 2 lajur
    public function neraca(){
        $data['page'] = 'Neraca';
        $data['formats'] = MsHeaderFormat::where('type',2)->get();
        return view('report_neracapl',$data);
    }

    public function neracatpl(Request $request)
    {
        $id = $request->format;
        $from = $request->from;
        $to = $request->to;
        $print = @$request->print;
        $company = MsCompany::first();
        $detail1 = MsDetailFormat::where('formathd_id',$id)->where('column',1)->orderBy('order','ASC')->get();
        $detail2 = MsDetailFormat::where('formathd_id',$id)->where('column',2)->orderBy('order','ASC')->get();
        $total = (count($detail1) > count($detail2) ? count($detail1) : count($detail2));
        $data = [
                'company' => $company,
                'datetxt' => date('d F Y',strtotime($from))." s/d ".date('d F Y',strtotime($to)),
                'detail1' => $detail1,
                'detail2' => $detail2,
                'from' => $from,
                'to' => $to,
                'total' =>$total,
                'variables' => []
            ];
        if($print == 1){ $data['jenis'] = 'print'; }else{ $data['jenis'] = 'none'; }
        $pdf = @$request->pdf;
        if(!empty($pdf)){
            $data['jenis'] = 'pdf';
            $pdf = PDF::loadView('report_neraca', $data)->setPaper('a4');
            return $pdf->download('NERACA.pdf');
        }
        return view('report_neraca', $data);
    }

    // 1 lajur
    public function profitloss(){
        $data['page'] = 'Laba Rugi';
        $data['formats'] = MsHeaderFormat::where('type',1)->get();
        return view('report_neracapl',$data);
    }

    public function profitlosstpl(Request $request)
    {
        $id = $request->format;
        $from = $request->from;
        $to = $request->to;
        $last_date = date("Y-m-t", strtotime($to));
        $print = @$request->print;
        $excel = @$request->excel;
        $company = MsCompany::first();
        $detail = MsDetailFormat::where('formathd_id',$id)->where('column',1)->orderBy('order','ASC')->get();
        $hdr = MsHeaderFormat::where('id',$id)->first();
        $name_file = $hdr->name;
        $data = [
                'company' => $company,
                'datetxt' => "Per ".date('d F Y',strtotime($to)),
                'detail' => $detail,
                'from' => $from,
                'to' => $to,
                'variables' => []
            ];
        if($print == 1){ $data['jenis'] = 'print'; }else{ $data['jenis'] = 'none'; }
        $pdf = @$request->pdf;
        if(!empty($pdf)){
            $data['jenis'] = 'pdf';
            $pdf = PDF::loadView('report_profitloss', $data)->setPaper('a4');
            return $pdf->download($name_file.'.pdf');
        }else if($excel){
            $excel = array();
            $mulai = 0;
            $variables = [];

            foreach($detail as $dt){
                $desc = html_entity_decode($dt->desc);
                $dt->setDate($from, $to);
                if(!empty($dt->header)) $desc = $desc;
                $dt->setVariables($variables);
                $calculate = $dt->calculateAccount($dt->jns);
                if(!empty($dt->variable)) $variables[$dt->variable] = $calculate;
                if($dt->hide == 0){
                    $deskripsi_excel = array(
                            'Deskripsi' =>$desc,
                            date('d F Y',strtotime($to)) =>(float)$calculate
                        );
                        $excel[$mulai] = $deskripsi_excel;
                        $mulai++;
                }
            }

            $border = 'A1:B';
            $tp = 'xls';
            $excelData = $excel;
            return Excel::create($name_file, function($excel) use ($excelData,$border,$name_file) {
                $excel->sheet($name_file, function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);
        }

        return view('report_profitloss', $data);
    }

    public function apview(){
        $data['invtypes'] = MsInvoiceType::all();
        $data['banks'] = MsCashBank::all();
        $data['payment_types'] = MsPaymentType::all();
        return view('report_ap',$data);
    }

    public function apAging(Request $request){
        $ty = @$request->jenis;
        $ag30 = @$request->ag30;
        $ag60 = @$request->ag60;
        $ag90 = @$request->ag90;
        $ag180 = @$request->ag180;
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $tyt = @$request->jenist;
        $sup_id = @$request->unit3;

        $data['tahun'] = 'Periode Sampai : '.date('M Y');
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Aged Payable Report By Supplier Key";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['unit'] = MsSupplier::where('id',$sup_id)->get();
        $data['tyt'] = $tyt;
        $data['ty'] = $ty;
        if($tyt != 1){
            $data['template'] = 'report_ap_aging';
        }else{
            $data['template'] = 'report_ap_aging_detail';
        }

        if($ty == 1 && $tyt == 2){
            $data['title_r'] = 'Summary Outstanding Ap';
        }else if($ty == 1 && $tyt == 1){
            $data['title_r'] = 'Detail Outstanding Ap';
        }else if($ty == 2 && $tyt == 2){
            $data['title_r'] = 'Summary Paid Ap';
        }else if($ty == 2 && $tyt == 1){
            $data['title_r'] = 'Detail Paid Ap';
        }else if($ty == 3 && $tyt == 2){
            $data['title_r'] = 'Summary All Ap';
        }else if($ty == 3 && $tyt == 1){
            $data['title_r'] = 'Detail All Ap';
        }else{
            $data['title_r'] = '';
        }
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        $data['label'] = explode('~', '1 - '.$ag30.'~'.$ag30.' - '.$ag60.'~'.$ag60.' - '.$ag90.'~'.'OVER '.$ag180);
        if($ty == 1){
            $fetch = TrApHeader::select('tr_ap_invoice_hdr.spl_id','ms_supplier.spl_code','ms_supplier.spl_name',
                    DB::raw("SUM(outstanding) AS total"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invoice_date::date) >= -1 AND (current_date::date - invoice_date::date) <=".$ag30." THEN outstanding ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invoice_date::date) > ".$ag30." AND (current_date::date - invoice_date::date)<=".$ag60." THEN outstanding ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invoice_date::date) >".$ag60." AND (current_date::date - invoice_date::date)<=".$ag90." THEN outstanding ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invoice_date::date) > ".$ag180." THEN outstanding ELSE 0 END)) AS agl180"))
                ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                ->where('tr_ap_invoice_hdr.posting','=',TRUE)
                ->where('outstanding','>',0)
                ->groupBy('ms_supplier.spl_code','ms_supplier.spl_name','tr_ap_invoice_hdr.spl_id')
                ->orderBy('spl_code', 'asc');
        }else if ($ty == 2){
            $fetch = TrApPaymentHeader::select('tr_ap_payment_hdr.spl_id','ms_supplier.spl_code','ms_supplier.spl_name',
                    DB::raw("SUM(tr_ap_payment_hdr.amount) AS total"),
                    DB::raw("SUM((CASE WHEN (current_date::date - payment_date::date) >= -1 AND (current_date::date - payment_date::date) <=".$ag30." THEN tr_ap_payment_hdr.amount ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - payment_date::date) > ".$ag30." AND (current_date::date - payment_date::date)<=".$ag60." THEN tr_ap_payment_hdr.amount ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - payment_date::date) >".$ag60." AND (current_date::date - payment_date::date)<=".$ag90." THEN tr_ap_payment_hdr.amount ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - payment_date::date) > ".$ag180." THEN tr_ap_payment_hdr.amount ELSE 0 END)) AS agl180"))
                ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_payment_hdr.spl_id')
                ->where('posting','=',TRUE)
                ->groupBy('tr_ap_payment_hdr.spl_id','ms_supplier.spl_code','ms_supplier.spl_name')
                ->orderBy('spl_code', 'asc');
        }else{
            if($tyt != 2){
                $fetch = MsSupplier::select('*')
                    ->orderBy('spl_code', 'asc');
            }else{
                //sama kyk not paid
                $fetch = TrApHeader::select('tr_ap_invoice_hdr.spl_id','ms_supplier.spl_code','ms_supplier.spl_name',
                    DB::raw("SUM(outstanding) AS total"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invoice_date::date) >= -1 AND (current_date::date - invoice_date::date) <=".$ag30." THEN outstanding ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invoice_date::date) > ".$ag30." AND (current_date::date - invoice_date::date)<=".$ag60." THEN outstanding ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invoice_date::date) >".$ag60." AND (current_date::date - invoice_date::date)<=".$ag90." THEN outstanding ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invoice_date::date) > ".$ag180." THEN outstanding ELSE 0 END)) AS agl180"))
                ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                ->where('tr_ap_invoice_hdr.posting','=',TRUE)
                ->where('outstanding','>',0)
                ->groupBy('ms_supplier.spl_code','ms_supplier.spl_name','tr_ap_invoice_hdr.id')
                ->orderBy('spl_code', 'asc');
            }
        }

        if($sup_id) $fetch = $fetch->where('ms_supplier.id','=',$sup_id);
        $fetch = $fetch->get();
        $data['invoices'] = $fetch;

        if($tyt != 2){
            $data['invoices'] = [];
            if($ty == 1){
                foreach ($fetch as $inv) {
                    $tempInv = [
                        'spl_code' => $inv->spl_code,
                        'spl_name' => $inv->spl_name,
                        'total' => $inv->total,
                        'ag30' => $inv->ag30,
                        'ag60' => $inv->ag60,
                        'ag90' => $inv->ag90,
                        'agl180' => $inv->agl180
                    ];
                    $tempInv['details'] = [];
                    $result = TrApHeader::select('tr_ap_invoice_hdr.*',
                                DB::raw("to_char(invoice_date, 'DD/MM/YYYY') AS tanggal"),
                                DB::raw("to_char(invoice_duedate, 'DD/MM/YYYY') AS tanggaldue"))
                            ->where('tr_ap_invoice_hdr.spl_id',$inv->spl_id)
                            ->where('outstanding','>',0)
                            ->where('posting',TRUE)
                        ->get();
                    foreach ($result as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->inv_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = $dif->d;

                        $tempInv['details'][] = [
                            'inv_number' => $value->invoice_no,
                            'tanggal' => $value->tanggal,
                            'tanggaldue' => $value->tanggaldue,
                            'inv_amount' => $value->total,
                            'inv_outstanding' => $value->outstanding,
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? $value->outstanding : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? $value->outstanding : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? $value->outstanding : 0),
                            'ags180' => ($difference > $ag180 ? $value->outstanding : 0)
                            ];
                    }
                    $data['invoices'][] = $tempInv;
                }
            }else if($ty == 2){
                foreach ($fetch as $inv) {
                    $tempInv = [
                        'spl_code' => $inv->spl_code,
                        'spl_name' => $inv->spl_name,
                        'total' => $inv->total,
                        'ag30' => $inv->ag30,
                        'ag60' => $inv->ag60,
                        'ag90' => $inv->ag90,
                        'agl180' => $inv->agl180
                    ];
                    $tempInv['details'] = [];
                    $result = TrApPaymentDetail::select('tr_ap_payment_dtl.amount','tr_ap_invoice_hdr.invoice_no',
                                DB::raw("to_char(tr_ap_payment_hdr.payment_date, 'DD/MM/YYYY') AS tanggal"),
                                DB::raw("to_char(tr_ap_invoice_hdr.invoice_duedate, 'DD/MM/YYYY') AS tanggaldue"))
                            ->join('tr_ap_payment_hdr','tr_ap_payment_dtl.aphdr_id',"=",'tr_ap_payment_hdr.id')
                            ->join('tr_ap_invoice_hdr','tr_ap_invoice_hdr.id',"=",'tr_ap_payment_dtl.aphdr_id')
                            ->where('tr_ap_invoice_hdr.spl_id',$inv->spl_id)
                            ->where('tr_ap_payment_hdr.posting',TRUE)
                        ->get();
                    foreach ($result as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->payment_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = $dif->d;

                        $tempInv['details'][] = [
                            'inv_number' => $value->invoice_no,
                            'tanggal' => $value->tanggal,
                            'tanggaldue' => $value->tanggaldue,
                            'inv_outstanding' => $value->amount,
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? $value->amount : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? $value->amount : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? $value->amount : 0),
                            'ags180' => ($difference > $ag180 ? $value->amount : 0)
                            ];
                    }
                    $data['invoices'][] = $tempInv;
                }
            }else{
                foreach ($fetch as $inv) {
                    $tempInv = [
                        'spl_code' => $inv->spl_code,
                        'spl_name' => $inv->spl_name
                    ];
                    $tempInv['details'] = [];
                    $result = TrApHeader::select('tr_ap_invoice_hdr.*',
                                DB::raw("to_char(invoice_date, 'DD/MM/YYYY') AS tanggal"),
                                DB::raw("to_char(invoice_duedate, 'DD/MM/YYYY') AS tanggaldue"))
                            ->where('tr_ap_invoice_hdr.spl_id',$inv->id)
                            ->where('posting',TRUE)
                        ->get();
                    foreach ($result as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->invoice_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = $dif->d;

                        $tempInv['details'][] = [
                            'inv_number' => $value->invoice_no,
                            'tanggal' => $value->tanggal,
                            'tanggaldue' => $value->tanggaldue,
                            'inv_outstanding' => $value->total,
                            'inv_tp' => 'INVOICE AP',
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? $value->total : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? $value->total : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? $value->total : 0),
                            'ags180' => ($difference > $ag180 ? $value->total : 0)
                            ];
                    }

                    $result2 = TrApPaymentDetail::select('tr_ap_payment_dtl.amount','tr_ap_invoice_hdr.invoice_no',
                                DB::raw("to_char(tr_ap_payment_hdr.payment_date, 'DD/MM/YYYY') AS tanggal"),
                                DB::raw("to_char(tr_ap_invoice_hdr.invoice_duedate, 'DD/MM/YYYY') AS tanggaldue"))
                            ->join('tr_ap_payment_hdr','tr_ap_payment_dtl.aphdr_id',"=",'tr_ap_payment_hdr.id')
                            ->join('tr_ap_invoice_hdr','tr_ap_invoice_hdr.id',"=",'tr_ap_payment_dtl.aphdr_id')
                            ->where('tr_ap_invoice_hdr.spl_id',$inv->spl_id)
                            ->where('tr_ap_payment_hdr.posting',TRUE)
                        ->get();
                    foreach ($result2 as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->payment_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = $dif->d;

                        $tempInv['details'][] = [
                            'inv_number' => $value->invoice_no,
                            'tanggal' => $value->tanggal,
                            'tanggaldue' => $value->tanggaldue,
                            'inv_outstanding' => ($value->amount * -1),
                            'inv_tp' => 'PAYMENT',
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? ($value->amount * -1) : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? ($value->amount * -1) : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? ($value->amount * -1) : 0),
                            'ags180' => ($difference > $ag180 ? ($value->amount * -1) : 0)
                            ];
                    }

                    $data['invoices'][] = $tempInv;
                }
            }
        }
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('AR_Aging_periode.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $data_ori = $fetch->toArray();
            $data = array();
            for($i=0; $i<count($data_ori); $i++){
                $name1 = '1-'.$ag30.' Days';
                $name2 = $ag30.'-'.$ag60.' Days';
                $name3 = $ag60.'-'.$ag90.' Days';
                $name4 = 'OVER '.$ag180.' Days';
                $data[$i]=array(
                    'Supplier Code' =>$data_ori[$i]['spl_code'],
                    'Nama Supplier' =>$data_ori[$i]['spl_name'],
                    'Total' =>number_format($data_ori[$i]['total']),
                    $name1 =>number_format($data_ori[$i]['ag30']),
                    $name2 =>number_format($data_ori[$i]['ag60']),
                    $name3 =>number_format($data_ori[$i]['ag90']),
                    $name4 =>number_format($data_ori[$i]['agl180']));
            }
            $border = 'A1:G';
            $tp = 'xls';
            return Excel::create('Aging Ap Report', function($excel) use ($data,$border) {
                $excel->sheet('Aging Ap Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function arsummary(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $unit_id = @$request->unit5;
        if(empty($unit_id)) return '<center><h3>Harap Masukkan Unit terlebih dahulu sebelum generate report AR Summary</h3></center>';

        $data['tahun'] = '';
        if(!empty($from) && !empty($to)) $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to))."<br>";
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "AR SUMMARY";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['unit_code'] = MsUnit::find($unit_id)->unit_code;
        $data['unit_sqrt'] = MsUnit::find($unit_id)->unit_sqrt;
        $data['tenan_name'] = TrContract::select('ms_tenant.tenan_name')
                    ->join('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id')
                    ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                    ->where('contr_status','=','confirmed')
                    ->where('contr_terminate_date','=',NULL)
                    ->where('tr_contract.unit_id',$unit_id)
                    ->get();
        $data['template'] = 'report_ar_summary';
        $data['type'] = 'none';

        $fetch = TrInvoice::where('inv_iscancel',FALSE)->where('inv_post','t')->where('unit_id', $unit_id)->orderBy('inv_date','ASC');
        $data['invoices'] = $fetch->get();

        $fetch2 = TrInvoice::select(
                    DB::raw("SUM(inv_outstanding) AS total"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_duedate::date) >= -1 AND (current_date::date - inv_duedate::date) <= 30 THEN inv_outstanding ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_duedate::date) > 30 AND (current_date::date - inv_duedate::date) <= 60 THEN inv_outstanding ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_duedate::date) > 60 AND (current_date::date - inv_duedate::date) <= 90 THEN inv_outstanding ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_duedate::date) > 90 THEN inv_outstanding ELSE 0 END)) AS agl180"))->
        where('inv_iscancel',FALSE)->where('inv_post','t')->where('unit_id', $unit_id)->first();
        $data['current'] = $fetch2;

        $denda = ReminderH::join('reminder_details','reminder_details.reminderh_id','=','reminder_header.id')->join('tr_invoice','reminder_details.inv_id','=','tr_invoice.id')->where('reminder_header.unit_id',$unit_id)->where('reminder_header.posting',1)->where('active_tagih',1)->get();
        $dn = array();
        $nilai_denda = 0;
        if(count($denda) > 0){
            foreach ($denda as $value) {
                $dn[$value->inv_number] = $value->denda_amount;
                $nilai_denda = $nilai_denda + $value->denda_amount;
            }
        }
        $data['dn'] = $dn;
        //print_r($dn);
        //die();
        $data['terbilang'] = $this->terbilang($fetch2->total + $nilai_denda);
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template4', $data)->setPaper('a4', 'landscape');
            return $pdf->download('AR_Summary.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $excelData = array();
            foreach($data['invoices'] as $inv){
                $kwitansi = $paydate = $checkno = $payamount = [];
                foreach($inv->paymentdtl as $paymdtl){
                    if(!$paymdtl->paymenthdr->status_void){
                        $kwitansi[] = $paymdtl->paymenthdr->no_kwitansi;
                        $paydate[] = $paymdtl->paymenthdr->invpayh_date;
                        $checkno[] = $paymdtl->paymenthdr->invpayh_checkno;
                        $payamount[] = $paymdtl->invpayd_amount;
                    }
                }

                if(count($paydate) <= 0){
                    // kalau tidak ada pembayaran denda
                    $date1=date_create(date('Y-m-d'));
                    $date2=date_create($inv->inv_duedate);
                    $diff=date_diff($date1,$date2);
                    // $hari = $diff->format("%a") - 7;
                    $hari = $diff->format("%a");
                    $denda = 1/1000 * $hari * $inv->inv_amount;
                }else{
                    // echo "denda gada";
                    $hari = $denda = 0;
                    if(end($paydate) > $inv->inv_duedate){
                        $date1=date_create(end($paydate));
                        $date2=date_create($inv->inv_duedate);
                        $diff=date_diff($date1,$date2);
                        $hari = $diff->format("%a");
                        $denda = 1/1000 * $hari * $inv->inv_amount;
                    }
                }

                // inv data
                $excelData[]=array(
                    'Tgl. JT' => date('d-m-Y',strtotime($inv->inv_duedate)),
                    'No. Invoice' => $inv->inv_number,
                    'Nilai Invoice' => "Rp ".number_format($inv->inv_amount),
                    'Tgl Bayar' => implode(", ", $paydate),
                    'No. Bayar' => implode(", ", $kwitansi),
                    'Pembayaran' => implode(", ", $payamount),
                    'Overdue' => $inv->inv_outstanding,
                    'Keterlambatan (Hari)' =>($hari < 0 ? '-' : $hari),
                    'Denda' => ($hari < 0 ? 0 : $denda),
                );
            }

            $border = 'A1:I';
            $tp = 'xls';
            return Excel::create('AR Summary Report', function($excel) use ($excelData,$border) {
                $excel->sheet('AR Summary Report', function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template4', $data);
        }
    }

    public function polist(Request $request){
        $ty = @$request->jenis;
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $tyt = @$request->jenist;
        $sup_id = @$request->unit3;

        $data['tahun'] = 'Periode Sampai : '.date('M Y');
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "PO List";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['unit'] = MsSupplier::where('id',$sup_id)->get();
        $data['tyt'] = $tyt;
        $data['ty'] = $ty;
        if($tyt != 1){
            $data['template'] = 'report_po_list';
        }else{
            $data['template'] = 'report_po_detail';
        }

        if($ty == 1 && $tyt == 1){
            $data['title_r'] = 'Summary Outstanding PO';
        }else if($ty == 1 && $tyt == 2){
            $data['title_r'] = 'Detail Outstanding PO';
        }else if($ty == 2 && $tyt == 1){
            $data['title_r'] = 'Summary Paid PO';
        }else if($ty == 2 && $tyt == 2){
            $data['title_r'] = 'Detail Paid PO';
        }else if($ty == 3 && $tyt == 1){
            $data['title_r'] = 'Summary All PO';
        }else if($ty == 3 && $tyt == 2){
            $data['title_r'] = 'Detail All PO';
        }else{
            $data['title_r'] = '';
        }
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        $data['all'] = 0;
        if($tyt == 1){
            //SUMMARY
            if($ty == 1){
                //BELUM BAYAR
                $fetch = TrApHeader::select('tr_ap_invoice_hdr.id','tr_ap_invoice_hdr.invoice_no','tr_ap_invoice_hdr.invoice_date','ms_supplier.spl_name','outstanding','total','po_number')
                    ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                    ->join('tr_purchase_order_hdr','tr_purchase_order_hdr.id',"=",'tr_ap_invoice_hdr.po_id')
                    ->where('outstanding','>',0)
                    ->where('tr_ap_invoice_hdr.posting','t')
                    ->orderBy('ms_supplier.spl_code', 'asc');
                if($sup_id) $fetch = $fetch->where('ms_supplier.id','=',$sup_id);
                $fetch = $fetch->get();
            }else if($ty == 2){
                //BAYAR
                $fetch = TrApHeader::select('tr_ap_invoice_hdr.id','tr_ap_invoice_hdr.invoice_no','tr_ap_invoice_hdr.invoice_date','ms_supplier.spl_name','outstanding','total','po_number')
                    ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                    ->join('tr_purchase_order_hdr','tr_purchase_order_hdr.id',"=",'tr_ap_invoice_hdr.po_id')
                    ->where('outstanding','<=',0)
                    ->where('tr_ap_invoice_hdr.posting','t')
                    ->orderBy('ms_supplier.spl_code', 'asc');
                    if($sup_id) $fetch = $fetch->where('ms_supplier.id','=',$sup_id);
                    $fetch = $fetch->get();
            }else{
                //ALL
                $data['all'] = 1;
                $allap = TrApHeader::select('tr_ap_invoice_hdr.id','tr_ap_invoice_hdr.invoice_no','tr_ap_invoice_hdr.invoice_date','ms_supplier.spl_name','outstanding','total','po_number')
                    ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                    ->join('tr_purchase_order_hdr','tr_purchase_order_hdr.id',"=",'tr_ap_invoice_hdr.po_id')
                    ->where('tr_ap_invoice_hdr.posting','t')
                    ->orderBy('ms_supplier.spl_code', 'asc');
                if($sup_id) $fetch = $allap->where('ms_supplier.id','=',$sup_id);
                $fetch = $allap->get();
            }
        }else{
            //DETAIL
            if($ty == 1){
                //BELUM BAYAR
                $data['all'] = 2;
                $fetch = TrApHeader::select('tr_ap_invoice_hdr.id','ms_supplier.spl_code','ms_supplier.id','ms_supplier.spl_name',DB::raw('SUM(tr_ap_invoice_hdr.total) AS npaid'), DB::raw('SUM(tr_ap_invoice_hdr.outstanding) AS outstanding'))
                    ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                    ->join('tr_purchase_order_hdr','tr_purchase_order_hdr.id',"=",'tr_ap_invoice_hdr.po_id')
                    ->where('outstanding','>',0)
                    ->where('posting','t')
                    ->groupBy('tr_ap_invoice_hdr.id','ms_supplier.spl_code','ms_supplier.id','ms_supplier.spl_name')
                    ->orderBy('ms_supplier.spl_code');
                if($sup_id) $fetch = $fetch->where('ms_supplier.id','=',$sup_id);
                $fetch = $fetch->get();
            }else if($ty == 2){
                //BAYAR
                $data['all'] = 3;
                $fetch = TrApHeader::select('tr_ap_invoice_hdr.id','ms_supplier.spl_code','ms_supplier.id','ms_supplier.spl_name',DB::raw('SUM(tr_ap_invoice_hdr.total) AS npaid'), DB::raw('SUM(tr_ap_invoice_hdr.outstanding) AS outstanding'))
                    ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                    ->join('tr_purchase_order_hdr','tr_purchase_order_hdr.id',"=",'tr_ap_invoice_hdr.po_id')
                    ->where('outstanding','<=',0)
                    ->where('posting','t')
                    ->groupBy('tr_ap_invoice_hdr.id','ms_supplier.spl_code','ms_supplier.id','ms_supplier.spl_name')
                    ->orderBy('ms_supplier.spl_code');
                if($sup_id) $fetch = $fetch->where('ms_supplier.id','=',$sup_id);
                $fetch = $fetch->get();
            }else{
                //ALL
                $belum = TrApHeader::select('tr_ap_invoice_hdr.id','ms_supplier.spl_code','ms_supplier.id','ms_supplier.spl_name',DB::raw('SUM(tr_ap_invoice_hdr.total) AS total'))
                    ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                    ->join('tr_purchase_order_hdr','tr_purchase_order_hdr.id',"=",'tr_ap_invoice_hdr.po_id')
                    ->where('outstanding','>',0)
                    ->where('posting','t')
                    ->groupBy('tr_ap_invoice_hdr.id','ms_supplier.spl_code','ms_supplier.id','ms_supplier.spl_name')
                    ->orderBy('ms_supplier.spl_code');
                if($sup_id) $belum = $belum->where('ms_supplier.id','=',$sup_id);
                $belum = $belum->get();

                $bayar = TrApHeader::select('tr_ap_invoice_hdr.id','ms_supplier.spl_code','ms_supplier.id','ms_supplier.spl_name',DB::raw('SUM(tr_ap_invoice_hdr.total) AS total'))
                    ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                    ->join('tr_purchase_order_hdr','tr_purchase_order_hdr.id',"=",'tr_ap_invoice_hdr.po_id')
                    ->where('outstanding',0)
                    ->where('posting','t')
                    ->groupBy('tr_ap_invoice_hdr.id','ms_supplier.spl_code','ms_supplier.id','ms_supplier.spl_name')
                    ->orderBy('ms_supplier.spl_code');
                if($sup_id) $bayar = $bayar->where('ms_supplier.id','=',$sup_id);
                $bayar = $bayar->get();

                $hasil_nl = array();
                foreach($belum as $ap){
                    $hasil_nl[] = $ap;
                }
                foreach($bayar as $ap){
                    $hasil_nl[] = $ap;
                }
                // var_dump($hasil_nl); die();
                $fetch = $hasil_nl;
            }
        }

        $data['invoices'] = $fetch;

        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'portrait');
            return $pdf->download('POList_Summary.pdf');

        }else if($excel){
            $data['type'] = 'excel';

        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function nonpolist(Request $request){
        $ty = @$request->jenis;
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $tyt = @$request->jenist;
        $sup_id = @$request->unit3;

        $data['tahun'] = 'Periode Sampai : '.date('M Y');
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Non PO List";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['unit'] = MsSupplier::where('id',$sup_id)->get();
        $data['tyt'] = $tyt;
        $data['ty'] = $ty;
        if($tyt == 1){
            $data['template'] = 'report_po_detail';
        }else{
            $data['template'] = 'report_nonpo_detail';
        }

        if($ty == 1 && $tyt == 1){
            $data['title_r'] = 'Summary Outstanding NON PO';
        }else if($ty == 1 && $tyt == 2){
            $data['title_r'] = 'Detail Outstanding NON PO';
        }else if($ty == 2 && $tyt == 1){
            $data['title_r'] = 'Summary Paid NON PO';
        }else if($ty == 2 && $tyt == 2){
            $data['title_r'] = 'Detail Paid NON PO';
        }else if($ty == 3 && $tyt == 1){
            $data['title_r'] = 'Summary All NON PO';
        }else if($ty == 3 && $tyt == 2){
            $data['title_r'] = 'Detail All NON PO';
        }else{
            $data['title_r'] = '';
        }
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        $data['all'] = 0;
        if($tyt == 1){
            $data['no_po'] = true;
            //DETAIL
            if($ty == 1){
                //BELUM BAYAR
                $fetch = TrApHeader::select('tr_ap_invoice_hdr.id','tr_ap_invoice_hdr.invoice_no','tr_ap_invoice_hdr.invoice_date','ms_supplier.spl_name','outstanding','total')
                    ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                    ->whereNull('po_id')
                    ->where('outstanding','>',0)
                    ->where('tr_ap_invoice_hdr.posting','t')
                    ->orderBy('ms_supplier.spl_code', 'asc');
                if($sup_id) $fetch = $fetch->where('ms_supplier.id','=',$sup_id);
                $fetch = $fetch->get();
            }else if($ty == 2){
                //BAYAR
                $fetch = TrApHeader::select('tr_ap_invoice_hdr.id','tr_ap_invoice_hdr.invoice_no','tr_ap_invoice_hdr.invoice_date','ms_supplier.spl_name','outstanding','total')
                    ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                    ->whereNull('po_id')
                    ->where('outstanding','<=',0)
                    ->where('tr_ap_invoice_hdr.posting','t')
                    ->orderBy('ms_supplier.spl_code', 'asc');
                if($sup_id) $fetch = $fetch->where('ms_supplier.id','=',$sup_id);
                $fetch = $fetch->get();
            }else{
                //ALL
                $data['all'] = 1;
                $allap = TrApHeader::select('tr_ap_invoice_hdr.id','tr_ap_invoice_hdr.invoice_no','tr_ap_invoice_hdr.invoice_date','ms_supplier.spl_name','outstanding','total')
                    ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                    ->whereNull('po_id')
                    ->where('tr_ap_invoice_hdr.posting','t')
                    ->orderBy('ms_supplier.spl_code', 'asc');
                if($sup_id) $fetch = $allap->where('ms_supplier.id','=',$sup_id);
                $fetch = $allap->get();

            }
        }else{
            //DETAIL
            if($ty == 1){
                //BELUM BAYAR
                $data['all'] = 2;
                $fetch = TrApHeader::select('ms_supplier.spl_name','invoice_no','invoice_duedate','tr_ap_invoice_hdr.note','invoice_date','payment_code','outstanding AS total')
                    ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                    ->leftjoin('tr_ap_payment_dtl','tr_ap_payment_dtl.aphdr_id',"=",'tr_ap_invoice_hdr.id')
                    ->leftjoin('tr_ap_payment_hdr','tr_ap_payment_hdr.id',"=",'tr_ap_payment_dtl.appaym_id')
                    ->where('tr_ap_invoice_hdr.posting','t')
                    ->where('tr_ap_invoice_hdr.po_id',NULL)
                    ->where('payment_code',NULL)
                    ->orderBy('invoice_no', 'asc');
                    if($sup_id) $fetch = $fetch->where('ms_supplier.id','=',$sup_id);
                    $fetch = $fetch->get();
            }else if($ty == 2){
                //BAYAR
                $data['all'] = 3;
                $fetch = TrApHeader::select('ms_supplier.spl_name','invoice_no','payment_code','payment_date','tr_ap_payment_dtl.amount','tr_ap_payment_hdr.note')
                    ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                    ->leftjoin('tr_ap_payment_dtl','tr_ap_payment_dtl.aphdr_id',"=",'tr_ap_invoice_hdr.id')
                    ->leftjoin('tr_ap_payment_hdr','tr_ap_payment_hdr.id',"=",'tr_ap_payment_dtl.appaym_id')
                    ->where('tr_ap_payment_hdr.posting','t')
                    ->where('tr_ap_invoice_hdr.po_id',NULL)
                    ->orderBy('payment_date', 'asc');
                    if($sup_id) $fetch = $fetch->where('ms_supplier.id','=',$sup_id);
                    $fetch = $fetch->get();
            }else{
                //ALL
                $belum = TrApHeader::select('tr_ap_invoice_hdr.id','ms_supplier.spl_name','invoice_no','invoice_duedate','tr_ap_invoice_hdr.note','invoice_date','payment_code','outstanding AS total')
                    ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                    ->leftjoin('tr_ap_payment_dtl','tr_ap_payment_dtl.aphdr_id',"=",'tr_ap_invoice_hdr.id')
                    ->leftjoin('tr_ap_payment_hdr','tr_ap_payment_hdr.id',"=",'tr_ap_payment_dtl.appaym_id')
                    ->where('tr_ap_invoice_hdr.posting','t')
                    ->where('tr_ap_invoice_hdr.po_id',NULL)
                    ->where('payment_code',NULL)
                    ->orderBy('invoice_no', 'asc');
                if($sup_id) $belum = $belum->where('ms_supplier.id','=',$sup_id);
                $belum = $belum->get();

                $bayar = TrApHeader::select('tr_ap_invoice_hdr.id','ms_supplier.spl_name','invoice_no','payment_code','payment_date','tr_ap_payment_dtl.amount','tr_ap_payment_hdr.note')
                    ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
                    ->leftjoin('tr_ap_payment_dtl','tr_ap_payment_dtl.aphdr_id',"=",'tr_ap_invoice_hdr.id')
                    ->leftjoin('tr_ap_payment_hdr','tr_ap_payment_hdr.id',"=",'tr_ap_payment_dtl.appaym_id')
                    ->where('tr_ap_payment_hdr.posting','t')
                    ->where('tr_ap_invoice_hdr.po_id',NULL)
                    ->orderBy('payment_date', 'asc');
                if($sup_id) $bayar = $bayar->where('ms_supplier.id','=',$sup_id);
                $bayar = $bayar->get();

                $hasil_nl = array();
                foreach($belum as $ap){
                    $hasil_nl[] = array('spl_name'=>$ap->spl_name,'kode'=>$ap->invoice_no,'amt'=>$ap->total, 'detail' => $ap->detail);
                }

                foreach($bayar as $ap){
                    if($ap->payment->count() > 0){
                        $paid = 0;
                        foreach($ap->payment as $apbyr){
                            if(!empty(@$apbyr->header->posting)){
                                $paid += $apbyr->amount;
                                // break;
                            }
                        }
                    }
                    $hasil_nl[] = array('spl_name'=>$ap->spl_name, 'kode'=>$ap->payment_code,'amt'=> $ap->amount,'paid' => $paid,'detail' => $ap->detail);
                }
                $fetch = $hasil_nl;
            }
        }

        $data['invoices'] = $fetch;
        // var_dump($data['invoices']); die();

        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'portrait');
            return $pdf->download('NONPOList_Summary.pdf');

        }else if($excel){
            $data['type'] = 'excel';

        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function phistory(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $sup_id = @$request->unit3;
        $from = @$request->from;
        $to = @$request->to;

        $data['tahun'] = 'Periode Sampai : '.date('M Y');
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "AP Payment History";
        $data['logo'] = MsCompany::first()->comp_image;

        $data['template'] = 'report_phistory';
        $data['title_r'] = 'AP Payment History';

        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        // $belum = TrApHeader::select('ms_supplier.spl_name','invoice_no','invoice_date','invoice_duedate','tr_ap_invoice_hdr.note','invoice_date','payment_code','outstanding AS total')
        //             ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
        //             ->leftjoin('tr_ap_payment_dtl','tr_ap_payment_dtl.aphdr_id',"=",'tr_ap_invoice_hdr.id')
        //             ->leftjoin('tr_ap_payment_hdr','tr_ap_payment_hdr.id',"=",'tr_ap_payment_dtl.appaym_id')
        //             ->where('tr_ap_invoice_hdr.posting','t')
        //             ->where('payment_code',NULL)
        //             ->orderBy('invoice_no', 'asc');
        // if($from) $belum = $belum->where('tr_ap_payment_hdr.payment_date','>=',$from);
        // if($to) $belum = $belum->where('tr_ap_payment_hdr.payment_date','<=',$to);
        // if($sup_id) $belum = $belum->where('ms_supplier.id','=',$sup_id);
        // $belum = $belum->get();

        // $hasil_nl = [];
        // foreach ($belum as $ap) {
        //     $hasil_nl[] = $ap;
        // }

        $bayar = TrApHeader::select('ms_supplier.spl_name','invoice_no','invoice_date','payment_code','payment_date','tr_ap_payment_dtl.amount','tr_ap_payment_hdr.note','cashbk_name','tr_ap_payment_dtl.amount')
            ->join('ms_supplier','ms_supplier.id',"=",'tr_ap_invoice_hdr.spl_id')
            ->leftjoin('tr_ap_payment_dtl','tr_ap_payment_dtl.aphdr_id',"=",'tr_ap_invoice_hdr.id')
            ->leftjoin('tr_ap_payment_hdr','tr_ap_payment_hdr.id',"=",'tr_ap_payment_dtl.appaym_id')
            ->leftjoin('ms_cash_bank','ms_cash_bank.id',"=",'tr_ap_payment_hdr.cashbk_id')
            ->where('tr_ap_payment_hdr.posting','t')
            ->orderBy('payment_date', 'asc');
            if($sup_id) $bayar = $bayar->where('ms_supplier.id','=',$sup_id);
        $bayar = $bayar->get();

        foreach ($bayar as $ap) {
            $hasil_nl[] = $ap;
        }

        // $hasil_nl = array();
        // if(count($belum) > 0){
        //     for($i=0; $i<count($belum); $i++){
        //         $hasil_nl[] = array(
        //             'kode'=>$belum[$i]->invoice_no,
        //             'tgl'=>$belum[$i]->invoice_date,
        //             'spl_name'=>$belum[$i]->spl_name,
        //             'bank'=>NULL,
        //             'debet'=>$belum[$i]->total,
        //             'kredit'=>0
        //         );
        //     }
        // }
        // if(count($bayar) > 0){
        //     for($p=0; $p<count($bayar); $p++){
        //         $hasil_nl[] = array(
        //             'kode'=>$bayar[$p]->payment_code,
        //             'tgl'=>$bayar[$p]->payment_date,
        //             'spl_name'=>$bayar[$p]->spl_name,
        //             'bank'=>$bayar[$p]->cashbk_name,
        //             'debet'=>0,
        //             'kredit'=>$bayar[$p]->amount
        //         );
        //     }
        // }
        $fetch = $hasil_nl;

        $data['invoices'] = $fetch;

        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'portrait');
            return $pdf->download('NONPOList_Summary.pdf');

        }else if($excel){
            $data['type'] = 'excel';

        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function budgetreport(){
        $data['page'] = 'Budget';
        $data['formats'] = MsHeaderFormat::where('type',1)->where('name','Budget')->get();
        $data['tahun'] = TrBudgetHdr::all();
        return view('report_budget',$data);
    }

    public function budgettpl(Request $request)
    {
        $id = $request->format;
        $tahun = $request->tahun;
        $print = @$request->print;
        $company = MsCompany::first();
        $detail = TrBudgetDetail::where('formathd_id',$id)->where('column',1)->orderBy('order','ASC')->get();
        $data = [
                'company' => $company,
                'detail' => $detail,
                'tahun' => $tahun,
                'variables' => [],
                'v_jan' => [],
                'v_feb' => [],
                'v_mar' => [],
                'v_apr' => [],
                'v_may' => [],
                'v_jun' => [],
                'v_jul' => [],
                'v_aug' => [],
                'v_sep' => [],
                'v_okt' => [],
                'v_nov' => [],
                'v_des' => []
            ];
        if($print == 1){ $data['jenis'] = 'print'; }else{ $data['jenis'] = 'none'; }
        $pdf = @$request->pdf;
        if(!empty($pdf)){
            $data['jenis'] = 'pdf';
            $pdf = PDF::loadView('budget_view', $data)->setPaper('a4');
            return $pdf->download('BUDGET.pdf');
        }

        return view('budget_view', $data);
    }

    public function realisasi(){
        $data['page'] = 'Realisasi';
        $data['formats'] = MsHeaderFormat::where('type',1)->where('name','Budget Vs Realisasi')->get();
        $data['tahun'] = TrBudgetHdr::all();
        return view('report_budget',$data);
    }

    public function realisasitpl(Request $request)
    {
        $id = $request->format;
        $tahun = $request->tahun;
        $print = @$request->print;
        $company = MsCompany::first();
        $detail = Realisasi::where('formathd_id',$id)->where('column',1)->orderBy('order','ASC')->get();
        $data = [
                'company' => $company,
                'detail' => $detail,
                'tahun' => $tahun,
                'variables' => [],
                'v_jan' => [],
                'v_feb' => [],
                'v_mar' => [],
                'v_apr' => [],
                'v_may' => [],
                'v_jun' => [],
                'v_jul' => [],
                'v_aug' => [],
                'v_sep' => [],
                'v_okt' => [],
                'v_nov' => [],
                'v_des' => [],
                'j_jan' => [],
                'j_feb' => [],
                'j_mar' => [],
                'j_apr' => [],
                'j_may' => [],
                'j_jun' => [],
                'j_jul' => [],
                'j_aug' => [],
                'j_sep' => [],
                'j_okt' => [],
                'j_nov' => [],
                'j_des' => []
            ];
        if($print == 1){ $data['jenis'] = 'print'; }else{ $data['jenis'] = 'none'; }
        $pdf = @$request->pdf;
        if(!empty($pdf)){
            $data['jenis'] = 'pdf';
            $pdf = PDF::loadView('realisasi_view', $data)->setPaper('a4');
            return $pdf->download('BUDGET VS REALISASI.pdf');
        }

        return view('realisasi_view', $data);
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
<<<<<<< Updated upstream
=======

    public function vasummary(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $bulan = @$request->bulan;
        $tahun = @$request->tahun;
        $inv_type = @$request->inv_type_2;

        $data['tahun'] = 'Periode : '.date('F',strtotime($tahun.'-'.$bulan.'-01')).' '.date('Y',strtotime($tahun.'-'.$bulan.'-01'))."<br>";
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "VA SUMMARY";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_va_summary';
        $data['type'] = 'none';

        $fetch = TrInvoice::select('ms_unit.unit_code','va_maintenance','va_utilities','tenan_name','inv_duedate','inv_amount','inv_number')
                ->join('ms_unit','ms_unit.id',"=",'tr_invoice.unit_id')
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->where('inv_iscancel','f')
                ->where('invtp_id',$inv_type)
                ->where(DB::raw('EXTRACT(MONTH FROM inv_date)'),$bulan)
                ->where(DB::raw('EXTRACT(YEAR FROM inv_date)'),$tahun);
        $data['invoices'] = $fetch->get();
        $data['inv_tp'] = $inv_type;

        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('VA_Summary.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $excelData = array();
            foreach($data['invoices'] as $inv){
                if($inv_type == 1){
                    $excelData[]=array(
                        'No.Unit'=>$inv->unit_code,
                        'No.Virtual'=>$inv->va_utilities,
                        'Nama'=>$inv->tenan_name,
                        'No.Invoice'=>$inv->inv_number,
                        'Tanggal Kadaluarsa'=>date('m/d/Y',strtotime($inv->inv_duedate)),
                        'Tanggal Jatuh Tempo'=>date('m/d/Y',strtotime($inv->inv_duedate)),
                        'Tagihan'=>(float)$inv->inv_amount
                    );
                }else{
                    $excelData[]=array(
                        'No.Unit'=>$inv->unit_code,
                        'No.Virtual'=>$inv->va_maintenance,
                        'Nama'=>$inv->tenan_name,
                        'No.Invoice'=>$inv->inv_number,
                        'Tanggal Kadaluarsa'=>date('m/d/Y',strtotime($inv->inv_duedate)),
                        'Tanggal Jatuh Tempo'=>date('m/d/Y',strtotime($inv->inv_duedate)),
                        'Tagihan'=>(float)$inv->inv_amount
                    );
                }
                
            }
            $border = 'A1:F';
            $tp = 'xls';
            return Excel::create('VA Summary Report', function($excel) use ($excelData,$border) {
                $excel->sheet('VA Summary Report', function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function sms(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $bulan = @$request->bulan;
        $tahun = @$request->tahun;
        $tglduedate = @MsConfig::where('name','duedate_interval')->first()->value;

        $data['tahun'] = 'Periode : '.date('F',strtotime($tahun.'-'.$bulan.'-01')).' '.date('Y',strtotime($tahun.'-'.$bulan.'-01'))."<br>";
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "SMS BLAST TEMPLATE";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_sms_summary';
        $data['type'] = 'none';

        $fetch = TrInvoice::select('ms_unit.unit_code','ms_tenant.tenan_name','ms_tenant.tenan_phone','ms_tenant.tenan_email',
        			DB::raw("LEFT(ms_unit.unit_code,2) AS tower"),
        			DB::raw("LEFT(ms_unit.unit_code,4) AS lantai"),
        			DB::raw("RIGHT(ms_unit.unit_code,2) AS nomor"),
                    DB::raw("SUM(inv_outstanding) AS total"),
                    DB::raw("SUM((CASE WHEN invtp_id = 2 THEN inv_outstanding ELSE 0 END)) AS maintenance"),
                    DB::raw("SUM((CASE WHEN invtp_id = 1 THEN inv_outstanding ELSE 0 END)) AS utilities")
                )
                ->join('ms_unit','ms_unit.id',"=",'tr_invoice.unit_id')
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->where('inv_iscancel','f')
                ->where('inv_post','t')
                ->where('ms_tenant.tenan_phone','<>','-')
                ->groupBy('ms_unit.unit_code','ms_tenant.tenan_name','ms_tenant.tenan_phone','ms_tenant.tenan_email')
                ->orderBy('total','ASC');
        $data['invoices'] = $fetch->get();
  
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('SMS_Summary.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $excelData = array();
            foreach($data['invoices'] as $inv){
                if($inv->tenan_phone != '-'){
                	switch ($inv->tower) {
                		case '01':
                			$twr = 'Twr.Kalyana';
                			break;
                		case '02':
                			$twr = 'Twr.Kirana';
                			break;
                		default:
                			$twr = 'Twr.Kalyana';
                			break;
                	}
                	$ltn = substr($inv->lantai, 2 , 2);
                	$text = $twr.' LT.'.$ltn.' NO '.(int)$inv->nomor;
                    $excelData[]=array(
                        'No Unit'=>$inv->unit_code,
                        'Tenan'=>$inv->tenan_name,
                        'No'=>$inv->tenan_phone,
                        'Email'=>$inv->tenan_email,
                        'Pesan'=>'Bpk/Ibu Yth, '.$text.' tagihan s/d bln '.
                        date('M y',strtotime($tahun.'-'.$bulan.'-01')).' Rp'.number_format($inv->total,0).
                        ' Rincian:SC:Rp'.number_format($inv->maintenance,0).';'.'Air:'.number_format($inv->utilities,0).';'.'(diluar denda jk ada).Tempo '.$tglduedate.' '.date('M',strtotime($tahun.'-'.$bulan.'-01')).' Info(02122232592)',
                        'Amount'=>number_format($inv->total,0)
                    );
                }
            }
            $border = 'A1:A';
            $tp = 'xls';
            return Excel::create('SMS Summary Report', function($excel) use ($excelData,$border) {
                $excel->sheet('SMS Summary Report', function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'none');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function depositsummary(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $bulan = @$request->bulan;
        $tahun = @$request->tahun;
        $rekap = @$request->rekap;

        
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "REPORT DEPOSIT";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_deposit';
        $data['type'] = 'none';

        if($rekap == 1){
            $data['tahun'] = "REKAP";
            $fetch = FittingIn::select('ms_unit.unit_code','ms_tenant.tenan_name','fit_number','fit_date','fit_refno','fit_amount','out_number','out_date','out_amount',
                    DB::raw("(CASE WHEN fit_amount >= 3000000 THEN 3000000 ELSE 0 END) AS fitout"),
                    DB::raw("(CASE WHEN fit_amount <> 200000 THEN fit_amount - 3000000 ELSE 200000 END) AS admin")
                )
                ->join('ms_unit','ms_unit.id',"=",'tr_fitting_in.unit_id')
                ->join('ms_tenant','ms_tenant.id',"=",'tr_fitting_in.tenan_id')
                ->leftjoin('tr_fitting_out','tr_fitting_in.id',"=",'tr_fitting_out.fit_id')
                ->orderBy('fit_date','ASC');
        }else{
            $data['tahun'] = 'Periode : '.date('F',strtotime($tahun.'-'.$bulan.'-01')).' '.date('Y',strtotime($tahun.'-'.$bulan.'-01'))."<br>";
            $fetch = FittingIn::select('ms_unit.unit_code','ms_tenant.tenan_name','fit_number','fit_date','fit_refno','fit_amount','out_number','out_date','out_amount',
                    DB::raw("(CASE WHEN fit_amount >= 3000000 THEN 3000000 ELSE 0 END) AS fitout"),
                    DB::raw("(CASE WHEN fit_amount <> 200000 THEN fit_amount - 3000000 ELSE 200000 END) AS admin")
                )
                ->join('ms_unit','ms_unit.id',"=",'tr_fitting_in.unit_id')
                ->join('ms_tenant','ms_tenant.id',"=",'tr_fitting_in.tenan_id')
                ->leftjoin('tr_fitting_out','tr_fitting_in.id',"=",'tr_fitting_out.fit_id')
                ->where(DB::raw('EXTRACT(MONTH FROM fit_date)'),$bulan)
                ->where(DB::raw('EXTRACT(YEAR FROM fit_date)'),$tahun)
                ->orderBy('fit_date','ASC');
        }
        $data['invoices'] = $fetch->get();
  
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Deposit_Summary.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $excelData = array();
            $total_fo = 0;
            $total_adm = 0;
            $total_saldo = 0;
            $total_potong = 0;
            $grand = 0;
            $grand2 = 0;
            foreach($data['invoices'] as $inv){
                if($inv->fitout == 0){
                    $saldo = 0;
                    $potong = 0;
                }else if($inv->fitout - $inv->out_amount > 0 && $inv->out_number != NULL){
                    $potong = $inv->fitout - $inv->out_amount;
                    $saldo = 0;
                }else{
                    $potong = 0;
                    $saldo = $inv->fitout - $inv->out_amount;
                }
                $excelData[]=array(
                    'No Unit'=>$inv->unit_code,
                    'Tenan'=>$inv->tenan_name,
                    'Tanggal'=>$inv->fit_date,
                    'Nomor FO'=>$inv->fit_number,
                    'Nilai FO'=>(float)$inv->fitout,
                    'Nilai Adm'=>(float)$inv->admin,
                    'Total'=>(float)$inv->fit_amount,
                    'Ref'=>$inv->fit_refno,
                    'No.Out'=>$inv->out_number,
                    'Tanggal Out'=>$inv->out_date,
                    'Amount'=>(float)$inv->out_amount,
                    'Pemotongan'=>(float)$potong,
                    'Saldo'=>(float)$saldo
                );
                $total_fo = $total_fo + $inv->fitout;
                $total_adm = $total_adm + $inv->admin;
                $grand = $grand + $inv->fit_amount;
                $grand2 = $grand2 + $inv->out_amount;
                $total_potong = $total_potong + $potong;
                $total_saldo = $total_saldo + $saldo;
            }
            $excelData[]=array(
                    'No Unit'=>'TOTAL',
                    'Tenan'=>'',
                    'Tanggal'=>'',
                    'Nomor FO'=>'',
                    'Nilai FO'=>(float)$total_fo,
                    'Nilai Adm'=>(float)$total_adm,
                    'Total'=>(float)$grand,
                    'Ref'=>'',
                    'No.Out'=>'',
                    'Tanggal Out'=>'',
                    'Amount'=>(float)$grand2,
                    'Pemotongan'=>(float)$total_potong,
                    'Saldo'=>(float)$total_saldo
                );
            $border = 'A1:A';
            $tp = 'xls';
            return Excel::create('Deposit Summary Report', function($excel) use ($excelData,$border) {
                $excel->sheet('Deposit Summary Report', function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'none');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function manualsummary(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $bulan = @$request->bulan;
        $tahun = @$request->tahun;

        $data['tahun'] = 'Periode : '.date('F',strtotime($tahun.'-'.$bulan.'-01')).' '.date('Y',strtotime($tahun.'-'.$bulan.'-01'))."<br>";
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "REPORT MANUAL INVOICE";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_manualinv';
        $data['type'] = 'none';

        $fetch = ManualHdr::select('ms_unit.unit_code','ms_tenant.tenan_name','manual_number','manual_date','manual_refno','manual_amount','name')
                ->join('ms_unit','ms_unit.id',"=",'tr_manualinv_hdr.unit_id')
                ->join('ms_tenant','ms_tenant.id',"=",'tr_manualinv_hdr.tenan_id')
                ->join('ms_manual_type','ms_manual_type.id',"=",'tr_manualinv_hdr.manual_type')
                ->where(DB::raw('EXTRACT(MONTH FROM manual_date)'),$bulan)
                ->where(DB::raw('EXTRACT(YEAR FROM manual_date)'),$tahun)
                ->orderBy('manual_date','ASC');
        $data['invoices'] = $fetch->get();
  
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('ManualInv_Summary.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $excelData = array();
            $grand = 0;
            foreach($data['invoices'] as $inv){
                $excelData[]=array(
                    'No Unit'=>$inv->unit_code,
                    'Tenan'=>$inv->tenan_name,
                    'Tanggal'=>$inv->manual_date,
                    'Nomor Inv'=>$inv->manual_number,
                    'Type'=>$inv->name,
                    'Total'=>(float)$inv->manual_amount,
                    'Ref'=>$inv->manual_refno
                );
                $grand = $grand + $inv->manual_amount;
            }
            $excelData[]=array(
                    'No Unit'=>'TOTAL',
                    'Tenan'=>'',
                    'Tanggal'=>'',
                    'Nomor Inv'=>'',
                    'Type'=>'',
                    'Total'=>(float)$grand,
                    'Ref'=>''
                );
            $border = 'A1:A';
            $tp = 'xls';
            return Excel::create('Manual Invoice Summary Report', function($excel) use ($excelData,$border) {
                $excel->sheet('Manual Invoice Summary Report', function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'none');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function dendasummary(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $bulan = @$request->bulan;
        $tahun = @$request->tahun;

        $data['tahun'] = 'Periode : '.date('F',strtotime($tahun.'-'.$bulan.'-01')).' '.date('Y',strtotime($tahun.'-'.$bulan.'-01'))."<br>";
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "REPORT DENDA";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_denda';
        $data['type'] = 'none';

        $fetch = TrDendaPayment::select('tr_denda_payment.*', 'ms_tenant.tenan_name', 'ms_unit.unit_code', 'reminder_header.reminder_no', 'ms_cash_bank.cashbk_name')
                    ->join('ms_tenant', 'ms_tenant.id',"=",'tr_denda_payment.tenan_id')
                    ->join('ms_unit', 'ms_unit.id',"=",'tr_denda_payment.unit_id')
                    ->join('reminder_header', 'reminder_header.id',"=",'tr_denda_payment.reminderh_id')
                    ->join('ms_cash_bank', 'ms_cash_bank.id',"=",'tr_denda_payment.bank_id')
                    ->where(DB::raw('EXTRACT(MONTH FROM denda_date)'),$bulan)
                    ->where(DB::raw('EXTRACT(YEAR FROM denda_date)'),$tahun)
                    ->orderBy('denda_date','ASC');

        $data['invoices'] = $fetch->get();
  
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Denda_Summary.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $excelData = array();
            $grand = 0;
            foreach($data['invoices'] as $inv){
                $excelData[]=array(
                    'No Unit'=>$inv->unit_code,
                    'Tenan'=>$inv->tenan_name,
                    'Tanggal'=>$inv->denda_date,
                    'Nomor Denda'=>$inv->denda_number,
                    'Bank'=>$inv->cashbk_name,
                    'Note'=>$inv->denda_keterangan,
                    'Total'=>(float)$inv->denda_amount,
                    'Posting'=>($inv->posting == 1 ? 'YES' : 'NO'),
                    'Void'=>($inv->status_void == 1 ? 'YES' : 'NO')
                );
                $grand = $grand + $inv->denda_amount;
            }
            $excelData[]=array(
                    'No Unit'=>'TOTAL',
                    'Tenan'=>'',
                    'Tanggal'=>'',
                    'Nomor Denda'=>'',
                    'Bank'=>'',
                    'Note'=>'',
                    'Total'=>(float)$grand,
                    'Posting'=>'',
                    'Void'=>''
                );
            $border = 'A1:A';
            $tp = 'xls';
            return Excel::create('Denda Summary Report', function($excel) use ($excelData,$border) {
                $excel->sheet('Denda Summary Report', function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'none');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function spsummary(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $sptype = @$request->sptype;

        $data['tahun'] = 'Periode : '.date('F').' '.date('Y')."<br>";
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "REPORT REMINDER MANUAL";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_sp';
        $data['type'] = 'none';

        $fetch = ReminderH::select('reminder_header.*', 'ms_unit.unit_code' ,'ms_tenant.tenan_name','ms_tenant.tenan_phone')
                    ->leftjoin('ms_unit_owner','ms_unit_owner.unit_id','=','reminder_header.unit_id')
                    ->leftJoin('ms_unit','reminder_header.unit_id','=','ms_unit.id')
                    ->leftjoin('ms_tenant','ms_tenant.id','=','ms_unit_owner.tenan_id')
                    ->where('ms_unit_owner.deleted_at',NULL)
                    ->where('posting',1)
                    ->where('active_tagih',1);
        if($sptype <> 0){
            $fetch = ReminderH::select('reminder_header.*', 'ms_unit.unit_code' ,'ms_tenant.tenan_name','ms_tenant.tenan_phone')
                    ->leftjoin('ms_unit_owner','ms_unit_owner.unit_id','=','reminder_header.unit_id')
                    ->leftJoin('ms_unit','reminder_header.unit_id','=','ms_unit.id')
                    ->leftjoin('ms_tenant','ms_tenant.id','=','ms_unit_owner.tenan_id')
                    ->where('ms_unit_owner.deleted_at',NULL)
                    ->where('sp_type',$sptype)
                    ->where('posting',1)
                    ->where('active_tagih',1);
        }

        $data['invoices'] = $fetch->get();
  
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('SP_Summary.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $excelData = array();
            $pokok = 0;
            $denda = 0;
            $total = 0;
            $no = 1;
            foreach($data['invoices'] as $inv){
                switch ($inv->sp_type) {
                    case '4':
                            $sp = 'SP 1';
                            break;
                    case '5':
                            $sp = 'SP 2';
                            break;
                    case '6':
                            $sp = 'SP 3';
                            break;
                     default:
                            $sp = 'Unidentified';
                         break;
                 }
                $excelData[]=array(
                    'No.'=>$no,
                    'Reminder No.'=>$inv->reminder_no,
                    'Unit'=>$inv->unit_code,
                    'Nama Tenant'=>$inv->tenan_name,
                    'Phone'=>$inv->tenan_phone,
                    'Tanggal'=>$inv->reminder_date,
                    'Pokok'=>(float)$inv->pokok_amount,
                    'Denda'=>(float)$inv->denda_total,
                    'Total'=>(float)$inv->denda_outstanding,
                    'Last Send'=>$inv->lastsent_date,
                    'SP' => $sp
                );
                $pokok = $pokok + $inv->pokok_amount;
                $denda = $denda + $inv->denda_total;
                $total = $total + $inv->denda_outstanding;
                $no++;
            }
            $excelData[]=array(
                    'No.'=>'TOTAL',
                    'Reminder No.'=>'',
                    'Unit'=>'',
                    'Nama Tenant'=>'',
                    'Phone'=>'',
                    'Tanggal'=>'',
                    'Pokok'=>(float)$pokok,
                    'Denda'=>(float)$denda,
                    'Total'=>(float)$total,
                    'Last Send'=>'',
                    'SP'=>''
                );
            $border = 'A1:K';
            $tp = 'xls';
            return Excel::create('SP Summary Report', function($excel) use ($excelData,$border) {
                $excel->sheet('SP Summary Report', function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'none');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function vaother(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $bulan = @$request->bulan;
        $tahun = @$request->tahun;
        $rekap = @$request->rekap;

        if($rekap == 0){
        $data['tahun'] = 'Periode : '.date('F',strtotime($tahun.'-'.$bulan.'-01')).' '.date('Y',strtotime($tahun.'-'.$bulan.'-01'))."<br>";
    	}else{
    	$data['tahun'] = 'Periode : '.date('Y',strtotime($tahun.'-'.$bulan.'-01'))."<br>";
    	}
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "VA OTHER";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_va_other';
        $data['type'] = 'none';

        if($rekap == 0){
        	$fetch = TrVaOther::select('unit_code','tenan_name','va_date','va_amount')
                ->join('ms_unit','ms_unit.id',"=",'tr_va_other.unit_id')
                ->join('ms_tenant','ms_tenant.id',"=",'tr_va_other.tenan_id')
                ->where(DB::raw('EXTRACT(MONTH FROM va_date)'),$bulan)
                ->where(DB::raw('EXTRACT(YEAR FROM va_date)'),$tahun)
                ->orderBy('va_date','ASC');
        }else{
        	$fetch = TrVaOther::select('unit_code','tenan_name','va_date','va_amount')
                ->join('ms_unit','ms_unit.id',"=",'tr_va_other.unit_id')
                ->join('ms_tenant','ms_tenant.id',"=",'tr_va_other.tenan_id')
                ->where(DB::raw('EXTRACT(YEAR FROM va_date)'),$tahun)
                ->orderBy('va_date','ASC');
        }
        $data['invoices'] = $fetch->get();

        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('VA_Other.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $excelData = array();
            foreach($data['invoices'] as $inv){
                $excelData[]=array(
                    	'Tanggal'=>$inv->va_date,
                        'No.Unit'=>$inv->unit_code,
                        'Nama'=>$inv->tenan_name,
                        'Amount'=>(float)$inv->va_amount
                    );
                
            }
            $border = 'A1:D';
            $tp = 'xls';
            return Excel::create('VA Other Report', function($excel) use ($excelData,$border) {
                $excel->sheet('VA Other Report', function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function arAging2(Request $request){
        $ty = @$request->jenis;
        $ag30 = @$request->ag30;
        $ag60 = @$request->ag60;
        $ag90 = @$request->ag90;
        $ag180 = @$request->ag180;
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $tyt = @$request->jenist;
        $cutoff = @$request->cutoff;
        $unit_id = @$request->unit3;

        $data['tahun'] = 'Periode Sampai : '.date('M Y');
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Aged Receivables Report By Customer Key";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['unit'] = MsUnit::where('id',$unit_id)->get();
        $data['tyt'] = $tyt;
        $data['ty'] = $ty;
        if($tyt == 1){
            $data['template'] = 'report_ar_aging';
        }else{
            $data['template'] = 'report_ar_aging_detail';
        }

        if($ty == 1 && $tyt == 1){
            $data['title_r'] = 'Summary Outstanding Invoice';
        }else if($ty == 1 && $tyt == 2){
            $data['title_r'] = 'Detail Outstanding Invoice';
        }else if($ty == 2 && $tyt == 1){
            $data['title_r'] = 'Summary Paid Invoice';
        }else if($ty == 2 && $tyt == 2){
            $data['title_r'] = 'Detail Paid Invoice';
        }else if($ty == 3 && $tyt == 1){
            $data['title_r'] = 'Summary All Invoice';
        }else if($ty == 3 && $tyt == 2){
            $data['title_r'] = 'Detail All Invoice';
        }else{
            $data['title_r'] = '';
        }
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
        $data['label'] = explode('~', '1 - '.$ag30.'~'.$ag30.' - '.$ag60.'~'.$ag60.' - '.$ag90.'~'.'OVER '.$ag180);
        if($ty == 1){
            $fetch = TrInvoice::select('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name','tr_contract.contr_bast_date',
                    DB::raw("SUM(inv_outstanding) AS total"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >= -1 AND (current_date::date - inv_date::date) <=".$ag30." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > ".$ag30." AND (current_date::date - inv_date::date)<=".$ag60." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >".$ag60." AND (current_date::date - inv_date::date)<=".$ag90." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > ".$ag90." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS agl180"))
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_invoice.unit_id')
                ->leftjoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->where('tr_contract.contr_status','=','confirmed')
                ->where('tr_invoice.inv_post','=',TRUE)
                ->where('tr_invoice.inv_outstanding','>',0)
                ->where('tr_invoice.inv_date','<=',$cutoff)
                ->groupBy('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name','tr_contract.contr_bast_date')
                ->orderBy('unit_code', 'asc');
        }else if ($ty == 2){
            $fetch = TrInvoicePaymhdr::select('tr_invoice_paymhdr.tenan_id','ms_tenant.tenan_name','ms_unit.unit_code','tr_contract.contr_bast_date',
                    DB::raw("SUM(invpayh_amount) AS total"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) >= -1 AND (current_date::date - invpayh_date::date) <=".$ag30." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) > ".$ag30." AND (current_date::date - invpayh_date::date)<=".$ag60." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) >".$ag60." AND (current_date::date - invpayh_date::date)<=".$ag90." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) > ".$ag90." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS agl180"))
                ->join('tr_invoice_paymdtl','tr_invoice_paymhdr.id',"=",'tr_invoice_paymdtl.invpayh_id')
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice_paymhdr.tenan_id')
                ->join('tr_invoice','tr_invoice.id','=','tr_invoice_paymdtl.inv_id')
                ->leftjoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_invoice.unit_id')
                ->where('tr_contract.contr_status','=','confirmed')
                ->where('invpayh_post','=',TRUE)
                ->where('tr_invoice_paymhdr.invpayh_date','<=',$cutoff)
                ->groupBy('tr_invoice_paymhdr.tenan_id','ms_tenant.tenan_name','ms_unit.unit_code','tr_contract.contr_bast_date')
                ->orderBy('ms_unit.unit_code', 'asc');
        }else{
            if($tyt == 2){
                $fetch = TrContract::select('tr_contract.id AS contr_id','ms_unit.unit_code','ms_tenant.tenan_name')
                    ->join('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id')
                    ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                    ->where('contr_status','=','confirmed')
                    ->where('contr_terminate_date','=',NULL)
                    ->orderBy('unit_code', 'asc');
            }else{
                //sama kyk not paid
               $fetch = TrInvoice::select('tr_invoice.tenan_id','tr_contract.contr_bast_date','ms_unit.unit_code','ms_tenant.tenan_name','tr_contract.contr_bast_date',
                    DB::raw("SUM(inv_outstanding) AS total"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >= -1 AND (current_date::date - inv_date::date) <=".$ag30." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > ".$ag30." AND (current_date::date - inv_date::date)<=".$ag60." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >".$ag60." AND (current_date::date - inv_date::date)<=".$ag90." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > ".$ag90." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS agl180"))
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_invoice.unit_id')
                ->leftjoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->where('tr_contract.contr_status','=','confirmed')
                ->where('tr_invoice.inv_post','=',TRUE)
                ->where('tr_invoice.inv_outstanding','>',0)
                ->where('tr_invoice.inv_date','<=',$cutoff)
                ->groupBy('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name','tr_contract.contr_bast_date')
                ->orderBy('unit_code', 'asc');
            }
        }

        if($unit_id) $fetch = $fetch->where('ms_unit.id','=',$unit_id);
        $fetch = $fetch->get();
        $data['invoices'] = $fetch;

        if($tyt == 2){
            $data['invoices'] = [];
            if($ty == 1){
                foreach ($fetch as $inv) {
                    $tempInv = [
                        'unit_code' => $inv->unit_code,
                        'contr_bast_date' => $inv->contr_bast_date,
                        'tenan_name' => $inv->tenan_name,
                        'total' => $inv->total,
                        'ag30' => $inv->ag30,
                        'ag60' => $inv->ag60,
                        'ag90' => $inv->ag90,
                        'agl180' => $inv->agl180
                    ];
                    $tempInv['details'] = [];
                    $result = TrInvoice::select('tr_invoice.*','tr_contract.contr_bast_date',
                                DB::raw("to_char(inv_date, 'DD/MM/YYYY') AS tanggal"),
                                DB::raw("to_char(inv_duedate, 'DD/MM/YYYY') AS tanggaldue"))
                            ->join('ms_unit','ms_unit.id',"=",'tr_invoice.unit_id')
                            ->leftjoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                            ->where('tr_contract.contr_status','=','confirmed')
                            ->where('ms_unit.unit_code',$inv->unit_code)
                            ->where('inv_outstanding','>',0)
                            ->where('tr_invoice.inv_date','<=',$cutoff)
                            ->where('inv_post',TRUE)
                        ->get();
                    foreach ($result as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->inv_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = abs((int)$dif->format('%R%a'));

                        $tempInv['details'][] = [
                            'inv_number' => $value->inv_number,
                            'contr_bast_date' => $value->contr_bast_date,
                            'tanggal' => $value->tanggal,
                            'tanggaldue' => $value->tanggaldue,
                            'inv_amount' => $value->inv_outstanding,
                            'inv_outstanding' => $value->inv_outstanding,
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? $value->inv_outstanding : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? $value->inv_outstanding : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? $value->inv_outstanding : 0),
                            'ags180' => ($difference > $ag90 ? $value->inv_outstanding : 0)
                            ];
                    }
                    $data['invoices'][] = $tempInv;
                }
            }else if($ty == 2){
                foreach ($fetch as $inv) {
                    $tempInv = [
                        'unit_code' => $inv->unit_code,
                        'contr_bast_date' => $inv->contr_bast_date,
                        'tenan_name' => $inv->tenan_name,
                        'total' => $inv->total,
                        'ag30' => $inv->ag30,
                        'ag60' => $inv->ag60,
                        'ag90' => $inv->ag90,
                        'agl180' => $inv->agl180
                    ];
                    $tempInv['details'] = [];
                    $result = TrInvoicePaymdtl::select('tr_invoice_paymdtl.invpayd_amount','tr_invoice.inv_number',
                                DB::raw("to_char(tr_invoice_paymhdr.invpayh_date, 'DD/MM/YYYY') AS tanggal"))
                            ->join('tr_invoice_paymhdr','tr_invoice_paymhdr.id',"=",'tr_invoice_paymdtl.invpayh_id')
                            ->join('tr_invoice','tr_invoice_paymdtl.inv_id',"=",'tr_invoice.id')
                            ->leftjoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                            ->where('tr_contract.contr_status','=','confirmed')
                            ->where('tr_invoice.tenan_id',$inv->tenan_id)
                            ->where('tr_invoice_paymhdr.tenan_id',$inv->tenan_id)
                            ->where('invpayh_post',TRUE)
                        ->get();
                    foreach ($result as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->invpayh_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = $dif->d;

                        $tempInv['details'][] = [
                            'inv_number' => $value->inv_number,
                            'contr_bast_date' => $value->contr_bast_date,
                            'tanggal' => $value->tanggal,
                            'inv_amount' => $value->invpayd_amount,
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? $value->invpayd_amount : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? $value->invpayd_amount : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? $value->invpayd_amount : 0),
                            'ags180' => ($difference > $ag90 ? $value->invpayd_amount : 0)
                            ];
                    }
                    $data['invoices'][] = $tempInv;
                }
            }else{
                foreach ($fetch as $inv) {
                    $tempInv = [
                        'unit_code' => $inv->unit_code,
                        'tenan_name' => $inv->tenan_name,
                        'contr_bast_date' => $inv->contr_bast_date
                    ];
                    $tempInv['details'] = [];
                    $result = TrInvoice::select('tr_invoice.*','tr_contract.contr_bast_date',
                                DB::raw("to_char(inv_date, 'DD/MM/YYYY') AS tanggal"))
                            ->leftjoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                            ->where('tr_contract.contr_status','=','confirmed')
                            ->where('tr_invoice.contr_id',$inv->contr_id)
                            ->where('inv_post',TRUE)
                        ->get();
                    foreach ($result as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->inv_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = $dif->d;

                        $tempInv['details'][] = [
                            'inv_number' => $value->inv_number,
                            'contr_bast_date' => $value->contr_bast_date,
                            'tanggal' => $value->tanggal,
                            'inv_amount' => $value->inv_amount,
                            'inv_tp' => 'INVOICE',
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? $value->inv_amount : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? $value->inv_amount : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? $value->inv_amount : 0),
                            'ags180' => ($difference > $ag90 ? $value->inv_amount : 0)
                            ];
                    }
                    $result2 = TrInvoicePaymdtl::select('tr_invoice_paymdtl.invpayd_amount','tr_invoice.inv_number','tr_contract.contr_bast_date',
                                DB::raw("to_char(tr_invoice_paymhdr.invpayh_date, 'DD/MM/YYYY') AS tanggal"))
                            ->join('tr_invoice_paymhdr','tr_invoice_paymhdr.id',"=",'tr_invoice_paymdtl.invpayh_id')
                            ->join('tr_invoice','tr_invoice_paymdtl.inv_id',"=",'tr_invoice.id')
                            ->leftjoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                            ->where('tr_contract.contr_status','=','confirmed')
                            ->where('tr_invoice_paymhdr.tenan_id',$inv->tenan_id)
                            ->where('tr_invoice_paymhdr.invpayh_date','<=',$cutoff)
                            ->where('invpayh_post',TRUE)
                        ->get();
                    foreach ($result2 as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->invpayh_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = $dif->d;

                        $tempInv['details'][] = [
                            'inv_number' => $value->inv_number,
                            'contr_bast_date' => $value->contr_bast_date,
                            'tanggal' => $value->tanggal,
                            'inv_amount' => ($value->invpayd_amount * -1),
                            'inv_tp' => 'PAYMENT',
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? ($value->invpayd_amount * -1) : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? ($value->invpayd_amount * -1) : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? ($value->invpayd_amount * -1) : 0),
                            'ags180' => ($difference > $ag90 ? ($value->invpayd_amount * -1) : 0)
                            ];
                    }
                    $data['invoices'][] = $tempInv;;
                }
            }
        }
        //print_r($data['invoices']);
        //die();
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('AR_Aging_periode.pdf');
        }else if($excel){
            $data['type'] = 'excel';

            $data_ori = $data['invoices'];
            $data = array();
            $total_semua = 0;
            $total_a30 = 0;
            $total_a60 = 0;
            $total_a90 = 0;
            $total_a180 = 0; 
            for($i=0; $i<count($data_ori); $i++){
                $name1 = '1-'.$ag30.' Days';
                $name2 = $ag30.'-'.$ag60.' Days';
                $name3 = $ag60.'-'.$ag90.' Days';
                $name4 = 'OVER '.$ag180.' Days';
                if(count($data_ori[$i]['details']) > 0){
                    $data[]=array(
                        'Unit Code' =>@$data_ori[$i]['unit_code'],
                        'Tgl Serah Terima' =>$data_ori[$i]['contr_bast_date'],
                        'Nama Tenant' =>$data_ori[$i]['tenan_name'],
                        'Tgl Invoice' =>'',
                        'Total' =>'',
                        $name1 =>'',
                        $name2 =>'',
                        $name3 =>'',
                        $name4 =>'',
                        'Summary'=>(float)$data_ori[$i]['total']
                    );
                }else{
                    $data[]=array(
                        'Unit Code' =>@$data_ori[$i]['unit_code'],
                        'Tgl Serah Terima' =>$data_ori[$i]['contr_bast_date'],
                        'Nama Tenant' =>$data_ori[$i]['tenan_name'],
                        'Tgl Invoice' =>'',
                        'Total' =>(float)$data_ori[$i]['total'],
                        $name1 =>(float)$data_ori[$i]['ag30'],
                        $name2 =>(float)$data_ori[$i]['ag60'],
                        $name3 =>(float)$data_ori[$i]['ag90'],
                        $name4 =>(float)$data_ori[$i]['agl180'],
                        'Summary'=>''
                    );
                }
                if(count($data_ori[$i]['details']) > 0){
                    for($k=0; $k<count($data_ori[$i]['details']); $k++){
                        $data[]=array(
                            'Unit Code' =>$data_ori[$i]['details'][$k]['inv_number'],
                            'Tgl Serah Terima' =>$data_ori[$i]['details'][$k]['contr_bast_date'],
                            'Nama Tenant' =>@$data_ori[$i]['unit_code'].' / '.$data_ori[$i]['tenan_name'],
                            'Tgl Invoice' =>$data_ori[$i]['details'][$k]['tanggal'],
                            'Total' =>(float)$data_ori[$i]['details'][$k]['inv_outstanding'],
                            $name1 =>(float)$data_ori[$i]['details'][$k]['ags30'],
                            $name2 =>(float)$data_ori[$i]['details'][$k]['ags60'],
                            $name3 =>(float)$data_ori[$i]['details'][$k]['ags90'],
                            $name4 =>(float)$data_ori[$i]['details'][$k]['ags180'],
                            'Summary'=>''
                        );
                    }
                }
                $total_semua = $total_semua + (float)$data_ori[$i]['total'];
                $total_a30 = $total_a30 + (float)$data_ori[$i]['ag30'];
                $total_a60 = $total_a60 + (float)$data_ori[$i]['ag60'];
                $total_a90 = $total_a90 + (float)$data_ori[$i]['ag90'];
                $total_a180 = $total_a180 + (float)$data_ori[$i]['agl180'];
            }
            $data[]=array(
                    'Unit Code' =>'TOTAL',
                    'Tgl Serah Terima' =>'',
                    'Nama Tenant' =>'',
                    'Tgl Invoice' =>'',
                    'Total' =>(float)$total_semua,
                    $name1 =>(float)$total_a30,
                    $name2 =>(float)$total_a60,
                    $name3 =>(float)$total_a90,
                    $name4 =>(float)$total_a180,
                    'Summary'=>(float)$total_semua
                );

            $border = 'A1:J';
            $tp = 'xls';
            return Excel::create('Aging Total Report', function($excel) use ($data,$border) {
                $excel->sheet('Aging Total Report', function($sheet) use ($data,$border)
                {
                    $total = count($data)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($data);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function cashflow(){
        $data['page'] = 'Cashflow';
        $data['formats'] = MsHeaderFormat::where('type',1)->where('name','LK-Cashflow')->get();
        $data['tahun'] = TrBudgetHdr::all();
        return view('report_budget',$data);
    }

    public function cashflowtpl(Request $request)
    {
        $id = $request->format;
        $tahun = $request->tahun;
        $print = @$request->print;
        $excel = @$request->excel;
        $company = MsCompany::first();
        $detail = Cashflow::where('formathd_id',$id)->where('column',1)->orderBy('order','ASC')->get();
        $data = [
                'company' => $company,
                'detail' => $detail,
                'tahun' => $tahun,
                'variables' => [],
                'v_jan' => [],
                'v_feb' => [],
                'v_mar' => [],
                'v_apr' => [],
                'v_may' => [],
                'v_jun' => [],
                'v_jul' => [],
                'v_aug' => [],
                'v_sep' => [],
                'v_okt' => [],
                'v_nov' => [],
                'v_des' => []
            ];
        if($print == 1){ $data['jenis'] = 'print'; }else{ $data['jenis'] = 'none'; }
        $pdf = @$request->pdf;
        if($pdf){
            $data['jenis'] = 'pdf';
            $pdf = PDF::loadView('cashflow_view', $data)->setPaper('a4');
            return $pdf->download('Cashflow.pdf');
        }else if($excel){
            $excel = array();
            $mulai = 0;
            $variables = [];
            $v_jan = [];
            $v_feb = [];
            $v_mar = [];
            $v_apr = [];
            $v_may = [];
            $v_jun = [];
            $v_jul = [];
            $v_aug = [];
            $v_sep = [];
            $v_okt = [];
            $v_nov = [];
            $v_des = [];

            foreach($detail as $dt){
                $desc = html_entity_decode($dt->desc);
                $dt->settahun($tahun);
                if(!empty($dt->header)) $desc = $desc;
                $dt->setVariables(0,$variables);
                $dt->setVariables('jan',$v_jan);
                $dt->setVariables('feb',$v_feb);
                $dt->setVariables('mar',$v_mar);
                $dt->setVariables('apr',$v_apr);
                $dt->setVariables('may',$v_may);
                $dt->setVariables('jun',$v_jun);
                $dt->setVariables('jul',$v_jul);
                $dt->setVariables('aug',$v_aug);
                $dt->setVariables('sep',$v_sep);
                $dt->setVariables('okt',$v_okt);
                $dt->setVariables('nov',$v_nov);
                $dt->setVariables('des',$v_des);
                $jan = $dt->cashflowledgerCalculate('1',$tahun);
                $feb = $dt->cashflowledgerCalculate('2',$tahun);
                $mar = $dt->cashflowledgerCalculate('3',$tahun);
                $apr = $dt->cashflowledgerCalculate('4',$tahun);
                $may = $dt->cashflowledgerCalculate('5',$tahun);
                $jun = $dt->cashflowledgerCalculate('6',$tahun);
                $jul = $dt->cashflowledgerCalculate('7',$tahun);
                $aug = $dt->cashflowledgerCalculate('8',$tahun);
                $sep = $dt->cashflowledgerCalculate('9',$tahun);
                $okt = $dt->cashflowledgerCalculate('10',$tahun);
                $nov = $dt->cashflowledgerCalculate('11',$tahun);
                $des = $dt->cashflowledgerCalculate('12',$tahun);
                $total = $jan+$feb+$mar+$apr+$may+$jun+$jul+$aug+$sep+$okt+$nov+$des;           
                if(!empty($dt->variable)) 
                $v_jan[$dt->variable] = $jan;
                $v_feb[$dt->variable] = $feb;
                $v_mar[$dt->variable] = $mar;
                $v_apr[$dt->variable] = $apr;
                $v_may[$dt->variable] = $may;
                $v_jun[$dt->variable] = $jun;
                $v_jul[$dt->variable] = $jul;
                $v_aug[$dt->variable] = $aug;
                $v_sep[$dt->variable] = $sep;
                $v_okt[$dt->variable] = $okt;
                $v_nov[$dt->variable] = $nov;
                $v_des[$dt->variable] = $des;
                if($dt->hide == 0){
                    $deskripsi_excel = array(
                            'Deskripsi'=>$desc,
                            'Januari'=>(float)$jan,
                            'Februari'=>(float)$feb,
                            'Maret'=>(float)$mar,
                            'April'=>(float)$apr,
                            'May'=>(float)$may,
                            'Juni'=>(float)$jun,
                            'Juli'=>(float)$jul,
                            'Agustus'=>(float)$aug,
                            'September'=>(float)$sep,
                            'Oktober'=>(float)$okt,
                            'November'=>(float)$nov,
                            'Desember'=>(float)$des,
                            'YTD '.$tahun=>(float)$total
                        );
                        $excel[$mulai] = $deskripsi_excel;
                        $mulai++;
                }
            }

            $border = 'A1:N';
            $tp = 'xls';
            $excelData = $excel;
            return Excel::create('Cashflow Summary Report', function($excel) use ($excelData,$border) {
                $excel->sheet('Cashflow Summary Report', function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);
        }

        return view('cashflow_view', $data);
    }

    public function ytdlaporan(){
        $data['page'] = 'YTD';
        $data['formats'] = MsHeaderFormat::where('type',1)->where('name','LK-PL (YTD)')->get();
        $data['tahun'] = TrBudgetHdr::all();
        return view('report_budget',$data);
    }

    public function ytdlaporantpl(Request $request)
    {
        $id = $request->format;
        $tahun = $request->tahun;
        $print = @$request->print;
        $excel = @$request->excel;
        $company = MsCompany::first();
        $detail = Cashflow::where('formathd_id',$id)->where('column',1)->orderBy('order','ASC')->get();
        $data = [
                'company' => $company,
                'detail' => $detail,
                'tahun' => $tahun,
                'variables' => [],
                'v_jan' => [],
                'v_feb' => [],
                'v_mar' => [],
                'v_apr' => [],
                'v_may' => [],
                'v_jun' => [],
                'v_jul' => [],
                'v_aug' => [],
                'v_sep' => [],
                'v_okt' => [],
                'v_nov' => [],
                'v_des' => []
            ];
        if($print == 1){ $data['jenis'] = 'print'; }else{ $data['jenis'] = 'none'; }
        $pdf = @$request->pdf;
        if($pdf){
            $data['jenis'] = 'pdf';
            $pdf = PDF::loadView('ytdledger_view', $data)->setPaper('a4');
            return $pdf->download('YTD.pdf');
        }else if($excel){
            $excel = array();
            $mulai = 0;
            $variables = [];
            $v_jan = [];
            $v_feb = [];
            $v_mar = [];
            $v_apr = [];
            $v_may = [];
            $v_jun = [];
            $v_jul = [];
            $v_aug = [];
            $v_sep = [];
            $v_okt = [];
            $v_nov = [];
            $v_des = [];

            foreach($detail as $dt){
                $desc = html_entity_decode($dt->desc);
                $dt->settahun($tahun);
                if(!empty($dt->header)) $desc = $desc;
                $dt->setVariables(0,$variables);
                $dt->setVariables('jan',$v_jan);
                $dt->setVariables('feb',$v_feb);
                $dt->setVariables('mar',$v_mar);
                $dt->setVariables('apr',$v_apr);
                $dt->setVariables('may',$v_may);
                $dt->setVariables('jun',$v_jun);
                $dt->setVariables('jul',$v_jul);
                $dt->setVariables('aug',$v_aug);
                $dt->setVariables('sep',$v_sep);
                $dt->setVariables('okt',$v_okt);
                $dt->setVariables('nov',$v_nov);
                $dt->setVariables('des',$v_des);
                $jan = $dt->cashflowCalculate('1',$tahun);
                $feb = $dt->cashflowCalculate('2',$tahun);
                $mar = $dt->cashflowCalculate('3',$tahun);
                $apr = $dt->cashflowCalculate('4',$tahun);
                $may = $dt->cashflowCalculate('5',$tahun);
                $jun = $dt->cashflowCalculate('6',$tahun);
                $jul = $dt->cashflowCalculate('7',$tahun);
                $aug = $dt->cashflowCalculate('8',$tahun);
                $sep = $dt->cashflowCalculate('9',$tahun);
                $okt = $dt->cashflowCalculate('10',$tahun);
                $nov = $dt->cashflowCalculate('11',$tahun);
                $des = $dt->cashflowCalculate('12',$tahun);
                $total = $jan+$feb+$mar+$apr+$may+$jun+$jul+$aug+$sep+$okt+$nov+$des;           
                if(!empty($dt->variable)) 
                $v_jan[$dt->variable] = $jan;
                $v_feb[$dt->variable] = $feb;
                $v_mar[$dt->variable] = $mar;
                $v_apr[$dt->variable] = $apr;
                $v_may[$dt->variable] = $may;
                $v_jun[$dt->variable] = $jun;
                $v_jul[$dt->variable] = $jul;
                $v_aug[$dt->variable] = $aug;
                $v_sep[$dt->variable] = $sep;
                $v_okt[$dt->variable] = $okt;
                $v_nov[$dt->variable] = $nov;
                $v_des[$dt->variable] = $des;
                if($dt->hide == 0){
                    $deskripsi_excel = array(
                            'Deskripsi'=>$desc,
                            'Januari'=>(float)$jan,
                            'Februari'=>(float)$feb,
                            'Maret'=>(float)$mar,
                            'April'=>(float)$apr,
                            'May'=>(float)$may,
                            'Juni'=>(float)$jun,
                            'Juli'=>(float)$jul,
                            'Agustus'=>(float)$aug,
                            'September'=>(float)$sep,
                            'Oktober'=>(float)$okt,
                            'November'=>(float)$nov,
                            'Desember'=>(float)$des,
                            'YTD '.$tahun=>(float)$total
                        );
                        $excel[$mulai] = $deskripsi_excel;
                        $mulai++;
                }
            }

            $border = 'A1:N';
            $tp = 'xls';
            $excelData = $excel;
            return Excel::create('YTD Summary Report', function($excel) use ($excelData,$border) {
                $excel->sheet('YTD Summary Report', function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);
        }

        return view('ytdledger_view', $data);
    }

    public function lebihbayar(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $bulan = date('M');
        $tahun = date('Y');

        $data['tahun'] = 'Periode : '.date('F',strtotime($tahun.'-'.$bulan.'-01')).' '.date('Y',strtotime($tahun.'-'.$bulan.'-01'))."<br>";
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Status Lebih Bayar";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_lebih_bayar';
        $data['type'] = 'none';

        $fetch = ExcessPayment::select('unit_code','total_amount')
            ->join('ms_unit','ms_unit.id',"=",'excess_payment.unit_id')
            ->orderBy('total_amount','DESC');
        
        $data['invoices'] = $fetch->get();

        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Status_Lebih_Bayar.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $excelData = array();
            foreach($data['invoices'] as $inv){
                $excelData[]=array(
                        'No.Unit'=>$inv->unit_code,
                        'Amount'=>(float)$inv->total_amount
                    );
                
            }
            $border = 'A1:B';
            $tp = 'xls';
            return Excel::create('Lebih Bayar Report', function($excel) use ($excelData,$border) {
                $excel->sheet('Lebih Bayar Report', function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function lebihpembayaran(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $sup_id = @$request->unit4;
        $from = @$request->from;
        $to = @$request->to;

        $data['tahun'] = 'Periode Sampai : '.$from.' s/d '.$to;
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Lebih Pembayaran Unit";
        $data['logo'] = MsCompany::first()->comp_image;

        $data['template'] = 'report_lebihpembayaran';
        $data['title_r'] = 'Lebih Pembayaran History';

        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }

        $bayar = LogExcessPayment::select('tr_invoice_paymhdr.invpayh_date','ms_unit.unit_code','ms_tenant.tenan_name','excess_amount','modulname')
            ->leftjoin('tr_invoice_paymhdr','tr_invoice_paymhdr.id',"=",'log_excess_payment.invpayh_id')
            ->leftjoin('ms_unit','ms_unit.id',"=",'log_excess_payment.unit_id')
            ->leftjoin('ms_tenant','ms_tenant.id',"=",'tr_invoice_paymhdr.tenan_id')
            ->where('tr_invoice_paymhdr.invpayh_date','>=',$from)
            ->where('tr_invoice_paymhdr.invpayh_date','<=',$to)
            ->where('log_excess_payment.modulname',NULL)
            ->orderBy('invpayh_date', 'asc');
            if($sup_id) $bayar = $bayar->where('ms_unit.id','=',$sup_id);
        $bayar = $bayar->get();

        $bayar2 = LogExcessPayment::select('tr_manualinv_hdr.manual_date','ms_unit.unit_code','ms_tenant.tenan_name','excess_amount','modulname')
            ->leftjoin('tr_manualinv_hdr','tr_manualinv_hdr.id',"=",'log_excess_payment.invpayh_id')
            ->leftjoin('ms_unit','ms_unit.id',"=",'log_excess_payment.unit_id')
            ->leftjoin('ms_tenant','ms_tenant.id',"=",'tr_manualinv_hdr.tenan_id')
            ->where('tr_manualinv_hdr.manual_date','>=',$from)
            ->where('tr_manualinv_hdr.manual_date','<=',$to)
            ->where('log_excess_payment.modulname','ManualInv')
            ->orderBy('manual_date', 'asc');
            if($sup_id) $bayar2 = $bayar2->where('ms_unit.id','=',$sup_id);
        $bayar2 = $bayar2->get();
        $hasil_nl = array();
        if(count($bayar) > 0){
            foreach ($bayar as $ap) {
                $hasil_nl[] = array(
                                'invpayh_date'=>$ap->invpayh_date,
                                'unit_code'=>$ap->unit_code,
                                'ms_tenant'=>$ap->tenan_name,
                                'excess_amount'=>$ap->excess_amount,
                                'modulname'=>$ap->modulname
                            );
            }
        }

        if(count($bayar2) > 0){
            foreach ($bayar2 as $ap2) {
                $hasil_nl[] = array(
                                'invpayh_date'=>$ap2->manual_date,
                                'unit_code'=>$ap2->unit_code,
                                'ms_tenant'=>$ap2->tenan_name,
                                'excess_amount'=>$ap2->excess_amount,
                                'modulname'=>$ap2->modulname
                            );
            }
        }

        usort($hasil_nl, function($a, $b) {
            return $a['invpayh_date'] <=> $b['invpayh_date'];
        });
        
        $data['invoices'] = $hasil_nl;

        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'portrait');
            return $pdf->download('LebihPembayaran_Summary.pdf');

        }else if($excel){
            $data['type'] = 'excel';
            $excelData = array();
            foreach($data['invoices'] as $inv){
                $excelData[]=array(
                        'Tanggal'=>$inv['invpayh_date'],
                        'No.Unit'=>$inv['unit_code'],
                        'Tenant'=>$inv['ms_tenant'],
                        'Amount'=>(float)$inv['excess_amount']
                    );
                
            }
            $border = 'A1:D';
            $tp = 'xls';
            return Excel::create('Lebih Bayar Report', function($excel) use ($excelData,$border) {
                $excel->sheet('Lebih Bayar Report', function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function piutangpenghuni(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $sup_id = @$request->unit4;
        $from = @$request->from;
        $to = @$request->to;

        $data['tahun'] = 'Periode Sampai : '.$from.' s/d '.$to;
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Pemotongan Piutang Penghuni";
        $data['logo'] = MsCompany::first()->comp_image;

        $data['template'] = 'report_piutangpenghuni';
        $data['title_r'] = 'Pemotongan Piutang Penghuni';

        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }

        $bayar = LogPaymentUsed::select('tr_invoice.inv_date','tr_invoice.inv_number','ms_unit.unit_code','ms_tenant.tenan_name','used_amount')
            ->leftjoin('tr_invoice','tr_invoice.id',"=",'log_payment_used.inv_id')
            ->leftjoin('ms_unit','ms_unit.id',"=",'log_payment_used.unit_id')
            ->leftjoin('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
            ->where('tr_invoice.inv_date','>=',$from)
            ->where('tr_invoice.inv_date','<=',$to)
            ->orderBy('inv_date', 'asc');
            if($sup_id) $bayar = $bayar->where('ms_unit.id','=',$sup_id);
        $bayar = $bayar->get();

        $hasil_nl = array();
        if(count($bayar) > 0){
            foreach ($bayar as $ap) {
                $hasil_nl[] = array(
                                'invpayh_date'=>$ap->inv_date,
                                'inv_number'=>$ap->inv_number,
                                'unit_code'=>$ap->unit_code,
                                'ms_tenant'=>$ap->tenan_name,
                                'excess_amount'=>$ap->used_amount
                            );
            }
        }
        
        $data['invoices'] = $hasil_nl;

        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'portrait');
            return $pdf->download('PiutangPenghuni_Summary.pdf');

        }else if($excel){
            $data['type'] = 'excel';
            $excelData = array();
            foreach($data['invoices'] as $inv){
                $excelData[]=array(
                        'Tanggal'=>$inv['invpayh_date'],
                        'No.Unit'=>$inv['unit_code'],
                        'Tenant'=>$inv['ms_tenant'],
                        'No Invoice'=>$inv['inv_number'],
                        'Amount'=>(float)$inv['excess_amount']
                    );
                
            }
            $border = 'A1:D';
            $tp = 'xls';
            return Excel::create('Lebih Bayar Report', function($excel) use ($excelData,$border) {
                $excel->sheet('Lebih Bayar Report', function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'thin');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);

        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function wablast(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;
        $print = @$request->print;
        $bulan = @$request->bulan;
        $tahun = @$request->tahun;
        $tglduedate = @MsConfig::where('name','duedate_interval')->first()->value;

        $data['tahun'] = 'Periode : '.date('F',strtotime($tahun.'-'.$bulan.'-01')).' '.date('Y',strtotime($tahun.'-'.$bulan.'-01'))."<br>";
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "WA BLAST TEMPLATE";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'wa_blast';
        $data['type'] = 'none';

        $fetch = TrInvoice::select('ms_unit.unit_code','ms_tenant.tenan_name','ms_tenant.tenan_phone','ms_tenant.tenan_email','ms_unit.va_utilities',
                    DB::raw("LEFT(ms_unit.unit_code,2) AS tower"),
                    DB::raw("LEFT(ms_unit.unit_code,4) AS lantai"),
                    DB::raw("RIGHT(ms_unit.unit_code,2) AS nomor"),
                    DB::raw("SUM(inv_outstanding) AS total"),
                    DB::raw("SUM((CASE WHEN invtp_id = 2 THEN inv_outstanding ELSE 0 END)) AS maintenance"),
                    DB::raw("SUM((CASE WHEN invtp_id = 1 THEN inv_outstanding ELSE 0 END)) AS utilities")
                )
                ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                ->join('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id')
                ->where('tr_contract.contr_status','confirmed')
                ->where('inv_iscancel','f')
                ->where('inv_post','t')
                ->where('ms_tenant.tenan_phone','<>','-')
                ->groupBy('ms_unit.unit_code','ms_tenant.tenan_name','ms_tenant.tenan_phone','ms_tenant.tenan_email','ms_unit.va_utilities')
                ->orderBy('total','ASC');
        $data['invoices'] = $fetch->get();

        $fetchdenda = ReminderH::select('ms_unit.unit_code','denda_total')
                    ->join('ms_unit','ms_unit.id',"=",'reminder_header.unit_id')
                    ->where('active_tagih','1')
                    ->where('posting','1');
        $data['denda'] = $fetchdenda->get();
        $totaldenda = count($data['denda']);
  
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('WA_Summary.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $excelData = array();
            foreach($data['invoices'] as $inv){
                if($inv->tenan_phone != '-'){
                    switch ($inv->tower) {
                        case '01':
                            $twr = 'Twr.Kalyana';
                            break;
                        case '02':
                            $twr = 'Twr.Kirana';
                            break;
                        default:
                            $twr = 'Twr.Kalyana';
                            break;
                    }
                    $ltn = substr($inv->lantai, 2 , 2);
                    $text = $twr.' LT.'.$ltn.' NO '.(int)$inv->nomor;
                    $amount_denda = 0;
                    if($totaldenda > 0){
                        foreach($data['denda'] as $dnd){
                            if($inv->unit_code == $dnd->unit_code){
                                $amount_denda = $dnd->denda_total;
                                break;
                            }
                        }
                    }
                    $excelData[]=array(
                        'No Unit'=>$inv->unit_code,
                        'Tenan'=>$inv->tenan_name,
                        'No'=>$inv->tenan_phone,
                        'Maintenance'=>(float)$inv->maintenance,
                        'Utilities'=>(float)$inv->utilities,
                        'Total'=>(float)$inv->total,
                        'Denda'=>(float)$amount_denda,
                        'Jatuh Tempo'=>date('Y-m-d',strtotime($tahun.'-'.$bulan.'-'.$tglduedate)),
                        'VA'=>$inv->va_utilities
                    );
                }
            }
            $border = 'A1:A';
            $tp = 'xls';
            return Excel::create('WA Summary Report', function($excel) use ($excelData,$border) {
                $excel->sheet('WA Summary Report', function($sheet) use ($excelData,$border)
                {
                    $total = count($excelData)+1;
                    $sheet->setBorder($border.$total, 'none');
                    $sheet->fromArray($excelData);
                });
            })->download($tp);
        }else{
            return view('layouts.report_template2', $data);
        }
    }
>>>>>>> Stashed changes
}
