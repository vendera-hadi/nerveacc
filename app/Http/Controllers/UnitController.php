<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsUnit;
use App\Models\MsUnitType;
use App\Models\User;

class UnitController extends Controller
{
	public function index(){
		return view('unit');
    }

    public function get(Request $request){
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
    		$temp['unit_name'] = $value->unit_name;
            try{
                $temp['untype_id'] = MsUnitType::findOrFail($value->untype_id)->untype_name;
            }catch(\Exception $e){
                $temp['untype_id'] = '-';
            }
            try{
                $temp['created_by'] = User::findOrFail($value->created_by)->name;
            }catch(\Exception $e){
                $temp['created_by'] = '-';
            }
    		$result['rows'][] = $temp;
    	}
        return response()->json($result);
    }

    public function insert(Request $request){
		$input = $request->all();
        $input['unit_id'] = md5(date('Y-m-d H:i:s'));
        $input['created_by'] = Auth::id();
        $input['updated_by'] = Auth::id();
		return MsUnit::create($input);    	
    }

    public function update(Request $request){
    	$id = $request->id;
    	$input = $request->all();
        $input['updated_by'] = Auth::id();
    	MsUnit::find($id)->update($input);
    	return MsUnit::find($id);
    }

    public function delete(Request $request){
    	$id = $request->id;
    	MsUnit::destroy($id);
    	return response()->json(['success'=>true]);
    }
}
