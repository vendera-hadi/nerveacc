<?php

use Illuminate\Database\Seeder;

class TrCurrencyRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tr_currency_rate')->insert([
        [
            'curr_rate_date' => '2016-01-30',
            'curr_rate_value' => '13000',
            'curr_code' => 2
        ]
        ]);
    }
}
