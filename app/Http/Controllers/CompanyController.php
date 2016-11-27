<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
// load model
use App\Models\MsCashBank;
use App\Models\MsCompany;
use Form;

class CompanyController extends Controller
{
    public function index(){
        return view('company');
    }

    public function index2(){
        $data['company'] = MsCompany::first();
        $data['cashbanks'] = MsCashBank::all();
        return view('company2', $data);
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
            $count = MsCompany::count();
            $fetch = MsCompany::select('ms_company.*','ms_cash_bank.cashbk_code')->join('ms_cash_bank',\DB::raw('ms_company.cashbk_id::integer'),"=",\DB::raw('ms_cash_bank.id::integer'));
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
                $temp['comp_name'] = $value->comp_name;
                $temp['comp_address'] = $value->comp_address;
                $temp['comp_phone'] = $value->comp_phone;
                $temp['comp_fax'] = $value->comp_fax;
                $temp['comp_sign_inv_name'] = $value->comp_sign_inv_name;
                $temp['comp_build_insurance'] = $value->comp_build_insurance;
                $temp['comp_npp_insurance'] = $value->comp_npp_insurance;
                $temp['cashbk_id'] = $value->cashbk_id;
                $temp['cashbk_code'] = $value->cashbk_code;
                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function getOptions(){
        try{
            $all = MsCashBank::all();
            $result = [];
            if(count($all) > 0){
                foreach ($all as $value) {
                    $result[] = ['id'=>$value->id, 'text'=>$value->cashbk_code];
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
            return MsCompany::create($input);        
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function update(Request $request){
        $this->validate($request, [
            'comp_name' => 'required|max:100',
            'comp_address' => 'required|max:150',
            'comp_phone' => 'required|max:20',
            'comp_fax' => 'max:20',
            'comp_build_insurance' => 'digits_between:1,18',
            'comp_npp_insurance' => 'digits_between:1,12',
            'comp_materai1' => 'required|digits_between:1,4',
            'comp_materai2' => 'required|digits_between:1,4',
            'comp_materai1_amount' => 'digits_between:1,10',
            'comp_materai2_amount' => 'digits_between:1,10',
        ]);

        $input = $request->except(['image']);
        $company = MsCompany::first();
        if ($request->hasFile('image')) {
            $extension = $request->image->extension();
            if(strtolower($extension)!='jpg' && strtolower($extension)!='png'){ 
                $request->session()->flash('error', 'Image Format must be jpg or png');
                return redirect()->back();
            }
            $path = $request->image->move(public_path('upload'), 'company.'.$extension);
            // dd($path);
            $input['comp_image'] = 'upload/company.'.$extension;
        }
        $company->update($input);
        $request->session()->flash('success', 'Update company data success');
        return redirect()->back();
    }

    public function update2(Request $request){
        try{
            $id = $request->id;
            $input = $request->all();
            MsCompany::find($id)->update($input);
            return MsCompany::find($id);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }

    public function delete(Request $request){
        try{
            $id = $request->id;
            MsCompany::destroy($id);
            return response()->json(['success'=>true]);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        } 
    }
}
