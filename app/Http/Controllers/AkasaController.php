<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Models\MsUnit;
use App\Models\TrInvoice;
use App\Models\TokenInvoice;
use App\Models\MsInvoiceType;
use App\Models\MsConfig;
use App\Models\MsMasterCoa;
use App\Models\MsJournalType;
use App\Models\TrLedger;
use App\Models\TrBank;
use App\Models\TrInvoicePaymhdr;
use App\Models\TrInvoicePaymdtl;
use App\Models\MsCashBank;
use DB, Excel, PDF, Auth;

class AkasaController extends Controller
{
    // middleware in construct
    public function __construct()
    {
        $this->middleware('akasaAuth')->only('outstanding','insertTokenPurchase','payment','getTokenFromMsp','inquiryCheck','trxCheck');
    }

    // checking outstanding of unit
    public function outstanding(Request $request)
    {
        $unit_no = $request->no_unit;
        if(empty($unit_no)) return $this->makeResponse(401, 'no_unit is required');

        // check unit if exists
        $unit = MsUnit::where('unit_code',$unit_no)->orWhere('unit_name',$unit_no)->first();
        if(empty($unit)) return $this->makeResponse(401, 'unit no not found');

        // if exist get invoice outstanding of unit
        $outstanding = TrInvoice::where('inv_post',1)->where('inv_iscancel',0)->whereHas('TrContract',  function($q) use ($unit){
            $q->where('unit_id', $unit->id);
          })->where('inv_duedate', '<', DB::raw('NOW()'))->sum('inv_outstanding');
        return $outstanding > 0 ? 'true' : 'false';
    }

    // insert token purchase
    public function insertTokenPurchase(Request $request)
    {
        $input = $request->only(['inv_date','inv_no','meter_no','cust_name','no_unit','location','tariff_index','daya','total_pay','slab_cost','water_cost','gas_cost','admin_cost','materai_cost','bpju','ppn','token_cost','total_kwh','token']);
        // create new
        try{
          // check invoice
          $check = TokenInvoice::where('inv_no',@$input['inv_no'])->first();
          if($check) return $this->makeResponse(401, 'This invoice is already exists in list');

          if(empty($input['slab_cost'])) $input['slab_cost'] = 0;
          if(empty($input['water_cost'])) $input['water_cost'] = 0;
          if(empty($input['gas_cost'])) $input['gas_cost'] = 0;
          if(empty($input['admin_cost'])) $input['admin_cost'] = 0;
          if(empty($input['materai_cost'])) $input['materai_cost'] = 0;
          if(empty($input['bpju'])) $input['bpju'] = 0;
          if(empty($input['ppn'])) $input['ppn'] = 0;
          if(empty($input['token_cost'])) $input['token_cost'] = 0;
          if(empty($input['total_kwh'])) $input['total_kwh'] = 0;
          $new = TokenInvoice::create($input);
          if($new) return 'true';
          else return 'false';
        }catch(\Exception $e){
          return $this->makeResponse(401, 'error occured when inserting token purchase invoice');
        }
    }

    protected function makeResponse($code, $message, $data = null){
        $result = [
            'resp_code' => $code,
            'resp_status' => ($code == 200) ? 'success' : 'error',
        ];
        $http_code = 200;

        if($code == 401)
            $http_code = $code;

        if(!empty($message)) $result['resp_message'] = $message;
        if(!empty($data)) $result['resp_data'] = $data;
        $result = response()->json($result,$http_code);
        return $result;
    }

    // list of token invoices
    public function invoices()
    {
        $data['akasa_coa_general'] = @MsConfig::where('name','akasa_coa_general')->first()->value;
        $data['akasa_coa_pju_token'] = @MsConfig::where('name','akasa_coa_pju_token')->first()->value;
        $data['akasa_coa_materai'] = @MsConfig::where('name','akasa_coa_materai')->first()->value;
        $data['akasa_coa_ppn'] = @MsConfig::where('name','akasa_coa_ppn')->first()->value;
        $data['akasa_coa_bank'] = @MsConfig::where('name','akasa_coa_bank')->first()->value;
        $data['accounts'] = MsMasterCoa::where('coa_year',date('Y'))->where('coa_isparent',0)->orderBy('coa_type')->get();
        return view('akasa.invoices', $data);
    }

    public function invoicesGet(Request $request)
    {
        try{
            // params
            $page = $request->page;
            $perPage = $request->rows;
            $page-=1;
            $offset = $page * $perPage;

            $datefrom = @$request->datefrom;
            $dateto = @$request->dateto;
            // @ -> isset(var) ? var : null
            $sort = @$request->sort;
            $order = @$request->order;
            $filters = @$request->filterRules;
            if(!empty($filters)) $filters = json_decode($filters);

            // olah data
            $count = TokenInvoice::count();
            $fetch = TokenInvoice::query();
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
                    if($op == 'like') $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                    else $fetch = $fetch->where($filter->field, $op, $filter->value);
                }
            }

