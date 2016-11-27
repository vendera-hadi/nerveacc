<?php

use Illuminate\Database\Seeder;

class MsCostDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ms_cost_detail')->insert([
        [
            'cost_id' => 1,
            'costd_name' => 'ELECTRICITY 900 W',
            'costd_unit' => 'Kwh',
            'costd_rate' => '350',
            'costd_burden' => '50000',
            'costd_admin' => '2500',
            'costd_ismeter' => TRUE
        ],
        [
            'cost_id' => 1,
            'costd_name' => 'ELECTRICITY 1300 W',
            'costd_unit' => 'Kwh',
            'costd_rate' => '550',
            'costd_burden' => '55000',
            'costd_admin' => '2500',
            'costd_ismeter' => TRUE
        ],
        [
            'cost_id' => 1,
            'costd_name' => 'ELECTRICITY 2500 W',
            'costd_unit' => 'Kwh',
            'costd_rate' => '750',
            'costd_burden' => '70000',
            'costd_admin' => '2500',
            'costd_ismeter' => TRUE
        ],
        [
            'cost_id' => 2,
            'costd_name' => 'WATER',
            'costd_unit' => 'M3',
            'costd_rate' => '150',
            'costd_burden' => '45000',
            'costd_admin' => '2500',
            'costd_ismeter' => TRUE
        ],
        [
            'cost_id' => 3,
            'costd_name' => 'GAZ',
            'costd_unit' => 'Liter',
            'costd_rate' => '350',
            'costd_burden' => '50000',
            'costd_admin' => '2500',
            'costd_ismeter' => TRUE
        ],
        [
            'cost_id' => 4,
            'costd_name' => 'SERVICE CHARGE',
            'costd_unit' => 'Bulan',
            'costd_rate' => '100',
            'costd_burden' => 0,
            'costd_admin' => 0,
            'costd_ismeter' => FALSE
        ],
        [
            'cost_id' => 5,
            'costd_name' => 'SINKING FUND',
            'costd_unit' => 'M2',
            'costd_rate' => '450',
            'costd_burden' => '50000',
            'costd_admin' => '2500',
            'costd_ismeter' => FALSE
        ],
        [
            'cost_id' => 6,
            'costd_name' => 'ASURANSI',
            'costd_unit' => 'M2',
            'costd_rate' => '655386.196',
            'costd_burden' => 0,
            'costd_admin' => 0,
            'costd_ismeter' => FALSE
        ],
        [
            'cost_id' => 7,
            'costd_name' => 'BIAYA PROMOSI LAIN 1',
            'costd_unit' => 'M2',
            'costd_rate' => '550',
            'costd_burden' => 0,
            'costd_admin' => 0,
            'costd_ismeter' => FALSE
        ],
        [
            'cost_id' => 8,
            'costd_name' => 'BIAYA PROMOSI LAIN 2',
            'costd_unit' => 'M2',
            'costd_rate' => '650',
            'costd_burden' => 0,
            'costd_admin' => 0,
            'costd_ismeter' => FALSE
        ]
        ]);
    }
}
