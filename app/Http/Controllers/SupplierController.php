<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsSupplier;
use App\Models\User;

class SupplierController extends Controller
{
	public function index(){
		return view('supplier');
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
            $count = MsSupplier::count();
            $fetch = MsSupplier::query();
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
                    if($filter->field == 'spl_isactive'){
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
                $temp['spl_id'] = $value->spl_id;
                $temp['spl_code'] = $value->spl_code;
                $temp['spl_name'] = $value->spl_name;
                $temp['spl_address'] = $value->spl_address;
                $temp['spl_city'] = $value->spl_city;
                $temp['spl_postal_code'] = $value->spl_postal_code;
                $temp['spl_phone'] = $value->spl_phone;
                $temp['spl_fax'] = $value->spl_fax;
                $temp['spl_cperson'] = $value->spl_cperson;
                $temp['spl_npwp'] = $value->spl_npwp;
                $temp['spl_isactive'] = !empty($value->spl_isactive) ? 'yes' : 'no';
                try{
                    $temp['created_by'] = User::findOrFail($value->created_by)->name;
                }catch(\Exception $e){
                    $temp['created_by'] = '-';
                }
                $temp['created_at'] = $value->created_at;
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
            $input['spl_id'] = md5(date('Y-m-d H:i:s'));
            $input['created_by'] = Auth::id();
            $input['updated_by'] = Auth::id();
    		return MsSupplier::create($input);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }     	
    }

    public function update(Request $request){
    	try{
            $id = $request->id;
        	$input = $request->all();
            $input['updated_by'] = Auth::id();
        	MsSupplier::find($id)->update($input);
        	return MsSupplier::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
    	try{
            $id = $request->id;
        	MsSupplier::destroy($id);
        	return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function ajaxdtl(Request $request)
    {
        try{
            $id = $request->id;
            $data = MsSupplier::find($id);
            return response()->json(['success'=>true, 'data' => $data]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }    
    }

}
