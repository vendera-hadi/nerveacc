<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\TrLedger;
use App\Models\TrInvoicePaymhdr;
use App\Models\TrInvoicePaymdtl;
use App\Models\TrInvpaymJournal;
use App\Models\MsMasterCoa;
use App\Models\MsJournalType;
use Auth;
use DB;

class PaymentController extends Controller
{
    public function posting(Request $request){
    	$id = $request->id;
    	$coayear = date('Y');
        $month = date('m');
        $journal = [];
        $payJournal = [];
    	
    	// get payment header
    	$paymentHd = TrInvoicePaymhdr::with('Cashbank')->find($id);
        if(!isset($paymentHd->Cashbank->coa_code)) return response()->json(['error'=>1, 'message'=> 'Cashbank Name: '.$paymentHd->Cashbank->cashbk_name.' need to be set with COA code']);
        // create journal DEBET utk piutang
        $coaDebet = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$paymentHd->Cashbank->coa_code)->first();
        if(empty($coaDebet)) return response()->json(['error'=>1, 'message'=>'COA Code: '.$paymentHd->Cashbank->coa_code.' is not found on this year list. Please ReInsert this COA Code']);

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
        $nextJournalNumber = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
        $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumber;
        // Cashbank Jadi DEBET di Payment
        $journal[] = [
                        'ledg_id' => "JRNL".str_replace(".", "", str_replace(" ", "",microtime())),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => date('Y-m-d'),
                        'ledg_refno' => $paymentHd->invpayh_checkno,
                        'ledg_debit' => $paymentHd->invpayh_amount,
                        'ledg_credit' => 0,
                        'ledg_description' => $coaDebet->coa_name,
                        'coa_year' => $coaDebet->coa_year,
                        'coa_code' => $coaDebet->coa_code,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id,
                        'dept_id' => 3 //hardcode utk finance
                    ];

        $payJournal[] = [
        		'ipayjour_date' => date('Y-m-d'),
        		'ipayjour_voucher' => $journalNumber,
        		'ipayjour_note' => 'Posting Payment '.$paymentHd->invpayh_checkno,
        		'coa_code' => $coaDebet->coa_code,
        		'ipayjour_debit' => $paymentHd->invpayh_amount,
        		'ipayjour_credit' => 0,
        		'invpayh_id' => $id
        	];
        // End DEBET

        // Create CREDIT
        // Piutang yang dijadiin debet di Invoice, sekarang jadiin kredit
        $paymentDtl = TrInvoicePaymdtl::join('tr_invoice','tr_invoice_paymdtl.inv_id','=','tr_invoice.id')
        								->join('ms_invoice_type','tr_invoice.invtp_id','=','ms_invoice_type.id')
        								->where('invpayh_id',$id)->first();
        if(!isset($paymentDtl->invtp_coa_ar)) return response()->json(['error'=>1, 'message'=> 'Invoice Type Name: '.$paymentDtl->invtp_name.' need to be set with COA code']);
        
       	$coaCredit = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$paymentDtl->invtp_coa_ar)->first();
        if(empty($coaCredit)) return response()->json(['error'=>1, 'message'=>'COA Code: '.$paymentDtl->invtp_coa_ar.' is not found on this year list. Please ReInsert this COA Code']);
        
        $journal[] = [
                        'ledg_id' => "JRNL".str_replace(".", "", str_replace(" ", "",microtime())),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => date('Y-m-d'),
                        'ledg_refno' => $paymentHd->invpayh_checkno,
                        'ledg_debit' => 0,
                        'ledg_credit' => $paymentHd->invpayh_amount,
                        'ledg_description' => $coaCredit->coa_name,
                        'coa_year' => $coaCredit->coa_year,
                        'coa_code' => $coaCredit->coa_code,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id,
                        'dept_id' => 3 //hardcode utk finance
                    ];

        $payJournal[] = [
        		'ipayjour_date' => date('Y-m-d'),
        		'ipayjour_voucher' => $journalNumber,
        		'ipayjour_note' => 'Posting Payment '.$paymentHd->invpayh_checkno,
        		'coa_code' => $coaCredit->coa_code,
        		'ipayjour_debit' => 0,
        		'ipayjour_credit' => $paymentHd->invpayh_amount,
        		'invpayh_id' => $id
        	];
        
        // INSERT DATABASE
        try{
            DB::transaction(function () use($id, $payJournal, $journal){
                // insert journal
                TrLedger::insert($journal);
                // insert invoice payment journal
                TrInvpaymJournal::insert($payJournal);
                // update posting to yes
                TrInvoicePaymhdr::where('id', $id)->update(['invpayh_post'=>1, 'posting_at'=>date('Y-m-d'), 'posting_by'=>Auth::id()]);
            });
        }catch(\Exception $e){
            return response()->json(['error'=>1, 'message'=> 'Error occured when posting invoice payment']);
        }

        return response()->json(['success'=>1, 'message'=>'Invoice Payment posted Successfully']);
    }
}
