<?php

use Illuminate\Database\Seeder;

class MsJournalTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ms_journal_type')->insert([
        [
            'jour_type_name' => 'JURNAL UMUM',
            'jour_type_prefix' => 'JU',
            'jour_type_isactive' => TRUE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'jour_type_name' => 'KAS MASUK',
            'jour_type_prefix' => 'KM',
            'jour_type_isactive' => TRUE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'jour_type_name' => 'BANK MASUK',
            'jour_type_prefix' => 'BM',
            'jour_type_isactive' => TRUE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'jour_type_name' => 'KAS KELUAR',
            'jour_type_prefix' => 'KS',
            'jour_type_isactive' => TRUE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'jour_type_name' => 'BANK KELUAR',
            'jour_type_prefix' => 'BK',
            'jour_type_isactive' => TRUE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ],
        [
            'jour_type_name' => 'ACCOUNT RECEIVABLE',
            'jour_type_prefix' => 'AR',
            'jour_type_isactive' => TRUE,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ]
        ]);
    }
}
