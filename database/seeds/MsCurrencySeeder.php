<?php

use Illuminate\Database\Seeder;

class MsCurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ms_currency')->insert([
        [
            'curr_name' => 'RUPIAH',
            'curr_isactive' => TRUE,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ],
        [
            'curr_name' => 'DOLLAR',
            'curr_isactive' => FALSE,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]
        ]);
    }
}
