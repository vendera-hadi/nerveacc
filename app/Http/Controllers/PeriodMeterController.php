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
use Session;
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
                $temp['prdmet_start_date'] = date("d-m-Y", strtotime($value->prdmet_start_date));
                $temp['prdmet_end_date'] = date("d-m-Y", strtotime($value->prdmet_end_date));
                $temp['prd_billing_date'] = date("d-m-Y", strtotime($value->prd_billing_date));
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
            $check_unit = MsUnit::count();
            $check_unit_contract = TrContract::join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')->where('tr_contract.contr_status','confirmed')->count();
            if($check_unit_contract >= $check_unit){
                $input = $request->all();
                $count = TrPeriodMeter::count();
                $ms = $count+1;
                $input['prdmet_id'] = 'PRD-'.date('Ymd').'-'.$ms;
                $prdstart = date("Y-m-d", strtotime($request->prdmet_start_date));
                $prdend = date("Y-m-d", strtotime($request->prdmet_end_date));
                $input['prdmet_start_date'] = $prdstart;
                $input['prdmet_end_date'] = $prdend;
                $input['prd_billing_date'] = date("Y-m-d", strtotime($request->prd_billing_date));
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
                        return ['status' => 1, 'message' => 'Periode Meter Sudah Pernah Dibuat dan Sudah Di Approve'];
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
            }else{
                return ['status' => 1, 'message' => 'Maaf ada Unit yang tidak ada kontraknya'];
            }
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }    
        return ['status' => 1, 'message' => 'Insert Success'];          
    }

    public function editModal(Request $request){
        try{
            $id = $request->id;
            $currentPrd = TrPeriodMeter::find($id);
            $electric = TrMeter::select('tr_meter.*','ms_cost_detail.costd_rate','tr_contract.contr_code','ms_unit.unit_code','ms_cost_detail.costd_name','ms_cost_detail.daya','ms_cost_detail.cost_id')
                    ->leftJoin('tr_contract','tr_contract.id',"=",'tr_meter.contr_id')
                    ->leftJoin('ms_cost_detail','ms_cost_detail.id',"=",'tr_meter.costd_id')
                    ->leftJoin('ms_unit','ms_unit.id',"=",'tr_meter.unit_id')
                    ->where('prdmet_id',$id)
                    ->where('ms_cost_detail.cost_id',1)
                    ->orderBy('ms_unit.unit_code','asc')
                    ->orderBy('ms_cost_detail.costd_name','asc')
                    ->get();
             $water = TrMeter::select('tr_meter.*','ms_cost_detail.costd_rate','tr_contract.contr_code','ms_unit.unit_code','ms_cost_detail.costd_name','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin','ms_cost_detail.cost_id','ms_cost_detail.daya')
                    ->leftJoin('tr_contract','tr_contract.id',"=",'tr_meter.contr_id')
                    ->leftJoin('ms_cost_detail','ms_cost_detail.id',"=",'tr_meter.costd_id')
                    ->leftJoin('ms_unit','ms_unit.id',"=",'tr_meter.unit_id')
                    ->where('prdmet_id',$id)
                    ->where('ms_cost_detail.cost_id',2)
                    ->orderBy('ms_unit.unit_code','asc')
                    ->orderBy('ms_cost_detail.costd_name','asc')
                    ->get();

            return view('modal.editmeter', ['listrik' => $electric,'air' => $water,'st'=>$currentPrd, 'prd'=>$id]);
        }catch(\Exception $e){
             return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }
    //KHUSUS CITYLOFT
    public function meterdetailUpdate(Request $request){
        $id = $request->input('tr_meter_id');
        $meter_end = $request->input('meter_end');
        $meter_start = $request->input('meter_start');
        $meter_rate = $request->input('meter_rate');
        $meter_burden = $request->input('meter_burden');
        $meter_admin = $request->input('meter_admin');
        $cost_id = $request->input('cost_id');
        $daya = $request->input('daya');
        try{
            DB::transaction(function () use($id, $meter_end, $meter_start, $meter_rate, $meter_burden, $meter_admin, $cost_id, $daya){
                foreach ($id as $key => $value) {
                    $meter_used = ($meter_end[$key] - $meter_start[$key]);
                    if($cost_id[$key] == '1'){
                        //CEK MIN 40 JAM PEMAKAIAN LISTRIK
                        $min = 40 * ($daya[$key]/1000) * $meter_rate[$key];
                        $elec_cost = $meter_used *  $meter_rate[$key];
                        if($elec_cost > $min){
                            $meter_cost = $elec_cost;
                        }else{
                            $meter_cost = $min;
                        }
                        $bpju = (0.03 * $meter_cost);
                        $subtotal = ($meter_cost + $bpju);
                        $biaya_admin = 10/100 * $subtotal;
                        $total = $subtotal + $biaya_admin;
                        $input = [
                            'meter_end' => $meter_end[$key],
                            'meter_used' => $meter_used,
                            'meter_cost' => $meter_cost,
                            'meter_admin' =>$biaya_admin,
                            'other_cost' => $bpju,
                            'total' => $total
                        ];
                    }else{
                        $bpju = 0;
                        $meter_cost = $meter_used * $meter_rate[$key];
                        $total = $meter_cost + $meter_burden[$key] + $meter_admin[$key] + $bpju;
                        $input = [
                            'meter_end' => $meter_end[$key],
                            'meter_used' => $meter_used,
                            'meter_cost' => $meter_cost,
                            'other_cost' => $bpju,
                            'total' => $total
                        ];
                    }
                    TrMeter::find($id[$key])->update($input);
                }
            });
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
        return ['status' => 1, 'message' => 'Update Success'];
    }
    /*
    ORIGINAL
    public function meterdetailUpdate(Request $request){
        $id = $request->input('tr_meter_id');
        $meter_end = $request->input('meter_end');
        $meter_start = $request->input('meter_start');
        $meter_rate = $request->input('meter_rate');
        $meter_burden = $request->input('meter_burden');
        $meter_admin = $request->input('meter_admin');
        $cost_id = $request->input('cost_id');
        $daya = $request->input('daya');
        try{
            DB::transaction(function () use($id, $meter_end, $meter_start, $meter_rate, $meter_burden, $meter_admin, $cost_id, $daya){
                foreach ($id as $key => $value) {
                    $meter_used = ($meter_end[$key] - $meter_start[$key]);
                    if($cost_id[$key] == '1'){
                        //CEK MIN 40 JAM PEMAKAIAN LISTRIK
                        $min = 40 * ($daya[$key]/1000) * $meter_rate[$key];
                        $elec_cost = $meter_used *  $meter_rate[$key];
                        if($elec_cost > $min){
                            $meter_cost = $elec_cost;
                        }else{
                            $meter_cost = $min;
                        }
                        $bpju = (0.03 * $meter_cost);
                        $total = ($meter_cost + $bpju);
                    }else{
                        $bpju = 0;
                        $meter_cost = $meter_used * $meter_rate[$key];
                        $total = $meter_cost + $meter_burden[$key] + $meter_admin[$key] + $bpju;
                    }
                    $input = [
                        'meter_end' => $meter_end[$key],
                        'meter_used' => $meter_used,
                        'meter_cost' => $meter_cost,
                        'other_cost' => $bpju,
                        'total' => $total
                    ];
                    TrMeter::find($id[$key])->update($input);
                }
            });
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
        return ['status' => 1, 'message' => 'Update Success'];
    }
    */
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

    public function unposting(Request $request){
        try{
            $id = $request->id;
            $input['status'] = false;
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

    public function downloadExcel($type,$cost)
    {
        if($cost == 1){
            $data = TrMeter::select('ms_unit.unit_code AS UNIT','ms_cost_detail.costd_name AS COST','ms_cost_detail.daya AS DAYA','tr_meter.meter_start AS START','tr_meter.meter_end AS END')
                        ->leftJoin('tr_contract','tr_contract.id',"=",'tr_meter.contr_id')
                        ->leftJoin('ms_cost_detail','ms_cost_detail.id',"=",'tr_meter.costd_id')
                        ->leftJoin('ms_unit','ms_unit.id',"=",'tr_meter.unit_id')
                        ->leftJoin('ms_floor','ms_unit.floor_id',"=",'ms_floor.id')
                        ->where('tr_meter.prdmet_id',$type)
                        ->where('ms_cost_detail.cost_id',$cost)
                        ->orderBy('ms_unit.unit_code','asc')
                        ->get()->toArray();
            $border = 'A1:E';
        }else{
            $data = TrMeter::select('ms_unit.unit_code AS UNIT','ms_cost_detail.costd_name AS COST','tr_meter.meter_start AS START','tr_meter.meter_end AS END')
                        ->leftJoin('tr_contract','tr_contract.id',"=",'tr_meter.contr_id')
                        ->leftJoin('ms_cost_detail','ms_cost_detail.id',"=",'tr_meter.costd_id')
                        ->leftJoin('ms_unit','ms_unit.id',"=",'tr_meter.unit_id')
                        ->leftJoin('ms_floor','ms_unit.floor_id',"=",'ms_floor.id')
                        ->where('tr_meter.prdmet_id',$type)
                        ->where('ms_cost_detail.cost_id',$cost)
                        ->orderBy('ms_unit.unit_code','asc')
                        ->get()->toArray();
            $border = 'A1:D';
        }
        $tp = 'xls';
        return Excel::create('meter_template', function($excel) use ($data,$border) {
            $excel->sheet('Unit Meter', function($sheet) use ($data,$border)
            {
                $total = count($data)+1;
                $sheet->setBorder($border.$total, 'thin');
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
            $meter = MsCostDetail::select('id','cost_id','daya','costd_name','costd_rate','costd_burden','costd_admin')->where('costd_ismeter',TRUE)->get();
            $array_meter=[];
            $array_rate =[];
            foreach ($meter as $row) {
                $array_meter[$row->costd_name]= $row->id;
                $array_rate[$row->daya]= $row->costd_rate.'~'.$row->costd_burden.'~'.$row->costd_admin;
            }

            $unit = MsUnit::select('id','unit_code')->get();
            $array_unit=[];
            foreach ($unit as $row) {
                $array_unit[$row->unit_code]= $row->id;
            }

            if(!empty($data) && $data->count()){
                foreach ($data as $key => $value) {
                    if(!empty(@$value->unit)){
                        $meter_used = ($value->end - $value->start);
                        $formula = explode('~', $array_rate[$value->daya]);

                        if (strpos($value->cost, 'ELECTRICITY') !== false) {
                            //CEK MIN 40 JAM PEMAKAIAN LISTRIK
                            $min = 40 * ($value->daya/1000) * $formula[0];
                            $elec_cost = $meter_used * $formula[0];
                            if($elec_cost > $min){
                                $meter_cost = $elec_cost;
                            }else{
                                $meter_cost = $min;
                            }
                            $bpju = (0.03 * $meter_cost);
                            $total = $meter_cost + $bpju;
                        }else{
                            $bpju = 0;
                            $formula = explode('~', $array_rate[0]);
                            $meter_cost = $meter_used * $formula[0];
                            $total =  $meter_cost + $formula[1] + $formula[2];
                        }
                        
                        DB::table('tr_meter')
                        ->where('prdmet_id', $prd)
                        ->where('costd_id', $array_meter[$value->cost])
                        ->where('unit_id', $array_unit[$value->unit])
                        ->update(['meter_end' => $value->end,'meter_used' => $meter_used,'meter_cost' => $meter_cost,'other_cost'=>$bpju,'total'=>$total]);
                    }
                }
                Session::flash('msg', 'Upload Success.');
                return back();
            }
        }
        return back();
    } 

}
