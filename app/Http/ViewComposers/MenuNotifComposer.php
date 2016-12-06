<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\TrContract;

class MenuNotifComposer
{

    /**
     * Create a new profile composer.
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct()
    {
        // Dependencies automatically resolved by service container...
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $totalNotif = 0;
        // get contract yg belum di closed
        $unclosed = TrContract::select('tr_contract.*','ms_tenant.tenan_name')
                    ->join('ms_tenant','ms_tenant.id',"=",'tr_contract.tenan_id')->where(function($query){
                        $query->where('contr_terminate_date','<=', date("Y-m-d", strtotime("+1 week")))->where('contr_status','confirmed');
                    })->orWhere(function($query){
                        $query->where('contr_enddate','<=', date("Y-m-d", strtotime("+1 week")))->whereNull('contr_terminate_date')->where('contr_status','confirmed');
                    })->count();
        if(!empty($unclosed)) $totalNotif+=1;
        $view->with('notif_unclosed', $unclosed);
        $view->with('total_notif', $totalNotif);
    }
}