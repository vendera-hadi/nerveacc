<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TrLedger;
use App\Models\MsMasterCoa;
use App\Models\MsGroupAccount;

class MsDetailFormat extends Model
{
    protected $table ='ms_detail_format';
    protected $fillable =['formathd_id','coa_code','desc','header','variable','formula','linespace','underline','column'];

    protected $from;
   	protected $to;
   	protected $variables;

    public function setDate($from, $to){
    	$this->from = $from." 00:00:00";
    	$this->to = $to." 23:59:59";
    }

    public function setVariables($data)
    {
    	$this->variables = $data;
    }

    public function calculateAccount()
    {
    	// jika berupa coa code
    	if(is_numeric($this->attributes['coa_code'])){
    		if($this->attributes['coa_code'] == 30120){
                // pengecualian buat laba rugi berjalan
                $total = $this->labarugiBerjalan();
            }else{
                $total = $this->getTotalFromLedger($this->attributes['coa_code']);
            }
    	}else if(substr($this->attributes['coa_code'], 0, 1) === '@'){ 
    		// kalau group account
    		$key = str_replace('@', '', $this->attributes['coa_code']);
    		$group = MsGroupAccount::where('grpaccn_name',$key)->first();
    		if($group){
    			$total = 0;
    			foreach ($group->detail as $dt) {
    				$total += $this->getTotalFromLedger($dt->coa_code);
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

    private function getTotalFromLedger($coacode)
    {
    	$coa = MsMasterCoa::where('coa_code','like',$coacode."%")->where('coa_year',date('Y'))->first();
		if($coa){
			$ledger = TrLedger::where('coa_code','like',$coacode."%")
				->where('ledg_date','>=',$this->from)->where('ledg_date','<=',$this->to)
				->select(\DB::raw('SUM(ledg_debit) as debit'), \DB::raw('SUM(ledg_credit) as credit'))->first();
			if(strpos($coa->coa_type, 'DEBET') !== false) $total = $coa->coa_beginning + abs($ledger->debit - $ledger->credit);
			else $total = $coa->coa_beginning + abs($ledger->credit - $ledger->debit);
		}else{
			$total = 0;
		}
		return $total;
    }

    private function labarugiBerjalan()
    {
        $coa = MsMasterCoa::where('coa_code','like',"30120%")->where('coa_year',date('Y'))->first();
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

    private function parseFormula()
    {
    	try{
	    	$operators = [];
	    	$vars = [];
	    	$chars = str_split($this->attributes['formula']);
	    	$temp = '';
	    	foreach ($chars as $char) {
	    		if($char == '+' || $char == '-'){
	    			$operators[] = '+';
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
