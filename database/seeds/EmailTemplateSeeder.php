<?php

use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder {

    public function run()
    {
        DB::table('ms_email_templates')->delete();

        DB::table('ms_email_templates')->insert([
          [
            'name' => 'SP1',
            'view' => 'print_reminder2',
            'title' => 'Surat SP1',
            'content' => 'template isi SP1'
          ],
          [
            'name' => 'SP2',
            'view' => 'print_reminder2',
            'title' => 'Surat SP2',
            'content' => 'template isi SP2'
          ]
        ]);
    }

}