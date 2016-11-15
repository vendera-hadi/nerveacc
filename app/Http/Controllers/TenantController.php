<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsTenant;
use App\Models\MsTenantType;
use App\Models\User;
use App\Models\MsUnitOwner;
use Validator;

class TenantController extends Controller
{
    public function index(){
        $data['tenantTypes'] = MsTenantType::all();
        return view('tenant',$data);
    }

    public function get(Request $request){
        try{
            // params
            $type = @$request->type;
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
            $fetch = MsTenant::select('ms_tenant.*','ms_tenant_type.tent_name')
                    ->leftJoin('ms_tenant_type','ms_tenant.tent_id',"=",'ms_tenant_type.id');
            if(!empty($type)){ 
                if($type == 'owner') $fetch = $fetch->where('ms_tenant_type.tent_isowner',1);
                else if($type == 'tenant') $fetch = $fetch->where('ms_tenant_type.tent_isowner',0);
            }
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
                $temp['tenan_fax'] = $value->tenan_phone;
                $temp['tenan_email'] = $value->tenan_email;
                $temp['tenan_address'] = $value->tenan_address;
                $temp['tenan_npwp'] = $value->tenan_npwp;
                $temp['tenan_taxname'] = $value->tenan_taxname;
                $temp['tenan_tax_address'] = $value->tenan_tax_address;
                $temp['tent_name'] = $value->tent_name;
                $temp['tenan_isppn'] = $value->tenan_isppn;
                $temp['tenan_ispkp'] = $value->tenan_ispkp;
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

    public function getPopupOptions(Request $request){
        $keyword = $request->input('keyword');
        $edit = $request->input('edit');
        // $isowner = $request->input('isowner', 0);
        if($keyword) $fetch = MsTenant::select('ms_tenant.*','ms_tenant_type.tent_name','ms_tenant_type.tent_isowner')
                                    ->join('ms_tenant_type','ms_tenant.tent_id','=','ms_tenant_type.id')
                                    ->where(function($query) use($keyword){
                                        $query->where(DB::raw('LOWER(tenan_name)'),'like','%'.$keyword.'%')->orWhere(DB::raw('LOWER(tenan_code)'),'like','%'.$keyword.'%');
                                    })->paginate(10);
        else $fetch = MsTenant::select('ms_tenant.*','ms_tenant_type.tent_name','ms_tenant_type.tent_isowner')
                                ->join('ms_tenant_type','ms_tenant.tent_id','=','ms_tenant_type.id')
                                ->paginate(10);
        return view('modal.popuptenant', ['tenants'=>$fetch, 'keyword'=>$keyword, 'edit'=>$edit]);
    }

    public function insert(Request $request){
        try{
        // var_dump($request->all());
            $messages = [
                'tenan_code.unique' => 'Tenant Code is taken',
                'tent_id.required' => 'Choose Tenant Type First',
                'tenan_idno.numeric' => 'KTP No must be in numeric format',
                'tenan_phone.numeric' => 'Phone must be in numeric format'
            ];
            $validator = Validator::make($request->all(), [
                'tenan_code' => 'required|unique:ms_tenant',
                'tenan_idno' => 'numeric',
                'tenan_phone' => 'numeric',
                'tenan_email' => 'required|email',
                'tent_id' => 'required'
            ],$messages);
            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json(['errorMsg' => $errors]);
            }
            $input = $request->all();
            $input['created_by'] = Auth::id();
            $input['updated_by'] = Auth::id();

            $tenant = MsTenant::create($input);

            if(isset($input['unit_id'])){
                // insert ke unit owner
                MsUnitOwner::create(['unit_id'=>$input['unit_id'], 'tenan_id'=>$tenant->id]);
            }
            
            return $tenant;        
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function update(Request $request){
        // try{
        // var_dump($request->all());
            $id = $request->id;
            $messages = [
                'tenan_code.unique' => 'Tenant Code must be unique',
                'tent_id.required' => 'Choose Tenant Type First',
                'tenan_idno.numeric' => 'KTP No must be in numeric format',
                'tenan_phone.numeric' => 'Phone must be in numeric format'
            ];
            $validator = Validator::make($request->all(), [
                'tenan_code' => 'required|unique:ms_tenant,tenan_code,'.$id,
                'tenan_idno' => 'numeric',
                'tenan_phone' => 'numeric',
                'tenan_email' => 'required|email',
                'tent_id' => 'required'
            ],$messages);
            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json(['errorMsg' => $errors]);
            }

            $input = $request->all();
            $input['updated_by'] = Auth::id();
            MsTenant::find($id)->update($input);
            return MsTenant::find($id);
        // }catch(\Exception $e){
        //     return response()->json(['errorMsg' => $e->getMessage()]);
        // } 
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

    public function edit(Request $request){
        $id = $request->id;
        $tenant = MsTenant::select('ms_tenant.*','ms_unit_owner.unit_id','ms_unit.unit_code','ms_unit.unit_name')->where('ms_tenant.id',$id)
            ->leftJoin('ms_unit_owner','ms_tenant.id','=','ms_unit_owner.tenan_id')
            ->leftJoin('ms_unit','ms_unit_owner.unit_id','=','ms_unit.id')
            ->first();
        return $tenant;
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
