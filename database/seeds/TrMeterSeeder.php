<?php

use Illuminate\Database\Seeder;

class TrMeterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tr_meter')->insert([
        [
            'meter_start' => '0',
            'meter_end' => '1000',
            'meter_used' => '1000',
            'meter_cost' => '550000',
            'meter_burden' => '55000',
            'meter_admin' => '2500',
            'costd_is' => 2,
            'contr_id' => 7,
            'prdmet_id' => 1,
            'unit_id' => 1
        ],
        [
            'meter_start' => '0',
            'meter_end' => '10',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 7,
            'prdmet_id' => 1,
            'unit_id' => 1
        ],
        [
            'meter_start' => '0',
            'meter_end' => '1000',
            'meter_used' => '1000',
            'meter_cost' => '750000',
            'meter_burden' => '70000',
            'meter_admin' => '2500',
            'costd_is' => 3,
            'contr_id' => 8,
            'prdmet_id' => 1,
            'unit_id' => 2
        ],
        [
            'meter_start' => '0',
            'meter_end' => '10',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 8,
            'prdmet_id' => 1,
            'unit_id' => 2
        ],
        [
            'meter_start' => '0',
            'meter_end' => '1000',
            'meter_used' => '1000',
            'meter_cost' => '350000',
            'meter_burden' => '50000',
            'meter_admin' => '2500',
            'costd_is' => 1,
            'contr_id' => 9,
            'prdmet_id' => 1,
            'unit_id' => 3
        ],
        [
            'meter_start' => '0',
            'meter_end' => '10',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 9,
            'prdmet_id' => 1,
            'unit_id' => 3
        ],
        [
            'meter_start' => '0',
            'meter_end' => '1000',
            'meter_used' => '1000',
            'meter_cost' => '550000',
            'meter_burden' => '55000',
            'meter_admin' => '2500',
            'costd_is' => 2,
            'contr_id' => 10,
            'prdmet_id' => 1,
            'unit_id' => 4
        ],
        [
            'meter_start' => '0',
            'meter_end' => '10',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 10,
            'prdmet_id' => 1,
            'unit_id' => 4
        ],
        [
            'meter_start' => '0',
            'meter_end' => '1000',
            'meter_used' => '1000',
            'meter_cost' => '550000',
            'meter_burden' => '55000',
            'meter_admin' => '2500',
            'costd_is' => 2,
            'contr_id' => 11,
            'prdmet_id' => 1,
            'unit_id' => 5
        ],
        [
            'meter_start' => '0',
            'meter_end' => '10',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 11,
            'prdmet_id' => 1,
            'unit_id' => 5
        ],
        [
            'meter_start' => '0',
            'meter_end' => '1000',
            'meter_used' => '1000',
            'meter_cost' => '750000',
            'meter_burden' => '70000',
            'meter_admin' => '2500',
            'costd_is' => 3,
            'contr_id' => 12,
            'prdmet_id' => 1,
            'unit_id' => 6
        ],
        [
            'meter_start' => '0',
            'meter_end' => '10',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 12,
            'prdmet_id' => 1,
            'unit_id' => 6
        ],
        [
            'meter_start' => '1000',
            'meter_end' => '2000',
            'meter_used' => '1000',
            'meter_cost' => '550000',
            'meter_burden' => '55000',
            'meter_admin' => '2500',
            'costd_is' => 2,
            'contr_id' => 7,
            'prdmet_id' => 2,
            'unit_id' => 1
        ],
        [
            'meter_start' => '10',
            'meter_end' => '20',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 7,
            'prdmet_id' => 2,
            'unit_id' => 1
        ],
        [
            'meter_start' => '1000',
            'meter_end' => '2000',
            'meter_used' => '1000',
            'meter_cost' => '750000',
            'meter_burden' => '70000',
            'meter_admin' => '2500',
            'costd_is' => 3,
            'contr_id' => 8,
            'prdmet_id' => 2,
            'unit_id' => 2
        ],
        [
            'meter_start' => '10',
            'meter_end' => '20',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 8,
            'prdmet_id' => 2,
            'unit_id' => 2
        ],
        [
            'meter_start' => '1000',
            'meter_end' => '2000',
            'meter_used' => '1000',
            'meter_cost' => '350000',
            'meter_burden' => '50000',
            'meter_admin' => '2500',
            'costd_is' => 1,
            'contr_id' => 9,
            'prdmet_id' => 2,
            'unit_id' => 3
        ],
        [
            'meter_start' => '10',
            'meter_end' => '20',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 9,
            'prdmet_id' => 2,
            'unit_id' => 3
        ],
        [
            'meter_start' => '1000',
            'meter_end' => '2000',
            'meter_used' => '1000',
            'meter_cost' => '550000',
            'meter_burden' => '55000',
            'meter_admin' => '2500',
            'costd_is' => 2,
            'contr_id' => 10,
            'prdmet_id' => 2,
            'unit_id' => 4
        ],
        [
            'meter_start' => '10',
            'meter_end' => '20',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 10,
            'prdmet_id' => 2,
            'unit_id' => 4
        ],
        [
            'meter_start' => '1000',
            'meter_end' => '2000',
            'meter_used' => '1000',
            'meter_cost' => '550000',
            'meter_burden' => '55000',
            'meter_admin' => '2500',
            'costd_is' => 2,
            'contr_id' => 11,
            'prdmet_id' => 2,
            'unit_id' => 5
        ],
        [
            'meter_start' => '10',
            'meter_end' => '20',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 11,
            'prdmet_id' => 2,
            'unit_id' => 5
        ],
        [
            'meter_start' => '1000',
            'meter_end' => '2000',
            'meter_used' => '1000',
            'meter_cost' => '750000',
            'meter_burden' => '70000',
            'meter_admin' => '2500',
            'costd_is' => 3,
            'contr_id' => 12,
            'prdmet_id' => 2,
            'unit_id' => 6
        ],
        [
            'meter_start' => '10',
            'meter_end' => '20',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 12,
            'prdmet_id' => 2,
            'unit_id' => 6
        ],
        [
            'meter_start' => '2000',
            'meter_end' => '3000',
            'meter_used' => '1000',
            'meter_cost' => '550000',
            'meter_burden' => '55000',
            'meter_admin' => '2500',
            'costd_is' => 2,
            'contr_id' => 7,
            'prdmet_id' => 3,
            'unit_id' => 1
        ],
        [
            'meter_start' => '20',
            'meter_end' => '30',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 7,
            'prdmet_id' => 3,
            'unit_id' => 1
        ],
        [
            'meter_start' => '2000',
            'meter_end' => '3000',
            'meter_used' => '1000',
            'meter_cost' => '750000',
            'meter_burden' => '70000',
            'meter_admin' => '2500',
            'costd_is' => 3,
            'contr_id' => 8,
            'prdmet_id' => 3,
            'unit_id' => 2
        ],
        [
            'meter_start' => '20',
            'meter_end' => '30',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 8,
            'prdmet_id' => 3,
            'unit_id' => 2
        ],
        [
            'meter_start' => '2000',
            'meter_end' => '3000',
            'meter_used' => '1000',
            'meter_cost' => '350000',
            'meter_burden' => '50000',
            'meter_admin' => '2500',
            'costd_is' => 1,
            'contr_id' => 9,
            'prdmet_id' => 3,
            'unit_id' => 3
        ],
        [
            'meter_start' => '20',
            'meter_end' => '30',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 9,
            'prdmet_id' => 3,
            'unit_id' => 3
        ],
        [
            'meter_start' => '2000',
            'meter_end' => '3000',
            'meter_used' => '1000',
            'meter_cost' => '550000',
            'meter_burden' => '55000',
            'meter_admin' => '2500',
            'costd_is' => 2,
            'contr_id' => 10,
            'prdmet_id' => 3,
            'unit_id' => 4
        ],
        [
            'meter_start' => '20',
            'meter_end' => '30',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 10,
            'prdmet_id' => 3,
            'unit_id' => 4
        ],
        [
            'meter_start' => '2000',
            'meter_end' => '3000',
            'meter_used' => '1000',
            'meter_cost' => '550000',
            'meter_burden' => '55000',
            'meter_admin' => '2500',
            'costd_is' => 2,
            'contr_id' => 11,
            'prdmet_id' => 3,
            'unit_id' => 5
        ],
        [
            'meter_start' => '20',
            'meter_end' => '30',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 11,
            'prdmet_id' => 3,
            'unit_id' => 5
        ],
        [
            'meter_start' => '2000',
            'meter_end' => '3000',
            'meter_used' => '1000',
            'meter_cost' => '750000',
            'meter_burden' => '70000',
            'meter_admin' => '2500',
            'costd_is' => 3,
            'contr_id' => 12,
            'prdmet_id' => 3,
            'unit_id' => 6
        ],
        [
            'meter_start' => '20',
            'meter_end' => '30',
            'meter_used' => '10',
            'meter_cost' => '1500',
            'meter_burden' => '45000',
            'meter_admin' => '2500',
            'costd_is' => 4,
            'contr_id' => 12,
            'prdmet_id' => 3,
            'unit_id' => 6
        ]
        ]);
    }
}
