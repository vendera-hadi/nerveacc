<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\MsMarketingAgent;

class MarketingAgentController extends Controller
{
    public function getOptMarketing(Request $request){
    	$key = $request->q;
        $fetch = MsMarketingAgent::select('id','mark_code','mark_name')->where(function($query) use($key){
            $query->where(\DB::raw('LOWER(mark_code)'),'like','%'.$key.'%')->orWhere(\DB::raw('LOWER(mark_name)'),'like','%'.$key.'%');
        })->where('mark_isactive','TRUE')->get();
        
        $result['results'] = [];
        foreach ($fetch as $key => $value) {
            $temp = ['id'=>$value->id, 'text'=>$value->mark_code." (".$value->mark_name.")"];
            array_push($result['results'], $temp);
        }
        return json_encode($result);
    }
}
