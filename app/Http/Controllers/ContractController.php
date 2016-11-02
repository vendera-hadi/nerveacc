<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\TrContract;
use App\Models\MsCostItem;
use App\Models\MsInvoiceType;
use App\Models\MsCostDetail;
use App\Models\MsUnit;
use App\Models\TrContractInvoice;
use Validator;
use DB;

class ContractController extends Controller
{
    public function index(){
        $data['cost_items'] = MsCostDetail::select('ms_cost_detail.id','ms_cost_item.cost_name','ms_cost_item.cost_code','ms_cost_detail.costd_name')->join('ms_cost_item','ms_cost_detail.cost_id','=','ms_cost_item.id')->get();
        $invoice_types = MsInvoiceType::all();
        $data['invoice_types'] = '';
        foreach ($invoice_types as $key => $val) {
            $data['invoice_types'] = $data['invoice_types'].'<option value="'.$val->invtp_code.'">'.$val->invtp_name.'</option>';
        }
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
                $temp['action'] = '<a href="#" data-toggle="modal" data-target="#detailModal" data-id="'.$value->id.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a> <a href="#"  data-id="'.$value->id.'" class="editctr"><i class="fa fa-pencil" aria-hidden="true"></i><small>Contract</small></a> <a href="#"  data-id="'.$value->id.'" class="editcitm"><i class="fa fa-pencil" aria-hidden="true"></i><small>Cost Items</small></a> <a href="#" data-id="'.$value->id.'" class="remove"><i class="fa fa-times" aria-hidden="true"></i></a>';
                
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
        $costdetail = TrContractInvoice::select('ms_invoice_type.invtp_name','ms_cost_detail.costd_name','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin','ms_cost_detail.costd_ismeter')
                ->join('ms_invoice_type',\DB::raw('tr_contract_invoice.invtp_code'),"=",\DB::raw('ms_invoice_type.invtp_code'))
                ->join('ms_cost_detail',\DB::raw('tr_contract_invoice.costd_is::integer'),"=",\DB::raw('ms_cost_detail.id::integer'))
                ->where('contr_id',$contractId)
                ->get();
        return view('modal.contract', ['fetch' => $fetch, 'costdetail' => $costdetail]);
    }

