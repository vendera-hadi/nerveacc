<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsUnit;
use App\Models\MsUnitType;
use App\Models\User;
use App\Models\MsFloor;
use App\Models\MsVirtualAccount;
use DB;

class UnitController extends Controller
{
    public function index(){
        return view('unit');
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
            $fetch = MsUnit::select('ms_unit.*','ms_unit_type.untype_name','ms_floor.floor_name')->join('ms_unit_type',\DB::raw('ms_unit.untype_id::integer'),"=",\DB::raw('ms_unit_type.id::integer'))->join('ms_floor',\DB::raw('ms_unit.floor_id::integer'),"=",\DB::raw('ms_floor.id::integer'));
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
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['unit_id'] = $value->unit_id;
                $temp['unit_code'] = $value->unit_code;
                $temp['unit_name'] = $value->unit_name;
                $temp['unit_sqrt'] = $value->unit_sqrt;
                $temp['unit_virtual_accn'] = $value->unit_virtual_accn;
                $temp['unit_isactive'] = $value->unit_isactive;
                $temp['untype_name'] = $value->untype_name;
                $temp['floor_name'] = $value->floor_name;
                $temp['floor_id'] = $value->floor_id;
                $temp['untype_id'] = $value->untype_id;
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
            $input['unit_id'] = md5(date('Y-m-d H:i:s'));
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
            MsUnit::destroy($id);
            return response()->json(['success'=>true]);
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
        // $isowner = $request->input('isowner', 0);
        if($keyword) $fetch = MsUnit::select('ms_unit.*','ms_virtual_account.viracc_no')->join('ms_virtual_account','ms_unit.unit_virtual_accn','=','ms_virtual_account.id')
                                    ->where(function($query) use($keyword){
                                        $query->where(DB::raw('LOWER(unit_code)'),'like','%'.$keyword.'%')->orWhere(DB::raw('LOWER(unit_name)'),'like','%'.$keyword.'%');
                                    });
        else $fetch = MsUnit::select('ms_unit.*','ms_virtual_account.viracc_no')->join('ms_virtual_account','ms_unit.unit_virtual_accn','=','ms_virtual_account.id');

        if(empty($getAll)) $fetch = $fetch->where('unit_isavailable',1);
        $fetch = $fetch->paginate(10);
        return view('modal.popupunit', ['units'=>$fetch, 'keyword'=>$keyword, 'edit'=>$edit]);
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
}
