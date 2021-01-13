<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TrLedger;
use App\Models\MsMasterCoa;
use App\Models\MsGroupAccount;
use App\Models\MsConfig;

class MsDetailFormat extends Model
{
    protected $table ='ms_detail_format';
    protected $fillable =['formathd_id','coa_code','desc','header','variable','formula','linespace','underline','column','order'];

    protected $from;
    protected $to;
    protected $variables;
    protected $v_ytd;

    public function setDate($from, $to){
        $this->from = $from." 00:00:00";
        $this->to = $to." 23:59:59";
    }

    public function setVariables($data)
    {
        $this->variables = $data;
    }

    public function calculateAccount($jns)
    {
        // jika berupa coa code
        if(is_numeric($this->attributes['coa_code'])){
            $coa_laba_rugi = @MsConfig::where('name','coa_laba_rugi')->first()->value;
            if(strlen($this->attributes['coa_code']) == 5){
                if(str_replace(' ','',$this->attributes['coa_code']) == str_replace(' ','',$coa_laba_rugi)){
                    // pengecualian buat laba rugi berjalan
                    $total = $this->labarugiBerjalanStartYeartoFrom() + $this->labarugiBerjalan();
                }else{
                    if($jns == 0){
                    $total = $this->getTotalFromLedgerStartYeartoFrom($this->attributes['coa_code']) - $this->getTotalFromLedgerStartYeartoFrom($this->attributes['coa_code']) + $this->getTotalFromLedger($this->attributes['coa_code'],$jns);
                    }else{
                        $total = $this->getTotalFromLedger($this->attributes['coa_code'],$jns);
                    }
                }
            }else{
                $ats = substr(str_replace(' ','',$this->attributes['coa_code']), -1);
                $st_month = date('m',strtotime($this->from));
                $st_year = date('Y',strtotime($this->from));
                $en_month = date('m',strtotime($this->to));
                $en_year = date('Y',strtotime($this->to)); 
                if($ats == 1){
                    // pengecualian buat laba rugi berjalan akasa
                    $total = $this->labarugiBerjalanStartYeartoFrom();
                }else{
                    $total = $this->berjalanakasa($st_month,$st_year,$en_month,$en_year);
                }
            }
        }else if(substr($this->attributes['coa_code'], 0, 1) === '@'){
            // kalau group account
            $key = str_replace('@', '', $this->attributes['coa_code']);
            $group = MsGroupAccount::where('grpaccn_name',$key)->first();
            if($group){
                $total = 0;
                foreach ($group->detail as $dt) {
                    if($jns == 0){
                        $total += $this->getTotalFromLedgerStartYeartoFrom($dt->coa_code) - $this->getTotalFromLedgerStartYeartoFrom($dt->coa_code) + $this->getTotalFromLedger($dt->coa_code,$jns);
                    }else{
                        $total += $this->getTotalFromLedger($dt->coa_code,$jns);
                    }
                }
            }else{
                $total = 0;
            }
        }else if(!empty($this->attributes['formula'])){
            return $this->parseFormula();
        }else{
            return 0;
        }
        return $total;
    }

    private function getTotalFromLedgerStartYeartoFrom($coacode)
    {
        $years = date('Y',strtotime($this->from));
        $coa = MsMasterCoa::where('coa_code','like',$coacode."%")->where('coa_year',$years)->first();
        if($coa){
            $ledger = TrLedger::where('coa_code','like',$coacode."%")
                ->where('ledg_date','>=',date('Y-01-01'))->where('ledg_date','<=',date('Y-m-d', strtotime('yesterday', strtotime($this->from) )))
                ->select(\DB::raw('SUM(ledg_debit) as debit'), \DB::raw('SUM(ledg_credit) as credit'))->first();
            if(strpos($coa->coa_type, 'DEBET') !== false) $total = $coa->coa_beginning + $ledger->debit - $ledger->credit;
            else $total = $coa->coa_beginning + $ledger->credit - $ledger->debit;
        }else{
            $total = 0;
        }
        return $total;
    }

    private function getTotalFromLedger($coacode,$jns)
    {
        $years = date('Y',strtotime($this->from));
        $coa = MsMasterCoa::where('coa_code','like',$coacode."%")->where('coa_year',$years)->first();
        if($jns == 0){
            if($coa){
                $ledger = TrLedger::where('coa_code','like',$coacode."%")
                    ->where('ledg_date','>=',$this->from)->where('ledg_date','<=',$this->to)
                    ->select(\DB::raw('SUM(ledg_debit) as debit'), \DB::raw('SUM(ledg_credit) as credit'))->first();
                if(strpos($coa->coa_type, 'DEBET') !== false) $total = $coa->coa_beginning + $ledger->debit - $ledger->credit;
                else $total = $coa->coa_beginning + $ledger->credit - $ledger->debit;
            }else{
                $total = 0;
            }
        }else{
            if($coa){
                $ledger = TrLedger::where('coa_code','like',$coacode."%")
                    ->where('ledg_date','>=',$this->from)->where('ledg_date','<=',$this->to)
                    ->select(\DB::raw('SUM(ledg_debit) as debit'), \DB::raw('SUM(ledg_credit) as credit'))->first();
                if(strpos($coa->coa_type, 'DEBET') !== false) $total = $ledger->debit - $ledger->credit;
                else $total = $ledger->credit - $ledger->debit;
            }else{
                $total = 0;
            }
        }
        return $total;
    }

