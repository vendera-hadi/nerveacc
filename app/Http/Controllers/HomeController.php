<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Models\TrContract;
use App\Models\MsUnit;
use App\Models\TrInvoice;
use App\Models\TrInvoicePaymhdr;
use App\Models\TrMeter;
use DB;

/**
 * Class HomeController
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $data['tenant'] = TrContract::where('contr_terminate_date',NULL)->count();
        $data['unit'] = MsUnit::count();
        $data['out'] = TrInvoice::select(DB::raw("SUM(inv_outstanding) AS ttl"))->where('inv_post',TRUE)->get();
        $data['inv'] = TrInvoice::where('inv_post',TRUE)->where(\DB::raw('date_part(\'year\', inv_date)'),'=',$year)->where(\DB::raw('date_part(\'month\', inv_date)'),'=',date('m'))->count();
        $fetch = TrInvoice::select(
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', inv_date) = 1 THEN inv_outstanding ELSE 0 END)) AS jan"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', inv_date) = 2 THEN inv_outstanding ELSE 0 END)) AS feb"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', inv_date) = 3 THEN inv_outstanding ELSE 0 END)) AS mar"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', inv_date) = 4 THEN inv_outstanding ELSE 0 END)) AS apr"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', inv_date) = 5 THEN inv_outstanding ELSE 0 END)) AS may"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', inv_date) = 6 THEN inv_outstanding ELSE 0 END)) AS jun"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', inv_date) = 7 THEN inv_outstanding ELSE 0 END)) AS jul"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', inv_date) = 8 THEN inv_outstanding ELSE 0 END)) AS aug"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', inv_date) = 9 THEN inv_outstanding ELSE 0 END)) AS sep"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', inv_date) = 10 THEN inv_outstanding ELSE 0 END)) AS okt"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', inv_date) = 11 THEN inv_outstanding ELSE 0 END)) AS nov"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', inv_date) = 12 THEN inv_outstanding ELSE 0 END)) AS des"),
            DB::raw("SUM(inv_outstanding) AS total")
            )
            ->where('inv_post',TRUE)
            ->whereYear('inv_date','=',$year)->get()->toArray();
        $isi = array();
        $isi[0] = (float)$fetch[0]['jan'];
        $isi[1] = (float)$fetch[0]['feb'];
        $isi[2] = (float)$fetch[0]['mar'];
        $isi[3] = (float)$fetch[0]['apr'];
        $isi[4] = (float)$fetch[0]['may'];
        $isi[5] = (float)$fetch[0]['jun'];
        $isi[6] = (float)$fetch[0]['jul'];
        $isi[7] = (float)$fetch[0]['aug'];
        $isi[8] = (float)$fetch[0]['sep'];
        $isi[9] = (float)$fetch[0]['okt'];
        $isi[10] =(float)$fetch[0]['nov'];
        $isi[11] = (float)$fetch[0]['des'];
        $data['hutang'] = json_encode($isi);

        $fetch2 = TrInvoicePaymHdr::select(
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', invpayh_date) = 1 THEN invpayh_amount ELSE 0 END)) AS jan"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', invpayh_date) = 2 THEN invpayh_amount ELSE 0 END)) AS feb"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', invpayh_date) = 3 THEN invpayh_amount ELSE 0 END)) AS mar"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', invpayh_date) = 4 THEN invpayh_amount ELSE 0 END)) AS apr"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', invpayh_date) = 5 THEN invpayh_amount ELSE 0 END)) AS may"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', invpayh_date) = 6 THEN invpayh_amount ELSE 0 END)) AS jun"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', invpayh_date) = 7 THEN invpayh_amount ELSE 0 END)) AS jul"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', invpayh_date) = 8 THEN invpayh_amount ELSE 0 END)) AS aug"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', invpayh_date) = 9 THEN invpayh_amount ELSE 0 END)) AS sep"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', invpayh_date) = 10 THEN invpayh_amount ELSE 0 END)) AS okt"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', invpayh_date) = 11 THEN invpayh_amount ELSE 0 END)) AS nov"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', invpayh_date) = 12 THEN invpayh_amount ELSE 0 END)) AS des"),
            DB::raw("SUM(invpayh_amount) AS total")
            )
            ->where('invpayh_post',TRUE)
            ->where('status_void',FALSE)
            ->whereYear('invpayh_date','=',$year)->get()->toArray();
        $isi2 = array();
        $isi2[0] = (float)$fetch2[0]['jan'];
        $isi2[1] = (float)$fetch2[0]['feb'];
        $isi2[2] = (float)$fetch2[0]['mar'];
        $isi2[3] = (float)$fetch2[0]['apr'];
        $isi2[4] = (float)$fetch2[0]['may'];
        $isi2[5] = (float)$fetch2[0]['jun'];
        $isi2[6] = (float)$fetch2[0]['jul'];
        $isi2[7] = (float)$fetch2[0]['aug'];
        $isi2[8] = (float)$fetch2[0]['sep'];
        $isi2[9] = (float)$fetch2[0]['okt'];
        $isi2[10] =(float)$fetch2[0]['nov'];
        $isi2[11] = (float)$fetch2[0]['des'];
        $data['bayar'] = json_encode($isi2);

        $total_all = $fetch[0]['total'] + $fetch2[0]['total'];

        $data['hutang_vs'] = (float)$fetch[0]['total'];
        $data['bayar_vs'] = (float)$fetch2[0]['total'];
        if($total_all == 0){
            $data['hutang_persen'] = 'N/A';
            $data['bayar_persen'] = 'N/A';
        }else{
            $data['hutang_persen'] = number_format($fetch[0]['total']/$total_all*100,2);
            $data['bayar_persen'] = number_format($fetch2[0]['total']/$total_all*100,2);
        }

        $fetchListrik = TrMeter::join('tr_period_meter','tr_period_meter.id','=','tr_meter.prdmet_id')->select(
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 1 THEN meter_used ELSE 0 END)) AS jan"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 2 THEN meter_used ELSE 0 END)) AS feb"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 3 THEN meter_used ELSE 0 END)) AS mar"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 4 THEN meter_used ELSE 0 END)) AS apr"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 5 THEN meter_used ELSE 0 END)) AS may"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 6 THEN meter_used ELSE 0 END)) AS jun"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 7 THEN meter_used ELSE 0 END)) AS jul"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 8 THEN meter_used ELSE 0 END)) AS aug"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 9 THEN meter_used ELSE 0 END)) AS sep"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 10 THEN meter_used ELSE 0 END)) AS okt"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 11 THEN meter_used ELSE 0 END)) AS nov"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 12 THEN meter_used ELSE 0 END)) AS des")
            )->whereHas('cost_detail', function($q){
                $q->where('cost_id',1);
            })->where(DB::raw("DATE_PART('YEAR', prdmet_end_date)"), $year)->first();
        $listrik[0] = (float)$fetchListrik['jan'];
        $listrik[1] = (float)$fetchListrik['feb'];
        $listrik[2] = (float)$fetchListrik['mar'];
        $listrik[3] = (float)$fetchListrik['apr'];
        $listrik[4] = (float)$fetchListrik['may'];
        $listrik[5] = (float)$fetchListrik['jun'];
        $listrik[6] = (float)$fetchListrik['jul'];
        $listrik[7] = (float)$fetchListrik['aug'];
        $listrik[8] = (float)$fetchListrik['sep'];
        $listrik[9] = (float)$fetchListrik['okt'];
        $listrik[10] =(float)$fetchListrik['nov'];
        $listrik[11] = (float)$fetchListrik['des'];
        $data['listrik'] = json_encode($listrik);

        $fetchAir = TrMeter::join('tr_period_meter','tr_period_meter.id','=','tr_meter.prdmet_id')->select(
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 1 THEN meter_used ELSE 0 END)) AS jan"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 2 THEN meter_used ELSE 0 END)) AS feb"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 3 THEN meter_used ELSE 0 END)) AS mar"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 4 THEN meter_used ELSE 0 END)) AS apr"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 5 THEN meter_used ELSE 0 END)) AS may"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 6 THEN meter_used ELSE 0 END)) AS jun"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 7 THEN meter_used ELSE 0 END)) AS jul"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 8 THEN meter_used ELSE 0 END)) AS aug"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 9 THEN meter_used ELSE 0 END)) AS sep"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 10 THEN meter_used ELSE 0 END)) AS okt"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 11 THEN meter_used ELSE 0 END)) AS nov"),
            DB::raw("SUM((CASE WHEN DATE_PART('MONTH', tr_period_meter.prdmet_end_date) = 12 THEN meter_used ELSE 0 END)) AS des")
            )->whereHas('cost_detail', function($q){
                $q->where('cost_id',2);
            })->where(DB::raw("DATE_PART('YEAR', prdmet_end_date)"), $year)->first();
        $air[0] = (float)$fetchAir['jan'];
        $air[1] = (float)$fetchAir['feb'];
        $air[2] = (float)$fetchAir['mar'];
        $air[3] = (float)$fetchAir['apr'];
        $air[4] = (float)$fetchAir['may'];
        $air[5] = (float)$fetchAir['jun'];
        $air[6] = (float)$fetchAir['jul'];
        $air[7] = (float)$fetchAir['aug'];
        $air[8] = (float)$fetchAir['sep'];
        $air[9] = (float)$fetchAir['okt'];
        $air[10] =(float)$fetchAir['nov'];
        $air[11] = (float)$fetchAir['des'];
        $data['air'] = json_encode($air);

        return view('home',$data);
    }

    public function contoh(){
        return view('contoh');
    }

    public function contohget(Request $request){
        $test = '{"total":"1","rows":[{"id":"63443","firstname":"3423","lastname":"1","phone":"4234","email":"234@ww.sda"}]}';
        $test = json_decode($test);
        return response()->json($test);
    }

    public function contohinsert(Request $request){
        // var_dump($request->all());
        $test = array_merge(['id'=>2], $request->all());
        return response()->json($test);
    }

    public function contohupdate(Request $request){
        // var_dump($request->all());
        $test = $request->all();
        return response()->json($test);
    }

    public function contohdelete(Request $request){
        return response()->json(['success'=>true]);
    }
}