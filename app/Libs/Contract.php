<?php
namespace App\Libs;

use App\Models\TrContract;
use App\Models\MsUnitOwner;
use App\Models\MsTenant;

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
                    ->where('contr_startdate','<=',$this->periodEnd)
                    ->where('contr_enddate','>=',$this->periodStart)->count();
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

    public function isOwner()
    {
        $unit_owner = MsUnitOwner::where('unit_id',$this->contract->unit_id)->first();
        if($unit_owner){
            if($unit_owner->tenan_id == $this->contract->tenan_id) return true;
        }
        return false;
    }

    public function getOwner()
    {
        return MsUnitOwner::where('unit_id',$this->contract->unit_id)->first();
    }

    public function getOwnerContract()
    {
        $owner = $this->getOwner();
        if(!$owner) return false;
        return TrContract::where('unit_id',$this->contract->unit_id)->where('tenan_id', $owner->tenan_id)->where('contr_status','confirmed')->where('contr_iscancel',false)->first();
    }

    public function getTenant()
    {
        return MsTenant::find($this->contract->tenan_id);
    }
}