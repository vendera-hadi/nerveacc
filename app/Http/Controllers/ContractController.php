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
use App\Models\MsConfig;
use App\Models\MsTenant;
use Validator;
use DB;
use Auth;
use Carbon\Carbon;

class ContractController extends Controller
{
    public function index(){
        $total_unit = MsUnit::count();
        $total_contract = TrContract::join('ms_tenant',\DB::raw('ms_tenant.id::integer'),"=",\DB::raw('tr_contract.tenan_id::integer'))
        ->where('contr_iscancel','f')
        ->where('contr_status','confirmed')
        ->where('tent_id',1)
        ->where('ms_tenant.deleted_at',NULL)
        ->count();
        $data['total_unit'] = $total_unit;
        $data['total_contract'] = $total_contract;
        $data['cost_items'] = MsCostDetail::select('ms_cost_detail.id','ms_cost_item.cost_name','ms_cost_item.cost_code','ms_cost_detail.costd_name')->join('ms_cost_item','ms_cost_detail.cost_id','=','ms_cost_item.id')->get();
        $invoice_types = MsInvoiceType::all();
        $data['marketing_agents'] = MsMarketingAgent::all();
        $data['invoice_types'] = '';
        foreach ($invoice_types as $key => $val) {
            $data['invoice_types'] = $data['invoice_types'].'<option value="'.$val->id.'">'.$val->invtp_name.'</option>';
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
            // contract yg bukan milik owner unit. tenan yg gada di ms_owner_list dimasukkan disini
            $fetch = TrContract::select('tr_contract.*','ms_tenant.tenan_name', 'ms_unit.unit_code')
            		->join('ms_tenant',\DB::raw('ms_tenant.id::integer'),"=",\DB::raw('tr_contract.tenan_id::integer'))
                    ->join('ms_unit', \DB::raw('ms_unit.id::integer'), '=', \DB::raw('tr_contract.unit_id::integer'))
                    ->leftJoin('ms_unit_owner', \DB::raw('tr_contract.tenan_id::integer'), '=', \DB::raw('ms_unit_owner.tenan_id::integer'))
                    ->whereNull('ms_unit_owner.tenan_id');
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
                if($op == 'like') $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                else $fetch = $fetch->where($filter->field, $op, $filter->value);
            }
            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            else $fetch->orderBy('ms_unit.unit_code');

            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['unit_code'] = $value->unit_code;
                $temp['contr_code'] = $value->contr_code;
                $temp['contr_no'] = $value->contr_no;
                $temp['contr_startdate'] = date('d/m/Y',strtotime($value->contr_startdate));
                $temp['contr_enddate'] = date('d/m/Y',strtotime($value->contr_enddate));
                if($temp['contr_enddate'] == '31/12/2030') $temp['contr_enddate'] = '-';
                $temp['tenan_name'] = $value->tenan_name;
                if($value->contr_status == 'confirmed') $status = '<strong class="text-success">'.$value->contr_status.'</strong>';
                else if($value->contr_status == 'cancelled' || $value->contr_status == 'closed') $status = '<strong class="text-danger">'.$value->contr_status.'</strong>';
                else $status = '<strong>'.$value->contr_status.'</strong>';
                $temp['contr_status'] = $status;
                $temp['contr_terminate_date'] = !empty($value->contr_terminate_date) ? date('d/m/Y',strtotime($value->contr_terminate_date)) : '';
                if($value->contr_status == 'inputed'){
                    $confirmed = '';
                    if(\Session::get('role')==1 || in_array(37,\Session::get('permissions'))){
                        $confirmed .= '<a href="#" title="Edit Contract" data-id="'.$value->id.'" class="editctr"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp;<a href="#" title="Edit Cost Item" data-id="'.$value->id.'" class="editcitm"><i class="fa fa-dollar" aria-hidden="true"></i></a>';
                    }
                    if(\Session::get('role')==1 || in_array(38,\Session::get('permissions'))){
                        $confirmed .= '&nbsp;<a href="#" title="Cancel Contract" data-id="'.$value->id.'" class="remove"><i class="fa fa-times" aria-hidden="true"></i></a>';
                    }
                }else if($value->contr_status == 'confirmed' && !empty($value->contr_terminate_date) ){
                    $confirmed = '';
                    if(\Session::get('role')==1 || in_array(38,\Session::get('permissions'))){
                        $confirmed .= '<a href="#" title="Cancel Contract" data-id="'.$value->id.'" class="remove"><i class="fa fa-times" aria-hidden="true"></i></a>';
                    }
                }else{
                    $confirmed = '';
                }

                if($value->contr_status != 'cancelled') $temp['action'] = '<a href="#" title="View Detail" data-toggle="modal" data-target="#detailModal" data-id="'.$value->id.'" class="getDetail"><i class="fa fa-eye" aria-hidden="true"></i></a>&nbsp; '.$confirmed;
                else $temp['action'] = '';
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function getOwner(Request $request){
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
            // contract disini adalah contract yg dimiliki oleh si owner. join dgn unit owner using tenan_id
            $fetch = TrContract::select('tr_contract.*')->join('ms_unit_owner', \DB::raw('tr_contract.tenan_id::integer'), '=', \DB::raw('ms_unit_owner.tenan_id::integer'))->groupBy('tr_contract.id');

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
                if($op == 'like') $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                else $fetch = $fetch->where($filter->field, $op, $filter->value);
            }
            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            // else $fetch->orderBy('ms_unit.unit_code');

            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['unit_code'] = $value->MsUnit->unit_code;
                $temp['contr_code'] = $value->contr_code;
                $temp['contr_no'] = $value->contr_no;
                $temp['contr_startdate'] = date('d/m/Y',strtotime($value->contr_startdate));
                $temp['contr_enddate'] = date('d/m/Y',strtotime($value->contr_enddate));
                if($temp['contr_enddate'] == '31/12/2030') $temp['contr_enddate'] = '-';
                $temp['tenan_name'] = $value->MsTenant->tenan_name;
                if($value->contr_status == 'confirmed') $status = '<strong class="text-success">'.$value->contr_status.'</strong>';
                else if($value->contr_status == 'cancelled' || $value->contr_status == 'closed') $status = '<strong class="text-danger">'.$value->contr_status.'</strong>';
                else $status = '<strong>'.$value->contr_status.'</strong>';
                $temp['contr_status'] = $status;
                $temp['contr_terminate_date'] = !empty($value->contr_terminate_date) ? date('d/m/Y',strtotime($value->contr_terminate_date)) : '';
<<<<<<< HEAD
                if($value->contr_status == 'inputed') $confirmed = '<a href="#" title="Edit Contract" data-id="'.$value->id.'" class="editctr"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                                                    &nbsp;<a href="#" title="Edit Cost Item" data-id="'.$value->id.'" class="editcitm"><i class="fa fa-dollar" aria-hidden="true"></i></a>
                                                                    &nbsp;<a href="#" title="Cancel Contract" data-id="'.$value->id.'" class="remove"><i class="fa fa-times" aria-hidden="true"></i></a>';
=======
                if($value->contr_status != 'confirmed')
                    $confirmed = '<a href="#" title="Edit Contract" data-id="'.$value->id.'" class="editctr"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp;
                        <a href="#" title="Edit Cost Item" data-id="'.$value->id.'" class="editcitm"><i class="fa fa-dollar" aria-hidden="true"></i></a>&nbsp;
                        <a href="#" title="Cancel Contract" data-id="'.$value->id.'" class="remove"><i class="fa fa-times" aria-hidden="true"></i></a>';
>>>>>>> 460d9aba3d0be0909f66eef3a774e7ad13fc6293
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
        $fetch = TrContract::select('tr_contract.*','ms_tenant.tenan_code','ms_tenant.tenan_name','ms_tenant.tenan_idno','ms_marketing_agent.mark_code','ms_marketing_agent.mark_name','ms_unit.virtual_account','ms_unit.unit_code','ms_unit.unit_name','ms_unit.unit_isactive')
        ->join('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id')
        ->leftJoin('ms_marketing_agent','ms_marketing_agent.id',"=",'tr_contract.mark_id')
        // ->join('ms_virtual_account','ms_virtual_account.id',"=",'tr_contract.viracc_id')
        ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
        ->where('tr_contract.id',$contractId)->first();
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
            $fetch = TrContract::select('tr_contract.*','ms_tenant.tenan_code','ms_tenant.tenan_name','ms_tenant.tenan_idno','ms_unit.unit_code','ms_unit.virtual_account','ms_unit.unit_name','ms_unit.unit_isactive','ms_unit_owner.tenan_id as owner')
            ->join('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id')
            ->join('ms_unit','ms_unit.id',"=",'tr_contract.unit_id')
            ->leftJoin('ms_unit_owner','ms_unit.id',"=",'ms_unit_owner.unit_id')
            // ->join('ms_virtual_account','ms_virtual_account.id',"=",'ms_unit.unit_virtual_accn')
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
            $costdetail = TrContractInvoice::select('ms_cost_detail.id','ms_cost_detail.cost_id','ms_cost_detail.costd_unit','ms_invoice_type.invtp_name','ms_cost_detail.costd_name','ms_cost_detail.costd_rate','ms_cost_detail.costd_burden','ms_cost_detail.costd_admin','ms_cost_detail.costd_ismeter','ms_cost_item.cost_name','ms_cost_item.cost_code','tr_contract_invoice.continv_period','tr_contract_invoice.invtp_id','tr_contract_invoice.order')
                ->join('ms_invoice_type','tr_contract_invoice.invtp_id',"=",'ms_invoice_type.id')
                ->join('ms_cost_detail','tr_contract_invoice.costd_id',"=",'ms_cost_detail.id')
                ->join('ms_cost_item','ms_cost_detail.cost_id',"=",'ms_cost_item.id')
                ->where('contr_id',$contractId)
                ->get();

            $invoice_types = MsInvoiceType::all();
            $inv_types_options = '';
            foreach ($invoice_types as $key => $val) {
                $inv_types_options = $inv_types_options.'<option value="'.$val->id.'">'.$val->invtp_name.'</option>';
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
        $fetch = TrContract::select('tr_contract.id','contr_code','contr_no','ms_tenant.tenan_name','ms_unit.unit_code')
                ->join('ms_unit','tr_contract.unit_id','=','ms_unit.id')
                ->join('ms_tenant','tr_contract.tenan_id','=','ms_tenant.id')
                ->where(\DB::raw('LOWER(ms_tenant.tenan_name)'),'ilike','%'.$key.'%')->orWhere(\DB::raw('LOWER(ms_unit.unit_code)'),'ilike','%'.$key.'%')
                ->orderBy('ms_tenant.tenan_name')->limit(15)->get();
        $result['results'] = [];
        // array_push($result['results'], ['id'=>"0",'text'=>'No Tenan / Unit']);
        foreach ($fetch as $key => $value) {
            $temp = ['id'=>$value->id, 'text'=>$value->tenan_name." / ".$value->unit_code];
            array_push($result['results'], $temp);
        }
        return json_encode($result);
    }

    public function insert(Request $request){
        // $messages = [
        //     'contr_code.unique' => 'Contract Code must be unique',
        //     'contr_no.unique' => 'Contract No must be unique',
        // ];

        // $validator = Validator::make($request->all(), [
        //     'contr_code' => 'required|unique:tr_contract',
        //     'contr_no' => 'required|unique:tr_contract',
        // ], $messages);

        // if ($validator->fails()) {
        //     $errors = $validator->errors()->first();
        //     return ['status' => 0, 'message' => $errors];
        // }
        $randomString = strtoupper(str_random(5));
        $input = [
            'contr_code' => 'B'.date('ym').$request->input('tenan_id').$randomString,
            'contr_no' => 'B'.date('ym').$request->input('tenan_id').$randomString,
            'contr_startdate' => $request->input('contr_startdate'),
            'contr_enddate' => $request->input('contr_enddate','2030-12-31'),
            'contr_bast_date' => $request->input('contr_bast_date'),
            'contr_bast_by' => $request->input('contr_bast_by'),
            'contr_note' => $request->input('contr_note'),
            'contr_status' => 'inputed',
            'tenan_id' => $request->input('tenan_id'),
            'mark_id' => !empty($request->input('mark_id')) ? $request->input('mark_id') : 0,
            'viracc_id' => 0,
            'const_id' => $request->input('const_id',0),
            'unit_id' => $request->input('unit_id'),
        ];
        $costd_ids = $request->input('costd_is');
        $inv_type = $request->input('inv_type');
        $cost_name = $request->input('cost_name');
        $cost_code = $request->input('cost_code');
        $orders = $request->input('order');

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
            // check owner
            $unitOwner = MsUnitOwner::where('unit_id',$request->input('unit_id'))->first();
            if(!empty($unitOwner)){
                $checkUnitOwner = TrContract::where('tenan_id',$unitOwner->tenan_id)->where('unit_id',$request->input('unit_id'))->where('contr_status','confirmed')->count();
                if($checkUnitOwner < 1)
                    return ['status' => 0, 'message' => 'Contract Pemilik harus dibuat dan diconfirm terlebih dahulu sebelum membuat contract tenant'];
            }else{
                return ['status' => 0, 'message' => 'Unit harus mempunyai owner dahulu'];
            }

            DB::transaction(function () use($input, $request, $cost_id, $costd_name, $costd_unit, $costd_rate, $costd_burden, $costd_admin, $inv_type, $is_meter, $costd_ids, $inv_type_custom, $cost_name, $cost_code, $periods, $orders) {
                $contract = TrContract::create($input);

                // insert
                if(count($costd_ids) > 0){
                    $total = 0;
                    foreach ($costd_ids as $key => $value) {
                        $inputContractInv = [
                            'contr_id' => $contract->id,
                            'invtp_id' => $inv_type[$key],
                            'costd_id' => $costd_ids[$key],
                            'continv_amount' => $total,
                            'continv_period' => $periods[$key],
                            'order' => $orders[$key]
                        ];
                        TrContractInvoice::create($inputContractInv);
                    }
                }

                // cek unit uda ada pasangan blum
                $checkUnitCtr = TrContract::where('unit_id',$request->input('unit_id'))->where('contr_status','confirmed')->count();
                if($checkUnitCtr >= 2){
                    // unit jadi unavailable
                    MsUnit::where('id',$request->input('unit_id'))->update(['unit_isavailable'=>0]);
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
        // $messages = [
        //     'contr_code.unique' => 'Contract Code must be unique',
        //     'contr_no.unique' => 'Contract No must be unique',
        // ];

        // $validator = Validator::make($request->all(), [
        //     'contr_code' => 'required|unique:tr_contract,contr_code,'.$request->input('id'),
        //     'contr_no' => 'required|unique:tr_contract,contr_no,'.$request->input('id'),
        // ], $messages);

        // if ($validator->fails()) {
        //     $errors = $validator->errors()->first();
        //     return ['status' => 0, 'message' => $errors];
        // }

        $update = [
            'contr_startdate' => $request->input('contr_startdate'),
            'contr_enddate' => $request->input('contr_enddate','2030-12-31'),
            'contr_bast_date' => $request->input('contr_bast_date'),
            'contr_bast_by' => $request->input('contr_bast_by'),
            'contr_note' => $request->input('contr_note')
        ];
        if($request->input('tenan_id')) $update['tenan_id'] = $request->input('tenan_id');
        if($request->input('mark_id')) $update['mark_id'] = $request->input('mark_id');
        // if($request->input('const_id')) $update['const_id'] = $request->input('const_id');
        // if($request->input('viracc_id')) $update['viracc_id'] = $request->input('viracc_id');

        if(!empty($request->input('unit_id')) && $request->input('current_unit_id') != $request->input('unit_id')){
            $update['unit_id'] = $request->input('unit_id');

            // check unit contract
            $checkUnitCtr = TrContract::where('unit_id', $request->input('unit_id'))->where('contr_status','confirmed')->count();
            if($checkUnitCtr >= 2){
                return ['status' => 0, 'message' => 'Update gagal, Unit sudah dipakai di contract lain'];
            }else{
                // unit lama jadi available
                MsUnit::where('id',$request->input('current_unit_id'))->update(['unit_isavailable'=>1]);
                // unit jadi unavailable
                MsUnit::where('id',$request->input('unit_id'))->update(['unit_isavailable'=>0]);
            }
        }

        TrContract::where('id',$request->input('id'))->update($update);
        return ['status' => 1, 'message' => 'Update Success'];
    }

    public function costdetailUpdate(Request $request){
        $contractIDs = $request->input('contr_id');
        $contractID = $request->input('cont');

        $cost_name = $request->input('cost_name');
        $cost_code = $request->input('cost_code');
        $orders = $request->input('order');
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

        TrContractInvoice::where('contr_id',$contractID)->delete();
            try{
                DB::transaction(function () use($cost_id, $costd_ids, $costd_name, $costd_unit, $costd_rate, $costd_burden, $costd_admin, $inv_type, $is_meter, $contractID, $cost_name, $cost_code, $inv_type_custom, $periods, $orders){
                    // delete all of cost detail of current contract id
                    
                    // reinsert to cost detail and tr contract invoice
                    // insert
                    if(count($costd_ids) > 0){
                        $total = 0;
                        foreach ($costd_ids as $key => $value) {
                            $inputContractInv = [
                                'contr_id' => $contractID,
                                'invtp_id' => $inv_type[$key],
                                'costd_id' => $costd_ids[$key],
                                'continv_amount' => $total,
                                'continv_period' => $periods[$key],
                                'order' => $orders[$key]
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
            $contract = TrContract::find($id);
            // balikin unit
            $unit = MsUnit::find($contract->unit_id);
            $unit->unit_isavailable = true;
            $unit->save();
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
            $fetch = TrContract::select('tr_contract.*','ms_tenant.tenan_name','ms_unit.unit_code')
                    ->join('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id')
                    ->join('ms_unit','ms_unit.id','=','tr_contract.unit_id');
            if(strtolower($pageName) != 'renewal') $fetch = $fetch->whereNull('tr_contract.contr_terminate_date');
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
                if($op == 'like') $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                else $fetch = $fetch->where($filter->field, $op, $filter->value);
            }
            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['unit_code'] = $value->unit_code;
                $temp['contr_code'] = $value->contr_code;
                $temp['contr_no'] = $value->contr_no;
                $temp['contr_startdate'] = $value->contr_startdate;
                $temp['contr_enddate'] = $value->contr_enddate;
                $temp['tenan_name'] = $value->tenan_name;
                $temp['contr_status'] = $value->contr_status;
                $temp['contr_terminate_date'] = $value->contr_terminate_date;
                $temp['checkbox'] = '<input type="checkbox" name="check" value="'.$value->id.'">';
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
        $ids = $request->id;
        if(!is_array($ids)) $ids = [$ids];

        foreach($ids as $id){
            // check unit
            $contract = TrContract::find($id);
            $checkUnitCtr = TrContract::where('unit_id',$contract->unit_id)->where('contr_status','confirmed')->count();
            if($checkUnitCtr >= 2){
                // gagal, unit dibatasin cuma di 2 contract aja
                return response()->json(['errorMsg' => 'Confirm gagal, unit dalam contract ini sudah dipakai di contract lain']);
            }
            TrContract::where('id',$id)->update(['contr_status'=>'confirmed']);
        }
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
                        'costd_is' => $value->costd_is,
                        'invtp_id' => $value->invtp_id,
                        'costd_id' =>$value->costd_id
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
                        'invtp_id' => $value->invtp_id,
                        'costd_id' => $value->costd_id
                    ]);
            }
        }
        return response()->json(['success'=>true]);
    }

    public function renew(Request $request){
        // $messages = [
        //     'contr_code.unique' => 'Contract Code must be unique',
        //     'contr_no.unique' => 'Contract No must be unique',
        // ];

        // $validator = Validator::make($request->all(), [
        //     'contr_code' => 'required|unique:tr_contract,contr_code,'.$request->input('id'),
        //     'contr_no' => 'required|unique:tr_contract,contr_no,'.$request->input('id'),
        // ], $messages);

        // if ($validator->fails()) {
        //     $errors = $validator->errors()->first();
        //     return ['errorMsg' => $errors];
        // }

        // new contract date must be after old one
        $id = $request->id;
        $startdate = $request->contr_startdate;
        $enddate = $request->contr_enddate;
        $current = TrContract::find($id);
        if($startdate < $current->contr_enddate) return ['errorMsg' => 'New Contract must be after the end date of the old contract one (after '.date('d/m/Y',strtotime($current->contr_enddate)).')'];
        if($startdate >= $enddate) return ['errorMsg' => 'Start date must be less than End Date'];
        //RACHMAT (*menurut gw harusnya cuma update tanggal aja gk usah ganti contract lama soalnya jadi kotor datanya/double)
        try{
            DB::transaction(function () use($current, $startdate, $enddate, $request, $id){
                $update = [
                    'contr_startdate' => $startdate,
                    'contr_enddate' => $enddate
                ];
                TrContract::where('id',$id)->update($update);
            });
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
        /*
        try{
            DB::transaction(function () use($current, $startdate, $enddate, $request, $id){
                $newdata = $current->replicate();
                // $newdata->contr_id = 'CTR'.str_replace(".", "", str_replace(" ", "",microtime()));
                $newdata->contr_startdate = $startdate;
                $newdata->contr_enddate = $enddate;
                $randomString = strtoupper(str_random(5));
                $newdata->contr_code = 'B'.date('ym').$newdata->tenan_id.$randomString;
                $newdata->contr_no = 'B'.date('ym').$newdata->tenan_id.$randomString;
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
        */
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
            // GET all contract yg
            //  1. Sudah ditag terminate (pny terminate date) yg masuk jangka waktu seminggu ini
            //  2. Contract yg bakal Expired dalam minggu2 ini
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
                if($op == 'like') $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                else $fetch = $fetch->where($filter->field, $op, $filter->value);
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

        // get all cost detail yg gunain meter yang ada di unit si tenant
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
        // $lastMonthPeriod = TrPeriodMeter::where('prdmet_start_date','>=',$tempTimeStart)->where('prdmet_end_date','<=',$tempTimeEnd)->where('status',1)->orderBy('id','desc')->first();
        $lastMonthPeriod = TrPeriodMeter::where('status',1)->orderBy('id','desc')->first();

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
        $ppjuData = MsConfig::where('name','ppju')->get();
        $contractData = TrContract::find($contr_id);

        // GENERATE INVOICE METER
        if(count($meter_units) > 0){
            // jika bukan owner, cari contract owner
            if(!empty($cutoffStatus)){
                $owner = MsUnitOwner::where('unit_id', $nonmeter_unit_id[0])->first();
                if(empty($owner)) return response()->json(['error'=>true, 'message'=>'Unit Owner not Found, Please Create New Contract of Unit Owner first']);
                // kalau owner punya, cari contract nya owner
                $contractOwner = TrContract::where('tenan_id',$owner->tenan_id)->where('unit_id',$nonmeter_unit_id[0])->where('contr_status','confirmed')->whereNull('contr_terminate_date')->first();
                if(empty($contractOwner)) return response()->json(['error'=>true, 'message'=>'Contract Owner of this Unit not Found, Please Create New Contract of Unit Owner first']);
            }

            $insertInvDetail = [];
            $proRateMeterRatio = date('d') / date('t');
            // grouping by Invoice Type
            $groupsInv = TrContractInvoice::select('invtp_id')->join('ms_invoice_type','tr_contract_invoice.invtp_id','=','ms_invoice_type.id')
                ->join('ms_cost_detail','tr_contract_invoice.costd_id','=','ms_cost_detail.id')
                ->where('contr_id',$contr_id)->where('costd_ismeter',1)->groupBy('invtp_id')->get();

            $groups = [];
            // SETIAP INV TYPE BIKIN 1 INVOICE
            foreach ($groupsInv as $grp) {
                $contrInv = TrContractInvoice::join('ms_invoice_type','tr_contract_invoice.invtp_id','=','ms_invoice_type.id')
                        ->where('contr_id',$contr_id)->where('invtp_id',$grp->invtp_id)->get();
                foreach ($contrInv as $cinv) {
                    $groups[$grp->invtp_id][] = $cinv->costd_id;
                }
            }
            foreach($groups as $keygrp => $grp){
                $totalAmount = 0;
                foreach($meter_units as $key => $unit) {
                    if(in_array($meter_costdids[$key], $grp)){
                        // input ke cutoff meter
                        $insertCutoff[$keygrp][] = ['unit_id'=>$unit, 'costd_id'=>$meter_costdids[$key], 'meter_start' => $meter_start[$key], 'meter_end'=>$meter_end[$key], 'close_date'=>date('Y-m-d')];
                        // input ke tr meter(optional)
                        $tempMeterUsed = $meter_end[$key] - $meter_start[$key];
                        $tempMeterCost = ($tempMeterUsed * $meter_rate[$key]) + $meter_burden[$key] + $meter_admin[$key];
                        if($meter_costdids[$key] == 4){
                            //AIR tanpa PPJU
                            $ppju = 0;
                        }else{
                            //LISTRIK yang ada PPJU
                            $ppju = $ppjuData[0]->value/100 * $tempMeterCost;
                        }
                        $totalMeter = $tempMeterCost + $ppju;
                        $totalAmount+=$totalMeter;
                        $insertTrMeter[$keygrp][] = ['meter_start' => $meter_start[$key], 'meter_end'=>$meter_end[$key], 'meter_used'=>$tempMeterUsed, 'meter_cost' => $tempMeterCost, 'meter_burden' => $meter_burden[$key], 'meter_admin' => $meter_admin[$key], 'costd_id' => $meter_costdids[$key], 'prdmet_id' => 0, 'contr_id' => $contr_id, 'unit_id'=>$unit,'other_cost'=>$ppju, 'total'=>$totalAmount ];

                        // buat inv detail
                        $tempCostdt = MsCostDetail::find($meter_costdids[$key]);
                        $insertInvDetail[$keygrp][] = ['invdt_amount' => $totalMeter, 'invdt_note' => $tempCostdt->costd_name." Periode ".date('01-m-Y')." s/d ".date('d-m-Y')." (Closed)",
                                                'costd_id'=>$meter_costdids[$key]];
                    }
                }
                if($totalAmount <= $companyData->comp_materai1_amount){
                    $insertInvDetail[$keygrp][] = ['invdt_amount' => $companyData->comp_materai1, 'invdt_note' => 'Stamp Duty', 'costd_id'=> 0];
                    $totalInv = $totalAmount + $companyData->comp_materai1;
                }else {
                    $insertInvDetail[$keygrp][] = ['invdt_amount' => $companyData->comp_materai2, 'invdt_note' => 'Stamp Duty', 'costd_id'=> 0];
                    $totalInv = $totalAmount + $companyData->comp_materai2;
                }
                $invoiceType = MsInvoiceType::find($keygrp);
                $lastInvoiceofMonth = TrInvoice::select('inv_number')->where('inv_number','like','CL-'.str_replace(" ", "", $invoiceType->invtp_prefix).'-'.substr($year, -2).$month.'-%')->orderBy('id','desc')->first();
                if($lastInvoiceofMonth){
                    $lastPrefix = explode('-', $lastInvoiceofMonth->inv_number);
                    $lastPrefix = (int) $lastPrefix[2];
                }else{
                    $lastPrefix = 0;
                }
                $newPrefix = $lastPrefix + 1;
                $newPrefix = str_pad($newPrefix, 4, 0, STR_PAD_LEFT);
                $invNo = "CL-".str_replace(" ", "", $invoiceType->invtp_prefix)."-".substr($year, -2).$month."-".$newPrefix;

                // generate invoice meter
                $insertInvMeter[$keygrp] = [
                                    'tenan_id'=>$tenan_id, 'inv_number'=>$invNo, 'inv_date'=>date('Y-m-d'),
                                    'inv_duedate'=>date('Y-m-d', strtotime('+1 month')), 'inv_amount'=>$totalInv,
                                    'inv_ppn'=>0.1, 'inv_ppn_amount'=> 1.1*$totalInv, 'inv_outstanding'=>$totalInv, 'inv_faktur_no' => $invNo,
                                    'inv_faktur_date'=>date('Y-m-d'), 'invtp_id' => $keygrp, 'contr_id' => $contr_id, 'created_by' => Auth::id(), 'updated_by' => Auth::id()
                                ];
                $newPrefix = $lastPrefix + 1;
                $newPrefix = str_pad($newPrefix, 4, 0, STR_PAD_LEFT);
                $invNo = "CL-".str_replace(" ", "", $invoiceType->invtp_prefix)."-".substr($year, -2).$month."-".$newPrefix;

                if(!empty($cutoffStatus)){
                    // Oper Cost detail ID ke contract owner
                    // cari next period dari contract owner sekarang
                    $contrInvOwner = TrContractInvoice::where('contr_id',$contractOwner->id)->where('invtp_id',$keygrp)->where('costd_id',$grp[$key])->first();
                    $contrInv = TrContractInvoice::where('contr_id',$contr_id)->where('invtp_id',$keygrp)->where('costd_id',$grp[$key])->first();
                }
            }
            DB::transaction(function () use($insertCutoff, $insertTrMeter, $insertInvMeter, $insertInvDetail, $cutoffStatus, $groups) {
                foreach($groups as $keygrp => $grp){
                    $meterIds = [];
                    $invoice = TrInvoice::create($insertInvMeter[$keygrp]);
                    // Kalo Cutoff itu true (alias dia tenant sewa) generate Invoice buat owner next periode nya hrs simpan di history
                    if(!empty($cutoffStatus)){
                        foreach ($insertCutoff[$keygrp] as $coff) {
                            CutoffHistory::create($coff);
                        }
                    }
                    foreach ($insertTrMeter[$keygrp] as $mtr) {
                        $meterIds[] = TrMeter::create($mtr);
                    }
                    foreach ($insertInvDetail[$keygrp] as $key => $invDt) {
                        $invDt['inv_id'] = $invoice->id;
                        if(isset($meterIds[$key])) $invDt['meter_id'] = $meterIds[$key]->id;
                        TrInvoiceDetail::create($invDt);
                    }
                }
            });
        }
        
        $status_kepemilikan = MsTenant::where('id',$tenan_id)->first();
        if($status_kepemilikan->tent_id == 1){
            //JIKA OWNER
            $update_unit = ['unit_isavailable'=>'t'];
            MsUnit::where('id',$contractData->unit_id)->update($update_unit);
            $update_tenan_type = ['tent_id'=>'4'];
            MsTenant::where('id',$contractData->tenan_id)->update($update_tenan_type);
            MsUnitOwner::where('unit_id',$contractData->unit_id)->where('tenan_id',$contractData->tenan_id)->delete();
        }else if($status_kepemilikan->tent_id == 2){
            //PINDAH COST DETAIL TENANT KE OWNER
            $contract_detail = TrContractInvoice::where('contr_id',$contr_id)->get();
            if(count($contract_detail) >0){
               $owner_unit = MsUnitOwner::where('unit_id',$contractData->unit_id)->get();
               $ctr_owner = TrContract::where('tenan_id',$owner_unit[0]->tenan_id)->where('unit_id',$owner_unit[0]->unit_id)->get();
               for($i=0; $i<count($contract_detail); $i++){
                     $inputContractInv = [
                                'continv_amount' => $contract_detail[$i]->continv_amount,
                                'continv_period' => $contract_detail[$i]->continv_period,
                                'continv_start_inv' => $contract_detail[$i]->continv_start_inv,
                                'continv_next_inv' => $contract_detail[$i]->continv_next_inv,
                                'contr_id' => $ctr_owner[0]->id,
                                'invtp_id' => $contract_detail[$i]->invtp_id,
                                'costd_id' => $contract_detail[$i]->costd_id,
                                'order' => $contract_detail[$i]->order,
                            ];
                            TrContractInvoice::create($inputContractInv);
               }
            }
        }

        //UPDATE STATUS KE CLOSED
        $update_contract = ['contr_status'=>'closed'];
        TrContract::where('id',$contr_id)->update($update_contract);
        return response()->json(['success'=>true, 'message'=>'Invoice Generated for this Closed Contract']);
    }

    public function getPopupOptions(Request $request){
        $keyword = $request->input('keyword');
        $fetch = TrContract::select('tr_contract.id','tr_contract.contr_code','tr_contract.contr_no','ms_unit.unit_name','ms_tenant.tenan_name')
                            ->join('ms_tenant','tr_contract.tenan_id','=','ms_tenant.id')
                            ->join('ms_unit','tr_contract.unit_id','=','ms_unit.id')
                            ->where('contr_status','confirmed')->where(function($query){
                                $query->whereNull('contr_terminate_date')->orWhere('contr_terminate_date','<',date('Y-m-d'));
                            });
        if($keyword) $fetch = $fetch->where(function($query) use($keyword){
                                            $query->where(\DB::raw('LOWER(contr_no)'),'like','%'.$keyword.'%')->orWhere(\DB::raw('LOWER(contr_code)'),'like','%'.$keyword.'%')
                                                ->orWhere(\DB::raw('LOWER(unit_name)'),'like','%'.$keyword.'%')->orWhere(\DB::raw('LOWER(tenan_name)'),'like','%'.$keyword.'%');
                                        });
        $fetch = $fetch->paginate(10);
        return view('modal.popupcontract', ['contracts'=>$fetch, 'keyword'=>$keyword, 'edit'=> null]);
    }

}
