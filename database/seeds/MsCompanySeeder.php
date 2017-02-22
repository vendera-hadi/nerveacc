<?php

use Illuminate\Database\Seeder;

class MsCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ms_company')->insert([
            'comp_name' => 'Unit Testing',
            'comp_address' => 'Jl. Jendral Sudirman No. 99 Jakarta',
            'comp_phone' => '021 111111',
            'comp_fax' => '021 111111',
            'comp_sign_inv_name' => 'Budi',
            'comp_build_insurance' => 200000,
            'comp_npp_insurance' => 5000,
            'comp_materai1' => 3000,
            'comp_materai1_amount' => 1000000,
            'comp_materai2' => 6000,
            'comp_materai2_amount' => 0,
            'cashbk_id' => 1,
            'comp_image' => 'company.png',
            'comp_sign_position' => 'Property Manager'
        ]);
    }
}
