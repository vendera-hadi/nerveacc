<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsTenantType;

class TenantTypeController extends Controller
{
	public function index(){
		return view('tenant_type');
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
    	$count = MsTenantType::count();
    	$fetch = MsTenantType::query();
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
    		$temp['tent_id'] = $value->tent_id;
    		$temp['tent_name'] = $value->tent_name;
    		$result['rows'][] = $temp;
    	}
        return response()->json($result);
    }

    public function insert(Request $request){
		$input = $request->all();
        $input['tent_id'] = md5(date('Y-m-d H:i:s'));
		return MsTenantType::create($input);    	
    }

    public function update(Request $request){
    	$id = $request->id;
    	$input = $request->all();
    	MsTenantType::find($id)->update($input);
    	return MsTenantType::find($id);
    }

    public function delete(Request $request){
    	$id = $request->id;
    	MsTenantType::destroy($id);
    	return response()->json(['success'=>true]);
    }
}
