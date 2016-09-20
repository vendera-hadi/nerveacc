<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsMasterCoa;

class CoaController extends Controller
{
	public function index(){
		return view('coa');
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
    	$count = MsMasterCoa::count();
    	$fetch = MsMasterCoa::query();
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
                if($filter->field == 'coa_isparent'){
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
    		$temp['coa_year'] = $value->coa_year;
    		$temp['coa_code'] = $value->coa_code;
            $temp['coa_name'] = $value->coa_name;
    		$temp['coa_isparent'] = !empty($value->coa_isparent) ? 'yes' : 'no';
            $temp['coa_level'] = $value->coa_level;
            $temp['coa_type'] = $value->coa_type;
            $temp['coa_beginning'] = $value->coa_beginning;
            $temp['coa_debit'] = $value->coa_debit;
            $temp['coa_credit'] = $value->coa_credit;
            $temp['coa_ending'] = $value->coa_ending;
    		$result['rows'][] = $temp;
    	}
        return response()->json($result);
    }

    public function insert(Request $request){
		$input = $request->all();
		return MsMasterCoa::create($input);    	
    }

    public function update(Request $request){
    	$id = $request->id;
    	$input = $request->all();
    	MsMasterCoa::find($id)->update($input);
    	return MsMasterCoa::find($id);
    }

    public function delete(Request $request){
    	$id = $request->id;
    	MsMasterCoa::destroy($id);
    	return response()->json(['success'=>true]);
    }
}
