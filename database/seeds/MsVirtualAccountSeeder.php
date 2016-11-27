<?php

use Illuminate\Database\Seeder;

class MsVirtualAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ms_virtual_account')->insert([
        [
            'viracc_no' => '0017911111112',
            'viracc_name' => 'Unit 01P',
            'viracc_isactive' => TRUE,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'viracc_no' => '0017911111113',
            'viracc_name' => 'Unit 01S',
            'viracc_isactive' => TRUE,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'viracc_no' => '0017911111114',
            'viracc_name' => 'Unit 02P',
            'viracc_isactive' => TRUE,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'viracc_no' => '0017911111115',
            'viracc_name' => 'Unit 03M',
            'viracc_isactive' => TRUE,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'viracc_no' => '0017911111116',
            'viracc_name' => 'Unit 04K',
            'viracc_isactive' => TRUE,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'viracc_no' => '0017911111117',
            'viracc_name' => 'Unit 05Z',
            'viracc_isactive' => TRUE,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ]
        ]);
    }
}
