<?php

use Illuminate\Database\Seeder;

class MsDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ms_department')->insert([
        [
            'dept_name' => 'ENGINEERING',
            'dept_isactive' => TRUE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
            'deleted_at' => NULL
        ],
        [
            'dept_name' => 'HRD',
            'dept_isactive' => TRUE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
            'deleted_at' => NULL
        ],
        [
            'dept_name' => 'FINANCE & ACCOUNTING',
            'dept_isactive' => TRUE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
            'deleted_at' => NULL
        ],
        [
            'dept_name' => 'GENERAL AFFAIR',
            'dept_isactive' => TRUE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
            'deleted_at' => NULL
        ]
        ]);
    }
}
