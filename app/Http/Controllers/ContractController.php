<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\TrContract;
use App\Models\MsCostItem;
use Validator;

class ContractController extends Controller
{
    public function index(){
        $data['cost_items'] = MsCostItem::all();
        return view('contract', $data);
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
            $count = TrContract::count();
            $fetch = TrContract::select('tr_contract.*','ms_tenant.tenan_name')
            		->join('ms_tenant',\DB::raw('ms_tenant.id::integer'),"=",\DB::raw('tr_contract.tenan_id::integer'));
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
                $temp['contr_code'] = $value->contr_code;
                $temp['contr_no'] = $value->contr_no;
                $temp['contr_startdate'] = $value->contr_startdate;
                $temp['contr_enddate'] = $value->contr_enddate;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['contr_status'] = $value->contr_status;
                $temp['contr_terminate_date'] = $value->contr_terminate_date;
                $temp['action'] = '<a href="#" data-toggle="modal" data-target="#detailModal" data-id="'.$value->id.'" class="getDetail">Detail</a> <a href="#" data-toggle="modal" data-target="#editModal" data-id="'.$value->id.'" class="edit">Edit</a> <a href="#" data-id="'.$value->id.'" class="remove">Remove</a>';
                
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getdetail(Request $request){
        $contractId = $request->id;
        $fetch = TrContract::select('tr_contract.*','ms_tenant.tenan_code','ms_tenant.tenan_name','ms_tenant.tenan_idno','ms_marketing_agent.mark_code','ms_marketing_agent.mark_name','ms_virtual_account.viracc_no','ms_virtual_account.viracc_name','ms_virtual_account.viracc_isactive','ms_contract_status.const_code','ms_contract_status.const_name','ms_unit.unit_code','ms_unit.unit_name','ms_unit.unit_isactive')
        ->join('ms_tenant',\DB::raw('ms_tenant.id::integer'),"=",\DB::raw('tr_contract.tenan_id::integer'))
        ->join('ms_marketing_agent',\DB::raw('ms_marketing_agent.id::integer'),"=",\DB::raw('tr_contract.mark_id::integer'))
        // ->join('ms_rental_period',\DB::raw('ms_rental_period.id::integer'),"=",\DB::raw('tr_contract.renprd_id::integer'))
        ->join('ms_virtual_account',\DB::raw('ms_virtual_account.id::integer'),"=",\DB::raw('tr_contract.viracc_id::integer'))
        ->join('ms_contract_status',\DB::raw('ms_contract_status.id::integer'),"=",\DB::raw('tr_contract.const_id::integer'))
        ->join('ms_unit',\DB::raw('ms_unit.id::integer'),"=",\DB::raw('tr_contract.unit_id::integer'))->first();
        return view('modal.contract', ['fetch' => $fetch]);
    }

    public function editModal(Request $request){
        try{
            $contractId = $request->id;
            $fetch = TrContract::select('tr_contract.*',\DB::raw('parent.contr_code as parent_code'),\DB::raw('parent.contr_no as parent_no'),'ms_tenant.tenan_code','ms_tenant.tenan_name','ms_tenant.tenan_idno','ms_marketing_agent.mark_code','ms_marketing_agent.mark_name','ms_virtual_account.viracc_no','ms_virtual_account.viracc_name','ms_virtual_account.viracc_isactive','ms_contract_status.const_code','ms_contract_status.const_name','ms_unit.unit_code','ms_unit.unit_name','ms_unit.unit_isactive')
            ->leftJoin(\DB::raw('tr_contract as parent'),\DB::raw('parent.id::integer'),"=",\DB::raw('tr_contract.contr_parent::integer'))
            ->join('ms_tenant',\DB::raw('ms_tenant.id::integer'),"=",\DB::raw('tr_contract.tenan_id::integer'))
            ->join('ms_marketing_agent',\DB::raw('ms_marketing_agent.id::integer'),"=",\DB::raw('tr_contract.mark_id::integer'))
            // ->join('ms_rental_period',\DB::raw('ms_rental_period.id::integer'),"=",\DB::raw('tr_contract.renprd_id::integer'))
            ->join('ms_virtual_account',\DB::raw('ms_virtual_account.id::integer'),"=",\DB::raw('tr_contract.viracc_id::integer'))
            ->join('ms_contract_status',\DB::raw('ms_contract_status.id::integer'),"=",\DB::raw('tr_contract.const_id::integer'))
            ->join('ms_unit',\DB::raw('ms_unit.id::integer'),"=",\DB::raw('tr_contract.unit_id::integer'))->first();
            $result = ['success'=>1, 'data'=>$fetch];
            return view('modal.editcontract', ['fetch' => $fetch]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function optionParent(Request $request){
        $key = $request->q;
        $fetch = TrContract::select('id','contr_code','contr_no')->where(\DB::raw('LOWER(contr_code)'),'like','%'.$key.'%')->orWhere(\DB::raw('LOWER(contr_no)'),'like','%'.$key.'%')->get();
        $result['results'] = [];
        array_push($result['results'], ['id'=>"0",'text'=>'No Parent']);
        foreach ($fetch as $key => $value) {
            $temp = ['id'=>$value->id, 'text'=>$value->contr_code." (".$value->contr_no.")"];
            array_push($result['results'], $temp);
        }
        return json_encode($result);
    }

    public function insert(Request $request){
        $messages = [
            'contr_code.unique' => 'Contract Code must be unique',
            'contr_no.unique' => 'Contract No must be unique',
        ];

        $validator = Validator::make($request->all(), [
            'contr_code' => 'required|unique:tr_contract',
            'contr_no' => 'required|unique:tr_contract',
        ], $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ['status' => 0, 'message' => $errors];
        }
        $input = [
            'contr_id' => 'CTR'.microtime(),
            'contr_parent' => $request->input('contr_parent'),
            'contr_code' => $request->input('contr_code'),
            'contr_no' => $request->input('contr_no'),
            'contr_startdate' => $request->input('contr_startdate'),
            'contr_enddate' => $request->input('contr_enddate'),
            'contr_bast_date' => $request->input('contr_bast_date'),
            'contr_bast_by' => $request->input('contr_bast_by'),
            'contr_note' => $request->input('contr_note'),
            'tenan_id' => $request->input('tenan_id'),
            'mark_id' => $request->input('mark_id'),
            // 'renprd_id' => $request->input('renprd_id'),
            'viracc_id' => $request->input('viracc_id'),
            'const_id' => $request->input('const_id'),
            'unit_id' => $request->input('unit_id')
        ];
        TrContract::create($input);
        return ['status' => 1, 'message' => 'Insert Success'];
    }

    public function update(Request $request){
        $messages = [
            'contr_code.unique' => 'Contract Code must be unique',
            'contr_no.unique' => 'Contract No must be unique',
        ];

        $validator = Validator::make($request->all(), [
            'contr_code' => 'required|unique:tr_contract,contr_code,'.$request->input('id'),
            'contr_no' => 'required|unique:tr_contract,contr_no,'.$request->input('id'),
        ], $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ['status' => 0, 'message' => $errors];
        }

        $update = [
            'contr_parent' => $request->input('contr_parent'),
            'contr_code' => $request->input('contr_code'),
            'contr_no' => $request->input('contr_no'),
            'contr_startdate' => $request->input('contr_startdate'),
            'contr_enddate' => $request->input('contr_enddate'),
            'contr_bast_date' => $request->input('contr_bast_date'),
            'contr_bast_by' => $request->input('contr_bast_by'),
            'contr_note' => $request->input('contr_note'),
            'tenan_id' => $request->input('tenan_id'),
            'mark_id' => $request->input('mark_id'),
            // 'renprd_id' => $request->input('renprd_id'),
            'viracc_id' => $request->input('viracc_id'),
            'const_id' => $request->input('const_id'),
            'unit_id' => $request->input('unit_id')
        ];
        TrContract::where('id',$request->input('id'))->update($update);
        return ['status' => 1, 'message' => 'Update Success'];
    }

    public function delete(Request $request){
        try{
            $id = $request->id;
            TrContract::destroy($id);
            return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }   
}
