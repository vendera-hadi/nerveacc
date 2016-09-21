<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsUnitType;

class UnitTypeController extends Controller
{
	public function index(){
		return view('unit_type');
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
    	$count = MsUnitType::count();
    	$fetch = MsUnitType::query();
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
                if($filter->field == 'untype_isactive'){
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
    		$temp['untype_id'] = $value->untype_id;
    		$temp['untype_name'] = $value->untype_name;
            $temp['untype_isactive'] = !empty($value->untype_isactive) ? 'yes' : 'no';
    		$result['rows'][] = $temp;
    	}
        return response()->json($result);
    }

    public function insert(Request $request){
		$input = $request->all();
        $input['untype_id'] = md5(date('Y-m-d H:i:s'));
        $input['created_by'] = Auth::id();
        $input['updated_by'] = Auth::id();
		return MsUnitType::create($input);    	
    }

    public function update(Request $request){
    	$id = $request->id;
    	$input = $request->all();
        $input['updated_by'] = Auth::id();
    	MsUnitType::find($id)->update($input);
    	return MsUnitType::find($id);
    }

    public function delete(Request $request){
    	$id = $request->id;
    	MsUnitType::destroy($id);
    	return response()->json(['success'=>true]);
    }
}