    private function labarugiBerjalanStartYeartoFrom()
    {
        $years = date('Y',strtotime($this->from));
        $coa_laba_rugi = @MsConfig::where('name','coa_laba_rugi')->first()->value;
        $coa = MsMasterCoa::where('coa_code','like',"$coa_laba_rugi%")->where('coa_year',$years)->first();
        // rekap pendapatan
        $ledgerProfit = TrLedger::where(function($query){
                        $query->where('coa_code','like',"4%")->orWhere('coa_code','like',"6%");
                })
                ->where('ledg_date','>=',date('Y-01-01'))->where('ledg_date','<=',date('Y-m-d', strtotime('yesterday', strtotime($this->from) )))
                ->select(\DB::raw('SUM(ledg_debit) as debit'), \DB::raw('SUM(ledg_credit) as credit'))->first();
        $profit = $ledgerProfit->credit - $ledgerProfit->debit;
        $ledgerLoss = TrLedger::where(function($query){
                        $query->where('coa_code','like',"5%")->orWhere('coa_code','like',"7%");
                })
                ->where('ledg_date','>=',date('Y-01-01'))->where('ledg_date','<=',date('Y-m-d', strtotime('yesterday', strtotime($this->from) )))
                ->select(\DB::raw('SUM(ledg_debit) as debit'), \DB::raw('SUM(ledg_credit) as credit'))->first();
        $loss = $ledgerLoss->debit - $ledgerLoss->credit;
        $result = $coa->coa_beginning + $profit - $loss;
        return $result;
    }

    private function labarugiBerjalan()
    {
        $years = date('Y',strtotime($this->from));
        $coa_laba_rugi = @MsConfig::where('name','coa_laba_rugi')->first()->value;
        $coa = MsMasterCoa::where('coa_code','like',"$coa_laba_rugi%")->where('coa_year',$years)->first();
        // rekap pendapatan
        $ledgerProfit = TrLedger::where(function($query){
                        $query->where('coa_code','like',"4%")->orWhere('coa_code','like',"6%");
                })->where('ledg_date','>=',$this->from)->where('ledg_date','<=',$this->to)
                ->select(\DB::raw('SUM(ledg_debit) as debit'), \DB::raw('SUM(ledg_credit) as credit'))->first();
        $profit = abs($ledgerProfit->credit - $ledgerProfit->debit);
        $ledgerLoss = TrLedger::where(function($query){
                        $query->where('coa_code','like',"5%")->orWhere('coa_code','like',"7%");
                })->where('ledg_date','>=',$this->from)->where('ledg_date','<=',$this->to)
                ->select(\DB::raw('SUM(ledg_debit) as debit'), \DB::raw('SUM(ledg_credit) as credit'))->first();
        $loss = abs($ledgerLoss->debit - $ledgerLoss->credit);
        $result = $coa->coa_beginning + $profit - $loss;
        return $result;
    }

    private function berjalanakasa($st_month,$st_year,$en_month,$en_year)
    {
        $pd_usaha = '@pendapatan_usaha';
        $bn_pokok = '@beban_pokok_usaha';
        $bn_umum = '@beban_umum_adm';
        $pd_lainnya = '@pendapatan_lainnya';
        $bn_lainnya = '@beban_lainnya';

        $pd_usaha_nilai = $this->hitunggrp($pd_usaha,$st_month,$st_year,$en_month,$en_year);
        $bn_pokok_nilai = $this->hitunggrp($bn_pokok,$st_month,$st_year,$en_month,$en_year);
        $bn_umum_nilai = $this->hitunggrp($bn_umum,$st_month,$st_year,$en_month,$en_year);
        $pd_lainnya_nilai = $this->hitunggrp($pd_lainnya,$st_month,$st_year,$en_month,$en_year);
        $bn_lainnya_nilai = $this->hitunggrp($bn_lainnya,$st_month,$st_year,$en_month,$en_year);

        $total = $pd_usaha_nilai-$bn_pokok_nilai-$bn_umum_nilai+$pd_lainnya_nilai-$bn_lainnya_nilai;

        return $total;
    }

    private function hitunggrp($key,$st_month,$st_year,$en_month,$en_year)
    {
        $key1 = str_replace('@', '', $key);
        $group = MsGroupAccount::where('grpaccn_name',$key1)->first();
        if($group){
            $total = 0;
            foreach ($group->detail as $dt) {
                $total += $this->getTotalFromLedgerAkasa($dt->coa_code,$st_month,$st_year,$en_month,$en_year);
            }
        }else{
            $total = 0;
        }
        return $total;
    }

    private function getTotalFromLedgerAkasa($coacode,$st_month,$st_year,$en_month,$en_year)
    {
        $start = $st_year.'-'.$st_month.'-01';
        $end = date("Y-m-t", strtotime($en_year.'-'.$en_month.'-01'));
        $years = $en_year;
        $coa = MsMasterCoa::where('coa_code','like',$coacode."%")->where('coa_year',$years)->first();
        if($coa){
            $ledger = TrLedger::where('coa_code','like',$coacode."%")
                ->where('ledg_date','>=',$start)->where('ledg_date','<=',$end)
                ->select(\DB::raw('SUM(ledg_debit) as debit'), \DB::raw('SUM(ledg_credit) as credit'))->first();
            if(strpos($coa->coa_type, 'DEBET') !== false) $total = $ledger->debit - $ledger->credit;
            else $total = $ledger->credit - $ledger->debit;
        }else{
            $total = 0;
        }
        return $total;
    }

    private function parseFormula()
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
            return $this->countFormula($vars, $operators);
        }catch(\Exception $e){
            return 0;
        }
    }

    private function countFormula($vars, $operators)
    {
        $total = $this->variables[$vars[0]];
        foreach ($operators as $key => $operator) {
            if($operator == '+') $total += $this->variables[$vars[$key+1]];
            if($operator == '-') $total -= $this->variables[$vars[$key+1]];
        }
        return $total;
    }

}
