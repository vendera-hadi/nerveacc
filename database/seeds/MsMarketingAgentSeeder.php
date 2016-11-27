<?php

use Illuminate\Database\Seeder;

class MsMarketingAgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ms_marketing_agent')->insert([
        [
            'mark_code' => 'AG001',
            'mark_name' => 'RIRIS HANDAYANI',
            'mark_address' => 'Kuningan City Jakarta Barat',
            'mark_phone' => '0815167895678',
            'mark_isactive' => TRUE,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ]
        ]);
    }
}
