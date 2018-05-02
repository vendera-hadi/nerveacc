<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TrLedger;
use App\Models\MsMasterCoa;
use App\Models\MsGroupAccount;
use DateTime;

class Cashflow extends Model
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

    public function settahun($tahun){
        $this->tahun = $tahun;
    }   

    public function setVariables($bulan,$data)
    {
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
    }

    public function cashflowCalculate($bulan,$tahun)
    {
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

}
