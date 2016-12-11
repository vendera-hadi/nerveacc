<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrInvoice;
use App\Models\TrInvoiceDetail;
use App\Models\MsCompany;
use PDF;

class ReportController extends Controller
{
	public function arview(){
		return view('report_ar');
	}

    public function arbyInvoice(Request $request){
    	$from = @$request->from;
    	$to = @$request->to;
    	$pdf = @$request->pdf;
    	$data['title'] = "AR Invoices";
    	$data['logo'] = MsCompany::first()->comp_image;
    	$fetch = TrInvoice::select('tr_invoice.id','tr_invoice.inv_number','tr_invoice.inv_date','tr_invoice.inv_duedate','tr_invoice.inv_amount','tr_invoice.inv_outstanding','tr_invoice.inv_ppn','tr_invoice.inv_ppn_amount','tr_invoice.inv_post','ms_invoice_type.invtp_name','ms_tenant.tenan_name','tr_contract.contr_no', 'ms_unit.unit_name','ms_floor.floor_name')
                    ->join('ms_invoice_type','ms_invoice_type.id',"=",'tr_invoice.invtp_id')
                    ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                    ->join('ms_unit','tr_contract.unit_id',"=",'ms_unit.id')
                    ->join('ms_floor','ms_unit.floor_id',"=",'ms_floor.id')
                    ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                    ->where('inv_iscancel',0);
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
    		$pdf = PDF::loadView('layouts.report_template', $data)->setPaper('a4', 'landscape');
        	return $pdf->download('AR_Invoice_'.$from.'_to_'.$to.'.pdf');
    	}else{
    		return view('layouts.report_template', $data);
    	}
    }

    public function arbyInvoiceCancel(Request $request){
    	$from = @$request->from;
    	$to = @$request->to;
    	$pdf = @$request->pdf;
    	$data['title'] = "AR Cancelled Invoices";
    	$data['logo'] = MsCompany::first()->comp_image;
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
    		$pdf = PDF::loadView('layouts.report_template', $data)->setPaper('a4', 'landscape');
        	return $pdf->download('AR_Invoice_'.$from.'_to_'.$to.'.pdf');
    	}else{
    		return view('layouts.report_template', $data);
    	}
    }
}
