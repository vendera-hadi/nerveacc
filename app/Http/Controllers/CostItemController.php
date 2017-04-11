<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsCostItem;
use App\Models\MsCostDetail;
use App\Models\User;
use App\Models\MsMasterCoa;

class CostItemController extends Controller
{
    public function index(){
        return view('costitem');
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
            $count = MsCostItem::count();
            $fetch = MsCostItem::query();
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
                    if($filter->field == 'cost_isactive'){
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
                $temp['cost_id'] = $value->cost_id;
                $temp['cost_code'] = $value->cost_code;
                $temp['cost_name'] = $value->cost_name;
                $temp['cost_coa_code'] = $value->cost_coa_code;
                $temp['cost_isactive'] = !empty($value->cost_isactive) ? 'yes' : 'no';
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
            $input['cost_id'] = md5(date('Y-m-d H:i:s'));
            $input['created_by'] = Auth::id();
            $input['updated_by'] = Auth::id();
            return response()->json(['success'=>true]);        
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function update(Request $request){
        try{
            $id = $request->id;
            $input['updated_by'] = Auth::id();
            $input = $request->all();
            MsCostItem::find($id)->update($input);
            return MsCostItem::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
        try{
            $id = $request->id;
            if($id == 4 || $id ==5 || $id ==6){
                return response()->json(['errorMsg'=>'Sorry This Item Cannot be Deleted']);
            }else{
                MsCostItem::destroy($id);
                return response()->json(['success'=>true]);
            }     
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getDetail(Request $request){
        $id = $request->id;
        $data = MsCostDetail::with('costitem')->find($id);
        if($data){
            if($data->costd_ismeter) $data->costd_ismeter = 'yes';
            else $data->costd_ismeter = 'no';
        }
        return response()->json($data);
    }

    public function cost_detail(Request $request){
        try{
            $id = $request->id;
            $count = MsCostDetail::count();
            $fetch = MsCostDetail::select('*')->where('cost_id',$id)->get();
            $count = $fetch->count();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['cost_id'] = $value->cost_id;
                $temp['costd_name'] = $value->costd_name;
                $temp['costd_unit'] = $value->costd_unit;
                $temp['costd_rate'] = $value->costd_rate;
                $temp['costd_burden'] = $value->costd_burden;
                $temp['costd_admin'] = $value->costd_admin;
                $temp['daya'] = $value->daya;
                $temp['costd_ismeter'] = !empty($value->costd_ismeter) ? 'yes' : 'no';
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getOptionsCoa(){
        try{
            $all = MsMasterCoa::where('coa_year',date('Y'))->where('coa_isparent',FALSE)->get();
            $result = [];
            if(count($all) > 0){
                foreach ($all as $value) {
                    $result[] = ['id'=>$value->coa_code, 'text'=>trim($value->coa_code).' - '.trim($value->coa_name)];
                }
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }
}
