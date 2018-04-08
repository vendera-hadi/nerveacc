<?php
namespace App\Libs;

use App\Models\MsCostDetail;
use App\Models\TrContract;

class Meter {

    public function setValue($parameters)
    {
        foreach ($parameters as $key => $value) {
          $this->$key = $value;
        }
    }

    public function setCostDetail($costitemId)
    {
        $this->costDetail = MsCostDetail::find($costitemId);
    }

    public function setContract($contractId)
    {
        $this->contract = TrContract::find($contractId);
    }

    public function getMeterUsed()
    {
        return $this->meter_end - $this->meter_start;
    }

}