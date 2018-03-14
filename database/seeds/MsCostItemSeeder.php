<?php

use Illuminate\Database\Seeder;

class MsCostItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ms_cost_item')->insert([
        [
            'cost_code' => 'ELC',
            'cost_name' => 'ELECTRICITY',
            'cost_isactive' => TRUE,
            'created_by' => 1,
            'updated_by' => 1,
            'is_service_charge' => FALSE,
            'is_insurance' => FALSE,
            'is_sinking_fund' => FALSE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'cost_code' => 'WTR',
            'cost_name' => 'WATER',
            'cost_isactive' => TRUE,
            'created_by' => 1,
            'updated_by' => 1,
            'is_service_charge' => FALSE,
            'is_insurance' => FALSE,
            'is_sinking_fund' => FALSE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'cost_code' => 'GAZ',
            'cost_name' => 'GAZ',
            'cost_isactive' => FALSE,
            'created_by' => 1,
            'updated_by' => 1,
            'is_service_charge' => FALSE,
            'is_insurance' => FALSE,
            'is_sinking_fund' => FALSE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'cost_code' => 'SRV',
            'cost_name' => 'SERVICE CHARGE',
            'cost_isactive' => TRUE,
            'created_by' => 1,
            'updated_by' => 1,
            'is_service_charge' => TRUE,
            'is_insurance' => FALSE,
            'is_sinking_fund' => FALSE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'cost_code' => 'SNK',
            'cost_name' => 'SKINKING FUND',
            'cost_isactive' => FALSE,
            'created_by' => 1,
            'updated_by' => 1,
            'is_service_charge' => FALSE,
            'is_insurance' => FALSE,
            'is_sinking_fund' => TRUE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'cost_code' => 'INS',
            'cost_name' => 'INSURANCE',
            'cost_isactive' => FALSE,
            'created_by' => 1,
            'updated_by' => 1,
            'is_service_charge' => FALSE,
            'is_insurance' => TRUE,
            'is_sinking_fund' => FALSE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'cost_code' => 'CST1',
            'cost_name' => 'CUSTOM 1',
            'cost_isactive' => FALSE,
            'created_by' => 1,
            'updated_by' => 1,
            'is_service_charge' => FALSE,
            'is_insurance' => FALSE,
            'is_sinking_fund' => FALSE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'cost_code' => 'CST2',
            'cost_name' => 'CUSTOM 2',
            'cost_isactive' => FALSE,
            'created_by' => 1,
            'updated_by' => 1,
            'is_service_charge' => FALSE,
            'is_insurance' => FALSE,
            'is_sinking_fund' => FALSE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'cost_code' => 'STAMP',
            'cost_name' => 'MATERAI',
            'cost_isactive' => TRUE,
            'created_by' => 1,
            'updated_by' => 1,
            'is_service_charge' => FALSE,
            'is_insurance' => FALSE,
            'is_sinking_fund' => FALSE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'cost_code' => 'ADM',
            'cost_name' => 'ADMINISTRASI',
            'cost_isactive' => TRUE,
            'created_by' => 1,
            'updated_by' => 1,
            'is_service_charge' => FALSE,
            'is_insurance' => FALSE,
            'is_sinking_fund' => FALSE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ]
        ]);
    }
}
