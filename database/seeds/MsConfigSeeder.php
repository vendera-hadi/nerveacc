<?php

use Illuminate\Database\Seeder;

class MsConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ms_config')->insert([
            [
                'name' => 'inv_outstanding_active',
                'desc' => 'Aktifkan Biaya Outstanding di Invoice',
                'value' => 0
            ],[
                'name' => 'denda_variable',
                'desc' => 'Variabel pengali denda',
                'value' => 0.001
            ],[
                'name' => 'denda_active',
                'desc' => 'Aktifkan Denda di Invoice',
                'value' => 0
            ],[
                'name' => 'footer_invoice',
                'desc' => 'Text untuk footer invoice',
                'value' => '<p>'
            ],[
                'name' => 'footer_label_inv',
                'desc' => 'Text untuk footer label invoice',
                'value' => '<p>'
            ],[
                'name' => 'service_charge_alias',
                'desc' => 'Nama lain dari Service Charge',
                'value' => 'IURAN PENGELOLAAN LINGKUNGAN'
            ],[
                'name' => 'duedate_interval',
                'desc' => 'Interval keterlambatan Invoice',
                'value' => 14
            ],[
                'name' => 'invoice_signature_flag',
                'desc' => 'Aktifkan Signature',
                'value' => 0
            ],[
                'name' => 'inv_body_email',
                'desc' => 'Template body email',
                'value' => 'ini contoh body email'
            ],[
                'name' => 'prefix_kuitansi',
                'desc' => 'Format prefix kuitansi (2 atau 3 digit)',
                'value' => 'KW'
            ],[
                'name' => 'use_ppn',
                'desc' => 'Gunakan PPN',
                'value' => 0
            ],[
                'name' => 'footer_po',
                'desc' => 'Template footer PO',
                'value' => 'footer'
            ],[
                'name' => 'footer_label_po',
                'desc' => 'Label footer PO',
                'value' => 'label'
            ],[
                'name' => 'digital_signature',
                'desc' => 'Image tanda tangan',
                'value' => ''
            ],[
                'name' => 'footer_signature_name',
                'desc' => 'Nama di tanda tangan',
                'value' => 'Tim Pengelola'
            ],[
                'name' => 'footer_signature_position',
                'desc' => 'Jabatan pada nama di signature',
                'value' => 'Pengelola'
            ],[
                'name' => 'po_prefix',
                'desc' => 'Prefix pada PO (2 atau 3 digit)',
                'value' => 'PO'
            ],[
                'name' => 'email_pengelola',
                'desc' => 'Email Pengelola',
                'value' => 'admin@example.com'
            ],[
                'name' => 'coa_laba_rugi',
                'desc' => 'COA Laba Rugi',
                'value' => 30120
            ]
            ]);
    }
}
