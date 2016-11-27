<?php

use Illuminate\Database\Seeder;

class TrPeriodMeterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tr_period_meter')->insert([
        [
            'prdmet_id' => 'PRD-20160201-1',
            'prdmet_start_date' => '2016-01-01',
            'prdmet_end_date' => '2016-01-31',
            'prd_billing_date' => '2016-02-01',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
            'status' => TRUE
        ],
        [
            'prdmet_id' => 'PRD-20160301-1',
            'prdmet_start_date' => '2016-02-01',
            'prdmet_end_date' => '2016-02-29',
            'prd_billing_date' => '2016-03-01',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
            'status' => TRUE
        ],
        [
            'prdmet_id' => 'PRD-20160401-1',
            'prdmet_start_date' => '2016-03-01',
            'prdmet_end_date' => '2016-03-31',
            'prd_billing_date' => '2016-04-01',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
            'status' => TRUE
        ]
        ]);
    }
}
