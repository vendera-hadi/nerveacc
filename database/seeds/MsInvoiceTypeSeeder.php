<?php

use Illuminate\Database\Seeder;

class MsInvoiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ms_invoice_type')->insert([
        [
            'invtp_name' => 'INVOICE UTILITIES',
            'invtp_prefix' => 'UT',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'invtp_name' => 'INVOICE MAINTENANCE',
            'invtp_prefix' => 'MN',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ]
        ]);
    }
}
