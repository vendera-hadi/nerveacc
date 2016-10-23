<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsUnitOwner;
use App\Models\MsUnit;
use App\Models\MsTenant;

class UnitOwnerController extends Controller
{
    public function index(){
        return view('unit_owner');
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
            $count = MsUnitOwner::count();
            $fetch = MsUnitOwner::select('ms_unit_owner.*','ms_unit.unit_name','ms_tenant.tenan_name')->leftJoin('ms_unit',\DB::raw('ms_unit_owner.unit_id'),"=",\DB::raw('ms_unit.unit_code'))->leftJoin('ms_tenant',\DB::raw('ms_unit_owner.tenan_id'),"=",\DB::raw('ms_tenant.tenan_code'));
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
                }
            }
            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['unitow_id'] = $value->unitow_id;
                $temp['unitow_start_date'] = $value->unitow_start_date;
                $temp['unit_id'] = $value->unit_id;
                $temp['tenan_id'] = $value->tenan_id;
                $temp['unit_name'] = $value->unit_name;
                $temp['tenan_name'] = $value->tenan_name;
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function unitopt(){
        try{
            $all = MsUnit::all();
            $result = [];
            if(count($all) > 0){
                foreach ($all as $value) {
                    $result[] = ['id'=>$value->unit_code, 'text'=>$value->unit_name];
                }
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function tenanopt(){
        try{
            $all = MsTenant::all();
            $result = [];
            if(count($all) > 0){
                foreach ($all as $value) {
                    $result[] = ['id'=>$value->tenan_code, 'text'=>$value->tenan_name];
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
            $input['unitow_id'] = md5(date('Y-m-d H:i:s'));
            return MsUnitOwner::create($input);        
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function update(Request $request){
        try{
            $id = $request->id;
            $input = $request->all();
            MsUnitOwner::find($id)->update($input);
            return MsUnitOwner::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
        try{
            $id = $request->id;
            MsUnitOwner::destroy($id);
            return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }
}
