<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsRentalPeriod;
use App\Models\User;

class RentalPeriodController extends Controller
{
	public function index(){
		return view('rentalPeriod');
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
            $count = MsRentalPeriod::count();
            $fetch = MsRentalPeriod::query();
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
                $temp['renprd_id'] = $value->renprd_id;
                $temp['renprd_name'] = $value->renprd_name;
                $temp['renprd_day'] = $value->renprd_day;
                try{
                    $temp['created_by'] = User::findOrFail($value->created_by)->name;
                }catch(\Exception $e){
                    $temp['created_by'] = '-';
                }
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
            $input['renprd_id'] = md5(date('Y-m-d H:i:s'));
            $input['created_by'] = Auth::id();
            $input['updated_by'] = Auth::id();
    		return MsRentalPeriod::create($input);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }     	
    }

    public function update(Request $request){
    	try{
            $id = $request->id;
        	$input = $request->all();
            $input['updated_by'] = Auth::id();
        	MsRentalPeriod::find($id)->update($input);
        	return MsRentalPeriod::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
    	try{
            $id = $request->id;
        	MsRentalPeriod::destroy($id);
        	return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getOptRentalPeriod(Request $request){
        $key = $request->q;
        $fetch = MsRentalPeriod::select('id','renprd_name')->where(\DB::raw('LOWER(renprd_name)'),'like','%'.$key.'%')->get();
        $result['results'] = [];
        foreach ($fetch as $key => $value) {
            $temp = ['id'=>$value->id, 'text'=>$value->renprd_name];
            array_push($result['results'], $temp);
        }
        return json_encode($result);
    }
}
