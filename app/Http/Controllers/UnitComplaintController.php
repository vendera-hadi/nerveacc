<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\TrComplaint;
use App\Models\User;

class UnitComplaintController extends Controller
{
    public function index(){
		return view('trcomplaint');
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
        	$count = TrComplaint::count();
        	$fetch = TrComplaint::select('tr_complaint.*','ms_unit.unit_name','ms_complaint.compl_code')
        						->join('ms_unit','ms_unit.id','=','tr_complaint.unit_id')
        						->join('ms_complaint','ms_complaint.id','=','tr_complaint.compl_id');
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
                    if($filter->field == 'compl_isactive'){
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
        		$temp['comtr_no'] = $value->comtr_no;
        		$temp['comtr_date'] = $value->comtr_date;
        		$temp['comtr_note'] = $value->comtr_note;
        		$temp['comtr_handling_date'] = $value->comtr_handling_date;
        		$temp['comtr_handling_by'] = $value->comtr_handling_by;
        		$temp['comtr_finish_date'] = $value->comtr_finish_date;
        		$temp['comtr_handling_note'] = $value->comtr_handling_note;
        		$temp['unit_name'] = $value->unit_name;
        		$temp['compl_code'] = $value->compl_code;
        		$temp['created_by'] = $value->created_by;
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
    		$lastRecord = TrComplaint::orderBy('id','desc')->first();
    		if(empty($lastRecord)) $lastId = 1;
    		else $lastId = $lastRecord->id + 1;
    		$input['comtr_date'] = date('Y-m-d H:i:s', strtotime($input['comtr_date']));
    		if(!empty($input['comtr_handling_date'])) $input["comtr_handling_date"] = date('Y-m-d H:i:s', strtotime($input['comtr_handling_date']));
    		else $input["comtr_handling_date"] = NULL;
    		if(!empty($input['comtr_finish_date'])) $input["comtr_finish_date"] = date('Y-m-d H:i:s', strtotime($input['comtr_finish_date']));
     		else $input['comtr_finish_date'] = NULL;
     		$input['comtr_no'] = 'COMPL-'.date('Y').'-'.$lastId;
    		$input['created_by'] = Auth::user()->name;
    		$input['updated_by'] = $input['created_by'];
    		return TrComplaint::create($input);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }     	
    }

    public function update(Request $request){
        try{
        	$id = $request->id;
        	$input = $request->all();
        	$input['comtr_date'] = date('Y-m-d H:i:s', strtotime($input['comtr_date']));
    		if(!empty($input['comtr_handling_date'])) $input["comtr_handling_date"] = date('Y-m-d H:i:s', strtotime($input['comtr_handling_date']));
    		else $input["comtr_handling_date"] = NULL;
    		if(!empty($input['comtr_finish_date'])) $input["comtr_finish_date"] = date('Y-m-d H:i:s', strtotime($input['comtr_finish_date']));
     		else $input['comtr_finish_date'] = NULL;
        	$input['created_by'] = Auth::user()->name;
        	TrComplaint::find($id)->update($input);
        	return TrComplaint::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
        try{
        	$id = $request->id;
        	TrComplaint::destroy($id);
        	return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }
}
