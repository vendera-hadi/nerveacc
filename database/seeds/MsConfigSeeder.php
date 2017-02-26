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
            ]
            ]);
    }
}
