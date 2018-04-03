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
use App\Models\MsConfig;
// use App\Models\ListrikAirLog;
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
                $input['prdmet_id'] = 'PRD-'.date('Ymd').'-'.strtoupper(str_random(3));
                $prdstart = date("Y-m-d", strtotime($request->prdmet_start_date));
                $prdend = date("Y-m-d", strtotime($request->prdmet_end_date));
                $input['prdmet_start_date'] = $prdstart;
                $input['prdmet_end_date'] = $prdend;
                $input['prd_billing_date'] = date("Y-m-d", strtotime($request->prd_billing_date));
                $input['created_by'] = Auth::id();
                $input['updated_by'] = Auth::id();
                $input['status'] = FALSE;

                // checking period meter suda ada ap blum
                // prd_billing_date itu buat patokan nentuin bulannya tagihan
                $today_month = date("m", strtotime($request->input('prd_billing_date')));
                $today_year = date("Y", strtotime($request->input('prd_billing_date')));
                $prdmeterExist = TrPeriodMeter::where('status', true)
                        ->where(DB::raw("EXTRACT(MONTH FROM prd_billing_date)"), $today_month)
                        ->where(DB::raw("EXTRACT(YEAR FROM prd_billing_date)"), $today_year)
                        ->first();
                if($prdmeterExist){
                    return response()->json(['errorMsg' => 'Periode Meter Sudah Pernah Dibuat dan Di Approve']);
                }else{
                    // ada maupun tidak asalkan blum approved tetap create baru
                    $newPeriodMeter =  TrPeriodMeter::create($input);
                    // get semua confirmed contract yg available dan buat meteran
                    $confirmedContract = TrContract::where('contr_status','confirmed')->where('contr_enddate', '>=', date('Y-m-d H:i:s'))->get();
                    // last month valid period meter
                    $last_month_date = strtotime("last month", strtotime($request->input('prd_billing_date')));
                    $prd_last = TrPeriodMeter::where('status', true)
                                ->where(DB::raw("EXTRACT(MONTH FROM prd_billing_date)"), date("m",$last_month_date))
                                ->where(DB::raw("EXTRACT(YEAR FROM prd_billing_date)"), date("Y",$last_month_date))
                                ->orderBy('created_at', 'desc')
                                ->first();
                    foreach ($confirmedContract as $ctr) {
                        // every contract invoice nya ctr yg punya invtp 1 dibuatkan meteran nya
                        foreach($ctr->contractInv->where('invtp_id',1) as $ctrInv){
                                // extract last meter value
                                $meter_last = false;
                                if($prd_last){
                                    $meter_last = TrMeter::select('meter_end','costd_id','unit_id')
                                        ->where('unit_id',$ctr->unit_id)
                                        ->where('costd_id',$ctrInv->costd_id)
                                        ->where('prdmet_id',$prd_last->id)
                                        ->first();
                                }

                                $m_end = 0;
                                if($meter_last) $m_end = $meter_last->meter_end;
                                $meterInput = [
                                        'meter_start'=> $m_end,
                                        'meter_end'=> 0,
                                        'meter_used'=> 0,
                                        'meter_cost'=> 0,
                                        'meter_burden'=> $ctrInv->costdetail->costd_burden,
                                        'meter_admin'=> $ctrInv->costdetail->costd_admin,
                                        'costd_id'=>  $ctrInv->costd_id,
                                        'contr_id'=> $ctr->id,
                                        'prdmet_id'=> $newPeriodMeter->id,
                                        'unit_id'=>$ctr->unit_id
                                    ];
                                $newMeter = TrMeter::create($meterInput);

                                // Jika contract tenant diputus dlm jangka waktu prd meter saat ini ambil akhir meter sebelumnya dr tabel cutoff utk dijadikan meter start di next meternya
                                $ct_meter = CutoffHistory::select('unit_id','costd_id','meter_end')
                                    ->where('close_date', '>=', $input['prdmet_start_date'])
                                    ->where('close_date', '<=', $input['prdmet_end_date'])
                                    ->where('unit_id',$ctr->unit_id)
                                    ->where('costd_id',$ctrInv->costd_id)
                                    ->orderBy('created_at','desc')
                                    ->first();
                                if($ct_meter){
                                    $newMeter->meter_start = $ct_meter->meter_end;
                                    $newMeter->save();
                                }
                        }
                    }

                }

            }else{
                return response()->json(['errorMsg' => 'Maaf ada Unit yang tidak ada kontraknya, Harap buat dulu kontrak (yg sudah dikonfirmasi) untuk setiap unit yg terdaftar']);
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
            $electric = TrMeter::select('tr_meter.*','ms_cost_detail.costd_rate','tr_contract.contr_code','ms_unit.unit_code','ms_cost_detail.costd_name','ms_cost_detail.daya','ms_cost_detail.cost_id','ms_cost_detail.value_type','ms_cost_detail.percentage','ms_cost_detail.grossup_pph')
                    ->leftJoin('tr_contract','tr_contract.id',"=",'tr_meter.contr_id')
                    ->leftJoin('ms_cost_detail','ms_cost_detail.id',"=",'tr_meter.costd_id')
                    ->leftJoin('ms_unit','ms_unit.id',"=",'tr_meter.unit_id')
                    ->where('prdmet_id',$id)
                    ->where('ms_cost_detail.cost_id',1)
                    ->where('tr_contract.contr_startdate','<=',$currentPrd->prdmet_end_date)
                    ->where('tr_contract.contr_status','confirmed')
                    ->orderBy('ms_unit.unit_code','asc')
                    ->orderBy('ms_cost_detail.costd_name','asc')
                    ->get();
             $water = TrMeter::select('tr_meter.*','ms_cost_detail.costd_rate','tr_contract.contr_code','ms_unit.unit_code','ms_cost_detail.costd_name','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin','ms_cost_detail.cost_id','ms_cost_detail.daya')
                    ->leftJoin('tr_contract','tr_contract.id',"=",'tr_meter.contr_id')
                    ->leftJoin('ms_cost_detail','ms_cost_detail.id',"=",'tr_meter.costd_id')
                    ->leftJoin('ms_unit','ms_unit.id',"=",'tr_meter.unit_id')
                    ->where('prdmet_id',$id)
                    ->where('ms_cost_detail.cost_id',2)
                    ->where('tr_contract.contr_startdate','<=',$currentPrd->prdmet_end_date)
                    ->where('tr_contract.contr_status','confirmed')
                    ->orderBy('ms_unit.unit_code','asc')
                    ->orderBy('ms_cost_detail.costd_name','asc')
                    ->get();

            return view('modal.editmeter', ['listrik' => $electric,'air' => $water,'st'=>$currentPrd, 'prd'=>$id]);
        }catch(\Exception $e){
             return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }
    //KHUSUS CITYLOFT
    // public function meterdetailUpdate(Request $request){
    //     $id = $request->input('tr_meter_id');
    //     // echo count($id); die();
    //     $meter_end = $request->input('meter_end');
    //     $meter_start = $request->input('meter_start');
    //     $meter_rate = $request->input('meter_rate');
    //     $meter_burden = $request->input('meter_burden');
    //     $meter_admin = $request->input('meter_admin');
    //     $cost_id = $request->input('cost_id');
    //     $daya = $request->input('daya');
    //     $period_meter_id = $request->input('prd_id');
    //     $trmeter = TrPeriodMeter::find($period_meter_id);
    //     $log_period = date('Y-m-01',strtotime($trmeter->prdmet_end_date));

    //     try{
    //         $totalUsedListrik = 0;
    //         $totalUsedAir = 0;
    //         DB::transaction(function () use($id, $meter_end, $meter_start, $meter_rate, $meter_burden, $meter_admin, $cost_id, $daya, $log_period, $totalUsedAir, $totalUsedListrik){
    //             foreach ($id as $key => $value) {
    //                 $meter_used = ($meter_end[$key] - $meter_start[$key]);
    //                 // echo "test: ".$key."\n";
    //                 if($cost_id[$key] == '1'){
    //                     $totalUsedListrik += $meter_used;
    //                     //CEK MIN 40 JAM PEMAKAIAN LISTRIK
    //                     $min = 40 * $daya[$key];
    //                     $elec_cost = $meter_used *  $meter_rate[$key];
    //                     if($meter_used > $min){
    //                         $meter_cost = $elec_cost;
    //                     }else{
    //                         $meter_cost = $min * $meter_rate[$key];
    //                     }

    //                     $bpju = (0.03 * $meter_cost);
    //                     $total = $meter_cost + $bpju;
    //                     $input = [
    //                         'meter_end' => $meter_end[$key],
    //                         'meter_used' => $meter_used,
    //                         'meter_cost' => $meter_cost,
    //                         'meter_admin' => $meter_admin,
    //                         'other_cost' => $bpju,
    //                         'total' => $total
    //                     ];
    //                 }else{
    //                     // echo $meter_used; die();
    //                     $totalUsedAir += $meter_used;
    //                     $bpju = 0;
    //                     $meter_cost = $meter_used * $meter_rate[$key];
    //                     $total = $meter_cost + $meter_burden[$key] + $meter_admin[$key] + $bpju;
    //                     $input = [
    //                         'meter_end' => $meter_end[$key],
    //                         'meter_used' => $meter_used,
    //                         'meter_cost' => $meter_cost,
    //                         'other_cost' => $bpju,
    //                         'total' => $total
    //                     ];
    //                 }

    //                 TrMeter::find($id[$key])->update($input);
    //             }
    //             // echo "Total listrik : ".$totalUsedListrik;
    //             // echo ", Total air : ".$totalUsedAir;
    //             // $log = ListrikAirLog::where('periode',$log_period)->first();
    //             // if(!$log){
    //             //     $log = new ListrikAirLog;
    //             //     $log->periode = $log_period;
    //             // }
    //             // $log->listrik = $totalUsedListrik;
    //             // $log->air = $totalUsedAir;
    //             // $log->save();
    //         });
    //     }catch(\Exception $e){
    //         return response()->json(['errorMsg' => $e->getMessage()]);
    //     }
    //     return ['status' => 1, 'message' => 'Update Success', 'listrik' => $totalUsedListrik, 'air' => $totalUsedAir];
    // }

    // ORIGINAL
    public function meterdetailUpdate(Request $request){
        $id = $request->input('tr_meter_id');
        $meter_end = $request->input('meter_end');
        $meter_start = $request->input('meter_start');
        $meter_rate = $request->input('meter_rate');
        $meter_burden = $request->input('meter_burden');
        $meter_admin = $request->input('meter_admin');
        $cost_id = $request->input('cost_id');
        $daya = $request->input('daya');
        $value_type = $request->input('value_type');
        $percentage = $request->input('percentage');
        $grossup = $request->input('grossup');
        try{
            DB::transaction(function () use($id, $meter_end, $meter_start, $meter_rate, $meter_burden, $meter_admin, $cost_id, $daya, $value_type, $percentage, $grossup){
                foreach ($id as $key => $value) {
                    $meter_used = ($meter_end[$key] - $meter_start[$key]);
                    if($cost_id[$key] == '1'){
                        //CEK MIN 40 JAM PEMAKAIAN LISTRIK
                        $min = 40 * $daya[$key] * $meter_rate[$key];
                        $elec_cost = $meter_used *  $meter_rate[$key];
                        if($elec_cost > $min){
                            $meter_cost = $elec_cost;
                        }else{
                            $meter_cost = $min;
                        }
                        $bpju = (0.03 * $meter_cost);
                        // echo "Meter cost $meter_cost<br>";
                        // echo "BPJU $bpju<br>";
                        $subtotal = $meter_cost + $bpju;
                        // echo "Subtotal $subtotal<br>";
                        // Tambah public area
                        if($value_type[$key] == 'percent'){
                            $public_area = $percentage[$key] / 100 * $subtotal;
                        }else{
                            $public_area = $percentage[$key];
                            if(empty($public_area)) $public_area = 0;
                        }
                        // echo "Public Area $public_area<br>";
                        $total = $subtotal + $meter_admin[$key] + $public_area;
                        // echo "Total before grossup $total<br>";
                        if(!empty($grossup[$key])){
                            $grossup_total = $total / 0.9 * 0.1;
                            // echo "Grossup $grossup_total<br>";
                            $total += $grossup_total;
                        }
                        // echo "Grandtotal $total<br>";
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
                $prdstart = date("Y-m-d", strtotime($request->prdmet_start_date));
                $prdend = date("Y-m-d", strtotime($request->prdmet_end_date));
                $input['prdmet_start_date'] = $prdstart;
                $input['prdmet_end_date'] = $prdend;
                $input['prd_billing_date'] = date("Y-m-d", strtotime($request->prd_billing_date));
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

/*
    //ORIGINAL
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
*/
    public function importExcel(Request $request)
    {
        if(Input::hasFile('import_file')){
            $path = Input::file('import_file')->getRealPath();
            $data = Excel::load($path, function($reader) {

            })->get();
            $prd = $request->input('prd');
            $ppju = MsConfig::where('name','ppju')->first();

            foreach ($data as $key => $value) {
                if(!empty(@$value->unit)){
                    $meter_used = $value->end - $value->start;
                    $costdt = MsCostDetail::where('costd_name',$value->cost)->first();
                    if(!$costdt) return redirect()->back()->with('error','Component Billing $value->cost uploaded does not exists, Pls download again upload template and reupload');
                    $unit = MsUnit::where('unit_code',$value->unit)->first();
                    if(!$unit) return redirect()->back()->with('error','Unit $value->unit uploaded does not exists, Pls download again upload template and reupload');
                    $meter_row = TrMeter::where('prdmet_id', $prd)
                                ->where('costd_id', $costdt->id)
                                ->where('unit_id', $unit->id);

                    // LISTRIK
                    if ($costdt->costitem->id == 1){
                        //CEK MIN 40 JAM PEMAKAIAN LISTRIK
                        $min = 40 * $value->daya * $costdt->costd_rate;
                        $elec_cost = $meter_used *  $costdt->costd_rate;
                        if($elec_cost > $min){
                            $meter_cost = $elec_cost;
                        }else{
                            $meter_cost = $min;
                        }
                        $bpju = ($ppju->value/100 * $meter_cost);
                        // echo "Meter cost $meter_cost<br>";
                        // echo "BPJU $bpju<br>";
                        $subtotal = $meter_cost + $bpju;
                        // echo "Subtotal $subtotal<br>";
                        // Tambah public area
                        if($costdt->value_type == 'percent'){
                            $public_area = $costdt->percentage / 100 * $subtotal;
                        }else{
                            $public_area = $costdt->percentage;
                            if(empty($public_area)) $public_area = 0;
                        }
                        // echo "Public Area $public_area<br>";
                        $total = $subtotal + $costdt->costd_admin + $public_area;
                        // echo "Total before grossup $total<br>";
                        if(!empty($costdt->grossup_pph)){
                            $grossup_total = $total / 0.9 * 0.1;
                            // echo "Grossup $grossup_total<br>";
                            $total += $grossup_total;
                        }
                        // echo "Grandtotal $total<br>";

                        $meter_row->update(['meter_end' => $value->end,'meter_used' => $meter_used,'meter_cost' => $meter_cost,'meter_admin'=>$costdt->costd_admin,'other_cost'=>$bpju,'total'=>$total]);
                    }else{
                        // AIR
                        $meter_cost = $meter_used * $costdt->costd_rate;
                        $total =  $meter_cost + $costdt->costd_burden + $costdt->costd_admin;
                        $meter_row->update(['meter_end' => $value->end,'meter_used' => $meter_used,'meter_cost' => $meter_cost,'other_cost'=>0,'total'=>$total]);
                    }
                }
            }
            Session::flash('msg', 'Upload Success.');
            return back();
        }
        return back();
    }

}