    public function ctrDetail(Request $request){
        try{
            $contractId = $request->id;
            $fetch = TrContract::select('tr_contract.*',\DB::raw('parent.contr_code as parent_code'),\DB::raw('parent.contr_no as parent_no'),'ms_tenant.tenan_code','ms_tenant.tenan_name','ms_tenant.tenan_idno','ms_marketing_agent.mark_code','ms_marketing_agent.mark_name','ms_contract_status.const_code','ms_contract_status.const_name','ms_unit.unit_code','ms_unit.unit_virtual_accn','ms_unit.unit_name','ms_unit.unit_isactive')
            ->leftJoin(\DB::raw('tr_contract as parent'),\DB::raw('parent.id::integer'),"=",\DB::raw('tr_contract.contr_parent::integer'))
            ->join('ms_tenant',\DB::raw('ms_tenant.id::integer'),"=",\DB::raw('tr_contract.tenan_id::integer'))
            ->join('ms_marketing_agent',\DB::raw('ms_marketing_agent.id::integer'),"=",\DB::raw('tr_contract.mark_id::integer'))
            // ->join('ms_rental_period',\DB::raw('ms_rental_period.id::integer'),"=",\DB::raw('tr_contract.renprd_id::integer'))
            // ->join('ms_virtual_account',\DB::raw('ms_virtual_account.id::integer'),"=",\DB::raw('tr_contract.viracc_id::integer'))
            ->join('ms_contract_status',\DB::raw('ms_contract_status.id::integer'),"=",\DB::raw('tr_contract.const_id::integer'))
            ->join('ms_unit',\DB::raw('ms_unit.id::integer'),"=",\DB::raw('tr_contract.unit_id::integer'))->where('tr_contract.id', $contractId)->first();
            $result = ['success'=>1, 'data'=>$fetch];
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function citmDetail(Request $request){
        try{
            $contractId = $request->id;
            $costdetail = TrContractInvoice::select('ms_cost_detail.id','ms_cost_detail.cost_id','ms_invoice_type.invtp_code','ms_invoice_type.invtp_name','ms_cost_detail.costd_name','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin','ms_cost_detail.costd_ismeter','ms_cost_item.cost_name','ms_cost_item.cost_code')
                ->join('ms_invoice_type',\DB::raw('tr_contract_invoice.invtp_code'),"=",\DB::raw('ms_invoice_type.invtp_code'))
                ->join('ms_cost_detail',\DB::raw('tr_contract_invoice.costd_is::integer'),"=",\DB::raw('ms_cost_detail.id::integer'))
                ->join('ms_cost_item',\DB::raw('ms_cost_detail.cost_id::integer'),"=",\DB::raw('ms_cost_item.id::integer'))
                ->where('contr_id',$contractId)
                ->get();

            $invoice_types = MsInvoiceType::all();
            $inv_types_options = '';
            foreach ($invoice_types as $key => $val) {
                $inv_types_options = $inv_types_options.'<option value="'.$val->invtp_code.'">'.$val->invtp_name.'</option>';
            }
            $cost_items = MsCostDetail::select('ms_cost_detail.id','ms_cost_item.cost_name','ms_cost_item.cost_code','ms_cost_detail.costd_name')->join('ms_cost_item','ms_cost_detail.cost_id','=','ms_cost_item.id')->get();
            return view('modal.editcontract', ['id'=>$contractId, 'costdetail' => $costdetail, 'invoice_types'=>$invoice_types, 'cost_items' => $cost_items, 'inv_types_options' => $inv_types_options
                ]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
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
            ->join('ms_unit',\DB::raw('ms_unit.id::integer'),"=",\DB::raw('tr_contract.unit_id::integer'))->where('tr_contract.id', $contractId)->first();
            $result = ['success'=>1, 'data'=>$fetch];

            $costdetail = TrContractInvoice::select('ms_cost_detail.id','ms_cost_detail.cost_id','ms_invoice_type.invtp_code','ms_invoice_type.invtp_name','ms_cost_detail.costd_name','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin','ms_cost_detail.costd_ismeter','ms_cost_item.cost_name','ms_cost_item.cost_code')
                ->join('ms_invoice_type',\DB::raw('tr_contract_invoice.invtp_code'),"=",\DB::raw('ms_invoice_type.invtp_code'))
                ->join('ms_cost_detail',\DB::raw('tr_contract_invoice.costd_is::integer'),"=",\DB::raw('ms_cost_detail.id::integer'))
                ->join('ms_cost_item',\DB::raw('ms_cost_detail.cost_id::integer'),"=",\DB::raw('ms_cost_item.id::integer'))
                ->where('contr_id',$contractId)
                ->get();

            $invoice_types = MsInvoiceType::all();
            $inv_types_options = '';
            foreach ($invoice_types as $key => $val) {
                $inv_types_options = $inv_types_options.'<option value="'.$val->invtp_code.'">'.$val->invtp_name.'</option>';
            }
            $cost_items = MsCostItem::all();
            return view('modal.editcontract', ['fetch' => $fetch, 'costdetail' => $costdetail, 'invoice_types'=>$invoice_types, 'cost_items' => $cost_items, 'inv_types_options' => $inv_types_options
                ]);
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
            'contr_id' => 'CTR'.str_replace(".", "", str_replace(" ", "",microtime())),
            // 'contr_parent' => $request->input('contr_parent'),
            'contr_code' => $request->input('contr_code'),
            'contr_no' => $request->input('contr_no'),
            'contr_startdate' => $request->input('contr_startdate'),
            'contr_enddate' => $request->input('contr_enddate'),
            'contr_bast_date' => $request->input('contr_bast_date'),
            'contr_bast_by' => $request->input('contr_bast_by'),
            'contr_note' => $request->input('contr_note'),
            'tenan_id' => $request->input('tenan_id'),
            'mark_id' => $request->input('mark_id'),
            'viracc_id' => $request->input('viracc_id'),
            'const_id' => $request->input('const_id'),
            'unit_id' => $request->input('unit_id')
        ];
        $costd_ids = $request->input('costd_is'); 
        $inv_type = $request->input('inv_type');
        $cost_name = $request->input('cost_name');
        $cost_code = $request->input('cost_code');

        $cost_id = $request->input('cost_id');
        $costd_name = $request->input('costd_name');
        $costd_unit = $request->input('costd_unit');
        $costd_rate = $request->input('costd_rate');
        $costd_burden = $request->input('costd_burden');
        $costd_admin = $request->input('costd_admin');
        $inv_type_custom = $request->input('inv_type_custom');
        $is_meter = $request->input('is_meter');
        try{
            DB::transaction(function () use($input, $cost_id, $costd_name, $costd_unit, $costd_rate, $costd_burden, $costd_admin, $inv_type, $is_meter, $costd_ids, $inv_type_custom, $cost_name, $cost_code) {
                $contract = TrContract::create($input);
                
                // insert
                if(count($costd_ids) > 0){
                    $total = 0;
                    foreach ($costd_ids as $key => $value) {
                        $inputContractInv = [
                            'continv_id' => 'CONINV'.str_replace(".", "", str_replace(" ", "",microtime())),
                            'contr_id' => $contract->id,
                            'invtp_code' => $inv_type[$key],
                            'costd_is' => $costd_ids[$key],
                            'continv_amount' => $total
                        ];
                        TrContractInvoice::create($inputContractInv);
                    }
                }

                 // unit jadi unavailable
                 MsUnit::where('id',$request->input('unit_id'))->update(['unit_isavailable'=>0]); 

                // insert custom
                if(count($cost_name) > 0){
                    foreach ($cost_name as $key => $value) {
                        // cost item
                        $input = [
                                    'cost_id' => 'COST'.str_replace(".", "", str_replace(" ", "",microtime())),
                                    'cost_code' => $cost_code[$key],
                                    'cost_name' => $cost_name[$key],
                                    'created_by' => \Auth::id(),
                                    'updated_by' => \Auth::id()
                                ];
                        $cost = MsCostItem::create($input);

                        // cost detail
                        $costd_is = 'COSTD'.str_replace(".", "", str_replace(" ", "",microtime())); 
                        $input = [
                            'costd_is' => $costd_is,
                            'cost_id' => $cost->id,
                            'costd_name' => $costd_name[$key],
                            'costd_unit' => $costd_unit[$key],
                            'costd_rate' => $costd_rate[$key],
                            'costd_burden' => $costd_burden[$key],
                            'costd_admin' => $costd_admin[$key],
                            'costd_ismeter' => $is_meter[$key] 
                        ];
                        $costdt = MsCostDetail::create($input);

                        // contract invoice
                        $total = 0;
                        // $total = $costd_rate[$key] + $costd_burden[$key] + $costd_admin[$key];
                        $inputContractInv = [
                            'continv_id' => 'CONINV'.str_replace(".", "", str_replace(" ", "",microtime())),
                            'contr_id' => $contract->id,
                            'invtp_code' => $inv_type_custom[$key],
                            'costd_is' => $costdt->id,
                            'continv_amount' => $total
                        ];
                        TrContractInvoice::create($inputContractInv);
                    }

                }

            });
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
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
            // 'contr_parent' => $request->input('contr_parent'),
            'contr_code' => $request->input('contr_code'),
            'contr_no' => $request->input('contr_no'),
            'contr_startdate' => $request->input('contr_startdate'),
            'contr_enddate' => $request->input('contr_enddate'),
            'contr_bast_date' => $request->input('contr_bast_date'),
            'contr_bast_by' => $request->input('contr_bast_by'),
            'contr_note' => $request->input('contr_note')
        ];
        if($request->input('tenan_id')) $update['tenan_id'] = $request->input('tenan_id');
        if($request->input('mark_id')) $update['mark_id'] = $request->input('mark_id');
        if($request->input('const_id')) $update['const_id'] = $request->input('const_id');
        if($request->input('viracc_id')) $update['viracc_id'] = $request->input('viracc_id');
        if($request->input('unit_id')){ 
            $update['unit_id'] = $request->input('unit_id');
            // unit jadi unavailable
            MsUnit::where('id',$request->input('unit_id'))->update(['unit_isavailable'=>0]);
        }

        TrContract::where('id',$request->input('id'))->update($update);
        return ['status' => 1, 'message' => 'Update Success'];
    }

    public function costdetailUpdate(Request $request){
        $contractIDs = $request->input('contr_id');
        $contractID = $contractIDs[0];

        $cost_name = $request->input('cost_name');
        $cost_code = $request->input('cost_code');
        $inv_type_custom = $request->input('inv_type_custom');
        
        $costd_ids = $request->input('costd_id');
        $cost_id = $request->input('cost_id');
        $costd_name = $request->input('costd_name');
        $costd_unit = $request->input('costd_unit');
        $costd_rate = $request->input('costd_rate');
        $costd_burden = $request->input('costd_burden');
        $costd_admin = $request->input('costd_admin');
        $inv_type = $request->input('inv_type');
        $is_meter = $request->input('is_meter');
        try{
            DB::transaction(function () use($cost_id, $costd_ids, $costd_name, $costd_unit, $costd_rate, $costd_burden, $costd_admin, $inv_type, $is_meter, $contractID, $cost_name, $cost_code, $inv_type_custom){
                // delete all of cost detail of current contract id
                TrContractInvoice::where('contr_id',$contractID)->delete();               
                // reinsert to cost detail and tr contract invoice
                // insert
                if(count($costd_ids) > 0){
                    $total = 0;
                    foreach ($costd_ids as $key => $value) {
                        $inputContractInv = [
                            'continv_id' => 'CONINV'.str_replace(".", "", str_replace(" ", "",microtime())),
                            'contr_id' => $contractID,
                            'invtp_code' => $inv_type[$key],
                            'costd_is' => $costd_ids[$key],
                            'continv_amount' => $total
                        ];
                        TrContractInvoice::create($inputContractInv);
                    }
                } 

                // insert custom
                if(count($cost_name) > 0){
                    foreach ($cost_name as $key => $value) {
                        // cost item
                        $input = [
                                    'cost_id' => 'COST'.str_replace(".", "", str_replace(" ", "",microtime())),
                                    'cost_code' => $cost_code[$key],
                                    'cost_name' => $cost_name[$key],
                                    'created_by' => \Auth::id(),
                                    'updated_by' => \Auth::id()
                                ];
                        $cost = MsCostItem::create($input);

                        // cost detail
                        $costd_is = 'COSTD'.str_replace(".", "", str_replace(" ", "",microtime())); 
                        $input = [
                            'costd_is' => $costd_is,
                            'cost_id' => $cost->id,
                            'costd_name' => $costd_name[$key],
                            'costd_unit' => $costd_unit[$key],
                            'costd_rate' => $costd_rate[$key],
                            'costd_burden' => $costd_burden[$key],
                            'costd_admin' => $costd_admin[$key],
                            'costd_ismeter' => $is_meter[$key] 
                        ];
                        $costdt = MsCostDetail::create($input);

                        // contract invoice
                        $total = 0;
                        // $total = $costd_rate[$key] + $costd_burden[$key] + $costd_admin[$key];
                        $inputContractInv = [
                            'continv_id' => 'CONINV'.str_replace(".", "", str_replace(" ", "",microtime())),
                            'contr_id' => $contractID,
                            'invtp_code' => $inv_type_custom[$key],
                            'costd_is' => $costdt->id,
                            'continv_amount' => $total
                        ];
                        TrContractInvoice::create($inputContractInv);
                    }

                }
            });
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
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
