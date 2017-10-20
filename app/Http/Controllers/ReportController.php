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
        $data['tahun'] = '';
        if(!empty($from) && !empty($to)) $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to));
        $data['name'] = MsCompany::first()->comp_name;
    	$data['title'] = "AR Invoices Report";
    	$data['logo'] = MsCompany::first()->comp_image;
    	$data['template'] = 'report_ar_invoice';
        if($print == 1){ $data['type'] = 'print'; }else{ $data['type'] = 'none'; }
    	$fetch = TrInvoice::select('tr_invoice.id','tr_invoice.inv_number','tr_invoice.inv_date','tr_invoice.inv_duedate','tr_invoice.inv_amount','tr_invoice.inv_outstanding','tr_invoice.inv_ppn','tr_invoice.inv_ppn_amount','tr_invoice.inv_post','ms_invoice_type.invtp_name','ms_tenant.tenan_name','tr_contract.contr_no', 'ms_unit.unit_name','ms_floor.floor_name')
                    ->join('ms_invoice_type','ms_invoice_type.id',"=",'tr_invoice.invtp_id')
                    ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                    ->join('ms_unit','tr_contract.unit_id',"=",'ms_unit.id')
                    ->join('ms_floor','ms_unit.floor_id',"=",'ms_floor.id')
                    ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                    ->where('inv_iscancel',0)
                    ->where('tr_invoice.inv_post','=',TRUE);
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
                	'invdt_amount' => "Rp. ".number_format($value->invdt_amount),
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
        	return $pdf->download('AR_Invoice_'.$from.'_to_'.$to.'.pdf');
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
            $fetch = TrInvoice::select('tr_invoice.tenan_id','tr_contract.id as contr_id','ms_unit.unit_code','ms_tenant.tenan_name',
                    DB::raw("SUM(inv_outstanding) AS total"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >= -1 AND (current_date::date - inv_date::date) <=".$ag30." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > ".$ag30." AND (current_date::date - inv_date::date)<=".$ag60." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >".$ag60." AND (current_date::date - inv_date::date)<=".$ag90." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > ".$ag180." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS agl180"))
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                ->where('tr_invoice.inv_post','=',TRUE)
                ->where('tr_invoice.inv_outstanding','>',0)
                ->groupBy('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name','tr_contract.id')
                ->orderBy('unit_code', 'asc');
        }else if ($ty == 2){
            $fetch = TrInvoicePaymhdr::select('ms_tenant.id AS tenan_id','ms_unit.unit_code','ms_tenant.tenan_name','contr_id',
                    DB::raw("SUM(invpayh_amount) AS total"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) >= -1 AND (current_date::date - invpayh_date::date) <=".$ag30." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) > ".$ag30." AND (current_date::date - invpayh_date::date)<=".$ag60." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) >".$ag60." AND (current_date::date - invpayh_date::date)<=".$ag90." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) > ".$ag180." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS agl180"))
                ->join('tr_contract','tr_contract.id',"=",'tr_invoice_paymhdr.contr_id')
                ->join('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                ->where('invpayh_post','=',TRUE)
                ->groupBy('ms_tenant.id','ms_unit.unit_code','ms_tenant.tenan_name','contr_id','ms_tenant.id')
                ->orderBy('unit_code', 'asc');
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
               $fetch = TrInvoice::select('tr_invoice.tenan_id','tr_contract.id as contr_id','ms_unit.unit_code','ms_tenant.tenan_name',
                    DB::raw("SUM(inv_outstanding) AS total"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >= -1 AND (current_date::date - inv_date::date) <=".$ag30." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > ".$ag30." AND (current_date::date - inv_date::date)<=".$ag60." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) >".$ag60." AND (current_date::date - inv_date::date)<=".$ag90." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - inv_date::date) > ".$ag180." THEN tr_invoice.inv_outstanding ELSE 0 END)) AS agl180"))
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                ->where('tr_invoice.inv_post','=',TRUE)
                ->where('tr_invoice.inv_outstanding','>',0)
                ->groupBy('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name','tr_contract.id')
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
                        'tenan_name' => $inv->tenan_name,
                        'total' => $inv->total,
                        'ag30' => $inv->ag30,
                        'ag60' => $inv->ag60,
                        'ag90' => $inv->ag90,
                        'agl180' => $inv->agl180
                    ];
                    $tempInv['details'] = [];
                    $result = TrInvoice::select('tr_invoice.*',
                                DB::raw("to_char(inv_date, 'DD/MM/YYYY') AS tanggal"),
                                DB::raw("to_char(inv_duedate, 'DD/MM/YYYY') AS tanggaldue"))
                            ->where('tr_invoice.contr_id',$inv->contr_id)
                            ->where('inv_outstanding','>',0)
                            ->where('inv_post',TRUE)
                        ->get();
                    foreach ($result as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->inv_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = $dif->d;

                        $tempInv['details'][] = [
                            'inv_number' => $value->inv_number,
                            'tanggal' => $value->tanggal,
                            'tanggaldue' => $value->tanggaldue,
                            'inv_amount' => $value->inv_amount,
                            'inv_outstanding' => $value->inv_outstanding,
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? $value->inv_outstanding : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? $value->inv_outstanding : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? $value->inv_outstanding : 0),
                            'ags180' => ($difference > $ag180 ? $value->inv_outstanding : 0)
                            ];
                    }
                    $data['invoices'][] = $tempInv;
                }
            }else if($ty == 2){
                foreach ($fetch as $inv) {
                    $tempInv = [
                        'unit_code' => $inv->unit_code,
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
                            ->where('tr_invoice.tenan_id',$inv->tenan_id)
                            ->where('tr_invoice_paymhdr.contr_id',$inv->contr_id)
                            ->where('invpayh_post',TRUE)
                        ->get();
                    foreach ($result as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->invpayh_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = $dif->d;

                        $tempInv['details'][] = [
                            'inv_number' => $value->inv_number,
                            'tanggal' => $value->tanggal,
                            'inv_amount' => $value->invpayd_amount,
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? $value->invpayd_amount : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? $value->invpayd_amount : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? $value->invpayd_amount : 0),
                            'ags180' => ($difference > $ag180 ? $value->invpayd_amount : 0)
                            ];
                    }
                    $data['invoices'][] = $tempInv;
                }
            }else{
                foreach ($fetch as $inv) {
                    $tempInv = [
                        'unit_code' => $inv->unit_code,
                        'tenan_name' => $inv->tenan_name
                    ];
                    $tempInv['details'] = [];
                    $result = TrInvoice::select('tr_invoice.*',
                                DB::raw("to_char(inv_date, 'DD/MM/YYYY') AS tanggal"))
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
                            'tanggal' => $value->tanggal,
                            'inv_amount' => $value->inv_amount,
                            'inv_tp' => 'INVOICE',
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? $value->inv_amount : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? $value->inv_amount : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? $value->inv_amount : 0),
                            'ags180' => ($difference > $ag180 ? $value->inv_amount : 0)
                            ];
                    }
                    $result2 = TrInvoicePaymdtl::select('tr_invoice_paymdtl.invpayd_amount','tr_invoice.inv_number',
                                DB::raw("to_char(tr_invoice_paymhdr.invpayh_date, 'DD/MM/YYYY') AS tanggal"))
                            ->join('tr_invoice_paymhdr','tr_invoice_paymhdr.id',"=",'tr_invoice_paymdtl.invpayh_id')
                            ->join('tr_invoice','tr_invoice_paymdtl.inv_id',"=",'tr_invoice.id')
                            ->where('tr_invoice_paymhdr.contr_id',$inv->contr_id)
                            ->where('invpayh_post',TRUE)
                        ->get();
                    foreach ($result2 as $key => $value) {
                        $datetime1 = new DateTime(date('Y-m-d'));
                        $datetime2 = new DateTime($value->invpayh_date);
                        $dif = $datetime1->diff($datetime2);
                        $difference = $dif->d;

                        $tempInv['details'][] = [
                            'inv_number' => $value->inv_number,
                            'tanggal' => $value->tanggal,
                            'inv_amount' => ($value->invpayd_amount * -1),
                            'inv_tp' => 'PAYMENT',
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? ($value->invpayd_amount * -1) : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? ($value->invpayd_amount * -1) : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? ($value->invpayd_amount * -1) : 0),
                            'ags180' => ($difference > $ag180 ? ($value->invpayd_amount * -1) : 0)
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
                    'Unit Code' =>$data_ori[$i]['unit_code'],
                    'Nama Tenant' =>$data_ori[$i]['tenan_name'],
                    'Total' =>number_format($data_ori[$i]['total']),
                    $name1 =>number_format($data_ori[$i]['ag30']),
                    $name2 =>number_format($data_ori[$i]['ag60']),
                    $name3 =>number_format($data_ori[$i]['ag90']),
                    $name4 =>number_format($data_ori[$i]['agl180']));
            }
            $border = 'A1:G';
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
                ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
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
            for($i=0; $i<count($data_ori); $i++){
                $data[$i]=array(
                    'No Invoice' =>$data_ori[$i]['inv_number'],
                    'Tgl Invoice' =>$data_ori[$i]['inv_date'],
                    'Jatuh Tempo' =>$data_ori[$i]['inv_duedate'],
                    'Tenan' =>$data_ori[$i]['tenan_name'],
                    'Unit' =>$data_ori[$i]['unit_name'],
                    'Total Outstanding' =>number_format($data_ori[$i]['inv_outstanding'],2));
            }
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
        $fetch = TrInvoicePaymhdr::select('tr_invoice_paymhdr.no_kwitansi','tr_invoice_paymhdr.invpayh_date','ms_payment_type.paymtp_name','ms_cash_bank.cashbk_name','tr_invoice_paymhdr.invpayh_checkno','tr_invoice.inv_number','tr_invoice_paymdtl.invpayd_amount','ms_tenant.tenan_name','tr_invoice.inv_post','ms_unit.unit_name')
                    ->join('tr_invoice_paymdtl','tr_invoice_paymhdr.id','=','tr_invoice_paymdtl.invpayh_id')
                    ->join('tr_invoice','tr_invoice_paymdtl.inv_id','=','tr_invoice.id')
                    ->join('tr_contract','tr_invoice.contr_id','=','tr_contract.id')
                    ->join('ms_tenant','ms_tenant.id','=','tr_invoice.tenan_id')
                    ->join('ms_unit','ms_unit.id','=','tr_contract.unit_id')
                    ->join('ms_cash_bank','tr_invoice_paymhdr.cashbk_id','=','ms_cash_bank.id')
                    ->join('ms_payment_type','tr_invoice_paymhdr.paymtp_code','=','ms_payment_type.id');

        $fetch2 = TrInvoice::select('tr_invoice.inv_number','tr_invoice.inv_date','tr_invoice.inv_amount','ms_tenant.tenan_name','tr_contract.contr_no','ms_unit.unit_name','ms_floor.floor_name')
                    ->join('ms_invoice_type','ms_invoice_type.id',"=",'tr_invoice.invtp_id')
                    ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                    ->join('ms_unit','tr_contract.unit_id',"=",'ms_unit.id')
                    ->join('ms_floor','ms_unit.floor_id',"=",'ms_floor.id')
                    ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                    ->where('inv_iscancel',0)
                    ->where('tr_invoice.inv_post','=',TRUE);

        if($from) $fetch = $fetch->where('tr_invoice_paymhdr.invpayh_date','>=',$from);
        if($to) $fetch = $fetch->where('tr_invoice_paymhdr.invpayh_date','<=',$to);

        if(!empty($unit_id)){ 
            $fetch = $fetch->where('tr_contract.unit_id',$unit_id);
            $fetch2 = $fetch2->where('tr_contract.unit_id',$unit_id);
            $unit = MsUnit::find($unit_id);
            $data['tahun'] .= "<br>Unit : ".$unit->unit_code."<br>";
        }
        if(!empty($inv_number)) $fetch = $fetch->where(DB::raw("LOWER(inv_number)"),'like','%'.$inv_number.'%');
        if(!empty($post_status)) $fetch = $fetch->where('tr_invoice.inv_post',$post_flag);
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
        
        $fetch2 = $fetch2->get();
        $data['inv'] = $fetch2;

        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Payment_'.$from.'_to_'.$to.'.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $data_ori = $fetch->toArray();
            $data_ori2 = $fetch2->toArray();
            $data = array();
            $total_debet = 0;
            $total_kredit =0;
            for($i=0; $i<count($data_ori); $i++){
                $data[$i]=array(
                    'No Invoice / No Kwitansi' =>$data_ori[$i]['no_kwitansi'],
                    'Tgl Invoice / Tgl Payment' =>$data_ori[$i]['invpayh_date'],
                    'Unit' =>$data_ori[$i]['unit_name'],
                    'Bank' =>$data_ori[$i]['cashbk_name'],
                    'No Giro' =>$data_ori[$i]['invpayh_checkno'],
                    'Debet' =>number_format($data_ori[$i]['invpayd_amount'],2),
                    'Kredit' => ''
                    );
                $total_debet = $total_debet + $data_ori[$i]['invpayd_amount'];       
            }
            for($k=0; $k<count($data_ori2); $k++){
                $data[$i]=array(
                    'No Invoice / No Kwitansi' =>$data_ori2[$k]['inv_number'],
                    'Tgl Invoice / Tgl Payment' =>$data_ori2[$k]['inv_date'],
                    'Unit' =>$data_ori2[$k]['unit_name'],
                    'Bank' =>'',
                    'No Giro' =>'',
                    'Debet' =>'',
                    'Kredit' => number_format($data_ori2[$k]['inv_amount'],2)
                    );
                $total_kredit = $total_kredit + $data_ori2[$k]['inv_amount'];
                $i++;  
            } 
            $data[$i]=array(
                    'No Invoice / No Kwitansi' =>'',
                    'Tgl Invoice / Tgl Payment' =>'',
                    'Unit' =>'',
                    'Bank' =>'',
                    'No Giro' =>'TOTAL',
                    'Debet' => number_format($total_debet,2),
                    'Kredit' => number_format($total_kredit,2)
                    );
            $border = 'A1:G';
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
                    'JANUARI' =>$data_ori[$i]['jan'],
                    'FEBRUARI' =>$data_ori[$i]['feb'],
                    'MARET' =>$data_ori[$i]['mar'],
                    'APRIL' =>$data_ori[$i]['apr'],
                    'MEI' =>$data_ori[$i]['may'],
                    'JUNI' =>$data_ori[$i]['jun'],
                    'JULI' =>$data_ori[$i]['jul'],
                    'AGUSTUS' =>$data_ori[$i]['aug'],
                    'SEPTEMBER' =>$data_ori[$i]['sep'],
                    'OKTOBER' =>$data_ori[$i]['okt'],
                    'NOVEMBER' =>$data_ori[$i]['nov'],
                    'DESEMBER' =>$data_ori[$i]['des'],
                    'TOTAL KONSUMSI' =>$data_ori[$i]['total']
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
        $fetch = MsUnit::select('ms_unit.unit_code','ms_unit.unit_sqrt','ms_unit.virtual_account','ms_floor.floor_name','ms_unit.meter_listrik','ms_unit.meter_air','ms_tenant.tenan_name','ms_tenant.tenan_idno','ms_tenant.tenan_phone','ms_tenant.tenan_fax','ms_tenant.tenan_email','ms_tenant.tenan_npwp','ms_tenant.tenan_address')
                ->join('ms_floor','ms_unit.floor_id',"=",'ms_floor.id')
                ->leftjoin('ms_unit_owner','ms_unit.id',"=",'ms_unit_owner.unit_id')
                ->leftjoin('ms_tenant','ms_tenant.id',"=",'ms_unit_owner.tenan_id')
                ->orderBy('ms_unit.unit_code');
        $fetch = $fetch->get();
        $data['invoices'] = $fetch;
        if($pdf){
            $data['type'] = 'pdf';
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Report_Unit.pdf');
        }else if($excel){
            $data['type'] = 'excel';
            $data = MsUnit::select('ms_unit.unit_code','ms_unit.unit_sqrt','ms_unit.virtual_account','ms_floor.floor_name','ms_unit.meter_listrik','ms_unit.meter_air','ms_tenant.tenan_name','ms_tenant.tenan_idno','ms_tenant.tenan_phone','ms_tenant.tenan_fax','ms_tenant.tenan_email','ms_tenant.tenan_npwp','ms_tenant.tenan_address')
                ->join('ms_floor','ms_unit.floor_id',"=",'ms_floor.id')
                ->leftjoin('ms_unit_owner','ms_unit.id',"=",'ms_unit_owner.unit_id')
                ->leftjoin('ms_tenant','ms_tenant.id',"=",'ms_unit_owner.tenan_id')
                ->orderBy('ms_unit.unit_code')
                ->get()->toArray();
            $border = 'A1:M';
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
            $data = MsTenant::select('tenan_name AS Name','tenan_idno AS NIP','tenan_phone AS Phone','tenan_fax AS Fax','tenan_email AS Email','tenan_address AS Address','tenan_npwp AS NPWP','tenan_taxname AS Taxname','tenan_tax_address AS TaxAddress')
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

        $fetch = TrLedger::join('ms_master_coa','ms_master_coa.coa_code','=','tr_ledger.coa_code')
                            ->join('ms_journal_type','ms_journal_type.id','=','tr_ledger.jour_type_id')
                            ->leftJoin('tr_invoice','tr_invoice.inv_number','=','tr_ledger.ledg_refno')
                            ->leftJoin('ms_tenant','ms_tenant.id','=','tr_invoice.tenan_id')
                            ->select('tr_ledger.coa_code','tr_ledger.closed_at','coa_name','ledg_date','ledg_description','ledg_debit','ledg_credit','jour_type_prefix','ledg_refno','tenan_id','tenan_name')
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
                    $query->where(DB::raw("LOWER(ledg_description)"),'like','%'.$keyword.'%')->orWhere(DB::raw("LOWER(tenan_name)"),'like','%'.$keyword.'%')->orWhere(DB::raw("LOWER(ledg_refno)"),'like','%'.$keyword.'%');
                });
        }
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
                    'Account No' =>$data_ori[$i]['coa_code'],
                    'Account Name' =>$data_ori[$i]['coa_name'],
                    'Date' =>date('d/m/Y',strtotime($data_ori[$i]['ledg_date'])),
                    'No Invoice/Payment' =>$data_ori[$i]['ledg_refno'],
                    'Deskripsi'=>$data_ori[$i]['ledg_description'],
                    'Debet' =>number_format($data_ori[$i]['ledg_debit'],2),
                    'Kredit' =>number_format($data_ori[$i]['ledg_credit'],2),
                    'Type' =>$data_ori[$i]['jour_type_prefix']
                    );
            }
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
        return view('layouts.report_template2', $data);
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
        if(!empty($to)){ $year = date('Y',strtotime($to)); }else{ $year = date('Y'); }

        $coa_code = MsMasterCoa::select('coa_code','coa_name','coa_beginning','coa_type')->where('coa_code','>=',$coa)->where('coa_code','<=',$tocoa)->where('coa_year','=',$year)->orderBy('coa_code','ASC')->get();
        $last_date = date('Y-m-d',(strtotime('-1 day',strtotime($from))));
        $first_date = $year.'-01-01';
        $data['invoices'] = [];
        $array_excel = [];
        foreach ($coa_code as $inv) {
            $mutasi = TrLedger::select(DB::raw("SUM(ledg_debit) AS total_debet"),DB::raw("SUM(ledg_credit) AS total_credit"))->where('coa_code',$inv->coa_code)->where('ledg_date','>=',$first_date)->where('ledg_date','<=',$last_date)->get();
            $mutasi_history = TrLedger::select('ledg_date','ledg_description','ledg_refno','ledg_debit','ledg_credit','dept_name','jour_type_prefix')
                            ->join('ms_department','tr_ledger.dept_id',"=",'ms_department.id')
                            ->join('ms_journal_type','tr_ledger.jour_type_id',"=",'ms_journal_type.id')
                            ->where('coa_code',$inv->coa_code)
                            ->where('ledg_date','>=',$from)
                            ->where('ledg_date','<=',$to)
                            ->orderBy('ledg_date','ASC')->get();
            $tempInv = ['coa'=>trim($inv->coa_code).' - '.$inv->coa_name];
            $array_excel[] = ['Tanggal'=>trim($inv->coa_code),'Uraian'=>$inv->coa_name,'Ref No'=>'','DEBET'=>'','KREDIT'=>'','Saldo Akhir'=>'','Kel Department'=>'','Kel Journal'=>''];
            $tempInv['details'] = [];
            if(trim($inv->coa_type) == 'DEBET'){
                $saldo_awal = $inv->coa_beginning + $mutasi[0]->total_debet - $mutasi[0]->total_credit;
                $tempInv['details'][] = [
                    'ledg_date' => '',
                    'ledg_description' =>'Saldo Awal Per '.date('d M Y',strtotime($to)),
                    'ledg_refno' => '',
                    'ledg_debit' => $saldo_awal,
                    'ledg_credit' => 0,
                    'saldo_akhir' => $saldo_awal,
                    'dept_name' => '',
                    'jour_type_prefix'=> ''
                ];
                $array_excel[] = ['Tanggal'=>'','Uraian'=>'Saldo Awal Per '.date('d M Y',strtotime($to)),'Ref No'=>'','DEBET'=>$saldo_awal,'KREDIT'=>0,'Saldo Akhir'=>$saldo_awal,'Kel Department'=>'','Kel Journal'=>''];
            }else{
                $saldo_awal = $inv->coa_beginning - $mutasi[0]->total_debet + $mutasi[0]->total_credit;
                $tempInv['details'][] = [
                    'ledg_date' => '',
                    'ledg_description' =>'Saldo Awal Per '.date('d M Y',strtotime($to)),
                    'ledg_refno' => '',
                    'ledg_debit' => 0,
                    'ledg_credit' => $saldo_awal,
                    'saldo_akhir' => $saldo_awal,
                    'dept_name' => '',
                    'jour_type_prefix'=> ''
                ];
                $array_excel[] = ['Tanggal'=>'','Uraian'=>'Saldo Awal Per '.date('d M Y',strtotime($to)),'Ref No'=>'','DEBET'=>0,'KREDIT'=>$saldo_awal,'Saldo Akhir'=>$saldo_awal,'Kel Department'=>'','Kel Journal'=>''];
            }
            $tot_mutasi = $saldo_awal;
            foreach ($mutasi_history as $key => $value) {
                if(trim($inv->coa_type) == 'DEBET'){
                    $tot_mutasi =  $tot_mutasi  + (float)$value->ledg_debit - (float)$value->ledg_credit; 
                }else{
                    $tot_mutasi =  $tot_mutasi  + (float)$value->ledg_credit - (float)$value->ledg_debit; 
                }
                $tempInv['details'][] = [
                    'ledg_date' => $value->ledg_date,
                    'ledg_description' => $value->ledg_description,
                    'ledg_refno' => $value->ledg_refno,
                    'ledg_debit' => $value->ledg_debit,
                    'ledg_credit' => $value->ledg_credit,
                    'saldo_akhir' => (float)$tot_mutasi,
                    'dept_name' => $value->dept_name,
                    'jour_type_prefix' => $value->jour_type_prefix,

                    ];
                $array_excel[] = ['Tanggal'=>$value->ledg_date,'Uraian'=>$value->ledg_description,'Ref No'=>$value->ledg_refno,'DEBET'=>$value->ledg_debit,'KREDIT'=>$value->ledg_credit,'Saldo Akhir'=>(float)$tot_mutasi,'Kel Department'=>$value->dept_name,'Kel Journal'=>$value->jour_type_prefix];
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
            for($i=0; $i<count($data_ori); $i++){
                $data[$i]=array(
                    'Account Code' =>$data_ori[$i]['coa_code'],
                    'Account Name' =>$data_ori[$i]['coa_name'],
                    'Saldo Awal' =>$data_ori[$i]['saldo_awal'],
                    'Debet'=>number_format($data_ori[$i]['debet'],2),
                    'Kredit' =>number_format($data_ori[$i]['credit'],2),
                    'Saldo Akhir' =>number_format($data_ori[$i]['saldo_akhir'],2)
                    );
            }
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
        //dd($detail1);
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
        $company = MsCompany::first();
        $detail = MsDetailFormat::where('formathd_id',$id)->where('column',1)->orderBy('order','ASC')->get();
        $data = [
                'company' => $company,
                'datetxt' => date('d F Y',strtotime($from))." s/d ".date('d F Y',strtotime($to)),
                'detail' => $detail,
                'from' => $from,
                'to' => $to,
                'variables' => []
            ];
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
        if($tyt == 1){
            $data['template'] = 'report_ap_aging';
        }else{
            $data['template'] = 'report_ap_aging_detail';
        }
        
        if($ty == 1 && $tyt == 1){
            $data['title_r'] = 'Summary Outstanding Ap';
        }else if($ty == 1 && $tyt == 2){
            $data['title_r'] = 'Detail Outstanding Ap';
        }else if($ty == 2 && $tyt == 1){
            $data['title_r'] = 'Summary Paid Ap';
        }else if($ty == 2 && $tyt == 2){
            $data['title_r'] = 'Detail Paid Ap';
        }else if($ty == 3 && $tyt == 1){
            $data['title_r'] = 'Summary All Ap';
        }else if($ty == 3 && $tyt == 2){
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
            if($tyt == 2){
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

        if($tyt == 2){
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
                                DB::raw("to_char(tr_ap_payment_hdr.payment_date, 'DD/MM/YYYY') AS tanggal"))
                            ->join('tr_ap_payment_hdr','tr_ap_payment_hdr.id',"=",'tr_ap_payment_dtl.appaym_id')
                            ->join('tr_ap_invoice_hdr','tr_ap_payment_dtl.aphdr_id',"=",'tr_invoice_paymhdr.id')
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
                            'inv_amount' => $value->amount,
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
                                DB::raw("to_char(invoice_date, 'DD/MM/YYYY') AS tanggal"))
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
                            'inv_amount' => $value->total,
                            'inv_tp' => 'INVOICE AP',
                            'ags30' => ($difference >= -1 && $difference <= $ag30 ? $value->total : 0),
                            'ags60' => ($difference > $ag30 && $difference <= $ag60 ? $value->total : 0),
                            'ags90' => ($difference > $ag60 && $difference <= $ag90 ? $value->total : 0),
                            'ags180' => ($difference > $ag180 ? $value->total : 0)
                            ];
                    }
                    
                    $result2 = TrApPaymentDetail::select('tr_ap_payment_dtl.amount','tr_ap_invoice_hdr.invoice_no',
                                DB::raw("to_char(tr_ap_payment_hdr.payment_date, 'DD/MM/YYYY') AS tanggal"))
                            ->join('tr_ap_payment_hdr','tr_ap_payment_hdr.id',"=",'tr_ap_payment_dtl.appaym_id')
                            ->join('tr_ap_invoice_hdr','tr_ap_payment_dtl.aphdr_id',"=",'tr_ap_invoice_hdr.id')
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
                            'inv_amount' => ($value->amount * -1),
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
}