            // jika ada date from
            if(!empty($datefrom)) $fetch = $fetch->where('inv_date','>=',$datefrom);
            // jika ada date to
            if(!empty($dateto)) $fetch = $fetch->where('inv_date','<=',$dateto);

            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['inv_date'] = date('d F Y',strtotime($value->inv_date));
                $temp['inv_no'] = $value->inv_no;
                $temp['meter_no'] = $value->meter_no;
                $temp['cust_name'] = $value->cust_name;
                $temp['no_unit'] = $value->no_unit;
                $temp['location'] = $value->location;
                $temp['tariff_index'] = $value->tariff_index;
                $temp['daya'] = $value->daya;
                $temp['total_pay'] = $value->total_pay;
                $temp['slab_cost'] = $value->slab_cost;
                $temp['water_cost'] = $value->water_cost;
                $temp['gas_cost'] = $value->gas_cost;
                $temp['admin_cost'] = $value->admin_cost;
                $temp['materai_cost'] = $value->materai_cost;
                $temp['bpju'] = $value->bpju;
                $temp['ppn'] = $value->ppn;
                $temp['token_cost'] = $value->token_cost;
                $temp['total_kwh'] = $value->total_kwh;
                $temp['token'] = $value->token;
                $temp['checkbox'] = '<input type="checkbox" name="check" data-posting="'.$value->inv_post.'" value="'.$value->id.'">';
                $temp['inv_post'] = !empty($value->inv_post) ? 'yes' : 'no';
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function exportPdf(Request $request){
        $from = $request->from;
        $to = $request->to;

        $tokenInvoices = TokenInvoice::query();
        if(!empty($from))
          $tokenInvoices = $tokenInvoices->where('inv_date', '>=', $from);
        if(!empty($to))
          $tokenInvoices = $tokenInvoices->where('inv_date', '<=', $to);
        $data['data'] = $tokenInvoices->get();
        $data['from'] = $from;
        $data['to'] = $to;

        $data['type'] = 'pdf';
        // return view('akasa.pdf_template', $data);
        $pdf = PDF::loadView('akasa.pdf_template', $data)->setPaper('a4', 'landscape');
        return $pdf->download('Akasa_Transaksi_Token.pdf');
    }

    public function exportExcel(Request $request){
        $from = $request->from;
        $to = $request->to;

        $tokenInvoices = TokenInvoice::query();
        if(!empty($from))
          $tokenInvoices = $tokenInvoices->where('inv_date', '>=', $from);
        if(!empty($to))
          $tokenInvoices = $tokenInvoices->where('inv_date', '<=', $to);
        $data = $tokenInvoices->get();

        // $data = array();
        // foreach($tokenInvoices as $inv){
        //     $data[]= array(
        //         'Tgl Kwintansi' => $inv->inv_date,
        //         'No Kwitansi' => $inv->inv_no,
        //         'No Meter' => $inv->meter_no,
        //         'Nama Customer' => $inv->cust_name,
        //         'No Unit' => $inv->no_unit,
        //         'Lokasi' => $inv->location,
        //         'Tariff Index' => $inv->tariff_index,
        //         'Daya' => $inv->daya,
        //         'Jumlah Bayar' => $inv->total_pay,
        //         'Biaya SLAB' => $inv->slab_cost,
        //         'Tagihan Air' => $inv->water_cost,
        //         'Tagihan Gas' => $inv->gas_cost,
        //         'Administrasi' => $inv->admin_cost,
        //         'Materai' => $inv->materai_cost,
        //         'Bea PJU (2.4%)' => $inv->bpju,
        //         'PPN 10%' => $inv->ppn,
        //         'Rp.Token' => $inv->token_cost,
        //         'Jumlah Kwh' => $inv->total_kwh,
        //         'Token' => $inv->token
        //       );
        // }
        $border = 'A1:S';
        $tp = 'xls';

        return Excel::create('Token Purchase Invoice Report', function($excel) use ($data,$border,$from,$to) {
            $excel->sheet('Token Purchase Invoice Report', function($sheet) use ($data,$border,$from,$to)
            {
                $sheet->loadView('akasa.xls_template')->with('invoices', $data)
                      ->with('from', $from)->with('to', $to)
                      ->setOrientation('landscape');
            })->getDefaultStyle()
              ->getAlignment()
              ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        })->download($tp);

    }

    public function ocbc(Request $request)
    {
        $fetch = TrInvoice::select('ms_tenant.tenan_name','ms_unit.unit_code','ms_unit.va_utilities AS virtual_account','tr_invoice.inv_outstanding',DB::raw("EXTRACT(MONTH FROM inv_date)::INTEGER AS bulan"),DB::raw("EXTRACT(YEAR FROM inv_date)::INTEGER AS tahun"),'inv_amount')
                    ->leftJoin('tr_contract','tr_contract.id',"=",'tr_invoice.contr_id')
                    ->leftJoin('ms_unit','tr_contract.unit_id',"=",'ms_unit.id')
                    ->leftJoin('ms_floor','ms_unit.floor_id',"=",'ms_floor.id')
                    ->join('ms_tenant','ms_tenant.id',"=",'tr_invoice.tenan_id')
                    ->where('tr_invoice.inv_post','=',TRUE)
                    ->where('tr_invoice.inv_outstanding','>',0)
                    ->orderBy('inv_date', 'asc')
                    ->get();
        return $fetch;
    }

