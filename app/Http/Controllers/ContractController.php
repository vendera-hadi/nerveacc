<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\TrContract;
use App\Models\MsCostItem;
use App\Models\MsInvoiceType;
use App\Models\MsCostDetail;
use App\Models\MsUnit;
use App\Models\MsMarketingAgent;
use App\Models\TrContractInvoice;
use App\Models\TrContractLog;
use App\Models\TrContractInvLog;
use Validator;
use DB;

class ContractController extends Controller
{
    public function index(){
        $data['cost_items'] = MsCostDetail::select('ms_cost_detail.id','ms_cost_item.cost_name','ms_cost_item.cost_code','ms_cost_detail.costd_name')->join('ms_cost_item','ms_cost_detail.cost_id','=','ms_cost_item.id')->get();
        $invoice_types = MsInvoiceType::all();
        $data['marketing_agents'] = MsMarketingAgent::all(); 
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
                $temp['contr_startdate'] = date('d/m/Y',strtotime($value->contr_startdate));
                $temp['contr_enddate'] = date('d/m/Y',strtotime($value->contr_enddate));
                $temp['tenan_name'] = $value->tenan_name;
                if($value->contr_status == 'confirmed') $status = '<strong class="text-success">'.$value->contr_status.'</strong>';
                else if($value->contr_status == 'cancelled' || $value->contr_status == 'closed') $status = '<strong class="text-danger">'.$value->contr_status.'</strong>';
                else $status = '<strong>'.$value->contr_status.'</strong>';
                $temp['contr_status'] = $status;
                $temp['contr_terminate_date'] = !empty($value->contr_terminate_date) ? date('d/m/Y',strtotime($value->contr_terminate_date)) : '';
                if($value->contr_status != 'confirmed') $confirmed = '<a href="#" title="Edit Contract" data-id="'.$value->id.'" class="editctr"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                                                    &nbsp;<a href="#" title="Edit Cost Item" data-id="'.$value->id.'" class="editcitm"><i class="fa fa-dollar" aria-hidden="true"></i></a>
                                                                    &nbsp;<a href="#" title="Cancel Contract" data-id="'.$value->id.'" class="remove"><i class="fa fa-times" aria-hidden="true"></i></a>';
                else if($value->contr_status == 'confirmed' && !empty($value->contr_terminate_date) ) $confirmed = '<a href="#" title="Cancel Contract" data-id="'.$value->id.'" class="remove"><i class="fa fa-times" aria-hidden="true"></i></a>';
                else $confirmed = '';
                
                if($value->contr_status != 'cancelled') $temp['action'] = '<a href="#" title="View Detail" data-toggle="modal" data-target="#detailModal" data-id="'.$value->id.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a>&nbsp; '.$confirmed;
                else $temp['action'] = '';
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
        ->join('ms_virtual_account',\DB::raw('ms_virtual_account.viracc_no'),"=",\DB::raw('tr_contract.viracc_id'))
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
            $fetch = TrContract::select('tr_contract.*','ms_virtual_account.viracc_no','ms_tenant.tenan_code','ms_tenant.tenan_name','ms_tenant.tenan_idno','ms_marketing_agent.mark_code','ms_marketing_agent.mark_name','ms_unit.unit_code','ms_unit.unit_virtual_accn','ms_unit.unit_name','ms_unit.unit_isactive')
            ->join('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id')
            ->leftJoin('ms_marketing_agent','ms_marketing_agent.id',"=",'tr_contract.mark_id')
            ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
            ->join('ms_virtual_account','ms_virtual_account.id',"=",'ms_unit.unit_virtual_accn')
            // ->join('ms_contract_status',\DB::raw('ms_contract_status.id::integer'),"=",\DB::raw('tr_contract.const_id::integer'))
            ->where('tr_contract.id', $contractId)->first();
            $result = ['success'=>1, 'data'=>$fetch];
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function citmDetail(Request $request){
        try{
            $contractId = $request->id;
            $costdetail = TrContractInvoice::select('ms_cost_detail.id','ms_cost_detail.cost_id','ms_invoice_type.invtp_code','ms_invoice_type.invtp_name','ms_cost_detail.costd_name','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin','ms_cost_detail.costd_ismeter','ms_cost_item.cost_name','ms_cost_item.cost_code','tr_contract_invoice.continv_period')
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
            'contr_code' => $request->input('contr_code'),
            'contr_no' => $request->input('contr_no'),
            'contr_startdate' => $request->input('contr_startdate'),
            'contr_enddate' => $request->input('contr_enddate'),
            'contr_bast_date' => $request->input('contr_bast_date'),
            'contr_bast_by' => $request->input('contr_bast_by'),
            'contr_note' => $request->input('contr_note'),
            'contr_status' => 'inputed',
            'tenan_id' => $request->input('tenan_id'),
            'mark_id' => $request->input('mark_id'),
            'viracc_id' => $request->input('viracc_id'),
            'const_id' => $request->input('const_id',0),
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
        $periods = $request->input('period');
        $is_meter = $request->input('is_meter');
        try{
            DB::transaction(function () use($input, $request, $cost_id, $costd_name, $costd_unit, $costd_rate, $costd_burden, $costd_admin, $inv_type, $is_meter, $costd_ids, $inv_type_custom, $cost_name, $cost_code, $periods) {
                $contract = TrContract::create($input);
                
                // insert
                if(count($costd_ids) > 0){
                    $total = 0;
                    foreach ($costd_ids as $key => $value) {
                        $inputContractInv = [
                            'contr_id' => $contract->id,
                            'invtp_code' => $inv_type[$key],
                            'costd_is' => $costd_ids[$key],
                            'continv_amount' => $total,
                            'continv_period' => $periods[$key]
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
                            'contr_id' => $contract->id,
                            'invtp_code' => $inv_type_custom[$key],
                            'costd_is' => $costdt->id,
                            'continv_amount' => $total,
                            'continv_period' => $periods[$key]
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
        // if($request->input('const_id')) $update['const_id'] = $request->input('const_id');
        if($request->input('viracc_id')) $update['viracc_id'] = $request->input('viracc_id');
        if(!empty($request->input('unit_id')) && $request->input('current_unit_id') != $request->input('unit_id')){ 
            $update['unit_id'] = $request->input('unit_id');
            // unit lama jadi available
            MsUnit::where('id',$request->input('current_unit_id'))->update(['unit_isavailable'=>1]);
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
        $periods = $request->input('period');
        $is_meter = $request->input('is_meter');
        try{
            DB::transaction(function () use($cost_id, $costd_ids, $costd_name, $costd_unit, $costd_rate, $costd_burden, $costd_admin, $inv_type, $is_meter, $contractID, $cost_name, $cost_code, $inv_type_custom, $periods){
                // delete all of cost detail of current contract id
                TrContractInvoice::where('contr_id',$contractID)->delete();               
                // reinsert to cost detail and tr contract invoice
                // insert
                if(count($costd_ids) > 0){
                    $total = 0;
                    foreach ($costd_ids as $key => $value) {
                        $inputContractInv = [
                            'contr_id' => $contractID,
                            'invtp_code' => $inv_type[$key],
                            'costd_is' => $costd_ids[$key],
                            'continv_amount' => $total,
                            'continv_period' => $periods[$key]
                        ];
                        TrContractInvoice::create($inputContractInv);
                    }
                } 

                // insert custom
                // if(count($cost_name) > 0){
                //     foreach ($cost_name as $key => $value) {
                //         // cost item
                //         $input = [
                //                     'cost_id' => 'COST'.str_replace(".", "", str_replace(" ", "",microtime())),
                //                     'cost_code' => $cost_code[$key],
                //                     'cost_name' => $cost_name[$key],
                //                     'created_by' => \Auth::id(),
                //                     'updated_by' => \Auth::id()
                //                 ];
                //         $cost = MsCostItem::create($input);

                //         // cost detail
                //         $costd_is = 'COSTD'.str_replace(".", "", str_replace(" ", "",microtime())); 
                //         $input = [
                //             'costd_is' => $costd_is,
                //             'cost_id' => $cost->id,
                //             'costd_name' => $costd_name[$key],
                //             'costd_unit' => $costd_unit[$key],
                //             'costd_rate' => $costd_rate[$key],
                //             'costd_burden' => $costd_burden[$key],
                //             'costd_admin' => $costd_admin[$key],
                //             'costd_ismeter' => $is_meter[$key] 
                //         ];
                //         $costdt = MsCostDetail::create($input);

                //         // contract invoice
                //         $total = 0;
                //         // $total = $costd_rate[$key] + $costd_burden[$key] + $costd_admin[$key];
                //         $inputContractInv = [
                //             'continv_id' => 'CONINV'.str_replace(".", "", str_replace(" ", "",microtime())),
                //             'contr_id' => $contractID,
                //             'invtp_code' => $inv_type_custom[$key],
                //             'costd_is' => $costdt->id,
                //             'continv_amount' => $total
                //         ];
                //         TrContractInvoice::create($inputContractInv);
                //     }

                // }
            });
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
        return ['status' => 1, 'message' => 'Update Success'];
    }

    public function delete(Request $request){
        try{
            $id = $request->id;
            // TrContract::destroy($id);
            TrContract::where('id',$id)->update(['contr_iscancel' => true,'contr_cancel_date' => date('Y-m-d'), 'contr_status' => 'cancelled']);
            return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function confirmation(){
        $data['pageType'] = 'Confirmation';
        return view('contract_other_template', $data);
    }

    public function addendum(){
        $data['pageType'] = 'Addendum';
        return view('contract_other_template', $data);
    }

    public function termination(){
        $data['pageType'] = 'Termination';
        return view('contract_other_template', $data);
    }

    public function renewal(){
        $data['pageType'] = 'Renewal';
        return view('contract_other_template', $data);
    }

    public function getOther(Request $request, $pageName = null){
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
                    ->join('ms_tenant',\DB::raw('ms_tenant.id::integer'),"=",\DB::raw('tr_contract.tenan_id::integer'))
                    ->whereNull('tr_contract.contr_terminate_date');
            // filter page
            if(strtolower($pageName) == 'confirmation'){
                $fetch = $fetch->where('tr_contract.contr_status','inputed');
            }else{
                $fetch = $fetch->where('tr_contract.contr_status','confirmed');
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
                $temp['contr_code'] = $value->contr_code;
                $temp['contr_no'] = $value->contr_no;
                $temp['contr_startdate'] = $value->contr_startdate;
                $temp['contr_enddate'] = $value->contr_enddate;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['contr_status'] = $value->contr_status;
                $temp['contr_terminate_date'] = $value->contr_terminate_date;
                if(strtolower($pageName) == 'confirmation'){
                    $temp['action'] = '<a href="#" data-id="'.$value->id.'" class="confirmStatus"><i class="fa fa-check" aria-hidden="true"></i></a>';
                }else if(strtolower($pageName) == 'addendum'){
                    $temp['action'] = '<a href="#" data-id="'.$value->id.'" class="rollbackStatus"><i class="fa fa-ban" aria-hidden="true"></i></a>';
                }else if(strtolower($pageName) == 'termination'){
                    $temp['action'] = '<a href="#" data-id="'.$value->id.'" class="terminateStatus"><i class="fa fa-times" aria-hidden="true"></i></a>';
                }else if(strtolower($pageName) == 'renewal'){
                    $temp['action'] = '<a href="#" data-id="'.$value->id.'" data-code="'.$value->contr_code.'" data-no="'.$value->contr_no.'" data-start="'.$value->contr_startdate.'" data-end="'.$value->contr_enddate.'" class="renewStatus"><i class="fa fa-copy" aria-hidden="true"></i></a>';       
                }
                // $temp['action'] = '<a href="#" data-toggle="modal" data-target="#detailModal" data-id="'.$value->id.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a> <a href="#"  data-id="'.$value->id.'" class="editctr"><i class="fa fa-pencil" aria-hidden="true"></i><small>Contract</small></a> <a href="#"  data-id="'.$value->id.'" class="editcitm"><i class="fa fa-pencil" aria-hidden="true"></i><small>Cost Items</small></a> <a href="#" data-id="'.$value->id.'" class="remove"><i class="fa fa-times" aria-hidden="true"></i></a>';
                
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function confirm(Request $request){
        $id = $request->id;
        TrContract::where('id',$id)->update(['contr_status'=>'confirmed']);
        return response()->json(['success'=>true]);
    }

    public function terminate(Request $request){
        $id = $request->id;
        $date = $request->contr_terminate_date;
        // TrContract::where('id',$id)->update(['contr_terminate_date'=>date('Y-m-d')]);
        TrContract::where('id',$id)->update(['contr_terminate_date'=> $date]);

        $contract = TrContract::find($id);
        // update log juga
        TrContractLog::create([
                'contlog_code' => $contract->contr_code,
                'contlog_no' => $contract->contr_no,
                'contlog_startdate' => $contract->contr_startdate,
                'contlog_enddate' => $contract->contr_enddate,
                'contlog_bast_date' => $contract->contr_bast_date,
                'contlog_bast_by' => $contract->contr_bast_by,
                'contlog_note' => $contract->note,
                'contr_id' => $contract->id,
                'tenan_id' => $contract->tenan_id,
                'viracc_id' => $contract->viracc_id
            ]);
        // tr contract inv log
        $continv = TrContractInvoice::where('contr_id',$id)->get();
        if(count($continv) > 0){
            foreach ($continv as $value) {
                TrContractInvLog::create([
                        'continv_amount' => $value->continv_amount,
                        'contr_id' => $id,
                        'invtp_code' => $value->invtp_code,
                        'costd_is' => $value->costd_is
                    ]);
            }
        }
        return response()->json(['success'=>true]);
    }

    public function inputed(Request $request){
        $id = $request->id;
        $note = $request->note;
        TrContract::where('id',$id)->update(['contr_status'=>'inputed', 'contr_note'=>$note]);

        $contract = TrContract::find($id);
        // update log juga
        TrContractLog::create([
                'contlog_code' => $contract->contr_code,
                'contlog_no' => $contract->contr_no,
                'contlog_startdate' => $contract->contr_startdate,
                'contlog_enddate' => $contract->contr_enddate,
                'contlog_bast_date' => $contract->contr_bast_date,
                'contlog_bast_by' => $contract->contr_bast_by,
                'contlog_note' => $contract->note,
                'contr_id' => $contract->id,
                'tenan_id' => $contract->tenan_id,
                'viracc_id' => $contract->viracc_id
            ]);
        // tr contract inv log
        $continv = TrContractInvoice::where('contr_id',$id)->get();
        if(count($continv) > 0){
            foreach ($continv as $value) {
                TrContractInvLog::create([
                        'continv_amount' => $value->continv_amount,
                        'contr_id' => $id,
                        'invtp_code' => $value->invtp_code,
                        'costd_is' => $value->costd_is
                    ]);
            }
        }
        return response()->json(['success'=>true]);
    }

    public function renew(Request $request){
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
            return ['errorMsg' => $errors];
        }

        // new contract date must be after old one
        $id = $request->id;
        $startdate = $request->contr_startdate;
        $enddate = $request->contr_enddate;
        $current = TrContract::find($id);
        if($startdate < $current->contr_enddate) return ['errorMsg' => 'New Contract must be after the end date of the old contract one (after '.date('d/m/Y',strtotime($current->contr_enddate)).')'];
        if($startdate >= $enddate) return ['errorMsg' => 'Start date must be less than End Date'];

        try{
            DB::transaction(function () use($current, $startdate, $enddate, $request, $id){
                $newdata = $current->replicate();
                // $newdata->contr_id = 'CTR'.str_replace(".", "", str_replace(" ", "",microtime()));
                $newdata->contr_startdate = $startdate;
                $newdata->contr_enddate = $enddate;
                $newdata->contr_code = $request->contr_code;
                $newdata->contr_no = $request->contr_no;
                $newdata->save();
                $newContractId = $newdata->id;

                $ctrInvoices = TrContractInvoice::where('contr_id',$id)->get();
                if($ctrInvoices->count() > 0){
                    foreach ($ctrInvoices as $key => $ctrInvoice) {
                        unset($ctrInvoice->id);
                        unset($ctrInvoice->continv_id);
                        $ctrInvoice->continv_id = 'CONINV'.str_replace(".", "", str_replace(" ", "",microtime()));
                        $ctrInvoice->contr_id = $newContractId;
                        $ctrInvoice = json_decode(json_encode($ctrInvoice), true);
                        TrContractInvoice::create($ctrInvoice);
                    }
                }
            });
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
        return response()->json(['success'=>true]);
    }
}
