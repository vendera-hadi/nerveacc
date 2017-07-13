<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// load model
use App\Models\MsGroupAccount;
use App\Models\MsGroupAccnDtl;
use App\Models\MsMasterCoa;
use App\Models\User;
use Auth;

class GroupAccountController extends Controller
{
    public function index(){
        $data['accounts'] = MsMasterCoa::where('coa_year',date('Y'))->where('coa_isparent',0)->orderBy('coa_type')->get();
		return view('groupaccount',$data);
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
        	$count = MsGroupAccount::count();
        	$fetch = MsGroupAccount::query();
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
                    if($filter->field == 'curr_isactive'){
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
        		$temp['grpaccn_name'] = $value->grpaccn_name;
                $temp['action'] = '<a data-id="'.$value->id.'" data-name="'.$value->grpaccn_name.'" class="editGroup"><i class="fa fa-eye">&nbsp;View Details</i></a>';
        		$result['rows'][] = $temp;
        	}
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getOptions(){
        try{
            $all = MsGroupAccount::all();
            $result = [];
            if(count($all) > 0){
                foreach ($all as $value) {
                    $result[] = ['id'=>$value->id, 'text'=>$value->grpaccn_name];
                }
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getDetail(Request $request)
    {
        try{
            $id = $request->id;
            $currentYear = date('Y');
            $fetch = MsGroupAccnDtl::select('ms_master_coa.*')->join('ms_master_coa','ms_group_accn_dtl.coa_code','=','ms_master_coa.coa_code')
                        ->where('grpaccn_id',$id)->where('ms_master_coa.coa_year',$currentYear)->orderBy('coa_code')->get();
            if($request->raw) return $fetch;
            $count = $fetch->count();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['coa_code'] = $value->coa_code;
                $temp['coa_year'] = $value->coa_year;
                $temp['coa_name'] = $value->coa_name;
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
            $input['created_by'] = $input['updated_by'] = Auth::id();
    		return MsGroupAccount::create($input);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }     	
    }

    public function update(Request $request){
        try{
        	$id = $request->id;
        	$groupacc = MsGroupAccount::find($id);
            $groupacc->grpaccn_name = @$request->grpaccn_name;
            $groupacc->updated_by = Auth::id();
            $groupacc->save();
        	return $groupacc;
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
        try{
        	$id = $request->id;
        	MsGroupAccount::destroy($id);
        	return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function updateDetail(Request $request){
        try{
            $id = $request->id;
            MsGroupAccnDtl::where('grpaccn_id',$id)->delete();
            if(count($request->coa_code) > 0){
                foreach ($request->coa_code as $coa) {
                    $groupdt = new MsGroupAccnDtl;
                    $groupdt->grpaccn_id = $id;
                    $groupdt->coa_code = $coa;
                    $groupdt->save();
                }
            }
            $request->session()->flash('success', 'Update success');
            return redirect()->back();
        }catch(\Exception $e){
            $request->session()->flash('error', 'Error Occured');
            return redirect()->back();
        } 
    }
}
