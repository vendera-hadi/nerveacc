<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
// load model
use App\Models\MsMasterCoa;
use App\Models\MsCashBank;
use App\Models\MsPaymentType;
use App\Models\MsDepartment;
use App\Models\TrBank;
use App\Models\TrBankJv;
use App\Models\MsJournalType;
use App\Models\TrLedger;
use Auth;
use DB;

class BankbookController extends Controller
{
	public function index()
	{
		$coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
		$data['cashbank_data'] = MsCashBank::all()->toArray();
        $data['payment_type_data'] = MsPaymentType::all()->toArray();
        $data['departments'] = MsDepartment::where('dept_isactive',1)->get();
		return view('bankbook',$data);
	}

	public function get(Request $request)
	{
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
            $count = TrBank::count();
            $fetch = TrBank::select('tr_bank.*','ms_payment_type.paymtp_name','ms_cash_bank.cashbk_name')
                    ->join('ms_payment_type',   'ms_payment_type.id',"=",'tr_bank.paymtp_id')
                    ->join('ms_cash_bank',   'ms_cash_bank.id',"=",'tr_bank.cashbk_id');

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

            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['trbank_no'] = $value->trbank_no;
                $temp['trbank_note'] = $value->trbank_note;
				$temp['coa_code'] = $value->coa_code;               
                $temp['paymtp_name'] = $value->paymtp_name;
                $temp['trbank_date'] = date('d/m/Y',strtotime($value->trbank_date));
                $temp['trbank_in'] = "Rp. ".number_format($value->trbank_in);
                $temp['trbank_out'] = "Rp. ".number_format($value->trbank_out);
                $temp['trbank_girodate'] = $value->trbank_girodate ?: date('d/m/Y',strtotime($value->trbank_girodate));
                $temp['trbank_post'] = $value->trbank_post ? 'yes' : 'no';
                $temp['checkbox'] = '<input type="checkbox" name="check" value="'.$value->id.'">';
                

                $action_button = '<a href="#" title="View Detail" data-toggle="modal" data-target="#detailModal" data-id="'.$value->id.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                
                if($temp['trbank_post'] == 'no'){
                    if(\Session::get('role')==1 || in_array(70,\Session::get('permissions'))){
                        $action_button .= ' | <a href="#" data-id="'.$value->id.'" title="Posting" class="posting-confirm"><i class="fa fa-arrow-circle-o-up"></i></a>';
                    }
                    $action_button .= ' | <a href="#" value="'.$value->id.'" class="remove"><i class="fa fa-times"></i></a>';
                }
                
                $temp['action_button'] = $action_button;

                // $temp['daysLeft']
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
	}

    public function insert(Request $request)
    {
        \DB::beginTransaction();
        try{
            $header = new TrBank;
            $header->trbank_no = $request->trbank_no;
            $header->trbank_date = $request->trbank_date;
            $header->trbank_recipient = $request->trbank_recipient;
            $header->trbank_group = $request->group;
            if(!empty($header->trbank_girodate)) $header->trbank_girodate = $request->trbank_girodate;
            $header->trbank_girono = $request->trbank_girono;
            $header->cashbk_id = $request->cashbk_id;
            $header->coa_code = MsCashBank::find($request->cashbk_id)->coa_code;
            $header->paymtp_id = $request->paymtp_id;
            $header->trbank_note = $request->trbank_note;
            $header->created_by = \Auth::id();
            $header->updated_by = \Auth::id();

            $details = [];
            $depts = $request->dept_id;
            $desc = $request->description;
            $amount = $request->amount;
            $total = 0;
            // lawanan
            foreach ($request->coa_code as $key => $coa) {
                $detail = new TrBankJv;
                $detail->coa_code = $coa;
                if($header->trbank_group == 'in') $detail->debit = $amount[$key];
                else $detail->credit = $amount[$key];
                $total += $amount[$key];
                $detail->note = $desc[$key];
                $detail->dept_id = $depts[$key];
                $details[] = $detail;
            }
            // cashbank
            $detail = new TrBankJv;
            $detail->coa_code = $header->coa_code;
            if($header->trbank_group == 'in') $detail->credit = $total;
            else $detail->debit = $total;
            $detail->note = $header->trbank_note;
            $detail->dept_id = 3;
            $details[] = $detail;

            if($header->trbank_group == 'in') $header->trbank_in = $total;
            else $header->trbank_out = $total;
            $header->save();
            $header->detail()->saveMany($details);

            \DB::commit();
            return response()->json(['success' => 1,'message' => 'Insert Success']);
        }catch(\Exception $e){
            \DB::rollback();
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function posting(Request $request){
        \DB::beginTransaction();
        try{
            $ids = $request->id;
            if(!is_array($ids)) $ids = [$ids];
            
            $coayear = date('Y');
            $month = date('m');
            $journaltype = '';
            foreach ($ids as $id) {
                $trbank = TrBank::find($id);
                if($trbank->trbank_group == 'in ') $journaltype = 'BM';
                else $journaltype = 'BK';

                // cari last prefix, order by journal type
                $jourType = MsJournalType::where('jour_type_prefix',$journaltype)->first();
                if(empty($jourType)) return response()->json(['error'=>1, 'message'=>'Please Create Journal Type with prefix "'.$journaltype.'" ']);
                $lastJournal = TrLedger::where('jour_type_id',$jourType->id)->latest()->first();
                if($lastJournal){
                    $lastJournalNumber = explode(" ", $lastJournal->ledg_number);
                    $lastJournalNumber = (int) end($lastJournalNumber);
                    $nextJournalNumber = $lastJournalNumber + 1;
                }else{
                    $nextJournalNumber = 1;
                }
                foreach ($trbank->detail()->get() as $detail) {
                    $nextJournalNumberConvert = str_pad($nextJournalNumber, 4, 0, STR_PAD_LEFT);
                    $journalNumber = $jourType->jour_type_prefix." ".$coayear.$month." ".$nextJournalNumberConvert;
                    $microtime = str_replace(".", "", str_replace(" ", "",microtime()));
                    $journal = [
                                    'ledg_id' => "JRNL".substr($microtime,10).str_random(5),
                                    'ledge_fisyear' => $coayear,
                                    'ledg_number' => $journalNumber,
                                    'ledg_date' => date('Y-m-d'),
                                    'ledg_refno' => $trbank->trbank_no,
                                    'ledg_debit' => $detail->debit,
                                    'ledg_credit' => $detail->credit,
                                    'ledg_description' => $detail->note,
                                    'coa_year' => $coayear,
                                    'coa_code' => $detail->coa_code,
                                    'created_by' => Auth::id(),
                                    'updated_by' => Auth::id(),
                                    'jour_type_id' => $jourType->id,
                                    'dept_id' => $detail->dept_id
                                ];
                    TrLedger::create($journal);
                }
                $trbank->trbank_post = true;
                $trbank->posting_at = date('Y-m-d');
                $trbank->save();
            }
            \DB::commit();
            return response()->json(['success' => 1,'message' => 'Posting Success']);
        }catch(\Exception $e){
            \DB::rollback();
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

}