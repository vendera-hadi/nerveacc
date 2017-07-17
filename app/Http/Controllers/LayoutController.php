<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MsHeaderFormat;
use App\Models\MsDetailFormat;
use App\Models\User;

class LayoutController extends Controller
{

	public function index()
	{
		return view('layout_settings');
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
	    	$count = MsHeaderFormat::count();
	    	// join dengan group account
	    	$fetch = MsHeaderFormat::query();
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
	    		$temp['name'] = $value->name;
	    		$temp['type'] = $value->type == 1 ? '1 Lajur' : '2 Lajur (T)';
	    		$temp['typeid'] = $value->type;
	    		$temp['action'] = '<a href="#" data-id="'.$value->id.'" class="editFormat"><i class="fa fa-pencil"></i> Edit Format</a>&nbsp;&nbsp;<a href="#" data-id="'.$value->id.'" class="editContent"><i class="fa fa-edit"></i> Edit Konten</a>';
	    		$result['rows'][] = $temp;
	    	}
	        return response()->json($result);
	    }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
	}

	public function upsert(Request $request)
	{
		try{
			$id = $request->id;
			if(empty($id)){ 
				$format = new MsHeaderFormat;
				$message = 'New format created';
			}else{ 
				$format = MsHeaderFormat::find($id);
				$message = 'Format updated';
			}
			$format->name = $request->name;
			$format->type = $request->type;
			$format->created_by = \Auth::id();
			$format->save();
			return response()->json(['success' => 1, 'message' => $message]);
		}catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
	}

	public function destroy(Request $request)
	{
		try{
			$id = $request->id;
			$format = MsHeaderFormat::find($id)->delete();
			return response()->json(['success' => 1, 'message' => 'Format deleted']);
		}catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
	}

	public function getDetail(Request $request)
	{
		try{
			$id = $request->id;
			$detail1 = MsDetailFormat::where('formathd_id',$id)->where('column',1)->get();
			$detail2 = MsDetailFormat::where('formathd_id',$id)->where('column',2)->get();
			return response()->json([
										'success' => 1, 
										'data1' => $detail1->isEmpty() ? null : $detail1,
										'data2' => $detail2->isEmpty() ? null : $detail2,
										]);
		}catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
	}

	public function updateDetail(Request $request)
	{
		try{
			$id = $request->id;
			$column = $request->column;
			$coa_code = $request->coa_code;
			$desc = $request->desc;
			$header = $request->header;
			$variable = $request->variable;
			$formula = $request->formula;
			$linespace = $request->linespace;
			$underline = $request->underline;
			$hide = $request->hide;
			MsDetailFormat::where('formathd_id',$id)->delete();
			\DB::beginTransaction();
			foreach ($coa_code as $key => $coa) {
				$detail = new MsDetailFormat;
				$detail->formathd_id = $id;
				$detail->coa_code = $coa;
				$detail->desc = $desc[$key];
				$detail->header = $header[$key];
				$detail->variable = $variable[$key];
				$detail->formula = $formula[$key];
				$detail->linespace = $linespace[$key];
				$detail->underline = $underline[$key];
				$detail->hide = $hide[$key];
				$detail->column = $column[$key];
				$detail->save();
			}
			\DB::commit();
			return response()->json(['success' => 1, 'message' => 'Format saved']);
		}catch(\Exception $e){
			\DB::rollBack();
            return response()->json(['errorMsg' => $e->getMessage()]);
        }	
	}

	public function preview(Request $request)
	{
		$id = $request->id;
		$header = MsHeaderFormat::find($id);
		$detail1 = MsDetailFormat::where('formathd_id',$id)->where('column',1)->get();
		$detail2 = MsDetailFormat::where('formathd_id',$id)->where('column',2)->get();
		$data = [
				'header' => $header,
				'detail1' => $detail1,
				'detail2' => $detail2
			];
		return view('layout_preview',$data);
	}

}