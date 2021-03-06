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
use App\Models\AllUnit;
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
            $fetch = MsUnit::query();

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
                    if(@$filter->field == 'tenan_name'){
                        $fetch = $fetch->whereHas('owner.tenant', function($query) use($filter){
                            $query->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),'ilike','%'.$filter->value.'%');
                        });
                    }else if(@$filter->field == 'untype_name'){
                        $fetch = $fetch->whereHas('UnitType', function($query) use($filter){
                            $query->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),'ilike','%'.$filter->value.'%');
                        });
                    }else if(@$filter->field == 'floor_name'){
                        $fetch = $fetch->whereHas('MsFloor', function($query) use($filter){
                            $query->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),'ilike','%'.$filter->value.'%');
                        });
                    }else if(@$filter->field == 'created_by'){
                        $fetch = $fetch->whereHas('createdBy', function($query) use($filter){
                            $query->where(\DB::raw('lower(trim(name))'),'ilike','%'.$filter->value.'%');
                        });
                    }else if(@$filter->field == 'updated_by'){
                        $fetch = $fetch->whereHas('updatedBy', function($query) use($filter){
                            $query->where(\DB::raw('lower(trim(name))'),'ilike','%'.$filter->value.'%');
                        });
                    }else{
                        if($op == 'like') $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                        else $fetch = $fetch->where($filter->field, $op, $filter->value);
                    }
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
                $temp['unit_isactive'] = $value->unit_isactive;
                $temp['untype_name'] = $value->UnitType->untype_name;
                $temp['floor_name'] = $value->MsFloor->floor_name;
                $temp['floor_id'] = $value->floor_id;
                $temp['untype_id'] = $value->untype_id;
                $temp['tenan_name'] = @$value->owner->tenant->tenan_name ?: '-';
                $temp['va_utilities'] = $value->va_utilities;
                $temp['va_maintenance'] = $value->va_maintenance;
                $temp['unit_isactive'] = !empty($value->unit_isactive) ? 'yes' : 'no';
                try{
                    $temp['created_by'] = User::findOrFail($value->created_by)->name;
                }catch(\Exception $e){
                    $temp['created_by'] = '-';
                }
                try{
                    $temp['updated_by'] = User::findOrFail($value->updated_by)->name;
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
                        'unit_isactive' => 1,
                        'air_start' => @$request->water_start,
                        'listrik_start' => @$request->listrik_start
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
                $upd = AllUnit::where('unit_code',@$request->unit_code)->first();
                if(count($upd) > 0){
                    $upd->used = 1;
                    $upd->save();
                }
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
                    'updated_by' => Auth::id(),
                    'air_start' => @$request->water_start,
                    'listrik_start' => @$request->listrik_start

                ];
            MsUnit::find($id)->update($updateUnit);

            $unitowner = MsUnitOwner::where('unit_id',$id)->first();
            if($unitowner){
                // bole ganti unit owner asalkan tidak ada contract confirmed atas unit itu
                $checkContract = TrContract::where('contr_status','confirmed')->where('unit_id',$id)->first();
                if($checkContract) return response()->json(['errorMsg' => 'Cannot change unit owner after close all contract of this unit first']);

                if(@$request->tenan_id) $unitowner->tenan_id = $request->tenan_id;
                $unitowner->unitow_start_date = $request->unitow_start_date;
                $unitowner->save();

                // $updateTenant = [
                //         'tenan_name' => @$request->tenan_name,
                //         'tenan_email' => @$request->tenan_email,
                //         'tenan_idno' => @$request->tenan_idno,
                //         'tenan_phone' => @$request->tenan_phone,
                //         'tenan_fax' => @$request->tenan_fax,
                //         'tenan_address' => @$request->tenan_address,
                //         'tenan_npwp' => @$request->tenan_npwp,
                //         'tenan_taxname' => @$request->tenan_taxname,
                //         'tenan_tax_address' => @$request->tenan_tax_address,
                //         'tenan_isppn' => !empty(@$request->tenan_isppn) ? 1 : 0,
                //         'tenan_ispkp' => !empty(@$request->tenan_ispkp) ? 1 : 0,
                //         'updated_by' => Auth::id()
                //     ];
                // MsTenant::find($unitowner->tenan_id)->update($updateTenant);
            }else{
                // create new
                // $tenant = MsTenant::create([
                //         'tenan_code' => "TN".date('ymdhis'),
                //         'tenan_name' => @$request->tenan_name,
                //         'tenan_email' => @$request->tenan_email,
                //         'tenan_idno' => @$request->tenan_idno,
                //         'tenan_phone' => @$request->tenan_phone,
                //         'tenan_fax' => @$request->tenan_fax,
                //         'tenan_address' => @$request->tenan_address,
                //         'tenan_npwp' => @$request->tenan_npwp,
                //         'tenan_taxname' => @$request->tenan_taxname,
                //         'tenan_tax_address' => @$request->tenan_tax_address,
                //         'tenan_isppn' => !empty(@$request->tenan_isppn) ? 1 : 0,
                //         'tenan_ispkp' => !empty(@$request->tenan_ispkp) ? 1 : 0,
                //         'tent_id' => 1,
                //         'created_by' => Auth::id(),
                //         'updated_by' => Auth::id()
                //     ]);
                MsUnitOwner::create(['unit_id'=>$id, 'tenan_id'=>$request->tenan_id, 'unitow_start_date' => @$request->unitow_start_date]);
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
            $checkContract = TrContract::where('contr_status','confirmed')
                                        ->where('unit_id', $id)
                                        ->first();
            if($checkContract){
                return response()->json(['errorMsg' => 'Tidak bisa delete Unit karna ada contract yang sedang berjalan untuk unit ini']);
            }else{
                MsUnit::destroy($id);
                // delete msunit owner
                MsUnitOwner::where('unit_id', $id)->delete();
                return response()->json(['success'=>true, 'message'=>'Delete Unit Success']);
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

        $owned_units = [];
        if(!empty($tenan_id)){
            // get owned unit
            $owned_units = MsUnitOwner::where('tenan_id',$tenan_id)->pluck('unit_id')->toArray();
        }

        // get unit yg available plus bs select unit dia sendiri
        if($keyword) $fetch = MsUnit::select('ms_unit.*','ms_unit_owner.tenan_id')
                                    ->leftJoin('ms_unit_owner','ms_unit.id','=','ms_unit_owner.unit_id')
                                    ->whereNull('ms_unit_owner.deleted_at')
                                    ->where(function($query) use($keyword){
                                        $query->where(DB::raw('LOWER(unit_code)'),'ilike','%'.$keyword.'%')->orWhere(DB::raw('LOWER(unit_name)'),'ilike','%'.$keyword.'%');
                                    });
        else $fetch = MsUnit::select('ms_unit.*','ms_unit_owner.tenan_id')
                        ->leftJoin('ms_unit_owner','ms_unit.id','=','ms_unit_owner.unit_id')
                        ->whereNull('ms_unit_owner.deleted_at');

        // KOMEN INI BIAR KELUAR SEMUA
        if($tenan_id){
            $cek_tenan = MsTenant::find($tenan_id);
            if($cek_tenan && @$cek_tenan->tent_id == 1){
                $fetch = $fetch->where('unit_isavailable',1);
            }
        }

        // filter owned unit
        if(count($owned_units) > 0){
            $fetch = $fetch->orWhere(function($query) use($owned_units){
                    $query->whereIn('ms_unit.id',$owned_units)->where('unit_isavailable',1);
                });
        }
        // echo $fetch->toSql(); die();
        //if(empty($getAll)) $fetch = $fetch->where('unit_isavailable',1);

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

            // history owner
            $prevowner = MsUnitOwner::onlyTrashed()->where('unit_id',$id)->orderBy('unitow_start_date');
            $prevownerIds = $prevowner->pluck('tenan_id');

            $renter = TrContract::where('unit_id',$id)->where('contr_status','confirmed');
            if($unitowner) $renter = $renter->where('tenan_id','!=',$tenant->id);
            if(count($prevownerIds) > 0) $renter = $renter->whereNotIn('tenan_id', $prevownerIds);
            $renter = $renter->with('MsTenant')->get();

            return view('modal.detailunit', ['unit' => $unit, 'unitowner' => $unitowner, 'tenant' => $tenant, 'renter' => $renter, 'prevowner' => $prevowner->get()]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function getunit(Request $request){
        $key = $request->q;
        $fetch = AllUnit::select('*')->where(function($query) use($key){
            $query->where(\DB::raw('LOWER(unit_code)'),'like','%'.$key.'%');
        })->where('used', 'FALSE')->get();
        $result = array();
        foreach ($fetch as $value) {
            $result[] = array(
                "unit_sqrt"=>$value->unit_sqrt,
                'stateName'=>$value->unit_code,
                "va_utilities"=>$value->va_utilities,
                "va_maintenance"=>$value->va_maintenance,
                "meter_air"=>$value->meter_air,
                "meter_listrik"=>$value->meter_listrik,
                "untype_id"=>$value->untype_id,
                "floor_id"=>$value->floor_id
            );
        }
        return response()->json($result);
    }

}
