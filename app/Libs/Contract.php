<?php
namespace App\Libs;

use App\Models\TrContract;

class Contract {

    public function __construct($periodStart, $periodEnd)
    {
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
    }

    // SETTER
    public function setInvType($invtp_id)
    {
        $this->invtp_id = $invtp_id;
    }

    public function setContract($contractId)
    {
        $this->contract = TrContract::find($contractId);
    }

    // GETTER
    public function countAvailable()
    {
        return TrContract::where('contr_iscancel',false)
                    ->where('contr_status','!=','closed')
                    ->where('contr_status','confirmed')
                    ->whereHas('contractInv', function($q){
                        $q->where('invtp_id', $this->invtp_id);
                      })
                    ->where('contr_startdate','<=',$this->periodStart)
                    ->where('contr_enddate','>=',$this->periodEnd)->count();
    }

    public function getAvailable()
    {
        return TrContract::where('contr_iscancel',false)
                    ->where('contr_status','!=','closed')
                    ->where('contr_status','confirmed')
                    ->whereHas('contractInv', function($q){
                        $q->where('invtp_id', $this->invtp_id);
                      })
                    ->where('contr_startdate','<=',$this->periodEnd)
                    ->where('contr_enddate','>=',$this->periodStart)->get();
    }

    public function getCostItems($invoice_type_id)
    {
        return $this->contract->contractInv->where('invtp_id',$invoice_type_id)->pluck('costd_id');
    }
}