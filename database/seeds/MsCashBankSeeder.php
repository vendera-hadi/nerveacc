<?php

use Illuminate\Database\Seeder;

class MsCashBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ms_cash_bank')->insert([
        [
            'cashbk_name' => 'Bank BCA # 035-360-000-9',
            'cashbk_isbank' => TRUE,
            'cashbk_accn_no' => '0353600009',
            'coa_code' => '10210',
            'curr_code' => '1',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ],
        [
            'cashbk_name' => 'Bank BCA # 035-318-889-2',
            'cashbk_isbank' => TRUE,
            'cashbk_accn_no' => '0353188892',
            'coa_code' => '10220',
            'curr_code' => '1',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ],
        [
            'cashbk_name' => 'Bank BCA # 035-36-000068 Virtual',
            'cashbk_isbank' => TRUE,
            'cashbk_accn_no' => '03536000068',
            'coa_code' => '10230',
            'curr_code' => '1',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ],
        [
            'cashbk_name' => 'Bank In Transit',
            'cashbk_isbank' => TRUE,
            'cashbk_accn_no' => '03536000064',
            'coa_code' => '10290',
            'curr_code' => '1',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]
        ]);
    }
}
