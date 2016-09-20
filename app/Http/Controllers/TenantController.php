<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsTenant;
use App\Models\User;

class TenantController extends Controller
{
	public function index(){
		return view('tenant');
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
        $count = MsTenant::count();
        $fetch = MsTenant::query();
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
            $temp['tenan_id'] = $value->tenan_id;
            $temp['tenan_code'] = $value->tenan_code;
            $temp['tenan_name'] = $value->tenan_name;
            $temp['tenan_idno'] = $value->tenan_idno;
            $temp['tenan_email'] = $value->tenan_email;
            $temp['tenan_address'] = $value->tenan_address;
            $temp['tenan_npwp'] = $value->tenan_npwp;
            $temp['tenan_taxname'] = $value->tenan_taxname;
            $temp['tenan_taxaddress'] = $value->tenan_taxaddress;
            try{
                $temp['created_by'] = User::findOrFail($value->created_by)->name;
            }catch(\Exception $e){
                $temp['created_by'] = '-';
            }
            $result['rows'][] = $temp;
        }
        return response()->json($result);
    }

    public function insert(Request $request){
		$input = $request->all();
        $input['tenan_id'] = md5(date('Y-m-d H:i:s'));
        $input['created_by'] = Auth::id();
        $input['updated_by'] = Auth::id();
		return MsTenant::create($input);    	
    }

    public function update(Request $request){
    	$id = $request->id;
    	$input = $request->all();
        $input['updated_by'] = Auth::id();
    	MsTenant::find($id)->update($input);
    	return MsTenant::find($id);
    }

    public function delete(Request $request){
    	$id = $request->id;
    	MsTenant::destroy($id);
    	return response()->json(['success'=>true]);
    }
}
