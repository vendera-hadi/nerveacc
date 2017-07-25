<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
// load model
use App\Models\MsMasterCoa;
use App\Models\MsCashBank;
use App\Models\MsPaymentType;
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
            $count = DB::table('bankbook_header')->count();
            $fetch = DB::table('bankbook_header')->select('bankbook_header.*','ms_payment_type.paymtp_name')
                    ->join('ms_payment_type',   'ms_payment_type.id',"=",'bankbook_header.paymtp_id');

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
                $temp['voucher_no'] = $value->voucher_no;
                $temp['note'] = $value->note;
                $temp['paymtp_name'] = $value->paymtp_name;
                $temp['transaction_date'] = date('d/m/Y',strtotime($value->transaction_date));
                $temp['amount'] = "Rp. ".number_format($value->amount);
                $temp['check_date'] = $value->transaction_date ?: date('d/m/Y',strtotime($value->transaction_date));
                $temp['is_posted'] = $value->is_posted ? 'yes' : 'no';
                $temp['checkbox'] = '<input type="checkbox" name="check" value="'.$value->id.'">';
                

                $action_button = '<a href="#" title="View Detail" data-toggle="modal" data-target="#detailModal" data-id="'.$value->id.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                
                if($temp['is_posted'] == 'no'){
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

}