    public function inquiry(Request $request)
    {
        try {
            $unit_code = @$request->unit_code;
            $month = @$request->month;
            $year = @$request->year;

            if(empty($unit_code)) return response()->json([
                    "error"=> true,
                    "error_code"=> 205,
                    "error_desc"=> "Unit code is required",
                ],400);
            if(empty($year)) return response()->json([
                    "error"=> true,
                    "error_code"=> 205,
                    "error_desc"=> "Year is required",
                ],400);

            // check unit existance
            $unit = MsUnit::where('unit_code',$unit_code)->orWhere('unit_name',$unit_code)->first();
            if(!$unit) return response()->json([
                    "error"=> true,
                    "error_code"=> 214,
                    "error_desc"=> "Unit tidak ada",
                ],400);

            // fetch invoices
            $invoices = TrInvoice::where('inv_post',1)->where('inv_iscancel',0)->where(DB::raw('extract(year from inv_date)'), $year)->whereHas('TrContract.MsUnit',  function($q) use ($unit_code){
                    $q->where('unit_code', $unit_code);
                })->where('inv_duedate', '<', DB::raw('NOW()'));
            if($month) $invoices->where(DB::raw('extract(month from inv_date)'), $month);
            $invoices = $invoices->get();
            if(empty(count($invoices))) return response()->json([
                    "error"=> true,
                    "error_code"=> 289,
                    "error_desc"=> "Tagihan belum ada",
                ],400);

            $result = [
                        "error"=> false,
                        "error_code"=> 200,
                        "error_desc"=> "Success",
                    ];
            $result['unit_code'] = $unit_code;
            $result['year'] = $year;
            if(!empty($month)) $result['month'] = $month;
            $result['tenant_name'] = @$invoices->first()->MsTenant->tenan_name;

            $result['invoices'] = [];
            $invoice_types = MsInvoiceType::all();
            $total = 0;
            foreach ($invoice_types as $key => $val) {
                $temp = TrInvoice::where('inv_post',1)->where('inv_iscancel',0)->where('invtp_id',$val->id)->where('inv_duedate', '<', DB::raw('NOW()'))->whereHas('TrContract.MsUnit',  function($q) use ($unit_code){
                        $q->where('unit_code', $unit_code);
                    })->where(DB::raw('extract(year from inv_date)'), $year);
                if($month) $temp->where(DB::raw('extract(month from inv_date)'), $month);
                $list = $temp->pluck('inv_amount','inv_number');
                $outstanding = $temp->sum('inv_outstanding');
                $sum = $temp->sum('inv_amount');
                $sum = (float) round($sum,2);

                $va = '';
                if($val->id == 1) $va = @$invoices->first()->TrContract->MsUnit->va_utilities;
                else if($val->id == 2) $va = @$invoices->first()->TrContract->MsUnit->va_maintenance;
                $result['invoices'][] = [
                        'type' => $val->invtp_name,
                        'virtual_account' => $va,
                        'total_invoice' => number_format($sum,2,'.', ''),
                        'total' => number_format($outstanding,2,'.', ''),
                        'invoice_no' => $list
                    ];
                $total += $sum;
            }

            // cek lunas semua atau ga
            if($total <= 0) return response()->json([
                    "error"=> true,
                    "error_code"=> 288,
                    "error_desc"=> "Tagihan lunas",
                ],400);

            $result['total_inv_amount'] = $total;
            $result['total_inv_amount'] = number_format($total,2,'.','');

            return response()->json($result);
        }catch(\Exception $e){
            return $this->makeResponse(400, $e->getMessage());
        }
    }

    public function posting(Request $request)
    {
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

        $coayear = date('Y');
        $month = date('m');
        $journal = [];

        // cari last prefix, order by journal type
        $jourType = MsJournalType::where('jour_type_prefix','JU')->first();
        if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "JU" first before posting an invoice']);
        $lastJournal = TrLedger::where('jour_type_id',$jourType->id)->latest()->first();
        if($lastJournal){
            $lastJournalNumber = explode(" ", $lastJournal->ledg_number);
            $lastJournalNumber = (int) end($lastJournalNumber);
            $nextJournalNumber = $lastJournalNumber + 1;
        }else{
            $nextJournalNumber = 1;
        }

        // coa debet
        $coabank = @MsConfig::where('name','akasa_coa_bank')->first()->value;
        $coaDebet = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$coabank)->first();
        if(!$coaDebet) return response()->json(['error'=>1, 'message'=>'COA for bank is not found. Please set the config first']);

