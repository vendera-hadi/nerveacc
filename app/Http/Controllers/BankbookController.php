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
use App\Models\Kurs;
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
                    ->join('ms_payment_type','ms_payment_type.id',"=",'tr_bank.paymtp_id')
                    ->leftJoin('ms_cash_bank','ms_cash_bank.id',"=",'tr_bank.cashbk_id');

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
                $temp['cashbk_name'] = $value->cashbk_name;               
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
                    if($value->trbank_group == 'KM '){
                        $action_button .= ' | <a href="'.route('bankbook.edit.transfer',$value->id).'" ><i class="fa fa-pencil"></i></a>';    
                    }else if($value->trbank_group == 'BM '){
                        $action_button .= ' | <a href="'.route('bankbook.edit.deposit',$value->id).'" ><i class="fa fa-pencil"></i></a>';
                    }else if($value->trbank_group == 'BK '){
                        $action_button .= ' | <a href="'.route('bankbook.edit.withdraw',$value->id).'" ><i class="fa fa-pencil"></i></a>';
                    }
                    $action_button .= ' | <a href="#" data-id="'.$value->id.'" class="remove"><i class="fa fa-times"></i></a>';
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

    public function detail(Request $request)
    {
        try{
            $id = $request->id;
            $trbank = TrBank::find($id);
            $result = '';
            $totaldebit = $totalcredit = 0;
            foreach ($trbank->detail as $key => $val) {
                $totalcredit += $val->credit;
                $totaldebit += $val->debit;
                $result .= '<tr>
                        <td>'.$val->coa_code.'</td>
                        <td>'.$val->coa->coa_name.'</td>
                        <td>'.$val->note.'</td>
                        <td>'.$val->dept->dept_name.'</td>
                        <td>Rp. '.number_format($val->debit,2).'</td>
                        <td>Rp. '.number_format($val->credit,2).'</td>
                    </tr>';

            }
            $result .= '<tr><td colspan="4">Total</td><td><b>Rp. '.number_format($totaldebit,2).'</b></td><td><b>Rp. '.number_format($totalcredit,2).'</b></td></tr>';
            return response()->json(['success' => 1,'data' => $result]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        try{
            $id = $request->id;
            TrBank::destroy($id);
            return response()->json(['success' => 1,'message' => 'Delete Success']);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function transfer(Request $request){
        $data['cashbank_data'] = MsCashBank::all()->toArray();
        $data['kurs'] = Kurs::orderBy('id')->get();
        return view('bankbook_transfer',$data);
    }

    public function dotransfer(Request $request){
        try{
            if(MsCashBank::find($request->to_coa_id)->coa_code == $request->from_coa)
                return redirect()->back()->with(['error' => 'Cant transfer to the same bank account']);

            \DB::beginTransaction();
            $header = new TrBank;
            $header->trbank_no = $request->trbank_no;
            $header->trbank_date = $request->trbank_date;
            $header->trbank_recipient = MsCashBank::find($request->to_coa_id)->cashbk_name;
            $header->trbank_group = 'KM';
            if(!empty($header->trbank_girodate)) $header->trbank_girodate = $request->trbank_girodate;
            $header->trbank_girono = '';
            $header->cashbk_id = $request->to_coa_id;
            $header->coa_code = MsCashBank::find($request->to_coa_id)->coa_code;
            $header->paymtp_id = 2;
            $header->trbank_note = $request->trbank_note;
            $header->created_by = \Auth::id();
            $header->updated_by = \Auth::id();
            $header->kurs_id = $request->kurs_id;
            $header->currency_val = Kurs::find($request->kurs_id)->value;

            $details = [];
            // cashbank
            $detail = new TrBankJv;
            $detail->coa_code = $header->coa_code;
            $detail->debit = $request->amount * $header->currency_val;
            $detail->note = $request->trbank_no;
            $detail->dept_id = 3;
            $details[] = $detail;

            $detail = new TrBankJv;
            $detail->coa_code = $request->from_coa;
            $detail->credit = $request->amount * $header->currency_val;
            $detail->note = $request->trbank_no;
            $detail->dept_id = 3;
            $details[] = $detail;

            $header->trbank_in = $request->amount * $header->currency_val;
            $header->save();
            $header->detail()->saveMany($details);
            \DB::commit();
            return redirect()->back()->with(['success' => 'Transfer Success']);
        }catch(\Exception $e){
            \DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function updatetransfer(Request $request, $id){
        try{
            if(MsCashBank::find($request->to_coa_id)->coa_code == $request->from_coa)
                return redirect()->back()->with(['error' => 'Cant transfer to the same bank account']);

            \DB::beginTransaction();
            $header = TrBank::find($id);
            if($request->trbank_no != $header->trbank_no) $header->trbank_no = $request->trbank_no;
            $header->trbank_date = $request->trbank_date;
            $header->trbank_recipient = MsCashBank::find($request->to_coa_id)->cashbk_name;
            $header->cashbk_id = $request->to_coa_id;
            $header->coa_code = MsCashBank::find($request->to_coa_id)->coa_code;
            $header->trbank_note = $request->trbank_note;
            $header->updated_by = \Auth::id();

            $header->kurs_id = $request->kurs_id;
            if($request->current_kurs_id != $header->kurs_id) $header->currency_val = Kurs::find($request->kurs_id)->value;

            // delete all details
            TrBankJv::where('trbank_id',$id)->delete();
            $details = [];
            // cashbank
            $detail = new TrBankJv;
            $detail->coa_code = $header->coa_code;
            $detail->debit = $request->amount * $header->currency_val;
            $detail->note = $request->trbank_no;
            $detail->dept_id = 3;
            $details[] = $detail;

            $detail = new TrBankJv;
            $detail->coa_code = $request->from_coa;
            $detail->credit = $request->amount * $header->currency_val;
            $detail->note = $request->trbank_no;
            $detail->dept_id = 3;
            $details[] = $detail;

            $header->trbank_in = $request->amount * $header->currency_val;
            $header->save();
            $header->detail()->saveMany($details);
            \DB::commit();
            return redirect()->back()->with(['success' => 'Update Success']);
        }catch(\Exception $e){
            \DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function edittransfer(Request $request, $id){
        $data['cashbank_data'] = MsCashBank::all()->toArray();
        $data['trbank'] = TrBank::find($id);
        $data['kurs'] = Kurs::orderBy('id')->get();
        return view('bankbook_transfer_edit',$data);
    }

    public function deposit(Request $request){
        $coaYear = date('Y');
        $data['kurs'] = Kurs::orderBy('id')->get();
        $data['cashbank_data'] = MsCashBank::all()->toArray();
        $data['departments'] = MsDepartment::where('dept_isactive',1)->get();
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        return view('bankbook_deposit',$data);
    }

    public function dodeposit(Request $request)
    {
        \DB::beginTransaction();
        try{
            $header = new TrBank;
            $header->trbank_no = $request->trbank_no;
            $header->trbank_date = $request->trbank_date;
            $header->trbank_recipient = MsCashBank::find($request->cashbk_id)->cashbk_name;
            $header->trbank_group = 'BM';
            if(!empty($header->trbank_girodate)) $header->trbank_girodate = $request->trbank_girodate;
            $header->trbank_girono = '';
            $header->cashbk_id = $request->cashbk_id;
            $header->coa_code = MsCashBank::find($request->cashbk_id)->coa_code;
            $header->paymtp_id = 2;
            $header->trbank_note = $request->trbank_note;
            $header->created_by = \Auth::id();
            $header->updated_by = \Auth::id();
            $header->kurs_id = $request->kurs_id;
            $header->currency_val = Kurs::find($request->kurs_id)->value;

            $details = [];
            $depts = $request->dept_id;
            $desc = $request->description;
            $amount = $request->amount;
            $coatype = $request->coa_type;
            $totaldebit = 0;
            $totalcredit = 0;
            if(count(@$request->coa_code) < 1) return redirect()->back()->with(['error' => 'Please insert coa for receiver']);
            // lawanan
            foreach ($request->coa_code as $key => $coa) {
                $type = strtolower($coatype[$key]);
                $detail = new TrBankJv;
                $detail->coa_code = $coa;
                $detail->$type = $amount[$key];
                if($type == 'credit') $totalcredit += $amount[$key];
                else $totaldebit += $amount[$key];
                $detail->note = $header->trbank_no;
                $detail->dept_id = $depts[$key];
                $details[] = $detail;
            }
            // cashbank
            $detail = new TrBankJv;
            $detail->coa_code = $header->coa_code;
            $detail->debit = $totalcredit - $totaldebit;
            $detail->note = $header->trbank_no;
            $detail->dept_id = 3;
            $details[] = $detail;

            $header->trbank_in = $totalcredit - $totaldebit;
            $header->save();
            $header->detail()->saveMany($details);

            \DB::commit();
            return redirect()->back()->with(['success' => 'Update Success']);
        }catch(\Exception $e){
            \DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function updatedeposit(Request $request, $id){
        \DB::beginTransaction();
        try{
            $header = TrBank::find($id);
            if($request->trbank_no != $header->trbank_no) $header->trbank_no = $request->trbank_no;
            $header->trbank_date = $request->trbank_date;
            $header->trbank_recipient = MsCashBank::find($request->cashbk_id)->cashbk_name;
            $header->cashbk_id = $request->cashbk_id;
            $header->coa_code = MsCashBank::find($request->cashbk_id)->coa_code;
            $header->trbank_note = $request->trbank_note;
            $header->updated_by = \Auth::id();

            // delete all details
            TrBankJv::where('trbank_id',$id)->delete();
            $details = [];
            $depts = $request->dept_id;
            $desc = $request->description;
            $amount = $request->amount;
            $coatype = $request->coa_type;
            $totaldebit = 0;
            $totalcredit = 0;
            if(count(@$request->coa_code) < 1) return redirect()->back()->with(['error' => 'Please insert coa for debit']);
            // lawanan
            foreach ($request->coa_code as $key => $coa) {
                $type = strtolower($coatype[$key]);
                $detail = new TrBankJv;
                $detail->coa_code = $coa;
                $detail->$type = $amount[$key];
                if($type == 'credit') $totalcredit += $amount[$key];
                else $totaldebit += $amount[$key];
                $detail->note = $header->trbank_no;
                $detail->dept_id = $depts[$key];
                $details[] = $detail;
            }
            // cashbank
            $detail = new TrBankJv;
            $detail->coa_code = $header->coa_code;
            $detail->debit = $totalcredit - $totaldebit;
            $detail->note = $header->trbank_no;
            $detail->dept_id = 3;
            $details[] = $detail;

            $header->trbank_in = $totalcredit - $totaldebit;
            $header->save();
            $header->detail()->saveMany($details);

            \DB::commit();
            return redirect()->back()->with(['success' => 'Update Success']);
        }catch(\Exception $e){
            \DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function editdeposit(Request $request, $id){
        $coaYear = date('Y');
        $data['kurs'] = Kurs::orderBy('id')->get();
        $data['cashbank_data'] = MsCashBank::all()->toArray();
        $data['departments'] = MsDepartment::where('dept_isactive',1)->get();
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        $data['trbank'] = TrBank::find($id);
        return view('bankbook_deposit_edit',$data);
    }

    public function withdraw(Request $request){
        $coaYear = date('Y');
        $data['cashbank_data'] = MsCashBank::all()->toArray();
        $data['departments'] = MsDepartment::where('dept_isactive',1)->get();
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        return view('bankbook_withdraw',$data);
    }

    public function dowithdraw(Request $request)
    {
        \DB::beginTransaction();
        try{
            // dd($request->all());
            $header = new TrBank;
            $header->trbank_no = $request->trbank_no;
            $header->trbank_date = $request->trbank_date;
            $header->trbank_recipient = $request->trbank_recipient;
            $header->trbank_group = 'BK';
            if(!empty($header->trbank_girodate)) $header->trbank_girodate = $request->trbank_girodate;
            $header->trbank_girono = '';     
            $coa_bank = MsCashBank::where('id',$request->from_coa)->get();
            $header->coa_code = $coa_bank[0]->coa_code;
            $header->cashbk_id = $request->from_coa;
            $header->paymtp_id = 2;
            $header->trbank_note = $request->trbank_note;
            $header->created_by = \Auth::id();
            $header->updated_by = \Auth::id();

            $details = [];
            $depts = $request->dept_id;
            $desc = $request->description;
            $amount = $request->amount;
            $total = 0;
            if(count(@$request->coa_code) < 1) return redirect()->back()->with(['error' => 'Please insert coa for receiver']);
            // lawanan
            foreach ($request->coa_code as $key => $coa) {
                $detail = new TrBankJv;
                $detail->coa_code = $coa;
                $detail->credit = $amount[$key];
                $total += $amount[$key];
                $detail->note = $desc[$key];
                $detail->dept_id = $depts[$key];
                $details[] = $detail;
            }
            // cashbank
            $detail = new TrBankJv;
            $detail->coa_code = $header->coa_code;
            $detail->debit = $total;
            $detail->note = 'Kirim uang';
            $detail->dept_id = 3;
            $details[] = $detail;

            $header->trbank_out = $total;
            $header->save();
            $header->detail()->saveMany($details);

            \DB::commit();
            return redirect()->back()->with(['success' => 'Update Success']);
        }catch(\Exception $e){
            \DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function editwithdraw(Request $request, $id){
        $coaYear = date('Y');
        $data['cashbank_data'] = MsCashBank::all()->toArray();
        $data['departments'] = MsDepartment::where('dept_isactive',1)->get();
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        $data['trbank'] = TrBank::find($id);
        return view('bankbook_withdraw_edit',$data);
    }

    public function updatewithdraw(Request $request, $id){
        \DB::beginTransaction();
        try{
            $header = TrBank::find($id);
            if($request->trbank_no != $header->trbank_no) $header->trbank_no = $request->trbank_no;
            $header->trbank_date = $request->trbank_date;
            $header->trbank_recipient = $request->trbank_recipient;
            $coa_bank = MsCashBank::where('id',$request->from_coa)->get();
            $header->coa_code = $coa_bank[0]->coa_code;
            $header->cashbk_id = $request->from_coa;
            $header->trbank_note = $request->trbank_note;
            $header->updated_by = \Auth::id();

            $details = [];
            $depts = $request->dept_id;
            $desc = $request->description;
            $amount = $request->amount;
            $total = 0;
            if(count(@$request->coa_code) < 1) return redirect()->back()->with(['error' => 'Please insert coa for receiver']);
            // delete all details
            TrBankJv::where('trbank_id',$id)->delete();
            // lawanan
            foreach ($request->coa_code as $key => $coa) {
                $detail = new TrBankJv;
                $detail->coa_code = $coa;
                $detail->credit = $amount[$key];
                $total += $amount[$key];
                $detail->note = $desc[$key];
                $detail->dept_id = $depts[$key];
                $details[] = $detail;
            }
            // cashbank
            $detail = new TrBankJv;
            $detail->coa_code = $header->coa_code;
            $detail->debit = $total;
            $detail->note = 'Kirim uang';
            $detail->dept_id = 3;
            $details[] = $detail;

            $header->trbank_out = $total;
            $header->save();
            $header->detail()->saveMany($details);

            \DB::commit();
            return redirect()->back()->with(['success' => 'Update Success']);
        }catch(\Exception $e){
            \DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function reconcile(Request $request)
    {
        $coaYear = date('Y');
        $data['cashbank_data'] = MsCashBank::all()->toArray();
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        $cashbankId = $request->cashbk_id;
        $start = $request->start;
        $end = $request->end;
        $data['trbanks'] = [];
        if($cashbankId && $start && $end){
            $bank = MsCashBank::find($cashbankId);
            $data['trbanks'] = TrBank::where('trbank_date','>=',$start." 00:00:00")->where('trbank_date','<=',$end.' 23:59:59')->where('coa_code', $bank->coa_code)->orderBy('trbank_date')->get();
        }

        return view('reconcile',$data);
    }

    public function reconcileUpdate(Request $request)
    {
        $ids = $request->id;
        $rekon = $request->rekon;
        if($ids){
            foreach ($ids as $key => $id) {
                $trbank = TrBank::find($id);
                $trbank->trbank_rekon = (bool)$rekon[$key];
                $trbank->save();
            }
        }
        return redirect()->back()->with(['success' => 'Update Success']);
    }

}