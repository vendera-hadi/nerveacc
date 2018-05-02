<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TrLedger;
use App\Models\MsMasterCoa;
use App\Models\MsGroupAccount;
use App\Models\TrBudgetDtl;
use App\Models\TrBudgetHdr;
use DateTime;

class Realisasi extends Model
{
    protected $table ='ms_detail_format';
    protected $fillable =['formathd_id','coa_code','desc','header','variable','formula','linespace','underline','column','order'];

    protected $tahun;
   	protected $variables;
    protected $v_jan;
    protected $v_feb;
    protected $v_mar;
    protected $v_apr;
    protected $v_may;
    protected $v_jun;
    protected $v_jul;
    protected $v_aug;
    protected $v_sep;
    protected $v_okt;
    protected $v_nov;
    protected $v_des;
    protected $j_jan;
    protected $j_feb;
    protected $j_mar;
    protected $j_apr;
    protected $j_may;
    protected $j_jun;
    protected $j_jul;
    protected $j_aug;
    protected $j_sep;
    protected $j_okt;
    protected $j_nov;
    protected $j_des;

    public function settahun($tahun){
        $this->tahun = $tahun;
    }   

    public function setVariables($bulan,$data,$type)
    {
        if($type == 1){
            switch ($bulan) {
                case 'jan':
                    $this->v_jan = $data;
                    break;
                case 'feb':
                    $this->v_feb = $data;
                    break;
                case 'mar':
                    $this->v_mar = $data;
                    break;
                case 'apr':
                    $this->v_apr = $data;
                    break;
                case 'may':
                    $this->v_may = $data;
                    break;
                case 'jun':
                    $this->v_jun = $data;
                    break;
                case 'jul':
                    $this->v_jul = $data;
                    break;
                case 'aug':
                    $this->v_aug = $data;
                    break;
                case 'sep':
                    $this->v_sep = $data;
                    break;
                case 'okt':
                    $this->v_okt = $data;
                    break;
                case 'nov':
                    $this->v_nov = $data;
                    break;
                case 'des':
                    $this->v_des = $data;
                    break;
                default:
                    $this->variables = $data;
                    break;
            }
        }else{
            switch ($bulan) {
                case 'jan':
                    $this->j_jan = $data;
                    break;
                case 'feb':
                    $this->j_feb = $data;
                    break;
                case 'mar':
                    $this->j_mar = $data;
                    break;
                case 'apr':
                    $this->j_apr = $data;
                    break;
                case 'may':
                    $this->j_may = $data;
                    break;
                case 'jun':
                    $this->j_jun = $data;
                    break;
                case 'jul':
                    $this->j_jul = $data;
                    break;
                case 'aug':
                    $this->j_aug = $data;
                    break;
                case 'sep':
                    $this->j_sep = $data;
                    break;
                case 'okt':
                    $this->j_okt = $data;
                    break;
                case 'nov':
                    $this->j_nov = $data;
                    break;
                case 'des':
                    $this->j_des = $data;
                    break;
                default:
                    $this->variables = $data;
                    break;
            }
        }
    }

    public function cashflowCalculate($bulan,$tahun,$type)
    {
        if($type == 1){
            // jika berupa coa code
            if(is_numeric($this->attributes['coa_code'])){
                $total = $this->getTotalFromLedgerStartYeartoFrom($this->attributes['coa_code'],$bulan) + $this->getTotalFromLedger($this->attributes['coa_code'],$bulan);   
            }else if(substr($this->attributes['coa_code'], 0, 1) === '@'){
                // kalau group account
                $key = str_replace('@', '', $this->attributes['coa_code']);
                $group = MsGroupAccount::where('grpaccn_name',$key)->first();
                if($group){
                    $total = 0;
                    foreach ($group->detail as $dt) {
                        $total += $this->getTotalFromLedgerStartYeartoFrom($dt->coa_code) + $this->getTotalFromLedger($dt->coa_code);
                    }
                }else{
                    $total = 0;
                }
            }else if(!empty($this->attributes['formula'])){
                return $this->parseFormula($bulan);
            }else{
                return 0;
            }
            return $total;
        }else{
            switch ($bulan) {
                case '1':
                    $bln = 'jan';
                    break;
                case '2':
                    $bln = 'feb';
                    break;
                case '3':
                    $bln = 'mar';
                    break;
                case '4':
                    $bln = 'apr';
                    break;
                case '5':
                    $bln = 'may';
                    break;
                case '6':
                    $bln = 'jun';
                    break;
                case '7':
                    $bln = 'jul';
                    break;
                case '8':
                    $bln = 'aug';
                    break;
                case '9':
                    $bln = 'sep';
                    break;
                case '10':
                    $bln = 'okt';
                    break;
                case '11':
                    $bln = 'nov';
                    break;
                case '12':
                    $bln = 'des';
                    break;
                default:
                    # code...
                    break;
            }

            if(is_numeric($this->attributes['coa_code']) == TRUE){
                $bdg = TrBudgetDtl::join('tr_budget_hdr','tr_budget_hdr.id','=','tr_budget_dtl.budget_id')
                ->where('tahun',$tahun)
                ->where('coa_code',$this->attributes['coa_code'])->get();
                $total = $bdg[0]->$bln;
            }else if(substr($this->attributes['coa_code'], 0, 1) === '@'){
                // kalau group account
                $key = str_replace('@', '', $this->attributes['coa_code']);
                $group = MsGroupAccount::where('grpaccn_name',$key)->first();
                if($group){
                    $total = 0;
                    foreach ($group->detail as $dt) {
                        $bdg = TrBudgetDtl::join('tr_budget_hdr','tr_budget_hdr.id','=','tr_budget_dtl.budget_id')
                        ->where('tahun',$tahun)
                        ->where('coa_code',$dt->coa_code)->get();
                        $total += $bdg[0]->$bln;
                    }
                }else{
                    $total = 0;
                }
            }else if(!empty($this->attributes['formula'])){
                return $this->parseFormulaBudget($bln);
            }else{
                return 0;
            }
            return $total;
        }
    }

