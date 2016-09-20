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
    }

    public function insert(Request $request){
		$input = $request->all();
        $input['renprd_id'] = md5(date('Y-m-d H:i:s'));
        $input['created_by'] = Auth::id();
        $input['updated_by'] = Auth::id();
		return MsRentalPeriod::create($input);    	
    }

    public function update(Request $request){
    	$id = $request->id;
    	$input = $request->all();
        $input['updated_by'] = Auth::id();
    	MsRentalPeriod::find($id)->update($input);
    	return MsRentalPeriod::find($id);
    }

    public function delete(Request $request){
    	$id = $request->id;
    	MsRentalPeriod::destroy($id);
    	return response()->json(['success'=>true]);
    }
}
