<?php

use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder {

    public function run()
    {
        DB::table('ms_email_templates')->delete();

        DB::table('ms_email_templates')->insert([
          [
            'name' => 'SP1',
            'view' => 'emails.sp1',
            'title' => 'Surat SP1',
            'content' => 'template isi SP1'
          ],
          [
            'name' => 'SP2',
            'view' => 'emails.sp2',
            'title' => 'Surat SP2',
            'content' => 'template isi SP2'
          ],
          [
            'name' => 'SP3',
            'view' => 'emails.sp3',
            'title' => 'Surat SP3',
            'content' => 'template isi SP3'
          ]
        ]);
    }

}