    private function getTotalFromLedgerStartYeartoFrom($coacode,$bulan)
    {
        $coa = MsMasterCoa::where('coa_code','like',$coacode."%")->where('coa_year',date('Y'))->first();
        if($coa){
            $from = ($this->tahun).'-'.$bulan.'-01';
            $ledger = TrLedger::where('coa_code','like',$coacode."%")
                ->where('ledg_date','>=',date('Y-01-01'))->where('ledg_date','<=',date('Y-m-d', strtotime('yesterday', strtotime($from) )))
                ->select(\DB::raw('SUM(ledg_debit) as debit'), \DB::raw('SUM(ledg_credit) as credit'))->first();
            if(strpos($coa->coa_type, 'DEBET') !== false) $total = $coa->coa_beginning + $ledger->debit - $ledger->credit;
            else $total = $coa->coa_beginning + $ledger->credit - $ledger->debit;
        }else{
            $total = 0;
        }
        return $total;
    }

    private function getTotalFromLedger($coacode,$bulan)
    {
        $from = ($this->tahun).'-'.$bulan.'-01';
        $d = new DateTime($from); 
        $to =  $d->format( 'Y-m-t' );
        $coa = MsMasterCoa::where('coa_code','like',$coacode."%")->where('coa_year',date('Y'))->first();
        if($coa){
            $ledger = TrLedger::where('coa_code','like',$coacode."%")
                ->where('ledg_date','>=',$from)->where('ledg_date','<=',$to)
                ->select(\DB::raw('SUM(ledg_debit) as debit'), \DB::raw('SUM(ledg_credit) as credit'))->first();
            if(strpos($coa->coa_type, 'DEBET') !== false) $total = $coa->coa_beginning + $ledger->debit - $ledger->credit;
            else $total = $coa->coa_beginning + $ledger->credit - $ledger->debit;
        }else{
            $total = 0;
        }
        return $total;
    }

    private function parseFormula($bulan)
    {
        try{
            $operators = [];
            $vars = [];
            $chars = str_split($this->attributes['formula']);
            $temp = '';
            foreach ($chars as $char) {
                if($char == '+' || $char == '-'){
                    $operators[] = $char;
                    $vars[] = $temp;
                    $temp = '';
                }else{
                    $temp .= $char;
                }
            }
            $vars[] = $temp;

            switch ($bulan) {
                case 'jan':
                    $isi = $this->v_jan;
                    break;
                case 'feb':
                    $isi = $this->v_feb;
                    break;
                case 'mar':
                    $isi = $this->v_mar;
                    break;
                case 'apr':
                    $isi = $this->v_apr;
                    break;
                case 'may':
                    $isi = $this->v_may;
                    break;
                case 'jun':
                    $isi = $this->v_jun;
                    break;
                case 'jul':
                    $isi = $this->v_jul;
                    break;
                case 'aug':
                    $isi = $this->v_aug;
                    break;
                case 'sep':
                    $isi = $this->v_sep;
                    break;
                case 'okt':
                    $isi = $this->v_okt;
                    break;
                case 'nov':
                    $isi = $this->v_nov;
                    break;
                case 'des':
                    $isi = $this->v_des;
                    break;
                default:
                     $isi = $this->variables;
                    break;
            }
        
            return $this->countFormula($vars, $operators,$isi);
        }catch(\Exception $e){
            return 0;
        }
    }

    private function countFormula($vars, $operators, $isi)
    {
        $total = $isi[$vars[0]];
        foreach ($operators as $key => $operator) {
            if($operator == '+') $total += $isi[$vars[$key+1]];
            if($operator == '-') $total -= $isi[$vars[$key+1]];
        }
        return $total;
    }

    private function parseFormulaBudget($bulan)
    {
        try{
            $operators = [];
            $vars = [];
            $chars = str_split($this->attributes['formula']);
            $temp = '';
            foreach ($chars as $char) {
                if($char == '+' || $char == '-'){
                    $operators[] = $char;
                    $vars[] = $temp;
                    $temp = '';
                }else{
                    $temp .= $char;
                }
            }
            $vars[] = $temp;

            switch ($bulan) {
                case 'jan':
                    $isi = $this->j_jan;
                    break;
                case 'feb':
                    $isi = $this->j_feb;
                    break;
                case 'mar':
                    $isi = $this->j_mar;
                    break;
                case 'apr':
                    $isi = $this->j_apr;
                    break;
                case 'may':
                    $isi = $this->j_may;
                    break;
                case 'jun':
                    $isi = $this->j_jun;
                    break;
                case 'jul':
                    $isi = $this->j_jul;
                    break;
                case 'aug':
                    $isi = $this->j_aug;
                    break;
                case 'sep':
                    $isi = $this->j_sep;
                    break;
                case 'okt':
                    $isi = $this->j_okt;
                    break;
                case 'nov':
                    $isi = $this->j_nov;
                    break;
                case 'des':
                    $isi = $this->j_des;
                    break;
                default:
                     $isi = $this->variables;
                    break;
            }
        
            return $this->countFormulaBudget($vars, $operators,$isi);
        }catch(\Exception $e){
            return 0;
        }
    }

    private function countFormulaBudget($vars, $operators, $isi)
    {
        $total = $isi[$vars[0]];
        foreach ($operators as $key => $operator) {
            if($operator == '+') $total += $isi[$vars[$key+1]];
            if($operator == '-') $total -= $isi[$vars[$key+1]];
        }
        return $total;
    }

}
