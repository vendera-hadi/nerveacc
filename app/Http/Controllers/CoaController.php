<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsMasterCoa;
use App\Models\MsCompany;
use App\Models\MsGroupAccount;
use App\Models\MsGroupAccnDtl;
use App\Models\MsHeaderFormat;
use App\Models\MsDetailFormat;
use Excel;

class CoaController extends Controller
{
	public function index(){
		return view('coa');
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
        	$count = MsMasterCoa::count();
        	$fetch = MsMasterCoa::query();
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
                    if($filter->field == 'coa_isparent'){
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
        		$temp['coa_year'] = $value->coa_year;
        		$temp['coa_code'] = $value->coa_code;
                $temp['coa_name'] = $value->coa_name;
        		$temp['coa_isparent'] = !empty($value->coa_isparent) ? 'yes' : 'no';
                $temp['coa_level'] = $value->coa_level;
                $temp['coa_type'] = $value->coa_type;
                $temp['coa_beginning'] = $value->coa_beginning;
                $temp['coa_debit'] = $value->coa_debit;
                $temp['coa_credit'] = $value->coa_credit;
                $temp['coa_ending'] = $value->coa_ending;
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
            $input['coa_beginning'] = 0;
            $input['coa_debit'] = 0;
            $input['coa_credit'] = 0;
            $input['coa_ending'] = 0;
		    return MsMasterCoa::create($input);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function update(Request $request){
        try{
            $id = $request->id;
        	$input = $request->all();
        	MsMasterCoa::find($id)->update($input);
        	return MsMasterCoa::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function delete(Request $request){
        try{
        	$id = $request->id;
            $coa = MsMasterCoa::find($id);
        	if(!empty($coa->coa_lock)) return response()->json(['errorMsg' => 'You can\'t delete this COA. COA is locked']);
            MsMasterCoa::destroy($id);
        	return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function getCoaYear(){
        try{
            $all = MsMasterCoa::orderBy('coa_year')->groupBy('id','coa_year')->get();
            $result = [];
            if(count($all) > 0){
                foreach ($all as $value) {
                    $result[] = ['id'=>$value->id, 'text'=>$value->coa_year];
                }
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function getCoaCode(){
        try{
            $all = MsMasterCoa::orderBy('coa_code')->groupBy('id','coa_code')->get();
            $result = [];
            if(count($all) > 0){
                foreach ($all as $value) {
                    $result[] = ['id'=>$value->id, 'text'=>$value->coa_code];
                }
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

     public function downloadCoaExcel()
    {
        $data_ori = MsMasterCoa::select('coa_year','coa_code','coa_name','coa_beginning','coa_debit','coa_credit','coa_ending')
                ->get()->toArray();
        $data = array();
        for($i=0; $i<count($data_ori); $i++){
            $data[$i]=array(
                'Year'=>$data_ori[$i]['coa_year'],
                'Chart Account Code'=>trim($data_ori[$i]['coa_code']),
                'Chart Account Name'=>$data_ori[$i]['coa_name'],
                'COA Beginning'=>number_format($data_ori[$i]['coa_beginning'],2),
                'COA Debit'=>number_format($data_ori[$i]['coa_debit'],2),
                'COA Credit'=>number_format($data_ori[$i]['coa_credit'],2),
                'COA Ending'=>number_format($data_ori[$i]['coa_ending'],2));
        }
        $border = 'A1:G';
        $tp = 'xls';
        return Excel::create('report_coa', function($excel) use ($data,$border) {
            $excel->sheet('mySheet', function($sheet) use ($data,$border)
            {
                $total = count($data)+1;
                $sheet->setBorder($border.$total, 'thin');
                $sheet->fromArray($data);
            });
        })->download($tp);
    }

    public function dlTemplateUploadCoaExcel()
    {
        $data_ori = MsMasterCoa::select('coa_year','coa_code','coa_name','coa_isparent','coa_level','coa_type', 'coa_beginning', 'coa_debit', 'coa_credit','coa_ending')
                ->get()->toArray();
        $data = array();
        foreach($data_ori as $coa){
            $data[]=array(
                'Year'=>$coa['coa_year'],
                'Chart Account Code'=>trim($coa['coa_code']),
                'Chart Account Name'=>$coa['coa_name'],
                'Is Parent ? (0 = tidak, 1 = ya)'=> !empty($coa['coa_isparent']) ? 1 : 0,
                'Parent COA Code'=> trim($coa['coa_level']),
                'COA Type (DEBET/KREDIT)'=> trim($coa['coa_type']),
                'Saldo Awal COA' => number_format($coa['coa_beginning'],2),
                'Total Debit' => number_format($coa['coa_debit'],2),
                'Total Credit' => number_format($coa['coa_credit'],2),
                'Total COA Value Last Year'=> number_format($coa['coa_ending'],2));
        }

        $border = 'A1:J';
        $tp = 'xls';
        return Excel::create('template_coa', function($excel) use ($data,$border) {
            $excel->sheet('mySheet', function($sheet) use ($data,$border)
            {
                $total = count($data)+1;
                $sheet->setBorder($border.$total, 'thin');
                $sheet->fromArray($data);
            });
        })->download($tp);
    }

    public function printCoa(Request $request){
        $data['name'] = MsCompany::first()->comp_name;
        $data['title'] = "COA Report";
        $data['logo'] = MsCompany::first()->comp_image;
        $data['template'] = 'report_coa_template';
        $data['tahun'] = '';
        $data['type'] = 'print';

        $fetch = MsMasterCoa::select('coa_year','coa_code','coa_name','coa_beginning','coa_debit','coa_credit','coa_ending');
        $fetch = $fetch->get();
        $data['coa'] = $fetch;
        return view('layouts.report_template2', $data);
    }

    public function upload(Request $request)
    {
        if($request->hasFile('file')){
            $path = $request->file('file')->getRealPath();
            $data = Excel::load($path, function($reader) {

            })->get();

            \DB::beginTransaction();
            try{
                if(!empty($data) && $data->count()){
                    MsMasterCoa::truncate();
                    foreach ($data as $key => $value) {
                        $insert[] = [
                                'coa_year' => $value->year,
                                'coa_code' => $value->chart_account_code,
                                'coa_name' => $value->chart_account_name,
                                'coa_isparent' => !empty($value->is_parent_0_tidak_1_ya) ? true : false,
                                'coa_level' => $value->parent_coa_code,
                                'coa_type' => $value->coa_type_debetkredit,
                                'coa_ending' => $value->total_coa_value_last_year,
                                'coa_beginning' => $value->saldo_awal_coa,
                                'coa_debit' => $value->total_debit,
                                'coa_credit' => $value->total_credit
                            ];
                    }
                    MsMasterCoa::insert($insert);

                    // hapus group account
                    MsGroupAccount::truncate();
                    MsGroupAccnDtl::truncate();
                    // hapus layout
                    MsDetailFormat::truncate();
                    MsHeaderFormat::truncate();
                    \DB::commit();
                    return back()->with('success', 'Upload new COA success');
                }else{
                    return back()->with('error','Excel kosong, data harap diisi');
                }

            }catch(\Exception $e){
                \DB::rollBack();
                return back()->with('error','Error terjadi, harap dilihat kembali format input yang dimasukkan benar atau salah');
                // return back()->with('error',$e->getMessage());
            }

        }
        return back();
    }
}
