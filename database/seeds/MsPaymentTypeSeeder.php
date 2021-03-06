<?php

use Illuminate\Database\Seeder;

class MsPaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ms_payment_type')->insert([
        [
            'paymtp_name' => 'CASH/TUNAI',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ],
        [
            'paymtp_name' => 'BANK',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ],
        [
            'paymtp_name' => 'CHECK',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]
        ]);
    }
}