        $successPosting = 0;
        $successIds = [];
        foreach ($ids as $id) {
            $invoice = TokenInvoice::find($id);
            // DEBET
            $nextJournalNumber = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
            $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumber;
            $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
            $journal[] = [
                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => date('Y-m-d'),
                        'ledg_refno' => $invoice->inv_no,
                        'ledg_debit' => $invoice->total_pay,
                        'ledg_credit' => 0,
                        'ledg_description' => 'Penjualan Token Listrik',
                        'coa_year' => $coaDebet->coa_year,
                        'coa_code' => $coaDebet->coa_code,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id,
                        'dept_id' => 3
                    ];
            // KREDIT
            // general
            $total_general = $invoice->slab_cost + $invoice->water_cost + $invoice->gas_cost + $invoice->admin_cost;
            if($total_general > 0){
                $nextJournalNumber++;
                $nextJournalNumber = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumber;
                $coa_general = @MsConfig::where('name','akasa_coa_general')->first()->value;
                $coaCredit = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$coa_general)->first();
                if(!$coaCredit) return response()->json(['error'=>1, 'message'=>'COA for Admin, SLAB, Water & Gas is not found. Please set the config first']);
                $journal[] = [
                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => date('Y-m-d'),
                        'ledg_refno' => $invoice->inv_no,
                        'ledg_debit' => 0,
                        'ledg_credit' => $total_general,
                        'ledg_description' => 'SLAB, Water, Gas, Admin Cost',
                        'coa_year' => $coaCredit->coa_year,
                        'coa_code' => $coaCredit->coa_code,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id,
                        'dept_id' => 3
                    ];
            }
            // pju & rptoken
            $total_pju_token = $invoice->bpju + $invoice->token_cost;
            if($total_pju_token > 0){
                $nextJournalNumber++;
                $nextJournalNumber = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumber;
                $coa_pju = @MsConfig::where('name','akasa_coa_pju_token')->first()->value;
                $coaCredit = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$coa_pju)->first();
                if(!$coaCredit) return response()->json(['error'=>1, 'message'=>'COA for BPJU & token is not found. Please set the config first']);
                $journal[] = [
                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => date('Y-m-d'),
                        'ledg_refno' => $invoice->inv_no,
                        'ledg_debit' => 0,
                        'ledg_credit' => $total_pju_token,
                        'ledg_description' => 'BPJU & Token Cost',
                        'coa_year' => $coaCredit->coa_year,
                        'coa_code' => $coaCredit->coa_code,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id,
                        'dept_id' => 3
                    ];
            }
            // materai
            if(@$invoice->materai_cost > 0){
                $nextJournalNumber++;
                $nextJournalNumber = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumber;
                $coa_materai = @MsConfig::where('name','akasa_coa_materai')->first()->value;
                $coaCredit = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$coa_materai)->first();
                if(!$coaCredit) return response()->json(['error'=>1, 'message'=>'COA for materai is not found. Please set the config first']);
                $journal[] = [
                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => date('Y-m-d'),
                        'ledg_refno' => $invoice->inv_no,
                        'ledg_debit' => 0,
                        'ledg_credit' => $invoice->materai_cost,
                        'ledg_description' => 'Materai',
                        'coa_year' => $coaCredit->coa_year,
                        'coa_code' => $coaCredit->coa_code,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id,
                        'dept_id' => 3
                    ];
            }
            // PPN
            if(@$invoice->ppn > 0){
                $nextJournalNumber++;
                $nextJournalNumber = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumber;
                $coa_ppn = @MsConfig::where('name','akasa_coa_ppn')->first()->value;
                $coaCredit = MsMasterCoa::where('coa_year',$coayear)->where('coa_code',$coa_ppn)->first();
                if(!$coaCredit) return response()->json(['error'=>1, 'message'=>'COA for ppn is not found. Please set the config first']);
                $journal[] = [
                        'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                        'ledge_fisyear' => $coayear,
                        'ledg_number' => $journalNumber,
                        'ledg_date' => date('Y-m-d'),
                        'ledg_refno' => $invoice->inv_no,
                        'ledg_debit' => 0,
                        'ledg_credit' => $invoice->ppn,
                        'ledg_description' => 'PPN',
                        'coa_year' => $coaCredit->coa_year,
                        'coa_code' => $coaCredit->coa_code,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'jour_type_id' => $jourType->id,
                        'dept_id' => 3
                    ];
            }
            $successIds[] = $id;
            $successPosting++;
        }

        // INSERT
        // var_dump($journal);
        try{
            DB::transaction(function () use($successIds, $journal){
                // insert journal
                TrLedger::insert($journal);
                if(count($successIds) > 0){
                    foreach ($successIds as $id) {
                        $invoice = TokenInvoice::find($id);
                        $invoice->update(['inv_post'=>1]);
                    }
                }
            });
        }catch(\Exception $e){
            return response()->json(['error'=>1, 'message'=> 'Error occured when posting invoice']);
        }

        return response()->json(['success'=>1, 'message'=>$successPosting.' Invoice posted Successfully']);

    }

    public function updateConfig(Request $request)
    {
        if(count($request->all()) > 0){
            foreach ($request->all() as $key => $value) {
                MsConfig::where('name',$key)->update(['value' => $value]);
            }
        }
        $request->session()->flash('success', 'Update configuration success');
        return redirect()->back();
    }

    /**
        MODUL PEMBAYARAN
    **/
    public function payment(Request $request)
    {
        try{
            if($request->isJson()){
                $request = $request->getContent();
                $request = json_decode($request, true);
            }else{
                $request = $request->all();
            }
            if(empty($request))
                return response()->json([
                        "error"=> true,
                        "error_code"=> 400,
                        "error_desc"=> "Wrong input format",
                    ],400);
            // check unit_code
            $unit_code = @$request['unit_code'];
            $unit = MsUnit::where('unit_code',$unit_code)->first();
            if(!$unit)
                return response()->json([
                        "error"=> true,
                        "error_code"=> 214,
                        "error_desc"=> "Unit tidak ada",
                    ],400);
            // check bank id
            $bank_id = @$request['bank_id'];
            $bank = MsCashBank::find($bank_id);
            if(!$bank)
                return response()->json([
                        "error"=> true,
                        "error_code"=> 214,
                        "error_desc"=> "Bank tidak ada",
                    ],400);

            $invoices = @$request['payments'];
            if(empty(count($invoices)))
                return response()->json([
                        "error"=> true,
                        "error_code"=> 289,
                        "error_desc"=> "Tidak ada tagihan dalam inputan",
                    ],400);
            // check setiap invoice ap ada outstanding, status posted, dan tdk cancel
            $countExist = $countNotExist = 0;
            foreach ($invoices as $inv) {
                $exist = TrInvoice::where('unit_id', $unit->id)->where('inv_number',$inv['invoice_no'])->where('inv_post', 1)->where('inv_iscancel', 0)->where('inv_outstanding','>',0)->first();
                if(!$exist && !empty(@$inv['amount']) && !empty(@$inv['invoice_no'])){
                    $countNotExist++;
                    continue;
                }
                $amount = $inv['amount'];
                DB::beginTransaction();
                try{
                    $lastPayment = TrInvoicePaymhdr::where(\DB::raw('EXTRACT(YEAR FROM created_at)'),'=',date('Y'))
                                ->where(\DB::raw('EXTRACT(MONTH FROM created_at)'),'=',date('m'))
                                ->orderBy('created_at','desc')->first();
                    $indexNumber = null;
                    if($lastPayment){
                        $index = explode('.',$lastPayment->no_kwitansi);
                        $index = (int) end($index);
                        $index+= 1;
                        $indexNumber = $index;
                        $index = str_pad($index, 3, "0", STR_PAD_LEFT);
                    }else{
                        $index = "001";
                        $indexNumber = 1;
                    }

                    // insert payment
                    $header = new TrInvoicePaymhdr;
                    $prefixKuitansi = @MsConfig::where('name','prefix_kuitansi')->first()->value;
                    $header->no_kwitansi = $prefixKuitansi.'-'.date('Y-m').'.'.$index;
                    $header->invpayh_date = date('Y-m-d');
                    $header->invpayh_checkno = "";
                    $header->invpayh_giro = null ;
                    $header->invpayh_note = "VA Payment ".$exist->inv_number;
                    $header->invpayh_post = false;
                    $header->paymtp_code = 2;
                    $header->cashbk_id = $bank_id;
                    $header->tenan_id = $exist->tenan_id;
                    $header->invpayh_settlamt = 1;
                    $header->invpayh_adjustamt = 1;
                    $header->invpayh_amount = $amount;
                    $header->updated_by = $header->created_by = 1;
                    $header->status_void = false;

                    // payment detail
                    if($header->save()){
                        $payment_id = $header->id;
                        $detail = new TrInvoicePaymdtl;
                        $outstanding = $exist->inv_amount - $amount;
                        if($outstanding <= 0) $outstanding = 0;
                        $detail->invpayd_amount = $amount;
                        $detail->inv_id = $exist->id;
                        $detail->invpayh_id = $payment_id;
                        $detail->last_outstanding = $exist->inv_amount;
                        $detail->save();
                        // jgn lupa update outstanding nya invoice skrg
                        $exist->inv_outstanding = $outstanding;
                        $exist->save();

                        // posting payment, panggil fungsi yg suda ada
                        // $postRequest = new Request;
                        // $postRequest->id = $payment_id;
                        // $posting = app('App\Http\Controllers\PaymentController')->posting($postRequest);
                        // $posting = json_decode($posting->getContent(), true);
                        // if(!empty(@$posting['error'])){
                        //     $header->invpayh_post = true;
                        //     $header->save();
                        // }
                    }
                    $countExist++;
                    // DB::rollback();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                    // dd($e->getMessage());
                    $countNotExist++;
                    continue;
                }
            }
            // echo "exist $countExist dan not $countNotExist";
            return response()->json([
                        "error"=> false,
                        "error_code"=> 200,
                        "error_desc"=> "Success, $countExist invoices set to paid dan $countNotExist was not set to paid",
                    ]);
        } catch(\Exception $e) {
            return response()->json([
                        "error"=> true,
                        "error_code"=> 400,
                        "error_desc"=> $e->getMessage(),
                    ],400);
        }
    }

    public function uploadPayment(Request $request)
    {
        $filepath = $request->file('file')->getPathName();
        $file = file($filepath);
        // read every line of txt
        $confirmed = $not_confirmed = 0;
        foreach($file as $key => $line){
            if($key==0) continue;
            $line = explode('|', $line);
            $created_time = @$line[0];
            $no_unit = @$line[1];
            $inv_no = @$line[3];
            $amount = @$line[4];
            $bank_id = @$line[5];
            $amount = (float) $amount;

            $year = substr($created_time, 0, 4);
            $month = substr($created_time, 4, 2);
            $day = substr($created_time, 6, 2);
            $created_date = $year.'-'.$month.'-'.$day;
            // scan di tr invoice payment detail join header. cocokin tgl payment & inv no
            $paymentHdr = TrInvoicePaymhdr::where('invpayh_date', $created_date)->whereHas('TrInvoicePaymdtl.TrInvoice', function($query) use($inv_no){
                $query->where('inv_number',$inv_no);
            })->first();
            if($paymentHdr){
                DB::beginTransaction();
                try{
                    // jika ada payment, check jml amount sama atau tidak
                    if(@$paymentHdr->invpayh_amount != $amount){
                        // jika tidak sama, balikin amount
                        $paymentDtl = $paymentHdr->TrInvoicePaymdtl->first();
                        $invoice = $paymentDtl->TrInvoice;
                        $invoice->inv_outstanding += $paymentHdr->invpayh_amount;
                        $paymentDtl->last_outstanding = $invoice->inv_outstanding;
                        // kurangin dengan amount asli pembayaran
                        $invoice->inv_outstanding -= $amount;
                        $invoice->save();
                        // ubah payment detail
                        $paymentHdr->invpayh_amount = $amount;
                        $paymentDtl->invpayd_amount = $amount;
                        $paymentDtl->save();
                    }
                    // posting
                    $postRequest = new Request;
                    $postRequest->id = $paymentHdr->id;
                    $posting = app('App\Http\Controllers\PaymentController')->posting($postRequest);
                    $posting = json_decode($posting->getContent(), true);
                    if(!empty(@$posting['error'])){
                        $paymentHdr->invpayh_post = true;
                        $paymentHdr->save();
                    }
                    $confirmed++;
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                    // dd($e->getMessage());
                    $not_confirmed++;
                    continue;
                }

            }else{
                // jika tidak ada dianggap new transaction
                DB::beginTransaction();
                try{
                    $lastPayment = TrInvoicePaymhdr::where(\DB::raw('EXTRACT(YEAR FROM created_at)'),'=',date('Y'))
                                ->where(\DB::raw('EXTRACT(MONTH FROM created_at)'),'=',date('m'))
                                ->orderBy('created_at','desc')->first();
                    $indexNumber = null;
                    if($lastPayment){
                        $index = explode('.',$lastPayment->no_kwitansi);
                        $index = (int) end($index);
                        $index+= 1;
                        $indexNumber = $index;
                        $index = str_pad($index, 3, "0", STR_PAD_LEFT);
                    }else{
                        $index = "001";
                        $indexNumber = 1;
                    }
                    $invoice = TrInvoice::where('inv_number',$inv_no)->first();

                    // insert payment
                    $header = new TrInvoicePaymhdr;
                    $prefixKuitansi = @MsConfig::where('name','prefix_kuitansi')->first()->value;
                    $header->no_kwitansi = $prefixKuitansi.'-'.date('Y-m').'.'.$index;
                    $header->invpayh_date = $created_date;
                    $header->invpayh_checkno = "";
                    $header->invpayh_giro = null ;
                    $header->invpayh_note = "VA Payment ".$inv_no;
                    $header->invpayh_post = false;
                    $header->paymtp_code = 2;
                    $header->cashbk_id = $bank_id;
                    $header->tenan_id = $invoice->tenan_id;
                    $header->invpayh_settlamt = 1;
                    $header->invpayh_adjustamt = 1;
                    $header->invpayh_amount = $amount;
                    $header->updated_by = $header->created_by = 1;
                    $header->status_void = false;

                    // payment detail
                    if($header->save()){
                        $payment_id = $header->id;
                        $detail = new TrInvoicePaymdtl;
                        $outstanding = $invoice->inv_amount - $amount;
                        if($outstanding <= 0) $outstanding = 0;
                        $detail->invpayd_amount = $amount;
                        $detail->inv_id = $invoice->id;
                        $detail->invpayh_id = $payment_id;
                        $detail->last_outstanding = $invoice->inv_amount;
                        $detail->save();
                        // jgn lupa update outstanding nya invoice skrg
                        $invoice->inv_outstanding = $outstanding;
                        $invoice->save();

                        // posting payment, panggil fungsi yg suda ada
                        $postRequest = new Request;
                        $postRequest->id = $payment_id;
                        $posting = app('App\Http\Controllers\PaymentController')->posting($postRequest);
                        $posting = json_decode($posting->getContent(), true);
                        if(!empty(@$posting['error'])){
                            $header->invpayh_post = true;
                            $header->save();
                        }
                    }
                    $confirmed++;
                    // DB::rollback();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                    // dd($e->getMessage());
                    $not_confirmed++;
                    continue;
                }
            }

            // scan note di trbank
            // $check = TrBank::where('trbank_note','VA Payment '.$inv_no)->first();
            // if($check){
            //     // jika ada di record check kecocokan amount dr file dengan amount record
            //     if($check->trbank_in == $amount && date('Ymd',strtotime($check->trbank_date)) == $created_time){
            //         $check->trbank_rekon = true;
            //         $check->save();
            //         $confirmed++;
            //     }else{
            //         $not_confirmed++;
            //     }
            // }else{
            //     $not_confirmed++;
            // }
        }
        return response()->json([
                        "error"=> false,
                        "error_code"=> 200,
                        "error_desc"=> "Success, $confirmed payments confirmed and $not_confirmed was not confirmed",
                    ]);
    }

    public function getTokenFromMsp(Request $request)
    {
        // cek unit
        $unit_code = @$request->unit_code;
        $nominal = @$request->nominal;
        $trx_id = @$request->trx_id;
        if(empty($unit_code))
            return response()->json([
                "error"=> true,
                "error_code"=> 404,
                "error_desc"=> "Unit code is required",
            ], 400);
        if(empty($nominal))
            return response()->json([
                "error"=> true,
                "error_code"=> 404,
                "error_desc"=> "Nominal is required",
            ], 400);
        if(empty($trx_id))
            return response()->json([
                "error"=> true,
                "error_code"=> 405,
                "error_desc"=> "Trx Code is required",
            ], 400);
        // cek unit availability
        $unit = MsUnit::where('unit_code',$unit_code)->first();
        if(!$unit)
            return response()->json([
                    "error"=> true,
                    "error_code"=> 400,
                    "error_desc"=> "Unit tidak ada",
                ],400);
        // cek nominal
        $nominal_whitelist = [50000,100000,150000,200000,250000];
        if(!in_array($nominal, $nominal_whitelist))
            return response()->json([
                    "error"=> true,
                    "error_code"=> 402,
                    "error_desc"=> "Nominal harus dalam opsi berikut : 50000, 100000, 150000, 200000, 250000",
                ],400);

        // check outstanding
        $req = new Request;
        $req->no_unit = $unit_code;
        $outstanding = $this->outstanding($req);
        if($outstanding == "true")
            return response()->json([
                    "error"=> true,
                    "error_code"=> 400,
                    "error_desc"=> "Unit masih memiliki tunggakan",
                ],400);

        try{
            // request ke MSP
            $endpoint_url = 'http://192.168.78.4/msp-api/post/purchase';
            $data_to_post = [
                'username' => 'kasirvirtual',
                'password' => md5('qqq123'),
                'no_unit' => $unit_code,
                //'no_unit' => 'QV001',
                'nominal' => $nominal
            ];
            $options = [
                CURLOPT_URL        => $endpoint_url,
                CURLOPT_POST       => true,
                CURLOPT_POSTFIELDS => $data_to_post,
                CURLOPT_RETURNTRANSFER => true
            ];
            $curl = curl_init();
            curl_setopt_array($curl, $options);
            $results = curl_exec($curl);
            $result = json_decode($results, true);

            if(!empty(@$result["Error"]))
                return response()->json([
                    "error"=> true,
                    "error_code"=> 400,
                    "error_desc"=> $result["Error"],
                ],400);

            // if lolos save logbook
            if(!empty(@$result["log_book"])){
                $log = $result["log_book"];
                $data = new TokenInvoice;
                $data->inv_date = $log["inv_date"];
                $data->inv_no = $log["inv_no"];
                $data->meter_no = $log["meter_no"];
                // $data->cust_name = $log["cust_name"];
                $data->cust_name = @$unit->owner->tenantWT->tenan_name;
                $data->no_unit = $log["no_unit"];
                $data->location = $log["location"];
                $data->tariff_index = $log["tariff_index"];
                $data->daya = $log["daya"];
                $data->total_pay = $log["total_pay"];
                $data->slab_cost = $log["slab_cost"];
                $data->water_cost = $log["water_cost"];
                $data->gas_cost = $log["gas_cost"];
                $data->admin_cost = $log["admin_cost"];
                $data->materai_cost = $log["materai_cost"];
                $data->bpju = $log["bpju"];
                $data->ppn = $log["ppn"];
                $data->token_cost = $log["token_cost"];
                $data->total_kwh = $log["total_kwh"];
                $data->token = $log["token"];
                $data->trx_id = $trx_id;
                $data->save();

                $response = [
                        "error"=> false,
                        "error_code"=> 200,
                        "inv_date"=> $data->inv_date,
                        "inv_no"=> $data->inv_no,
                        "meter_no"=> $data->meter_no,
                        "cust_name"=> $data->cust_name,
                        "no_unit"=> $data->no_unit,
                        "location"=> $data->location,
                        "tariff_index"=> $data->tariff_index,
                        "daya"=> $data->daya,
                        "total_pay" => $data->total_pay,
                        "slab_cost"=> $data->slab_cost,
                        "water_cost"=> $data->water_cost,
                        "gas_cost"=> $data->gas_cost,
                        "admin_cost"=> $data->admin_cost,
                        "materai_cost"=> $data->materai_cost,
                        "bpju"=> $data->bpju,
                        "ppn"=> $data->ppn,
                        "token_cost"=> $data->token_cost,
                        "total_kwh"=> $data->total_kwh,
                        "token"=> $data->token,
                        "trx_id" => $data->trx_id
                    ];
                return response()->json($response, 200);

            }else{
                return false;
            }

        }catch(\Exception $e){
            return response()->json([
                    "error"=> true,
                    "error_code"=> 400,
                    "error_desc"=> "Error occured when communicating with token server",
                ],400);
        }

    }

    public function inquiryCheck(Request $request){
        $unit_code = @$request->unit_code;
        if(empty($unit_code))
            return response()->json([
                "error"=> true,
                "error_code"=> 404,
                "error_desc"=> "Unit code is required",
            ], 400);
        // cek unit availability
        $unit = MsUnit::where('unit_code',$unit_code)->first();
        if(!$unit)
            return response()->json([
                    "error"=> true,
                    "error_code"=> 400,
                    "error_desc"=> "Unit tidak ada",
                ],400);
        // tembak ke MSP dapetin daya
        try{
            $endpoint_url = 'http://192.168.78.4/msp-api/...';
            $data_to_post = [
                'username' => 'kasirvirtual',
                'password' => md5('qqq123'),
                'no_unit' => $unit_code
            ];
            $options = [
                CURLOPT_URL        => $endpoint_url,
                CURLOPT_POST       => true,
                CURLOPT_POSTFIELDS => $data_to_post,
                CURLOPT_RETURNTRANSFER => true
            ];
            $curl = curl_init();
            curl_setopt_array($curl, $options);
            $results = curl_exec($curl);
            $result = json_decode($results, true);

            if(!empty(@$result["Error"]))
                return response()->json([
                    "error"=> true,
                    "error_code"=> 400,
                    "error_desc"=> $result["Error"],
                ],400);

            if(!empty(@$result["log_book"])){
                $log = $result["log_book"];
                $response = [
                        'cust_name' => @$unit->owner->tenantWT->tenan_name,
                        'daya' => $log["daya"]
                    ];
                return response()->json($response, 200);
            }else{
                return response()->json([
                    "error"=> true,
                    "error_code"=> 400,
                    "error_desc"=> 'Customer data not found'
                ],400);
            }
        }catch(\Exception $e){
            return response()->json([
                    "error"=> true,
                    "error_code"=> 400,
                    "error_desc"=> "Error occured when communicating with token server",
                ],400);
        }
    }

    public function trxCheck(Request $request){
        // cek unit
        $unit_code = @$request->unit_code;
        $nominal = @$request->nominal;
        $trx_id = @$request->trx_id;
        if(empty($unit_code))
            return response()->json([
                "error"=> true,
                "error_code"=> 404,
                "error_desc"=> "Unit code is required",
            ], 400);
        if(empty($nominal))
            return response()->json([
                "error"=> true,
                "error_code"=> 404,
                "error_desc"=> "Nominal is required",
            ], 400);
        if(empty($trx_id))
            return response()->json([
                "error"=> true,
                "error_code"=> 405,
                "error_desc"=> "Trx Code is required",
            ], 400);
        // cek unit availability
        $unit = MsUnit::where('unit_code',$unit_code)->first();
        if(!$unit)
            return response()->json([
                    "error"=> true,
                    "error_code"=> 400,
                    "error_desc"=> "Unit tidak ada",
                ],400);
        // cek logbook
        $fetch = TokenInvoice::where('trx_id',$trx_id)->where('total_pay',$nominal)->where('no_unit',$unit_code)->first();
        if($fetch){
            $data = $fetch;
            $response = [
                        "error"=> false,
                        "error_code"=> 200,
                        "inv_date"=> $data->inv_date,
                        "inv_no"=> $data->inv_no,
                        "meter_no"=> $data->meter_no,
                        "cust_name"=> $data->cust_name,
                        "no_unit"=> $data->no_unit,
                        "location"=> $data->location,
                        "tariff_index"=> $data->tariff_index,
                        "daya"=> $data->daya,
                        "total_pay" => $data->total_pay,
                        "slab_cost"=> $data->slab_cost,
                        "water_cost"=> $data->water_cost,
                        "gas_cost"=> $data->gas_cost,
                        "admin_cost"=> $data->admin_cost,
                        "materai_cost"=> $data->materai_cost,
                        "bpju"=> $data->bpju,
                        "ppn"=> $data->ppn,
                        "token_cost"=> $data->token_cost,
                        "total_kwh"=> $data->total_kwh,
                        "token"=> $data->token,
                        "trx_id" => $data->trx_id
                    ];
            return response()->json($response, 200);
        }else{
            return response()->json([
                    "error"=> false,
                    "error_code"=> 200,
                    "error_desc"=> "Transaksi tidak ditemukan",
                ],200);
        }
    }

}