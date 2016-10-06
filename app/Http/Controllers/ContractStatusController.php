<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsContractStatus;
use App\Models\User;

class ContractStatusController extends Controller
{
    public function index(){
		return view('contractstatus');
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
        	$count = MsContractStatus::count();
        	$fetch = MsContractStatus::query();
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
        		$temp['const_code'] = $value->const_code;
        		$temp['const_order'] = $value->const_order;
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

    public function insert(Request $request){
        try{
    		$input = $request->all();
    		$input['created_by'] = Auth::id();
    		$input['updated_by'] = Auth::id();
    		return MsContractStatus::create($input);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }     	
    }

    public function update(Request $request){
        try{
        	$id = $request->id;
        	$input = $request->all();
        	$input['updated_by'] = Auth::id();
        	MsContractStatus::find($id)->update($input);
        	return MsContractStatus::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
        try{
        	$id = $request->id;
        	MsContractStatus::destroy($id);
        	return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getOptionContractStatus(Request $request){
        $key = $request->q;
        $fetch = MsContractStatus::select('id','const_name')->where(\DB::raw('LOWER(const_name)'),'like','%'.$key.'%')->get();
        $result['results'] = [];
        foreach ($fetch as $key => $value) {
            $temp = ['id'=>$value->id, 'text'=>$value->const_name];
            array_push($result['results'], $temp);
        }
        return json_encode($result);
    }
}
