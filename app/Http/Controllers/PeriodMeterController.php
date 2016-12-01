<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use Input;
// load model
use App\Models\TrPeriodMeter;
use App\Models\User;
use App\Models\TrContract;
use App\Models\TrContractInvoice;
use App\Models\TrMeter;
use App\Models\MsUnit;
use App\Models\MsCostDetail;
use App\Models\CutoffHistory;
use DB;
use Excel;
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
        try{
            $input = $request->all();
            $count = TrPeriodMeter::count();
            $ms = $count+1;
            $input['prdmet_id'] = 'PRD-'.date('Ymd').'-'.$ms;
            $prdstart = explode('/',$request->prdmet_start_date);
            $prdend = explode('/',$request->prdmet_end_date);
            $input['prdmet_start_date'] = implode('-', [$prdstart[2],$prdstart[0],$prdstart[1]]);
            $input['prdmet_end_date'] = implode('-', [$prdend[2],$prdend[0],$prdend[1]]);
            $input['created_by'] = Auth::id();
            $input['updated_by'] = Auth::id();
            $input['status'] = FALSE;
            if($count > 0){
                $today_month = date("m", strtotime($request->input('prd_billing_date'))); 
                $today_year = date("Y", strtotime($request->input('prd_billing_date'))); 
                $cek_last = TrPeriodMeter::where('tr_period_meter.status', TRUE)
                    ->where(DB::raw("EXTRACT(MONTH FROM prd_billing_date)"), $today_month)
                    ->where(DB::raw("EXTRACT(YEAR FROM prd_billing_date)"), $today_year)
                    ->count();
                if($cek_last == 0){
                    $prd_last = TrPeriodMeter::select('id')
                        ->where('tr_period_meter.status', TRUE)
                        ->orderBy('prd_billing_date', 'desc')
                        ->take(1)
                        ->get();
                    $last_id =  TrPeriodMeter::create($input);
                    $insertedId = $last_id->id;
                    $unit_kontrak = MsUnit::select('ms_unit.id AS unit_id','ms_unit.unit_code','tr_contract.id AS contr_id','tr_contract_invoice.costd_id','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','costd_admin')
                    ->leftJoin('tr_contract','tr_contract.unit_id',"=",'ms_unit.id')
                    ->leftJoin('tr_contract_invoice','tr_contract_invoice.contr_id',"=",'tr_contract.id')
                    ->leftJoin('ms_cost_detail','ms_cost_detail.id',"=",'tr_contract_invoice.costd_id')
                    ->where('tr_contract_invoice.invtp_id', 1)
                    ->where('ms_cost_detail.costd_ismeter', TRUE)
                    ->get();
            
                    for($i=0; $i<count($unit_kontrak); $i++){
                        $meter = TrMeter::select('meter_end','costd_id','unit_id')
                            ->where('unit_id',$unit_kontrak[$i]->unit_id)
                            ->where('costd_id',$unit_kontrak[$i]->costd_id)
                            ->where('prdmet_id',$prd_last[0]->id)
                            ->get();
                        $m_end = 0;
                        if(count($meter) > 0){
                            $m_end = $meter[0]->meter_end;
                        }
                        $inputs = [ 
                                    'meter_start'=> $m_end,
                                    'meter_end'=> 0,
                                    'meter_used'=> 0,
                                    'meter_cost'=> 0,
                                    'meter_burden'=> $unit_kontrak[$i]->costd_burden,
                                    'meter_admin'=> $unit_kontrak[$i]->costd_admin,
                                    'costd_id'=>  $unit_kontrak[$i]->costd_id,
                                    'contr_id'=> $unit_kontrak[$i]->contr_id,
                                    'prdmet_id'=> $insertedId,
                                    'unit_id'=>$unit_kontrak[$i]->unit_id
                                ];
                        $lts = TrMeter::create($inputs);
                        $last_idmeter = $lts->id;
                        
                        $ct_meter = CutoffHistory::select('unit_id','costd_id','meter_end')
                            ->where('close_date', '>=', $input['prdmet_start_date'])
                            ->where('close_date', '<=', $input['prdmet_end_date'])
                            ->where('unit_id',$unit_kontrak[$i]->unit_id)
                            ->where('costd_id',$unit_kontrak[$i]->costd_id)
                            ->get();
                        if(count($ct_meter)>0){
                            TrMeter::where('id', $last_idmeter)->update(['meter_start' => $ct_meter[0]->meter_end]);
                        }
                    }   
                }else{
                    echo 'Periode Meter Sudah Pernah Dibuat dan Sudah Di Approve';
                }
            }else{
                $last_id =  TrPeriodMeter::create($input);
                $insertedId = $last_id->id;
                $unit_kontrak = MsUnit::select('ms_unit.id AS unit_id','ms_unit.unit_code','tr_contract.id AS contr_id','tr_contract_invoice.costd_id','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','costd_admin')
                ->leftJoin('tr_contract','tr_contract.unit_id',"=",'ms_unit.id')
                ->leftJoin('tr_contract_invoice','tr_contract_invoice.contr_id',"=",'tr_contract.id')
                ->leftJoin('ms_cost_detail','ms_cost_detail.id',"=",'tr_contract_invoice.costd_id')
                ->where('tr_contract_invoice.invtp_id', 1)
                ->where('ms_cost_detail.costd_ismeter', TRUE)
                ->get();
                for($i=0; $i<count($unit_kontrak); $i++){
                    $inputs = [ 
                                'meter_start'=> '0',
                                'meter_end'=> '0',
                                'meter_used'=> '0',
                                'meter_cost'=> '0',
                                'meter_burden'=> $unit_kontrak[$i]->costd_burden,
                                'meter_admin'=> $unit_kontrak[$i]->costd_admin,
                                'costd_id'=>  $unit_kontrak[$i]->costd_id,
                                'contr_id'=> $unit_kontrak[$i]->contr_id,
                                'prdmet_id'=> $insertedId,
                                'unit_id'=>$unit_kontrak[$i]->unit_id
                            ];
                    TrMeter::create($inputs);
                }
            }
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }    
        return ['status' => 1, 'message' => 'Insert Success'];      	
    }

    public function testing(){
        /*
        $unit_kontrak = MsUnit::select('ms_unit.id AS unit_id','ms_unit.unit_code','tr_contract.id AS contr_id','tr_contract_invoice.costd_id','ms_cost_detail.costd_rate','ms_cost_detail.costd_rate','costd_admin')
            ->leftJoin('tr_contract','tr_contract.unit_id',"=",'ms_unit.id')
            ->leftJoin('tr_contract_invoice','tr_contract_invoice.contr_id',"=",'tr_contract.id')
            ->leftJoin('ms_cost_detail','ms_cost_detail.id',"=",'tr_contract_invoice.costd_id')
            ->where('tr_contract_invoice.invtp_id', 1)
            ->where('ms_cost_detail.costd_ismeter', TRUE)
            ->get();
        echo $unit_kontrak;
        */
        /*
        $lst_meter = TrMeter::where('prdmet_id',$prd_last[0]->id)->get();
                $ct_meter = CutoffHistory::select('unit_id','costd_id','meter_end')
                    ->where('close_date', '>=', $request->input('prdmet_start_date'))
                    ->where('close_date', '<=', $request->input('prdmet_end_date'))
                    ->get();
        $prd_last = TrPeriodMeter::select('id')
                    ->where('tr_period_meter.status', TRUE)
                    ->orderBy('prd_billing_date', 'desc')
                    ->take(1)
                    ->get();
        $lst_meter = TrMeter::select('tr_meter.meter_end','tr_meter.unit_id','tr_meter.costd_id','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin','cutoff_history.meter_end AS cutoff_end')
            ->leftJoin('ms_cost_detail','ms_cost_detail.id',"=",'tr_meter.costd_id')
            ->leftJoin('cutoff_history', function ($join) {
                $join->on('tr_meter.unit_id', '=', 'cutoff_history.unit_id');
                $join->on('tr_meter.costd_id', '=', 'cutoff_history.costd_id');
            })
            ->where('prdmet_id',$prd_last[0]->id)->get();
        
        $ct_meter = CutoffHistory::select('unit_id','costd_id','meter_end')
            ->where('close_date', '>=', '2016-04-01')
            ->where('close_date', '<=', '2016-04-30')
            ->get();
        
        $unit_kontrak = MsUnit::select('ms_unit.id AS unit_id','ms_unit.unit_code','tr_contract.id AS contr_id','tr_contract_invoice.costd_id','ms_cost_detail.costd_rate','ms_cost_detail.costd_rate','costd_admin')
        ->leftJoin('tr_contract','tr_contract.unit_id',"=",'ms_unit.id')
        ->leftJoin('tr_contract_invoice','tr_contract_invoice.contr_id',"=",'tr_contract.id')
        ->leftJoin('ms_cost_detail','ms_cost_detail.id',"=",'tr_contract_invoice.costd_id')
        ->where('tr_contract_invoice.invtp_id', 1)
        ->where('ms_cost_detail.costd_ismeter', TRUE)
        ->get();
        echo $unit_kontrak;
        */
        $ct_meter = CutoffHistory::select('unit_id','costd_id','meter_end')
                        ->where('close_date', '>=', $request->input('meter_start'))
                        ->where('close_date', '<=', $request->input('meter_end'))
                        ->where('unit_id',$unit_kontrak[$i]->unit_id)
                        ->where('costd_id',$unit_kontrak[$i]->costd_id)
                        ->get();
        echo $ct_meter;
    }

    public function editModal(Request $request){
        try{
            $id = $request->id;
            $currentPrd = TrPeriodMeter::find($id);
            $fetch = TrMeter::select('tr_meter.*','ms_cost_detail.costd_rate','tr_contract.contr_code','ms_unit.unit_code','ms_cost_detail.costd_name')
                    ->leftJoin('tr_contract','tr_contract.id',"=",'tr_meter.contr_id')
                    ->leftJoin('ms_cost_detail','ms_cost_detail.id',"=",'tr_meter.costd_id')
                    ->leftJoin('ms_unit','ms_unit.id',"=",'tr_meter.unit_id')
                    ->where('prdmet_id',$id)
                    ->get();

            return view('modal.editmeter', ['meter' => $fetch,'st'=>$currentPrd, 'prd'=>$id]);
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
            return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function update(Request $request){
        try{
        	$id = $request->id;
            $status = TrPeriodMeter::select('status')->where('id',$id)->get();
            if($status[0]->status == FALSE){
                $input = $request->all();
                $input['updated_by'] = Auth::id();
                TrPeriodMeter::find($id)->update($input);
                return TrPeriodMeter::find($id);
            }else{
                return response()->json(['success'=>false,'errorMsg' => "Sorry Meter already Posted"]);
            }
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
        try{
        	$id = $request->id;
            $status = TrPeriodMeter::select('status')->where('id',$id)->get();
            if($status[0]->status == FALSE){
                TrPeriodMeter::destroy($id);
                TrMeter::where('prdmet_id', $id)->delete();
                return response()->json(['success'=>true]);
            }else{
                return response()->json(['success'=>false,'errorMsg' => "Sorry Meter already Posted"]);
            }
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function downloadExcel($type)
    {
        $data = TrMeter::select('tr_contract.contr_code','ms_unit.unit_code','ms_cost_detail.costd_name','tr_meter.meter_start','tr_meter.meter_end')
                    ->leftJoin('tr_contract','tr_contract.id',"=",'tr_meter.contr_id')
                    ->leftJoin('ms_cost_detail','ms_cost_detail.id',"=",'tr_meter.costd_id')
                    ->leftJoin('ms_unit','ms_unit.id',"=",'tr_meter.unit_id')
                    ->where('prdmet_id',$type)
                    ->get()->toArray();
        $tp = 'csv';
        return Excel::create('meter_template', function($excel) use ($data) {
            $excel->sheet('mySheet', function($sheet) use ($data)
            {
                $sheet->fromArray($data);
            });
        })->download($tp);
    }
    public function importExcel2(Request $request)
    {
        if(Input::hasFile('import_file')){
            $path = Input::file('import_file')->getRealPath();
            $data = Excel::load($path, function($reader) {

            })->get();
            $prd = $request->input('prd');
            if(!empty($data) && $data->count()){
                foreach ($data as $key => $value) {
                    $insert[] = ['meter_start' => $value->meter_start, 'meter_end' => $value->meter_end, 'meter_used' => $value->meter_used, 'meter_cost' => $value->meter_cost, 'meter_burden' => $value->meter_burden, 'meter_admin' => $value->meter_admin, 'prdmet_id' => $value->prdmet_id, 'costd_is' => $value->costd_is, 'contr_id' => $value->contr_id];
                }
                if(!empty($insert)){
                    DB::table('tr_meter')->insert($insert);
                    return back();
                }
            }
        }
        return back();
    } 

    public function importExcel(Request $request)
    {
        if(Input::hasFile('import_file')){
            $path = Input::file('import_file')->getRealPath();
            $data = Excel::load($path, function($reader) {

            })->get();
            $prd = $request->input('prd');
            $meter = MsCostDetail::select('id','costd_name')->where('costd_ismeter',TRUE)->get();
            $array_meter=[];
            foreach ($meter as $row) {
                $array_meter[$row->costd_name]= $row->id;
            }

            $unit = MsUnit::select('id','unit_code')->get();
            $array_unit=[];
            foreach ($unit as $row) {
                $array_unit[$row->unit_code]= $row->id;
            }

            if(!empty($data) && $data->count()){
                foreach ($data as $key => $value) {
                    DB::table('tr_meter')
                    ->where('prdmet_id', $prd)
                    ->where('costd_id', $array_meter[$value->costd_name])
                    ->where('unit_id', $array_unit[$value->unit_id])
                    ->update(['meter_end' => $value->meter_end]);
                }
                return back();
            }
        }
        return back();
    } 

}
