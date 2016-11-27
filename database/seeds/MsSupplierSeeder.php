<?php

use Illuminate\Database\Seeder;

class MsSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ms_supplier')->insert([
        [
            'spl_code' => 'SP001',
            'spl_name' => 'OC2 COMPUTER',
            'spl_address' => 'Harco Mangga Dua',
            'spl_city' => 'Jakarta',
            'spl_postal_code' => '12480',
            'spl_phone' => '021987654523',
            'spl_fax' => '021987654523',
            'spl_cperson' => 'Ibu Emma',
            'spl_npwp' => '357691724502000',
            'spl_isactive' => TRUE,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]
        ]);
    }
}
