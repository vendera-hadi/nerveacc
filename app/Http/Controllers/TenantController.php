<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsTenant;
use App\Models\MsTenantType;
use App\Models\User;
use Validator;

class TenantController extends Controller
{
    public function index(){
        return view('tenant');
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
            $count = MsTenant::count();
            $fetch = MsTenant::select('ms_tenant.*','ms_tenant_type.tent_name')->leftJoin('ms_tenant_type',\DB::raw('ms_tenant.tent_id::integer'),"=",\DB::raw('ms_tenant_type.id::integer'));
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
                $temp['tenan_code'] = $value->tenan_code;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['tenan_idno'] = $value->tenan_idno;
                $temp['tenan_phone'] = $value->tenan_phone;
                $temp['tenan_email'] = $value->tenan_email;
                $temp['tenan_address'] = $value->tenan_address;
                $temp['tenan_npwp'] = $value->tenan_npwp;
                $temp['tenan_taxname'] = $value->tenan_taxname;
                $temp['tenan_tax_address'] = $value->tenan_tax_address;
                $temp['tent_name'] = $value->tent_name;
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getOptions(){
        try{
            $all = MsTenantType::all();
            $result = [];
            if(count($all) > 0){
                foreach ($all as $value) {
                    $result[] = ['id'=>$value->id, 'text'=>$value->tent_name];
                }
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function insert(Request $request){
        try{
            $messages = [
                'tenan_code.unique' => 'Tenant Code must be unique',
            ];
            $validator = Validator::make($request->all(), [
                'tenan_code' => 'required|unique:ms_tenant',
            ],$messages);
            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json(['errorMsg' => $errors]);
            }

            $input = $request->all();
            $input['created_by'] = Auth::id();
            $input['updated_by'] = Auth::id();
            return MsTenant::create($input);        
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function update(Request $request){
        try{
            $id = $request->id;
            $messages = [
                'tenan_code.unique' => 'Tenant Code must be unique',
            ];
            $validator = Validator::make($request->all(), [
                'tenan_code' => 'required|unique:ms_tenant,tenan_code,'.$id,
            ],$messages);
            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json(['errorMsg' => $errors]);
            }

            $input = $request->all();
            $input['updated_by'] = Auth::id();
            MsTenant::find($id)->update($input);
            return MsTenant::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
        try{
            $id = $request->id;
            MsTenant::destroy($id);
            return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getOptTenant(Request $request){
        $key = $request->q;
        $fetch = MsTenant::select('id','tenan_code','tenan_name')->where(\DB::raw('LOWER(tenan_code)'),'like','%'.$key.'%')->orWhere(\DB::raw('LOWER(tenan_name)'),'like','%'.$key.'%')->get();
        $result['results'] = [];
        foreach ($fetch as $key => $value) {
            $temp = ['id'=>$value->id, 'text'=>$value->tenan_code." (".$value->tenan_name.")"];
            array_push($result['results'], $temp);
        }
        return json_encode($result);
    }
}
