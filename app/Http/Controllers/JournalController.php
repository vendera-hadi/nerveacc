<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\MsMasterCoa;
use App\Models\MsDepartment;
use App\Models\MsJournalType;
use App\Models\TrLedger;
use DB;

class JournalController extends Controller
{
    public function index(){
    	$coaYear = date('Y');
    	$data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->orderBy('coa_type')->get();
    	$data['departments'] = MsDepartment::where('dept_isactive',1)->get();
        $data['journal_types'] = MsJournalType::where('jour_type_isactive',1)->get();
        return view('journal_list', $data);
    }

    public function insert(Request $request){
        // check refno
        if(!isset($request->ledg_refno)) return response()->json(['errorMsg' => 'Refno is required']);
        $checkRefno = TrLedger::where('ledg_refno', $request->ledg_refno)->first();
        if($checkRefno) return response()->json(['errorMsg' => 'Refno is already exist']);

        $coa_code = $request->input('coa_code');
        $debit = $request->input('debit');
        $credit = $request->input('credit');
        if(count($coa_code) > 0){
            DB::transaction(function () use($request, $coa_code, $debit, $credit){
                foreach ($coa_code as $key => $value) {
                    $input = [
                            'ledg_id' => 'JRNL'.str_replace(".", "", str_replace(" ", "",microtime())),
                            'ledge_fisyear' => date('Y'),
                            'ledg_number' => $coa_code[$key], //ini sementara coa code dulu
                            'ledg_date' => $request->ledg_date,
                            'ledg_refno' => $request->ledg_refno,
                            'ledg_debit' => $debit[$key],
                            'ledg_credit' => $credit[$key],
                            'ledg_description' => $request->ledg_description,
                            'coa_year' => date('Y'),
                            'coa_code' => $coa_code[$key],
                            'dept_code' => $request->dept_code,
                            'created_by' => \Auth::id(),
                            'updated_by' => \Auth::id(),
                            'jour_type_id' => $request->jour_type_id
                        ];
                    TrLedger::create($input);
                }
            });
            return ['status' => 1, 'message' => 'Update Success'];
        }
    }

    public function get(Request $request){
        var_dump($request->all());
    }

    public function accountSelect2(Request $request){
    	$key = $request->q;
    	$coaYear = date('Y');
        $fetch = MsMasterCoa::select('coa_code','coa_name','coa_type')->where(function($query) use($key){
            $query->where(\DB::raw('LOWER(coa_code)'),'like','%'.$key.'%')->orWhere(\DB::raw('LOWER(coa_name)'),'like','%'.$key.'%');
        })->where('coa_year',$coaYear)->get();
        
        $result['results'] = [];
        foreach ($fetch as $key => $value) {
            $temp = ['id'=>$value->id, 'text'=>$value->coa_code." ".$value->coa_name];
            array_push($result['results'], $temp);
        }
        return json_encode($result);
    }
}
