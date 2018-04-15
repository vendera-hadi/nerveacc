<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsSupplier;
use App\Models\MsMasterCoa;
use App\Models\User;
use Excel;

class SupplierController extends Controller
{
	public function index(){
        $coaYear = date('Y');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
		return view('supplier', $data);
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
            $count = MsSupplier::count();
            $fetch = MsSupplier::query();
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
                    if($filter->field == 'spl_isactive'){
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
                $temp['coa_code'] = $value->coa_code;
                $temp['spl_id'] = $value->spl_id;
                $temp['spl_code'] = $value->spl_code;
                $temp['spl_name'] = $value->spl_name;
                $temp['spl_address'] = $value->spl_address;
                $temp['spl_city'] = $value->spl_city;
                $temp['spl_postal_code'] = $value->spl_postal_code;
                $temp['spl_phone'] = $value->spl_phone;
                $temp['spl_fax'] = $value->spl_fax;
                $temp['spl_cperson'] = $value->spl_cperson;
                $temp['spl_npwp'] = $value->spl_npwp;
                $temp['spl_isactive'] = !empty($value->spl_isactive) ? 'yes' : 'no';
                try{
                    $temp['created_by'] = User::findOrFail($value->created_by)->name;
                }catch(\Exception $e){
                    $temp['created_by'] = '-';
                }
                $temp['created_at'] = $value->created_at;
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
            $input['spl_id'] = md5(date('Y-m-d H:i:s'));
            $input['created_by'] = Auth::id();
            $input['updated_by'] = Auth::id();
    		return MsSupplier::create($input);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }     	
    }

    public function update(Request $request){
    	try{
            $id = $request->id;
        	$input = $request->all();
            $input['updated_by'] = Auth::id();
        	MsSupplier::find($id)->update($input);
        	return MsSupplier::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
    	try{
            $id = $request->id;
        	MsSupplier::destroy($id);
        	return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function ajaxdtl(Request $request)
    {
        try{
            $id = $request->id;
            $data = MsSupplier::find($id);
            return response()->json(['success'=>true, 'data' => $data]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }    
    }

    public function downloadSupplierExcel()
    {
        $data_ori = MsSupplier::select('spl_code','spl_name','spl_address','spl_city','spl_postal_code','spl_phone','spl_fax','spl_cperson','spl_npwp','spl_isactive')
                ->get()->toArray();
        $data = array();
        for($i=0; $i<count($data_ori); $i++){
            $data[$i]=array(
                'Supplier Code'=>trim($data_ori[$i]['spl_code']),
                'Nama Supplier'=>trim($data_ori[$i]['spl_name']),
                'Alamat'=>trim($data_ori[$i]['spl_address']),
                'Kota'=>trim($data_ori[$i]['spl_city']),
                'Postal Code'=>trim($data_ori[$i]['spl_postal_code']),
                'Phone'=>trim($data_ori[$i]['spl_phone']),
                'Fax'=>trim($data_ori[$i]['spl_fax']),
                'Contact Person'=>trim($data_ori[$i]['spl_cperson']),
                'NPWP'=>trim($data_ori[$i]['spl_npwp']),
                'Status'=>($data_ori[$i]['spl_isactive'] == 't' ? 'YES' : 'NO')
            );
        }
        $border = 'A1:J';
        $tp = 'xls';
        return Excel::create('report_supplier', function($excel) use ($data,$border) {
            $excel->sheet('mySheet', function($sheet) use ($data,$border)
            {
                $total = count($data)+1;
                $sheet->setBorder($border.$total, 'thin');
                $sheet->fromArray($data);
            });
        })->download($tp);
    }

}
