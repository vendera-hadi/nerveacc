<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use Input;
// load model
use App\Models\TrBudgetHdr;
use App\Models\User;
use App\Models\TrBudgetDtl;
use App\Models\MsMasterCoa;
use DB;
use Excel;
use Session;

class BudgetController extends Controller
{
    public function index(){
        return view('budget');
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
            $count = TrBudgetHdr::count();
            $fetch = TrBudgetHdr::query();
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
                $temp['tahun'] = $value->tahun;
                $temp['created_by'] = $value->created_by;
                $temp['created_at'] = date('d-M-Y',strtotime($value->created_at));
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
            $input['tahun'] = $request->tahun;
            $input['created_by'] = Auth::id();
            $input['updated_by'] = Auth::id();
            $budgetExist = TrBudgetHdr::where('tahun', $request->tahun)->first();
            if($budgetExist){
                return response()->json(['errorMsg' => 'Budget Sudah Pernah dibuat']);
            }else{
                $newBudget =  TrBudgetHdr::create($input);
                $coa = MsMasterCoa::where('coa_year',date('Y'))
                            ->where('coa_code', 'like', '4%%')
                            ->orwhere('coa_code', 'like', '5%%')
                            ->where('coa_isparent',false)
                            ->orderBy('coa_code','DESC')
                            ->get();
                foreach ($coa as $ctr){
                    $coaInput = [
                        'budget_id'=> $newBudget->id,
                        'coa_code'=> $ctr->coa_code
                    ];
                    $newDetailBudget = TrBudgetDtl::create($coaInput);
                }
            }
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
        return ['status' => 1, 'message' => 'Insert Success'];
    }

    public function update(Request $request){
        try{
            $id = $request->id;
            $input = $request->all();
            $input['tahun'] = $request->tahun;
            $input['updated_by'] = Auth::id();
            TrBudgetHdr::find($id)->update($input);
            return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function delete(Request $request){
        try{
            $id = $request->id;
            TrBudgetHdr::destroy($id);
            TrBudgetDtl::where('budget_id', $id)->delete();
            return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function editModal(Request $request){
        try{
            $id = $request->id;
            $currentBudget = TrBudgetHdr::find($id);
            $budget = TrBudgetDtl::select('tr_budget_dtl.*','ms_master_coa.coa_name')
                    ->leftJoin('tr_budget_hdr','tr_budget_hdr.id',"=",'tr_budget_dtl.budget_id')
                    ->where('budget_id',$id)
                    ->where('tr_budget_hdr.tahun',$currentBudget->tahun)
                    ->orderBy('tr_budget_dtl.coa_code','desc')
                    ->get();
            return view('modal.editbudget', ['budget' => $budget,'prd'=>$id]);
        }catch(\Exception $e){
             return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function downloadExcel($prd)
    {
        $data = TrBudgetDtl::select('tr_budget_dtl.coa_code AS COA','ms_master_coa.coa_name AS NAME','jan AS JANUARI','feb AS FEBRUARI','mar AS MARET','apr AS APRIL','may AS MAY','jun AS JUNI','jul AS JULI','aug AS AGUSTUS','sep AS SEPTEMBER','okt AS OKTOBER','nov AS NOVEMBER','des AS DESEMBER')
            ->leftJoin('ms_master_coa','ms_master_coa.coa_code',"=",'tr_budget_dtl.coa_code')
            ->where('tr_budget_dtl.budget_id',$prd)
            ->orderBy('tr_budget_dtl.coa_code','desc')
            ->get()->toArray();
        $border = 'A1:N';
        $tp = 'xls';
        return Excel::create('budget_template', function($excel) use ($data,$border) {
            $excel->sheet('budget tahunan', function($sheet) use ($data,$border)
            {
                $total = count($data)+1;
                $sheet->setBorder($border.$total, 'thin');
                $sheet->fromArray($data);
            });
        })->download($tp);
    }

    public function budgetdetailUpdate(Request $request){
        $id = $request->input('tr_dbudget_id');
        $jan = $request->input('jan');
        $feb = $request->input('feb');
        $mar = $request->input('mar');
        $apr = $request->input('apr');
        $may = $request->input('may');
        $jun = $request->input('jun');
        $jul = $request->input('jul');
        $aug = $request->input('aug');
        $sep = $request->input('sep');
        $okt = $request->input('okt');
        $nov = $request->input('nov');
        $des = $request->input('des');
        try{
            DB::transaction(function () use($id, $jan, $feb, $mar, $apr, $may, $jun, $jul, $aug, $sep, $okt, $nov, $des){
                foreach ($id as $key => $value) {
                    $input = [
                        'jan' => $jan[$key],
                        'feb' => $feb[$key],
                        'mar' => $mar[$key],
                        'apr' => $apr[$key],
                        'may' => $may[$key],
                        'jun' => $jun[$key],
                        'jul' => $jul[$key],
                        'aug' => $aug[$key],
                        'sep' => $sep[$key],
                        'okt' => $okt[$key],
                        'nov' => $nov[$key],
                        'des' => $des[$key]
                    ];
                    TrBudgetDtl::find($id[$key])->update($input);
                }
            });
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
        return ['status' => 1, 'message' => 'Update Success'];
    }

    public function importExcel(Request $request)
    {
        if(Input::hasFile('import_file')){
            $path = Input::file('import_file')->getRealPath();
            $data = Excel::load($path, function($reader) {})->get();
            $prd = $request->input('prd');

            foreach ($data as $key => $value) {
                if(!empty(@$value->coa)){
                    $coa_data = MsMasterCoa::where('coa_code',$value->coa)->first();
                    if(!$coa_data) return redirect()->back()->with('error','$value->coa uploaded does not exists, Pls download again upload template and reupload');
                    $bud_row = TrBudgetDtl::where('budget_id', $prd)
                                ->where('coa_code', $value->coa);
                    $bud_row->update([
                        'jan' => $value->januari,
                        'feb' => $value->februari,
                        'mar' => $value->maret,
                        'apr' => $value->april,
                        'may' => $value->may,
                        'jun' => $value->juni,
                        'jul' => $value->juli,
                        'aug' => $value->agustus,
                        'sep' => $value->september,
                        'okt' => $value->oktober,
                        'nov' => $value->november,
                        'des' => $value->desember
                    ]);
                }
            }
            Session::flash('msg', 'Upload Success.');
            return back();
        }
        return back();
    }

}
