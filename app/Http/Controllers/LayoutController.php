<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NeracaFmt;
use App\Models\User;

class LayoutController extends Controller
{

	public function index()
	{
		return view('layout_settings');
	}

	public function get(){
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
	    	$count = NeracaFmt::count();
	    	// join dengan group account
	    	$fetch = NeracaFmt::groupBy('kodefmt');
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
	    		$temp['kodefmt'] = $value->kodefmt;
	    		$temp['action'] = '<a href="#" class="editFormat"><i class="fa fa-edit"></i></a>';
	    		$result['rows'][] = $temp;
	    	}
	        return response()->json($result);
	    }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
	}

}