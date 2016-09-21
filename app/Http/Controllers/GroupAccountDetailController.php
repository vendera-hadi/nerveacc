<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
// load model
use App\Models\MsGroupAccnDtl;
use App\Models\User;

class GroupAccountDetailController extends Controller
{
    public function index(){
		return view('groupaccountdetail');
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
	    	$count = MsGroupAccnDtl::count();
	    	// join dengan group account
	    	$fetch = MsGroupAccnDtl::select('ms_group_accn_dtl.*','ms_group_account.grpaccn_name')->join('ms_group_account',\DB::raw('ms_group_account.id::integer'),"=",\DB::raw('ms_group_accn_dtl.grpaccn_id::integer'));
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
	    		$temp['grpaccn_name'] = $value->grpaccn_name;
	    		$temp['coa_year'] = $value->coa_year;
	    		$temp['coa_code'] = $value->coa_code;
	    		$result['rows'][] = $temp;
	    	}
	        return response()->json($result);
	    }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function insert(Request $request){
    	try{
			$input = $request->all();
			return MsGroupAccnDtl::create($input);
		}catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }     	
    }

    public function update(Request $request){
    	try{
	    	$id = $request->id;
	    	$input = $request->all();
	    	MsGroupAccnDtl::find($id)->update($input);
	    	return MsGroupAccnDtl::find($id);
	    }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
    	try{
	    	$id = $request->id;
	    	MsGroupAccnDtl::destroy($id);
	    	return response()->json(['success'=>true]);
	    }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }
}
