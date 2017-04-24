<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrInvoice;
use App\Models\TrInvoiceDetail;
use App\Models\TrInvoicePaymhdr;
use App\Models\MsCompany;
use App\Models\TrContract;
use App\Models\TrMeter;
use App\Models\MsUnit;
use App\Models\MsTenant;
use PDF;
use DB;
use Excel;

class ReportController extends Controller
{
	public function arview(){
		return view('report_ar');
	}

    public function arbyInvoice(Request $request){
    	$from = @$request->from;
    	$to = @$request->to;
    	$pdf = @$request->pdf;
        $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to));
        $data['name'] = MsCompany::first()->comp_name;
    	$data['title'] = "AR Invoices Report";
    	$data['logo'] = MsCompany::first()->comp_image;
    	$data['template'] = 'report_ar_invoice';
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
                	'invdt_amount' => "Rp. ".number_format($value->invdt_amount),
                	'meter_start' => (int)$value->meter_start,
                	'meter_end' => (int)$value->meter_end,
                	'meter_used' => !empty($value->meter_used) ? (int)$value->meter_used." ".$value->costd_unit : (int)$value->meter_used
                	];
            }
            $data['invoices'][] = $tempInv;
        }
    	if($pdf){
    		$pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
        	return $pdf->download('AR_Invoice_'.$from.'_to_'.$to.'.pdf');
    	}else{
    		return view('layouts.report_template2', $data);
    	}
    }

    public function arbyInvoiceCancel(Request $request){
    	$from = @$request->from;
    	$to = @$request->to;
    	$pdf = @$request->pdf;
        $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to));
        $data['name'] = MsCompany::first()->comp_name;
    	$data['title'] = "AR Cancelled Invoices";
    	$data['logo'] = MsCompany::first()->comp_image;
    	$data['template'] = 'report_ar_invoice';
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
    		$pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
        	return $pdf->download('AR_Invoice_Cancel_'.$from.'_to_'.$to.'.pdf');
    	}else{
    		return view('layouts.report_template2', $data);
    	}
    }

    public function arAging(Request $request){
        $ty = @$request->ty;
    	$ag30 = @$request->ag30;
    	$ag60 = @$request->ag60;
        $ag90 = @$request->ag90;
        $ag180 = @$request->ag180;
    	$pdf = @$request->pdf;

        $data['tahun'] = 'Periode Sampai : '.date('M Y');
        $data['name'] = MsCompany::first()->comp_name;
    	$data['title'] = "Aging Invoices";
    	$data['logo'] = MsCompany::first()->comp_image;
    	$data['template'] = 'report_ar_aging';
        $data['ty'] = $ty;
        $data['label'] = explode('~', '1 - '.$ag30.'~'.$ag30.' - '.$ag60.'~'.$ag60.' - '.$ag90.'~'.'> '.$ag180);
        if($ty == 1){
            $fetch = TrInvoice::select('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name',
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
                ->groupBy('tr_invoice.tenan_id','ms_unit.unit_code','ms_tenant.tenan_name');
        }else if ($ty == 2){
            $fetch = TrInvoicePaymhdr::select('ms_tenant.id','ms_unit.unit_code','ms_tenant.tenan_name',
                    DB::raw("SUM(invpayh_amount) AS total"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) >= -1 AND (current_date::date - invpayh_date::date) <=".$ag30." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS ag30"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) > ".$ag30." AND (current_date::date - invpayh_date::date)<=".$ag60." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS ag60"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) >".$ag60." AND (current_date::date - invpayh_date::date)<=".$ag90." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS ag90"),
                    DB::raw("SUM((CASE WHEN (current_date::date - invpayh_date::date) > ".$ag180." THEN tr_invoice_paymhdr.invpayh_amount ELSE 0 END)) AS agl180"))
                ->join('tr_contract','tr_contract.id',"=",'tr_invoice_paymhdr.contr_id')
                ->join('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                ->groupBy('ms_tenant.id','ms_unit.unit_code','ms_tenant.tenan_name');
        }
        $fetch = $fetch->get();
    	$data['invoices'] = $fetch;
    	if($pdf){
    		$pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
        	return $pdf->download('AR_Aging_periode_'.date('M Y').'.pdf');
    	}else{
    		return view('layouts.report_template2', $data);
    	}
    }

    public function outContr(Request $request){
        $from = @$request->from;
        $to = @$request->to;
        $pdf = @$request->pdf;
         $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to));
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Outstanding By Contract";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_out_contr';
        $fetch = TrInvoice::select('tr_contract.contr_code','ms_unit.unit_name','ms_tenant.tenan_name',
                    DB::raw("SUM(inv_outstanding) AS outstanding"))
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                ->where('tr_invoice.inv_outstanding','>',0)
                ->groupBy('ms_unit.unit_name','ms_tenant.tenan_name','tr_contract.contr_code');
        if($from) $fetch = $fetch->where('inv_date','>=',$from);
        if($to) $fetch = $fetch->where('inv_date','<=',$to);
        $fetch = $fetch->get();
        $data['invoices'] = $fetch;
        if($pdf){
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Outstanding_By_Contract_'.$from.'_to_'.$to.'.pdf');
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function outInv(Request $request){
        $from = @$request->from;
        $to = @$request->to;
        $pdf = @$request->pdf;
        $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to));
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Outstanding By Invoices";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_out_inv';
        $fetch = TrInvoice::select('tr_invoice.inv_number','tr_invoice.inv_date','tr_invoice.inv_duedate','ms_unit.unit_name','ms_tenant.tenan_name','tr_invoice.inv_outstanding')
                ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                ->join('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
                ->where('tr_invoice.inv_outstanding','>',0)
                ->groupBy('tr_invoice.inv_number','ms_unit.unit_name','ms_tenant.tenan_name','tr_invoice.inv_date','tr_invoice.inv_duedate','tr_invoice.inv_outstanding')
                ->orderBy('tr_invoice.inv_date','ms_unit.unit_name');
        if($from) $fetch = $fetch->where('inv_date','>=',$from);
        if($to) $fetch = $fetch->where('inv_date','<=',$to);
        $fetch = $fetch->get();
        $data['invoices'] = $fetch;
        if($pdf){
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Outstanding_By_Invoice_'.$from.'_to_'.$to.'.pdf');
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function paymHistory(Request $request){
        $from = @$request->from;
        $to = @$request->to;
        $pdf = @$request->pdf;
        $data['tahun'] = 'Periode : '.date('d M Y',strtotime($from)).' s/d '.date('d M Y',strtotime($to));
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "Payment History";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_payment';
        $fetch = TrInvoicePaymhdr::select('tr_invoice_paymhdr.invpayh_date','ms_payment_type.paymtp_name','ms_cash_bank.cashbk_name','tr_invoice_paymhdr.invpayh_checkno','tr_invoice.inv_number','tr_invoice_paymdtl.invpayd_amount')
                    ->join('tr_invoice_paymdtl','tr_invoice_paymhdr.id','=','tr_invoice_paymdtl.invpayh_id')
                    ->join('tr_invoice','tr_invoice_paymdtl.inv_id','=','tr_invoice.id')
                    ->join('ms_cash_bank','tr_invoice_paymhdr.cashbk_id','=','ms_cash_bank.id')
                    ->join('ms_payment_type','tr_invoice_paymhdr.paymtp_code','=','ms_payment_type.id');
                    // ->where('invpayh_post',1)
        if($from) $fetch = $fetch->where('tr_invoice_paymhdr.invpayh_date','>=',$from);
        if($to) $fetch = $fetch->where('tr_invoice_paymhdr.invpayh_date','<=',$to);
        $fetch = $fetch->get();
        $data['payments'] = $fetch;
        if($pdf){
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Payment_'.$from.'_to_'.$to.'.pdf');
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
                ->groupBy('ms_unit.unit_code');
        $fetch = $fetch->get();
        $data['invoices'] = $fetch;
        if($pdf){
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Report_ReadingMeter_'.$year.'_'.$ctname.'.pdf');
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function ReportUnit(Request $request){
        $pdf = @$request->pdf;

        $data['title'] = "Report Unit";
        $data['tahun'] = '';
        $data['logo'] = MsCompany::first()->comp_image;
        $data['name'] = MsCompany::first()->comp_name;
        $data['template'] = 'report_unit';
        $fetch = MsUnit::select('ms_unit.unit_code','ms_unit.unit_sqrt','ms_unit.virtual_account','ms_floor.floor_name','ms_unit.meter_listrik','ms_unit.meter_air','ms_tenant.tenan_name','ms_tenant.tenan_idno','ms_tenant.tenan_phone','ms_tenant.tenan_fax','ms_tenant.tenan_email','ms_tenant.tenan_npwp','ms_tenant.tenan_address')
                ->join('ms_floor','ms_unit.floor_id',"=",'ms_floor.id')
                ->leftjoin('ms_unit_owner','ms_unit.id',"=",'ms_unit_owner.unit_id')
                ->leftjoin('ms_tenant','ms_tenant.id',"=",'ms_unit_owner.tenan_id');
        $fetch = $fetch->get();
        $data['invoices'] = $fetch;
        if($pdf){
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Report_Unit.pdf');
        }else{
            return view('layouts.report_template2', $data);
        }
    }

    public function ReportTenant(Request $request){
        $pdf = @$request->pdf;
        $excel = @$request->excel;

        $data['title'] = "Report Tenant";
        $data['tahun'] = '';
        $data['logo'] = MsCompany::first()->comp_image;
        $data['name'] = MsCompany::first()->comp_name;
        $data['template'] = 'report_tenant';
        $fetch = MsTenant::select('ms_tenant.*')
                ->orWhereNull('deleted_at');
        $fetch = $fetch->get();
        $data['invoices'] = $fetch;
        if($pdf){
            $pdf = PDF::loadView('layouts.report_template2', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Report_Tenant.pdf');
        }else if($excel){
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
}
