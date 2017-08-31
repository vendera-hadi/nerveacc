<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\TrApPaymentHeader;
use Auth;
use DB;

class TreasuryController extends Controller
{
	public function index(){
		return view('ap_payment', array());
	}

	public function get(Request $request){
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
            $count = TrApPaymentHeader::count();
            $fetch = TrApPaymentHeader::select('tr_ap_payment_hdr.*','ms_payment_type.paymtp_name','ms_supplier.spl_name')
            			->join('ms_payment_type',   'ms_payment_type.id',"=",'tr_ap_payment_hdr.paymtp_id')
        				->join('ms_supplier', 'ms_supplier.id', '=', 'tr_ap_payment_hdr.spl_id');
        
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
                
                $temp['payment_code'] = $value->payment_code;
                $temp['spl_name'] = $value->spl_name;
                $temp['invoice_no'] = collect($value->detail)->map(function($detail){
                	return $detail->apheader->invoice_no;
                })->implode(', ');
                $temp['paymtp_name'] = $value->paymtp_name;
                $temp['payment_date'] = date('d/m/Y',strtotime($value->payment_date));
                $temp['amount'] = "Rp. ".number_format($value->amount);
               
                $temp['checkbox'] = '<input type="checkbox" name="check" value="'.$value->id.'">';
                $invpayh_post = $temp['posting'] = !empty($value->posting_at) ? 'yes' : 'no';

                $action_button = '<a href="#" title="View Detail" data-toggle="modal" data-target="#detailModal" data-id="'.$value->id.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                
                if($invpayh_post == 'no'){
                    // if(\Session::get('role')==1 || in_array(70,\Session::get('permissions'))){
                        $action_button .= ' | <a href="#" data-id="'.$value->id.'" title="Posting Payment" class="posting-confirm"><i class="fa fa-arrow-circle-o-up"></i></a>';
                    // }
                    // if(\Session::get('role')==1 || in_array(71,\Session::get('permissions'))){
                        $action_button .= ' | <a href="payment/void?id='.$value->id.'" title="Void" class="void-confirm"><i class="fa fa-ban"></i></a>';
                    // }
                }
                // $action_button .= ' | <a href="'.url('invoice/print_kwitansi?id='.$value->id).'" class="print-window" data-width="640" data-height="660"><i class="fa fa-file"></i></a>';

                $temp['action_button'] = $action_button;

                // $temp['daysLeft']
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getdetail(Request $request){
        $id = $request->id;
        $header = TrApPaymentHeader::find($id);
        return view('modal.treasury', ['header' => $header]);
	}
}