<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsCostDetail;
use App\Models\MsCostItem;

class CostDetailController extends Controller
{
    public function index(){
        return view('costdetail');
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
            $count = MsCostDetail::count();
            //$fetch = MsCostDetail::query();
            $fetch = MsCostDetail::select('ms_cost_detail.*','ms_cost_item.cost_name')->join('ms_cost_item',\DB::raw('ms_cost_item.cost_code::char'),"=",\DB::raw('ms_cost_detail.cost_id::char'));
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
                    if($filter->field == 'costd_ismeter'){
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
                $temp['costd_is'] = $value->costd_is;
                $temp['cost_id'] = $value->cost_id;
                $temp['cost_name'] = $value->cost_name;
                $temp['costd_name'] = $value->costd_name;
                $temp['costd_unit'] = $value->costd_unit;
                $temp['costd_rate'] = $value->costd_rate;
                $temp['costd_burden'] = $value->costd_burden;
                $temp['costd_admin'] = $value->costd_admin;
                $temp['costd_ismeter'] = !empty($value->costd_ismeter) ? 'yes' : 'no';
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getOptions(){
        try{
            $all = MsCostItem::all()->where('cost_isactive',TRUE);
            $result = [];
            if(count($all) > 0){
                foreach ($all as $value) {
                    $result[] = ['cost_code'=>$value->cost_id, 'text'=>$value->cost_name];
                }
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function insert(Request $request){
        try{
            $input = $request->all();
            $input['costd_is'] = md5(date('Y-m-d H:i:s'));
            return MsCostDetail::create($input);        
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function update(Request $request){
        try{
            $id = $request->id;
            $input = $request->all();
            MsCostDetail::find($id)->update($input);
            return MsCostDetail::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
        try{
            $id = $request->id;
            MsCostDetail::destroy($id);
            return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }
}