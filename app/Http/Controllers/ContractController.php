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
use App\Models\MsUnitOwner;
use App\Models\TrContractInvoice;
use App\Models\TrContractLog;
use App\Models\TrContractInvLog;
use App\Models\TrPeriodMeter;
use App\Models\TrMeter;
use App\Models\TrInvoice;
use App\Models\TrInvoiceDetail;
use App\Models\CutoffHistory;
use App\Models\MsCompany;
use Validator;
use DB;
use Auth;
use Carbon\Carbon;

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
        $fetch = TrContract::select('tr_contract.*','ms_tenant.tenan_code','ms_tenant.tenan_name','ms_tenant.tenan_idno','ms_marketing_agent.mark_code','ms_marketing_agent.mark_name','ms_virtual_account.viracc_no','ms_virtual_account.viracc_name','ms_virtual_account.viracc_isactive','ms_unit.unit_code','ms_unit.unit_name','ms_unit.unit_isactive')
        ->join('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id')
        ->leftJoin('ms_marketing_agent','ms_marketing_agent.id',"=",'tr_contract.mark_id')
        ->join('ms_virtual_account','ms_virtual_account.id',"=",'tr_contract.viracc_id')
        ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')->first();
        $costdetail = TrContractInvoice::select('ms_invoice_type.invtp_name','ms_cost_detail.costd_name','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin','ms_cost_detail.costd_ismeter')
                ->join('ms_invoice_type','tr_contract_invoice.invtp_id',"=",'ms_invoice_type.id')
                ->join('ms_cost_detail','tr_contract_invoice.costd_id',"=",'ms_cost_detail.id')
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

    public function unclosed(){
        return view('contract_unclosed');
    }

    public function unclosedList(Request $request){
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
                    ->join('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id')->where(function($query){
                        $query->where('contr_terminate_date','<=', date("Y-m-d", strtotime("+1 week")))->where('contr_status','confirmed');
                    })->orWhere(function($query){
                        $query->where('contr_enddate','<=', date("Y-m-d", strtotime("+1 week")))->whereNull('contr_terminate_date')->where('contr_status','confirmed');
                    });
            // pake ini utk list yg semingguan 
            // whereBetween('contr_terminate_date', [date('Y-m-d'), date("Y-m-d", strtotime("+1 week"))])

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
                $created = new Carbon($value->contr_enddate);
                $now = new Carbon(date('Y-m-d'));
                $datediff = ($created->diff($now)->days < 1) ? 'today' : $created->diffInDays($now, false);
                if($created->diffInDays($now, false) < 0 && $created->diffInDays($now, false) > -31) $datediff = " <strong>(H".$datediff.")</strong>";
                else if($created->diffInDays($now, false) > 0 || $created->diffInDays($now, false) < -31) $datediff = "";
                $temp['contr_enddate'] = date('d/m/Y',strtotime($value->contr_enddate)).$datediff;
                $temp['enddate_diff'] = $created->diffInDays($now, false);
                $temp['tenan_name'] = $value->tenan_name;
                if($value->contr_status == 'confirmed') $status = '<strong class="text-success">'.$value->contr_status.'</strong>';
                else if($value->contr_status == 'cancelled' || $value->contr_status == 'closed') $status = '<strong class="text-danger">'.$value->contr_status.'</strong>';
                else $status = '<strong>'.$value->contr_status.'</strong>';
                $temp['contr_status'] = $status;

                $terminate = !empty($value->contr_terminate_date) ? new Carbon($value->contr_terminate_date) : '';
                $now = new Carbon(date('Y-m-d'));
                if($terminate != ''){ 
                    $datediffCount = $terminate->diffInDays($now, false);
                    if($datediffCount > 0) $datediffCount = '+'.$datediffCount." LATE";
                    $datediff = ($terminate->diff($now)->days < 1) ? 'today' : 'H'.$datediffCount;
                    $datediff = "<strong>(".$datediff.")</strong>";
                    $temp['terminate_diff'] = $terminate->diffInDays($now, false);
                }else{ 
                    $datediff = "";
                    $temp['terminate_diff'] = "";
                }

                $temp['contr_terminate_date'] = !empty($value->contr_terminate_date) ? date('d/m/Y',strtotime($value->contr_terminate_date))." ".$datediff : '';                
                $temp['action'] = '<a href="#" title="View Detail" data-toggle="modal" data-target="#detailModal" data-id="'.$value->id.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a>&nbsp; <a href="#" title="Close Contract" data-toggle="modal" data-target="#closeCtrModal" data-id="'.$value->id.'" class="closeContract"><i class="fa fa-sign-out" aria-hidden="true"></i></a>';
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function closeCtrModal(Request $request){
        $id = $request->id;
        $contract = TrContract::find($id);
        // cek apakah si contract ini adalah si owner
        $cekOwner = MsUnitOwner::where('unit_id',$contract->unit_id)->where('tenan_id',$contract->tenan_id)->first();
        $data['cutoffFlag'] = 0;
        // klo owner, tetep generate tapi minta renew contract. kalo bukan owner, generate dan cutoff nya dilimpahin 
        if(!$cekOwner) $data['cutoffFlag'] = 1;

        // cari kontrak pairing, owner dari unit tersebut

        // get all meter yang ada di unit si tenant
        $data['contInvMeter'] = TrContractInvoice::join('ms_cost_detail','tr_contract_invoice.costd_id','=','ms_cost_detail.id')
                                ->join('tr_contract','tr_contract.id','=','tr_contract_invoice.contr_id')
                                ->join('ms_unit','tr_contract.unit_id','=','ms_unit.id')
                                ->join('ms_cost_item','ms_cost_detail.cost_id','=','ms_cost_item.id')
                                ->where('contr_id',$id)->where('costd_ismeter',1)->get();
        $data['contInvNoMeter'] = TrContractInvoice::join('ms_cost_detail','tr_contract_invoice.costd_id','=','ms_cost_detail.id')
                                ->join('tr_contract','tr_contract.id','=','tr_contract_invoice.contr_id')
                                ->join('ms_unit','tr_contract.unit_id','=','ms_unit.id')
                                ->join('ms_cost_item','ms_cost_detail.cost_id','=','ms_cost_item.id')
                                ->where('contr_id',$id)->where('costd_ismeter',0)->get();
        if(count($data['contInvMeter']) > 0){
            $data['contractNo'] = $data['contInvMeter'][0]->contr_no;
            $data['unitCode'] = $data['contInvMeter'][0]->unit_code;
        }else{
            $data['contractNo'] = $data['contInvNoMeter'][0]->contr_no;
            $data['unitCode'] = $data['contInvNoMeter'][0]->unit_code;
        }
        $data['contr_id'] = $id;
        $data['tenan_id'] = $contract->tenan_id;
        // LAST MONTH PERIOD METER
        $tempTimeStart = date("Y-m-01", strtotime("-1 months"));
        $tempTimeEnd = date("Y-m-t", strtotime($tempTimeStart));
        $lastMonthPeriod = TrPeriodMeter::where('prdmet_start_date','>=',$tempTimeStart)->where('prdmet_end_date','<=',$tempTimeEnd)->where('status',1)->orderBy('id','desc')->first();                 
        // kalau last month period ketemu, tampung meter start nya adalah meter end dari period kmaren
        $lastMeterLog = [];
        if($lastMonthPeriod){
            $lastMeter = TrMeter::where('prdmet_id',$lastMonthPeriod->id)->get();
            if($lastMeter){
                foreach ($lastMeter as $lmtr) {
                    $lastMeterLog[$lmtr->costd_id] = $lmtr->meter_end;
                }
            }  
        }
        $data['lastMeter'] = $lastMeterLog;
        return view('modal.closecontract', $data);
    }

    public function closeCtrProcess(Request $request){
        // var_dump($request->all()); die();
        $meter_units = @$request->unit_id;
        $meter_costdids = @$request->costd_id;
        $meter_start = @$request->meter_start;
        $meter_end = @$request->meter_end;
        $meter_rate = @$request->meter_rate;
        $meter_burden = @$request->meter_burden;
        $meter_admin = @$request->meter_admin;
        $nonmeter_unit_id = @$request->nonmeter_unit_id;
        $nonmeter_costd_id = @$request->nonmeter_costd_id;
        $nonmeter_rate = @$request->nonmeter_rate;
        $nonmeter_burden = @$request->nonmeter_burden;
        $nonmeter_admin = @$request->nonmeter_admin;

        $contr_id = @$request->contr_id;
        $tenan_id = @$request->tenan_id;
        $cutoffStatus = @$request->cutoff;

        $insertCutoff = [];
        $insertTrMeter = [];
        $insertInvDetail = [];
        
        $year = date('Y');
        $month = date('m');

        $companyData = MsCompany::first();

        // GENERATE INVOICE NON METER
        if(count($nonmeter_unit_id) > 0){
            $groupsInv = TrContractInvoice::select('invtp_id')->join('ms_invoice_type','tr_contract_invoice.invtp_id','=','ms_invoice_type.id')
                        ->join('ms_cost_detail','tr_contract_invoice.costd_id','=','ms_cost_detail.id')
                        ->where('contr_id',$contr_id)->where('costd_ismeter',0)->groupBy('invtp_id')->get();

            // jika bukan owner, cari contract owner
            if(!empty($cutoffStatus)){
                $owner = MsUnitOwner::where('unit_id', $nonmeter_unit_id[0])->first();
                if(empty($owner)) return response()->json(['error'=>true, 'message'=>'Unit Owner not Found, Please Create New Contract of Unit Owner first']);
                // kalau owner punya, cari contract nya owner
                $contractOwner = TrContract::where('tenan_id',$owner->tenan_id)->where('unit_id',$nonmeter_unit_id[0])->first();
                if(empty($contractOwner)) return response()->json(['error'=>true, 'message'=>'Contract Owner of this Unit not Found, Please Create New Contract of Unit Owner first']);
            }

            $groups = [];
            foreach ($groupsInv as $grp) {
                $contrInv = TrContractInvoice::join('ms_invoice_type','tr_contract_invoice.invtp_id','=','ms_invoice_type.id')
                        ->where('contr_id',$contr_id)->where('invtp_id',$grp->invtp_id)->get();
                foreach ($contrInv as $cinv) {
                    $groups[$grp->invtp_id][] = $cinv->costd_id;
                }
            }
// MASI ADA LOGIKA OPER COST DETAIL
            foreach($groups as $keygrp => $grp){
                $totalAmount = 0;
                $totalAmountOwner = 0;
                $insertOwnerInvDetail[$keygrp] = [];
                // looping jumlah masukan form
                foreach($nonmeter_unit_id as $key => $unit) {
                    if(in_array($nonmeter_costd_id[$key], $grp)){
                        // dapetin rate dari cost detail
                        $currCostDetail = MsCostDetail::join('ms_cost_item','ms_cost_detail.cost_id','=','ms_cost_item.id')->where('ms_cost_detail.id',$nonmeter_costd_id[$key])->first();
                        // LOGIKA SERVICE CHARGE
                        if($currCostDetail->is_service_charge){
                            // find unit utk ngambil luas unit
                            $currUnit = MsUnit::find($unit);
                            // cari periode invoice
                            $currTrInv = TrContractInvoice::where('contr_id',$contr_id)->where('invtp_id',$keygrp)->where('costd_id',$grp)->first();
                            $totalDayinPeriod = $currTrInv->continv_period * 30;
                            $date1 = date_create(date('Y-m-d'));
                            if(!empty($currTrInv->continv_start_inv)){ 
                                $startPeriodInv = $currTrInv->continv_start_inv;
                                $endPeriodInv = $currTrInv->continv_next_inv;
                            }else{ 
                                $startPeriodInv = date('01-m-Y');
                                $endPeriodInv = date('t-m-Y'); 
                            }
                            $date2 = date_create($startPeriodInv);
                            $usedDay = date_diff($date1, $date2)->format('%a');
                            $daysLeft = $totalDayinPeriod - $usedDay;
                            
                            // prorate cost tenan
                            $tempProrateCost = ($usedDay / $totalDayinPeriod * $nonmeter_rate[$key] * $currUnit->unit_sqrt) + $nonmeter_burden[$key] + $nonmeter_admin[$key];
                            $tempProrateCost = floor($tempProrateCost);
                            $totalAmount+=$tempProrateCost;
                            $insertInvDetail[$keygrp][] = ['invdt_amount' => $tempProrateCost, 'invdt_note' => $currCostDetail->costd_name." Periode ".date('d-m-Y',strtotime($startPeriodInv))." s/d ".date('d-m-Y')." (Closed)",
                                                'costd_id'=>$meter_costdids[$key]];
                            
                            // hitungan cutoff an owner
                            if(!empty($cutoffStatus)){
                                $tempProrateCostOwner = ($daysLeft / $totalDayinPeriod * $nonmeter_rate[$key] * $currUnit->unit_sqrt) + $nonmeter_burden[$key] + $nonmeter_admin[$key];
                                $tempProrateCostOwner = floor($tempProrateCostOwner);
                                $insertOwnerInvDetail[$keygrp][] = ['invdt_amount' => $tempProrateCostOwner, 'invdt_note' => $currCostDetail->costd_name." Periode ".date('d-m-Y')." s/d ".date('d-m-Y',strtotime($endPeriodInv))." (Cutoff Tenan)",
                                                'costd_id'=>$meter_costdids[$key]];
                            }
                        }else if($currCostDetail->is_sinking_fund){

                        }else if($currCostDetail->is_insurance){

                        }else{

                        }
                        die();
                    }
                }
            }

        }

        // GENERATE INVOICE METER
        // if meter input exist
        // if(count($meter_units) > 0){
        //     $proRateMeterRatio = date('d') / date('t');
        //     
        //     // grouping by Invoice Type
        //     $groupsInv = TrContractInvoice::select('invtp_id')->join('ms_invoice_type','tr_contract_invoice.invtp_id','=','ms_invoice_type.id')
        //                 ->join('ms_cost_detail','tr_contract_invoice.costd_id','=','ms_cost_detail.id')
        //                 ->where('contr_id',$contr_id)->where('costd_ismeter',1)->groupBy('invtp_id')->get();

        //     $groups = [];
        //     // SETIAP INV TYPE BIKIN 1 INVOICE
        //     foreach ($groupsInv as $grp) {
        //         $contrInv = TrContractInvoice::join('ms_invoice_type','tr_contract_invoice.invtp_id','=','ms_invoice_type.id')
        //                 ->where('contr_id',$contr_id)->where('invtp_id',$grp->invtp_id)->get();
        //         foreach ($contrInv as $cinv) {
        //             $groups[$grp->invtp_id][] = $cinv->costd_id;
        //         }
        //     }

        //     // $contrInv = TrContractInvoice::join('ms_invoice_type','tr_contract_invoice.invtp_id','=','ms_invoice_type.id')
        //                 // ->where('contr_id',$contr_id)->where('costd_id',$meter_costdids[0])->get();
        //     // siapin buat inv type
        //     // $invType = $contrInv->invtp_code;
        //     foreach($groups as $keygrp => $grp){
        //         $totalAmount = 0;
        //         foreach($meter_units as $key => $unit) {
        //             if(in_array($meter_costdids[$key], $grp)){
        //                 // input ke cutoff meter
        //                 $insertCutoff[$keygrp][] = ['unit_id'=>$unit, 'costd_id'=>$meter_costdids[$key], 'meter_start' => $meter_start[$key], 'meter_end'=>$meter_end[$key], 'close_date'=>date('Y-m-d')];
        //                 // input ke tr meter(optional)
        //                 $tempMeterUsed = $meter_end[$key] - $meter_start[$key];
        //                 $tempMeterCost = ($proRateMeterRatio * $tempMeterUsed * $meter_rate[$key]) + $meter_burden[$key] + $meter_admin[$key];
        //                 $totalAmount+=$tempMeterCost;
        //                 $insertTrMeter[$keygrp][] = ['meter_start' => $meter_start[$key], 'meter_end'=>$meter_end[$key], 'meter_used'=>$tempMeterUsed, 'meter_cost' => $tempMeterCost, 'meter_burden' => $meter_burden[$key], 'meter_admin' => $meter_admin[$key], 'costd_id' => $meter_costdids[$key], 'prdmet_id' => 0, 'contr_id' => $contr_id, 'unit_id'=>$unit ];    
                        
        //                 // buat inv detail
        //                 $tempCostdt = MsCostDetail::find($meter_costdids[$key]);
        //                 $insertInvDetail[$keygrp][] = ['invdt_amount' => $tempMeterCost, 'invdt_note' => $tempCostdt->costd_name." Periode ".date('01-m-Y')." s/d ".date('d-m-Y')." (Closed)",
        //                                         'costd_id'=>$meter_costdids[$key]];
        //             }
        //         }
        //         if($totalAmount <= $companyData->comp_materai1_amount) $insertInvDetail[$keygrp][] = ['invdt_amount' => $companyData->comp_materai1, 'invdt_note' => 'Stamp Duty', 'costd_id'=> 0];
        //         else $insertInvDetail[$keygrp][] = ['invdt_amount' => $companyData->comp_materai2, 'invdt_note' => 'Stamp Duty', 'costd_id'=> 0];
                
        //         $invoiceType = MsInvoiceType::find($keygrp);
        //         $lastInvoiceofMonth = TrInvoice::select('inv_number')->where('inv_number','like','CL-'.str_replace(" ", "", $invoiceType->invtp_prefix).'-'.substr($year, -2).$month.'-%')->orderBy('id','desc')->first();
        //         if($lastInvoiceofMonth){
        //             $lastPrefix = explode('-', $lastInvoiceofMonth->inv_number);
        //             $lastPrefix = (int) $lastPrefix[2];               
        //         }else{
        //             $lastPrefix = 0;
        //         }
        //         $newPrefix = $lastPrefix + 1;
        //         $newPrefix = str_pad($newPrefix, 4, 0, STR_PAD_LEFT);
        //         $invNo = "CL-".str_replace(" ", "", $invoiceType->invtp_prefix)."-".substr($year, -2).$month."-".$newPrefix;
        //         // generate invoice meter
        //         $insertInvMeter[$keygrp] = [
        //                             'tenan_id'=>$tenan_id, 'inv_number'=>$invNo, 'inv_date'=>date('Y-m-d'), 
        //                             'inv_duedate'=>date('Y-m-d', strtotime('+1 month')), 'inv_amount'=>$totalAmount,
        //                             'inv_ppn'=>0.1, 'inv_ppn_amount'=> 1.1*$totalAmount, 'inv_outstanding'=>0, 'inv_faktur_no' => $invNo,
        //                             'inv_faktur_date'=>date('Y-m-d'), 'invtp_id' => $keygrp, 'contr_id' => $contr_id, 'created_by' => Auth::id(), 'updated_by' => Auth::id()
        //                         ];
        //     }

        //     // ubah tipe data  invtp_id
        //     // alter table "public"."tr_invoice" alter column invtp_id type integer using invtp_id::numeric
        //     // CTT: Cutoff History dijadiin patokan utk generate Invoice Si Owner
        //     DB::transaction(function () use($insertCutoff, $insertTrMeter, $insertInvMeter, $insertInvDetail, $cutoffStatus, $groups) {
        //         foreach($groups as $keygrp => $grp){
        //             $meterIds = [];
        //             $invoice = TrInvoice::create($insertInvMeter[$keygrp]);
        //             // Kalo Cutoff itu true (alias dia tenant sewa) generate Invoice buat owner next periode nya hrs simpan di history
        //             if(!empty($cutoffStatus)){
        //                 foreach ($insertCutoff[$keygrp] as $coff) {
        //                     CutoffHistory::create($coff);
        //                 }
        //             }
        //             foreach ($insertTrMeter[$keygrp] as $mtr) {
        //                 $meterIds[] = TrMeter::create($mtr);   
        //             }
        //             foreach ($insertInvDetail[$keygrp] as $key => $invDt) {
        //                 $invDt['inv_id'] = $invoice->id;
        //                 if(isset($meterIds[$key])) $invDt['meter_id'] = $meterIds[$key]->id;
        //                 TrInvoiceDetail::create($invDt);
        //             }
        //         }
        //     });
        //     echo 'Invoice Meter Generated';
        // }



    }

}
