<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsFixedAsset;
use App\Models\MsCategoryAsset;

class AssetsController extends Controller
{
    public function index(){
        return view('assets');
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
            $count = MsFixedAsset::count();
            $fetch = MsFixedAsset::select('ms_fixed_asset.*','ms_category_asset.catas_name')->join('ms_category_asset',\DB::raw('ms_fixed_asset.catas_id::char'),"=",\DB::raw('ms_category_asset.catas_id::char'));
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
                    if($filter->field == 'fixas_isdelete'){
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
                $temp['fixas_code'] = $value->fixas_code;
                $temp['fixas_name'] = $value->fixas_name;
                $temp['fixas_aqc_date'] = $value->fixas_aqc_date;
                $temp['fixas_age'] = $value->fixas_age;
                $temp['fixas_supplier'] = $value->fixas_supplier;
                $temp['fixas_pono'] = $value->fixas_pono;
                $temp['fixas_total_depr'] = $value->fixas_total_depr;
                $temp['fixas_isdelete'] = !empty($value->fixas_isdelete) ? 'yes' : 'no';
                $temp['catas_id'] = $value->catas_id;
                $temp['catas_name'] = $value->catas_name;
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function category_option(){
        try{
            $all = MsCategoryAsset::all();
            $result = [];
            if(count($all) > 0){
                foreach ($all as $value) {
                    $result[] = ['id'=>$value->catas_id, 'text'=>$value->catas_name];
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
            return MsFixedAsset::create($input);        
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function update(Request $request){
        try{
            $id = $request->id;
            $input = $request->all();
            MsFixedAsset::find($id)->update($input);
            return MsFixedAsset::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
        try{
            $id = $request->id;
            MsFixedAsset::destroy($id);
            return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }
}
