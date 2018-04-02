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
            'invtp_coa_ar' => 10330,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'invtp_name' => 'INVOICE MAINTENANCE',
            'invtp_prefix' => 'MN',
            'invtp_coa_ar' => 10310,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'invtp_name' => 'INVOICE LAIN-LAIN',
            'invtp_prefix' => 'OT',
            'invtp_coa_ar' => 10390,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ]
        ]);
    }
}
