<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\TrPeriodMeter;
use App\Models\User;
use App\Models\TrContract;
use App\Models\TrContractInvoice;
use App\Models\TrMeter;
use DB;
class PeriodMeterController extends Controller
{
    public function index(){
		return view('period_meter');
    }

    public function get(Request $request){
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
        	$count = TrPeriodMeter::count();
        	$fetch = TrPeriodMeter::query();
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
                    // special condition
                    if($filter->field == 'status'){
                        if(strtolower($filter->value) == "Posted") $filter->value = "true";
                        else $filter->value = "false";
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
        		$temp['prdmet_id'] = $value->prdmet_id;
                $temp['prdmet_start_date'] = $value->prdmet_start_date;
        		$temp['prdmet_end_date'] = $value->prdmet_end_date;
                $temp['prd_billing_date'] = $value->prd_billing_date;
                $temp['created_by'] = $value->created_by;
                $temp['status'] = !empty($value->status) ? 'Posted' : 'Need Approval';
        		try{
        			$temp['created_by'] = User::findOrFail($value->created_by)->name;
        		}catch(\Exception $e){
        			$temp['created_by'] = '-';
        		}
        		$result['rows'][] = $temp;
        	}
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function insert(Request $request){
		$input = $request->all();
        $input['prdmet_id'] = 'PRD'.date('Ymd');
		$input['created_by'] = Auth::id();
		$input['updated_by'] = Auth::id();
        try{
            DB::transaction(function () use($input, $request) {
                $last_id =  TrPeriodMeter::create($input);
                $insertedId = $last_id->id;
                $meter = TrPeriodMeter::select('prdmet_id')->where('status',true)->orderBy('prd_billing_date','desc')->limit(1)->get();
                if(count($meter) == 0){
                    $tanggal = $request->input('prd_billing_date');
                     $kontrak = TrContract::select('tr_contract.contr_code','tr_contract_invoice.costd_is','ms_cost_detail.costd_name','ms_cost_detail.costd_unit','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin')
                    ->join('tr_contract_invoice',\DB::raw('tr_contract_invoice.contr_id::char'),"=",\DB::raw('tr_contract.id::char'))
                    ->join('ms_cost_detail',\DB::raw('tr_contract_invoice.costd_is::integer'),"=",\DB::raw('ms_cost_detail.id::integer'))
                    ->where('tr_contract.contr_startdate', '<=', $tanggal)
                    ->where('tr_contract.contr_enddate', '>=', $tanggal)
                    ->where('tr_contract.contr_status','confirmed')
                    ->groupBy('tr_contract.contr_code','tr_contract_invoice.costd_is','ms_cost_detail.costd_name','ms_cost_detail.costd_unit','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin')
                    ->get();
                    for($i=0; $i<count($kontrak); $i++){
                        $inputs = [ 
                            'meter_start'=> '0',
                            'meter_end'=> '0',
                            'meter_used'=> '0',
                            'meter_cost'=> '0',
                            'meter_burden'=> $kontrak[$i]->costd_burden,
                            'meter_admin'=> $kontrak[$i]->costd_admin,
                            'cosid_is'=>  $kontrak[$i]->costd_is,
                            'contract_id' => $kontrak[$i]->contr_code,
                            'prdmet_id'=> $insertedId
                        ];
                        $costd_is = TrMeter::create($inputs);
                    }
                    
                }else{

                }

            });
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
        return ['status' => 1, 'message' => 'Insert Success'];        	
    }

    public function editModal(Request $request){
        try{
            $prdmet = $request->id;
            $fetch = TrMeter::select('tr_contract.contr_id','tr_contract.contr_code','tr_meter.*','ms_cost_detail.costd_rate')
                    ->join('tr_contract',\DB::raw('tr_contract.contr_code::char'),"=",\DB::raw('tr_meter.contract_id::char'))
                    ->join('ms_cost_detail',\DB::raw('tr_meter.cosid_is::integer'),"=",\DB::raw('ms_cost_detail.id::integer'))
                    ->where('tr_meter.prdmet_id', $prdmet)
                    ->orderBy('tr_meter.id','ASC')
                    ->get();
            return view('modal.editmeter', ['meter' => $fetch]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function meterdetailUpdate(Request $request){
        $id = $request->input('tr_meter_id');
        $meter_end = $request->input('meter_end');
        $meter_start = $request->input('meter_start');
        $meter_rate = $request->input('meter_rate');
        $meter_burden = $request->input('meter_burden');
        $meter_admin = $request->input('meter_admin');
        try{
            DB::transaction(function () use($id, $meter_end, $meter_start, $meter_rate, $meter_burden, $meter_admin){
                foreach ($id as $key => $value) {
                    $input = [
                        'meter_end' => $meter_end[$key],
                        'meter_used' => ($meter_end[$key] - $meter_start[$key]),
                        'meter_cost' => ((($meter_end[$key] - $meter_start[$key]) * $meter_rate[$key]) + $meter_burden[$key] + $meter_admin[$key])
                    ];
                    TrMeter::find($id[$key])->update($input);
                }
            });
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
        return ['status' => 1, 'message' => 'Update Success'];
    }

    public function approve(Request $request){
        try{
            $id = $request->id;
            $input['status'] = true;
            TrPeriodMeter::find($id)->update($input);
            return TrPeriodMeter::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function update(Request $request){
        try{
        	$id = $request->id;
        	$input = $request->all();
        	$input['updated_by'] = Auth::id();
        	TrPeriodMeter::find($id)->update($input);
        	return TrPeriodMeter::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
        try{
        	$id = $request->id;
        	TrPeriodMeter::destroy($id);
        	return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

}
