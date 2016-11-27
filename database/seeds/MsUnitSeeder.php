<?php

use Illuminate\Database\Seeder;

class MsUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ms_unit')->insert([
        [
            'unit_code' => '01P',
            'unit_name' => 'Unit 01P',
            'unit_sqrt' => 87.5,
            'unit_virtual_accn' => 1,
            'unit_isactive' => TRUE,
            'unit_isavailable' => FALSE,
            'created_by' => 1,
            'updated_by' => 1,
            'untype_id' => 1,
            'floor_id' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'unit_code' => '01S',
            'unit_name' => 'Unit 01S',
            'unit_sqrt' => 87.5,
            'unit_virtual_accn' => 2,
            'unit_isactive' => TRUE,
            'unit_isavailable' => FALSE,
            'created_by' => 1,
            'updated_by' => 1,
            'untype_id' => 1,
            'floor_id' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'unit_code' => '02P',
            'unit_name' => 'Unit 02P',
            'unit_sqrt' => 87.5,
            'unit_virtual_accn' => 3,
            'unit_isactive' => TRUE,
            'unit_isavailable' => FALSE,
            'created_by' => 1,
            'updated_by' => 1,
            'untype_id' => 1,
            'floor_id' => 2,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],[
            'unit_code' => '03M',
            'unit_name' => 'Unit 03M',
            'unit_sqrt' => 87.5,
            'unit_virtual_accn' => 4,
            'unit_isactive' => TRUE,
            'unit_isavailable' => FALSE,
            'created_by' => 1,
            'updated_by' => 1,
            'untype_id' => 1,
            'floor_id' => 3,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'unit_code' => '04K',
            'unit_name' => 'Unit 04K',
            'unit_sqrt' => 61,
            'unit_virtual_accn' => 5,
            'unit_isactive' => TRUE,
            'unit_isavailable' => FALSE,
            'created_by' => 1,
            'updated_by' => 1,
            'untype_id' => 1,
            'floor_id' => 4,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'unit_code' => '05Z',
            'unit_name' => 'Unit 05Z',
            'unit_sqrt' => 62,
            'unit_virtual_accn' => 6,
            'unit_isactive' => TRUE,
            'unit_isavailable' => FALSE,
            'created_by' => 1,
            'updated_by' => 1,
            'untype_id' => 1,
            'floor_id' => 5,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ]
        ]);
    }
}
