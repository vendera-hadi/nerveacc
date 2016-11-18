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
        $input = $request->all();
        $count = TrPeriodMeter::count();
        $ms = $count+1;
        $input['prdmet_id'] = 'PRD-'.date('Ymd').'-'.$ms;
        $input['created_by'] = Auth::id();
        $input['updated_by'] = Auth::id();
        try{
            DB::transaction(function () use($input, $request) {
                $meter = TrPeriodMeter::select('id','prdmet_id','prd_billing_date')->where('status',true)->orderBy('prd_billing_date','desc')->limit(1)->get();
                if((count($meter) == 0) || ($meter[0]->prd_billing_date > $request->prd_billing_date)){
                    $last_id =  TrPeriodMeter::create($input);
                    $insertedId = $last_id->id;
                    $tanggal = $request->input('prd_billing_date');
                    if(count($meter) == 0){
                        /*
                        $kontrak = TrContract::select('tr_contract.id','tr_contract_invoice.costd_is','ms_cost_detail.costd_name','ms_cost_detail.costd_unit','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin')
                        ->join('tr_contract_invoice',\DB::raw('tr_contract_invoice.contr_id::integer'),"=",\DB::raw('tr_contract.id::integer'))
                        ->join('ms_cost_detail',\DB::raw('tr_contract_invoice.costd_is::integer'),"=",\DB::raw('ms_cost_detail.id::integer'))
                        ->where('tr_contract.contr_startdate', '<=', $tanggal)
                        ->where('tr_contract.contr_enddate', '>=', $tanggal)
                        ->where('tr_contract.contr_status','confirmed')
                        ->where('tr_contract.contr_terminate_date',NULL)
                        ->where('ms_cost_detail.costd_ismeter',TRUE)
                        ->groupBy('tr_contract.id','tr_contract_invoice.costd_is','ms_cost_detail.costd_name','ms_cost_detail.costd_unit','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin')
                        ->get();
                        */
                        $kontrak = MsUnit::select('id')->get();
                        $cost = MsCostDetail::where('costd_ismeter',TRUE)->get();
                        for($i=0; $i<count($kontrak); $i++){
                            for($j=0; $j<count($cost); $j++){
                                $kontrak_unit = TrContract::where('unit_id',$kontrak[$i]->id)
                                ->where('contr_startdate', '<=', $tanggal)
                                ->where('contr_enddate', '>=', $tanggal)
                                ->where('contr_status','confirmed')
                                ->where('contr_terminate_date',NULL)->get();
                                if(count($kontrak_unit)>0){
                                    $kontrak_id = $kontrak_unit[0]->id;
                                }else{
                                    $kontrak_id = NULL;
                                }
                                $inputs = [ 
                                    'meter_start'=> '0',
                                    'meter_end'=> '0',
                                    'meter_used'=> '0',
                                    'meter_cost'=> '0',
                                    'meter_burden'=> $cost[$j]->costd_burden,
                                    'meter_admin'=> $cost[$j]->costd_admin,
                                    'costd_is'=>  $cost[$j]->id,
                                    'prdmet_id'=> $insertedId,
                                    'contr_id'=> $kontrak_id,
                                    'unit_id'=>$kontrak[$i]->id
                                ];
                                $costd_is = TrMeter::create($inputs);
                            }
                        }     
                    }else{
                        /*
                        $kontrak = TrContract::select('tr_contract.id','tr_contract_invoice.costd_is','ms_cost_detail.costd_name','ms_cost_detail.costd_unit','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin')
                        ->join('tr_contract_invoice',\DB::raw('tr_contract_invoice.contr_id::integer'),"=",\DB::raw('tr_contract.id::integer'))
                        ->join('ms_cost_detail',\DB::raw('tr_contract_invoice.costd_is::integer'),"=",\DB::raw('ms_cost_detail.id::integer'))
                        ->where('tr_contract.contr_startdate', '<=', $tanggal)
                        ->where('tr_contract.contr_enddate', '>=', $tanggal)
                        ->where('tr_contract.contr_status','confirmed')
                        ->where('tr_contract.contr_terminate_date',NULL)
                        ->where('ms_cost_detail.costd_ismeter',TRUE)
                        ->groupBy('tr_contract.id','tr_contract_invoice.costd_is','ms_cost_detail.costd_name','ms_cost_detail.costd_unit','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin')
                        ->get();
                        */
                        $kontrak = MsUnit::select('id')->get();
                        $cost = MsCostDetail::where('costd_ismeter',TRUE)->get();
                        $last_meter = TrMeter::select('meter_end','costd_is','contr_id','unit_id')->where('prdmet_id',$meter[0]->id)->get();
                        for($i=0; $i<count($kontrak); $i++){
                            for($j=0; $j<count($cost); $j++){
                                $ls = 0;
                                for($k=0; $k<count($last_meter); $k++){
                                    if(($last_meter[$k]->unit_id === $kontrak[$i]->id) && ($last_meter[$k]->costd_is === $cost[$j]->id)){
                                        $ls = $last_meter[$k]->meter_end;
                                    }
                                }
                                $kontrak_unit = TrContract::where('unit_id',$kontrak[$i]->id)
                                ->where('contr_startdate', '<=', $tanggal)
                                ->where('contr_enddate', '>=', $tanggal)
                                ->where('contr_status','confirmed')
                                ->where('contr_terminate_date',NULL)->get();
                                if(count($kontrak_unit)>0){
                                    $kontrak_id = $kontrak_unit[0]->id;
                                }else{
                                    $kontrak_id = NULL;
                                }
                                $inputs = [ 
                                    'meter_start'=> $ls,
                                    'meter_end'=> '0',
                                    'meter_used'=> '0',
                                    'meter_cost'=> '0',
                                    'meter_burden'=> $cost[$j]->costd_burden,
                                    'meter_admin'=> $cost[$j]->costd_admin,
                                    'costd_is'=>  $cost[$j]->id,
                                    'contr_id' => $kontrak_id,
                                    'prdmet_id'=> $insertedId,
                                    'unit_id'=>$kontrak[$i]->id
                                ];
                                $costd_is = TrMeter::create($inputs);
                            }
                        }
                    }
                }else{
                    return response()->json(['success'=>false,'errorMsg' => "Periode Billing Cannot Backdate"]);
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
            $fetch = TrMeter::select('ms_unit.unit_code','tr_contract.contr_no','ms_cost_detail.costd_name','tr_contract.contr_code','tr_meter.*','ms_cost_detail.costd_rate')
                    ->leftJoin('tr_contract',\DB::raw('tr_contract.id::integer'),"=",\DB::raw('tr_meter.contr_id::integer'))
                    ->join('ms_cost_detail',\DB::raw('tr_meter.costd_is::integer'),"=",\DB::raw('ms_cost_detail.id::integer'))
                    ->join('ms_unit',\DB::raw('tr_meter.unit_id::integer'),"=",\DB::raw('ms_unit.id::integer'))
                    ->where('tr_meter.prdmet_id', $prdmet)
                    ->orderBy('tr_meter.id','ASC')
                    ->get();
            $status = TrPeriodMeter::select('status')->where('id',$prdmet)->get();
            return view('modal.editmeter', ['meter' => $fetch,'st'=>$status, 'prd'=>$prdmet]);
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
        $data = TrMeter::get()->toArray();
        return Excel::create('itsolutionstuff_example', function($excel) use ($data) {
            $excel->sheet('mySheet', function($sheet) use ($data)
            {
                $sheet->fromArray($data);
            });
        })->download($type);
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
                    ->where('costd_is', $array_meter[$value->costd_is])
                    ->where('unit_id', $array_unit[$value->unit_id])
                    ->update(['meter_end' => $value->meter_end]);
                }
                return back();
            }
        }
        return back();
    } 

}
