<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\TrContract;
use App\Models\MsUnit;
use App\Models\MsUnitType;
use App\Models\MsUnitOwner;
use App\Models\MsTenant;
use App\Models\User;
use App\Models\MsFloor;
use App\Models\MsVirtualAccount;
use DB;

class UnitController extends Controller
{
    public function index(){
        $data['floors'] = MsFloor::all();
        $data['unittypes'] = MsUnitType::all();
        return view('unit', $data);
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
            $count = MsUnit::count();
            $fetch = MsUnit::select('ms_unit.*','ms_unit_type.untype_name','ms_floor.floor_name','ms_tenant.tenan_name')->join('ms_unit_type',\DB::raw('ms_unit.untype_id::integer'),"=",\DB::raw('ms_unit_type.id::integer'))->join('ms_floor',\DB::raw('ms_unit.floor_id::integer'),"=",\DB::raw('ms_floor.id::integer'))
                    ->leftJoin('ms_unit_owner', 'ms_unit.id','=','ms_unit_owner.unit_id')
                    ->leftJoin('ms_tenant', 'ms_tenant.id', '=', 'ms_unit_owner.tenan_id');
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
                    if($filter->field == 'unit_isactive'){
                        if(strtolower($filter->value) == "yes") $filter->value = "true";
                        else $filter->value = "false";
                    }
                    // end special condition
                    if($op == 'like') $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                    else $fetch = $fetch->where($filter->field, $op, $filter->value);
                }
            }
            $count = $fetch->count();
            if(!empty($sort)){ $fetch = $fetch->orderBy($sort,$order); }else{ $fetch = $fetch->orderBy('ms_unit.unit_code','asc'); }
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['unit_id'] = $value->unit_id;
                $temp['unit_code'] = $value->unit_code;
                $temp['unit_name'] = $value->unit_name;
                $temp['unit_sqrt'] = $value->unit_sqrt." m2";
                $temp['unit_virtual_accn'] = $value->virtual_account;
                $temp['unit_isactive'] = $value->unit_isactive;
                $temp['untype_name'] = $value->untype_name;
                $temp['floor_name'] = $value->floor_name;
                $temp['floor_id'] = $value->floor_id;
                $temp['untype_id'] = $value->untype_id;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['va_utilities'] = $value->va_utilities;
                $temp['va_maintenance'] = $value->va_maintenance;
                $temp['unit_isactive'] = !empty($value->unit_isactive) ? 'yes' : 'no';
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

    public function getAll(){
        try{
            $all = MsUnit::all();
            $result = [];
            if(count($all) > 0){
                foreach ($all as $value) {
                    $result[] = ['id'=>$value->id, 'text'=>$value->unit_name];
                }
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function getOptions(){
        try{
            $all = MsUnitType::all();
            $result = [];
            if(count($all) > 0){
                foreach ($all as $value) {
                    $result[] = ['id'=>$value->id, 'text'=>$value->untype_name];
                }
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function fopt(){
        try{
            $all = MsFloor::all();
            $result = [];
            if(count($all) > 0){
                foreach ($all as $value) {
                    $result[] = ['id'=>$value->id, 'text'=>$value->floor_name];
                }
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function insert(Request $request){
        try{
            $input = $request->all();
            DB::transaction(function () use($request){
                // unit
                $unit = MsUnit::create([
                        'unit_code' => @$request->unit_code,
                        'unit_name' => @$request->unit_code,
                        'unit_sqrt' => @$request->unit_sqrt,
                        'unit_virtual_accn' => 0,
                        'virtual_account' => @$request->virtual_account,
                        'va_maintenance' => @$request->va_maintenance,
                        'va_utilities' => @$request->va_utilities,
                        'meter_air' => @$request->meter_air,
                        'meter_listrik' => @$request->meter_listrik,
                        'floor_id' => @$request->floor_id,
                        'untype_id' => @$request->untype_id,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'unit_isavailable' => 1,
                        'unit_isactive' => 1
                    ]);

                if(empty(@$request->owner)){
                    $tenant = MsTenant::create([
                            'tenan_code' => "TN".date('ymdhis'),
                            'tenan_name' => @$request->tenan_name,
                            'tenan_email' => @$request->tenan_email,
                            'tenan_idno' => @$request->tenan_idno,
                            'tenan_phone' => @$request->tenan_phone,
                            'tenan_fax' => @$request->tenan_fax,
                            'tenan_address' => @$request->tenan_address,
                            'tenan_npwp' => @$request->tenan_npwp,
                            'tenan_taxname' => @$request->tenan_taxname,
                            'tenan_tax_address' => @$request->tenan_tax_address,
                            'tenan_isppn' => !empty(@$request->tenan_isppn) ? 1 : 0,
                            'tenan_ispkp' => !empty(@$request->tenan_ispkp) ? 1 : 0,
                            'tent_id' => 1,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id()
                        ]);
                }else{
                    $tenant = MsTenant::find($request->tenan_id);
                }

                MsUnitOwner::create(['unit_id'=>$unit->id, 'tenan_id'=>$tenant->id, 'unitow_start_date' => @$request->unitow_start_date]);
            });
            return response()->json(['success'=>true, 'message'=>'Input Unit Success']);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function insert2(Request $request){
        try{
            $input = $request->all();
            // $input['unit_id'] = md5(date('Y-m-d H:i:s'));
            $input['unit_isavailable'] = 1;
            $input['created_by'] = Auth::id();
            $input['updated_by'] = Auth::id();
            return MsUnit::create($input);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function update(Request $request){
        try{
            $id = $request->id;
            $input = $request->all();
            // unit
            $updateUnit = [
                    'unit_code' => @$request->unit_code,
                    'unit_name' => @$request->unit_code,
                    'unit_sqrt' => @$request->unit_sqrt,
                    'virtual_account' => @$request->virtual_account,
                    'va_maintenance' => @$request->va_maintenance,
                    'va_utilities' => @$request->va_utilities,
                    'meter_air' => @$request->meter_air,
                    'meter_listrik' => @$request->meter_listrik,
                    'floor_id' => @$request->floor_id,
                    'untype_id' => @$request->untype_id,
                    'updated_by' => Auth::id()
                ];
            MsUnit::find($id)->update($updateUnit);

            $unitowner = MsUnitOwner::where('unit_id',$id)->first();
            if($unitowner){
                $unitowner->unitow_start_date = $request->unitow_start_date;
                $unitowner->save();

                $updateTenant = [
                        'tenan_name' => @$request->tenan_name,
                        'tenan_email' => @$request->tenan_email,
                        'tenan_idno' => @$request->tenan_idno,
                        'tenan_phone' => @$request->tenan_phone,
                        'tenan_fax' => @$request->tenan_fax,
                        'tenan_address' => @$request->tenan_address,
                        'tenan_npwp' => @$request->tenan_npwp,
                        'tenan_taxname' => @$request->tenan_taxname,
                        'tenan_tax_address' => @$request->tenan_tax_address,
                        'tenan_isppn' => !empty(@$request->tenan_isppn) ? 1 : 0,
                        'tenan_ispkp' => !empty(@$request->tenan_ispkp) ? 1 : 0,
                        'updated_by' => Auth::id()
                    ];
                MsTenant::find($unitowner->tenan_id)->update($updateTenant);
            }else{
                // create new
                $tenant = MsTenant::create([
                        'tenan_code' => "TN".date('ymdhis'),
                        'tenan_name' => @$request->tenan_name,
                        'tenan_email' => @$request->tenan_email,
                        'tenan_idno' => @$request->tenan_idno,
                        'tenan_phone' => @$request->tenan_phone,
                        'tenan_fax' => @$request->tenan_fax,
                        'tenan_address' => @$request->tenan_address,
                        'tenan_npwp' => @$request->tenan_npwp,
                        'tenan_taxname' => @$request->tenan_taxname,
                        'tenan_tax_address' => @$request->tenan_tax_address,
                        'tenan_isppn' => !empty(@$request->tenan_isppn) ? 1 : 0,
                        'tenan_ispkp' => !empty(@$request->tenan_ispkp) ? 1 : 0,
                        'tent_id' => 1,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id()
                    ]);
                MsUnitOwner::create(['unit_id'=>$unit->id, 'tenan_id'=>$tenant->id, 'unitow_start_date' => @$request->unitow_start_date]);
            }
            return response()->json(['success'=>true, 'message'=>'Update Unit Success']);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function update2(Request $request){
        try{
            $id = $request->id;
            $input = $request->all();
            $input['updated_by'] = Auth::id();
            MsUnit::find($id)->update($input);
            return MsUnit::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function delete(Request $request){
        try{
            $id = $request->id;
            // cek unit tidak boleh di delete kalo ada contract sedang berjalan
            $checkContract = TrContract::where('contr_status','confirmed')->where('contr_enddate', '>', date('Y-m-d H:i:s'))->whereNull('contr_terminate_date')->first();
            if($checkContract){
                return response()->json(['errorMsg' => 'Tidak bisa delete Unit karna ada contract yang sedang berjalan untuk unit ini']);
            }else{
                MsUnit::destroy($id);
                return response()->json(['success'=>true]);
            }
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function getOptUnit(Request $request){
        $key = $request->q;
        $fetch = MsUnit::select('id','unit_code','unit_name')->where(function($query) use($key){
            $query->where(\DB::raw('LOWER(unit_code)'),'like','%'.$key.'%')->orWhere(\DB::raw('LOWER(unit_name)'),'like','%'.$key.'%');
        })->where('unit_isactive', 'TRUE')->get();
        $result['results'] = [];
        foreach ($fetch as $key => $value) {
            $temp = ['id'=>$value->id, 'text'=>$value->unit_name." (".$value->unit_code.")"];
            array_push($result['results'], $temp);
        }
        return json_encode($result);
    }

    public function getPopupOptions(Request $request){
        $keyword = $request->input('keyword');
        $edit = $request->input('edit');
        $getAll = @$request->input('all');
        $tenan_id = @$request->tenan;
        // $isowner = $request->input('isowner', 0);
        // get unit yg available plus bs select unit dia sendiri
        if($keyword) $fetch = MsUnit::select('ms_unit.*','ms_unit_owner.tenan_id')
                                    // ->join('ms_virtual_account','ms_unit.unit_virtual_accn','=','ms_virtual_account.id')
                                    ->leftJoin('ms_unit_owner','ms_unit.id','=','ms_unit_owner.unit_id')
                                    ->where(function($query) use($keyword){
                                        $query->where(DB::raw('LOWER(unit_code)'),'ilike','%'.$keyword.'%')->orWhere(DB::raw('LOWER(unit_name)'),'ilike','%'.$keyword.'%');
                                    });
        else $fetch = MsUnit::select('ms_unit.*','ms_unit_owner.tenan_id')
                        ->leftJoin('ms_unit_owner','ms_unit.id','=','ms_unit_owner.unit_id');
                        // ->join('ms_virtual_account','ms_unit.unit_virtual_accn','=','ms_virtual_account.id');

        // KOMEN INI BIAR KELUAR SEMUA
        $cek_tenan = MsTenant::where('id',$tenan_id)->get();
        if($cek_tenan[0]->tent_id == 1){
            $fetch = $fetch->where('unit_isavailable',1);
        }                
        //if(empty($getAll)) $fetch = $fetch->where('unit_isavailable',1);
        if(!empty($tenan_id)){
            // get owned unit
            $owned_units = MsUnit::select('ms_unit.*','ms_unit_owner.tenan_id')
                        ->join('ms_unit_owner','ms_unit.id','=','ms_unit_owner.unit_id')
                        ->where('ms_unit_owner.tenan_id',$tenan_id)->get();
        }else{
            $owned_units = [];
        }
        // $fetch = $fetch->orWhere('ms_unit_owner.tenan_id','=',$tenan_id);
        $fetch = $fetch->paginate(10);
        return view('modal.popupunit', ['units'=>$fetch, 'keyword'=>$keyword, 'edit'=>$edit, 'owned_units' => $owned_units, 'tenan_id' => $tenan_id]);
    }

    public function getPopupOptions2(Request $request){
        $keyword = $request->input('keyword');
        $edit = $request->input('edit');
        $getAll = @$request->input('all');
        $tenan_id = @$request->tenan;
        $fetch = MsUnit::where('unit_isavailable','t');
        $fetch = $fetch->paginate(10);
        $owned_units = [];
        return view('modal.popupunit', ['units'=>$fetch, 'keyword'=>$keyword, 'edit'=>$edit, 'owned_units' => $owned_units, 'tenan_id' => $tenan_id]);
    }

    public function getOptions_account(){
        try{
            $all = MsVirtualAccount::all()->where('viracc_isactive',TRUE);
            $result = [];
            if(count($all) > 0){
                foreach ($all as $value) {
                    $result[] = ['id'=>$value->id, 'text'=>$value->viracc_no.' - '.$value->viracc_name];
                }
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function newAjaxUnitDetail(Request $request){
        try{
            $unit = MsUnit::find($request->id)->toArray();
            $unitowner = MsUnitOwner::where('unit_id',$request->id)->first();
            if($unitowner){
                $unit['unitow_start_date'] = $unitowner->unitow_start_date;
                // jika unit owner ada, ambil data tenan
                $tenant = MsTenant::find($unitowner->tenan_id)->toArray();
                $unit['tenant'] = $tenant;
            }
            return response()->json($unit);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function getdetail(Request $request){
        try{
            $id = $request->id;
            $unit = MsUnit::with('MsFloor','UnitType')->find($id);
            $unitowner = MsUnitOwner::where('unit_id',$id)->first();
            if($unitowner){
                $tenant = MsTenant::find($unitowner->tenan_id);
            }else{
                $tenant = null;
            }
            $renter = TrContract::where('unit_id',$id);
            if($unitowner) $renter = $renter->where('tenan_id','!=',$tenant->id);
            $renter = $renter->with('MsTenant')->get();

            return view('modal.detailunit', ['unit' => $unit, 'unitowner' => $unitowner, 'tenant' => $tenant, 'renter' => $renter]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

}
