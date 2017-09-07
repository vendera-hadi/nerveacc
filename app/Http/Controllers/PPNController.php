<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsPPN;
use App\Models\MsMasterCoa;
use App\Models\User;

class PPNController extends Controller
{
	public function index(){
		$coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
		return view('ppn', $data);
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
        	$count = MsPPN::count();
        	$fetch = MsPPN::query();
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
        		$temp['name'] = $value->name;
        		$temp['amount'] = $value->amount * 100;
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
    		$ppn = new MsPPN;
            $ppn->name = $request->name;
            $ppn->amount = $request->amount / 100;
            $ppn->coa_code = $request->coa_code;
            $ppn->save();
    		return $ppn;
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }    	
    }

    public function update(Request $request){
        try{
            $id = $request->id;
        	$ppn = MsPPN::find($id);
            $ppn->name = $request->name;
            $ppn->amount = $request->amount / 100;
            $ppn->coa_code = $request->coa_code;
            $ppn->save();
            return $ppn;
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function delete(Request $request){
        try{
        	$id = $request->id;
        	MsPPN::destroy($id);
        	return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }
}