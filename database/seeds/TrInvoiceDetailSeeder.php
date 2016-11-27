<?php

use Illuminate\Database\Seeder;

class TrInvoiceDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tr_invoice_detail')->insert([
        [
            'invdt_amount' => '607500',
            'invdt_note' => 'ELECTRICITY Per: 01/01/2016 - 31/01/2016<br>-Start: 0 End: 1000 Consumption: 1000 Kwh',
            'costd_id' => '2',
            'inv_id' => '7',
            'meter_id' => '1'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/01/2016 - 31/01/2016<br>-Start: 0 End: 10 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '7',
            'meter_id' => '2'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '7',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '822500',
            'invdt_note' => 'ELECTRICITY Per: 01/01/2016 - 31/01/2016<br>-Start: 0 End: 1000 Consumption: 1000 Kwh',
            'costd_id' => '3',
            'inv_id' => '8',
            'meter_id' => '3'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/01/2016 - 31/01/2016<br>-Start: 0 End: 10 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '8',
            'meter_id' => '4'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '8',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '402500',
            'invdt_note' => 'ELECTRICITY Per: 01/01/2016 - 31/01/2016<br>-Start: 0 End: 1000 Consumption: 1000 Kwh',
            'costd_id' => '1',
            'inv_id' => '9',
            'meter_id' => '5'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/01/2016 - 31/01/2016<br>-Start: 0 End: 10 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '9',
            'meter_id' => '6'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '9',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '607500',
            'invdt_note' => 'ELECTRICITY Per: 01/01/2016 - 31/01/2016<br>-Start: 0 End: 1000 Consumption: 1000 Kwh',
            'costd_id' => '2',
            'inv_id' => '10',
            'meter_id' => '7'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/01/2016 - 31/01/2016<br>-Start: 0 End: 10 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '10',
            'meter_id' => '8'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '10',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '607500',
            'invdt_note' => 'ELECTRICITY Per: 01/01/2016 - 31/01/2016<br>-Start: 0 End: 1000 Consumption: 1000 Kwh',
            'costd_id' => '2',
            'inv_id' => '11',
            'meter_id' => '9'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/01/2016 - 31/01/2016<br>-Start: 0 End: 10 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '11',
            'meter_id' => '10'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '11',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '607500',
            'invdt_note' => 'ELECTRICITY Per: 01/01/2016 - 31/01/2016<br>-Start: 0 End: 1000 Consumption: 1000 Kwh',
            'costd_id' => '3',
            'inv_id' => '12',
            'meter_id' => '11'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/01/2016 - 31/01/2016<br>-Start: 0 End: 10 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '12',
            'meter_id' => '12'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '12',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/01/2016 - 31/01/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '1',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '1',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/01/2016 - 31/01/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '2',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '2',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/01/2016 - 31/01/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '3',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '3',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/01/2016 - 31/01/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '4',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '4',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/01/2016 - 31/01/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '5',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '5',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/01/2016 - 31/01/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '6',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '6',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '607500',
            'invdt_note' => 'ELECTRICITY Per: 01/02/2016 - 29/02/2016<br>-Start: 1000 End: 2000 Consumption: 1000 Kwh',
            'costd_id' => '2',
            'inv_id' => '19',
            'meter_id' => '13'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/02/2016 - 29/02/2016<br>-Start: 10 End: 20 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '19',
            'meter_id' => '14'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '19',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '822500',
            'invdt_note' => 'ELECTRICITY Per: 01/02/2016 - 29/02/2016<br>-Start: 1000 End: 2000 Consumption: 1000 Kwh',
            'costd_id' => '3',
            'inv_id' => '20',
            'meter_id' => '15'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/02/2016 - 29/02/2016<br>-Start: 10 End: 20 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '20',
            'meter_id' => '16'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '20',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '402500',
            'invdt_note' => 'ELECTRICITY Per: 01/02/2016 - 29/02/2016<br>-Start: 1000 End: 2000 Consumption: 1000 Kwh',
            'costd_id' => '1',
            'inv_id' => '21',
            'meter_id' => '17'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/02/2016 - 29/02/2016<br>-Start: 10 End: 20 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '21',
            'meter_id' => '18'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '21',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '607500',
            'invdt_note' => 'ELECTRICITY Per: 01/02/2016 - 29/02/2016<br>-Start: 1000 End: 2000 Consumption: 1000 Kwh',
            'costd_id' => '2',
            'inv_id' => '22',
            'meter_id' => '19'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/02/2016 - 29/02/2016<br>-Start: 10 End: 20 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '22',
            'meter_id' => '20'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '22',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '607500',
            'invdt_note' => 'ELECTRICITY Per: 01/02/2016 - 29/02/2016<br>-Start: 1000 End: 2000 Consumption: 1000 Kwh',
            'costd_id' => '2',
            'inv_id' => '23',
            'meter_id' => '21'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/02/2016 - 29/02/2016<br>-Start: 10 End: 20 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '23',
            'meter_id' => '22'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '23',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '607500',
            'invdt_note' => 'ELECTRICITY Per: 01/02/2016 - 29/02/2016<br>-Start: 1000 End: 2000 Consumption: 1000 Kwh',
            'costd_id' => '3',
            'inv_id' => '24',
            'meter_id' => '23'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/02/2016 - 29/02/2016<br>-Start: 10 End: 20 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '24',
            'meter_id' => '24'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '24',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/02/2016 - 29/02/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '13',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '13',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/02/2016 - 29/02/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '14',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '14',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/02/2016 - 29/02/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '15',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '15',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/02/2016 - 29/02/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '16',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '16',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/02/2016 - 29/02/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '17',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '17',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/02/2016 - 29/02/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '18',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '18',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '607500',
            'invdt_note' => 'ELECTRICITY Per: 01/03/2016 - 31/03/2016<br>-Start: 2000 End: 3000 Consumption: 1000 Kwh',
            'costd_id' => '2',
            'inv_id' => '31',
            'meter_id' => '25'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/03/2016 - 31/03/2016<br>-Start: 20 End: 30 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '31',
            'meter_id' => '26'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '31',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '822500',
            'invdt_note' => 'ELECTRICITY Per: 01/03/2016 - 31/03/2016<br>-Start: 2000 End: 3000 Consumption: 1000 Kwh',
            'costd_id' => '3',
            'inv_id' => '32',
            'meter_id' => '27'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/03/2016 - 31/03/2016<br>-Start: 20 End: 30 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '32',
            'meter_id' => '28'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '32',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '402500',
            'invdt_note' => 'ELECTRICITY Per: 01/03/2016 - 31/03/2016<br>-Start: 2000 End: 3000 Consumption: 1000 Kwh',
            'costd_id' => '1',
            'inv_id' => '33',
            'meter_id' => '29'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/03/2016 - 31/03/2016<br>-Start: 20 End: 30 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '33',
            'meter_id' => '30'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '33',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '607500',
            'invdt_note' => 'ELECTRICITY Per: 01/03/2016 - 31/03/2016<br>-Start: 2000 End: 3000 Consumption: 1000 Kwh',
            'costd_id' => '2',
            'inv_id' => '34',
            'meter_id' => '31'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/03/2016 - 31/03/2016<br>-Start: 20 End: 30 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '34',
            'meter_id' => '32'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '34',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '607500',
            'invdt_note' => 'ELECTRICITY Per: 01/03/2016 - 31/03/2016<br>-Start: 2000 End: 3000 Consumption: 1000 Kwh',
            'costd_id' => '2',
            'inv_id' => '35',
            'meter_id' => '33'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/03/2016 - 31/03/2016<br>-Start: 20 End: 30 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '35',
            'meter_id' => '34'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '35',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '607500',
            'invdt_note' => 'ELECTRICITY Per: 01/03/2016 - 31/03/2016<br>-Start: 2000 End: 3000 Consumption: 1000 Kwh',
            'costd_id' => '3',
            'inv_id' => '36',
            'meter_id' => '35'
        ],
        [
            'invdt_amount' => '49000',
            'invdt_note' => 'WATER Per: 01/03/2016 - 31/03/2016<br>-Start: 20 End: 30 Consumption: 10 M3',
            'costd_id' => '4',
            'inv_id' => '36',
            'meter_id' => '36'
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '36',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/03/2016 - 31/03/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '25',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '25',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/03/2016 - 31/03/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '26',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '26',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/03/2016 - 31/03/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '27',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '27',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/03/2016 - 31/03/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '28',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '28',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/03/2016 - 31/03/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '29',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '29',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '962500',
            'invdt_note' => 'SERVICE CHARGE Per: 01/03/2016 - 31/03/2016<br>Rp. 11.000 X 87,5 Sqrm X 1 Bulan',
            'costd_id' => '6',
            'inv_id' => '30',
            'meter_id' => NULL
        ],
        [
            'invdt_amount' => '3000',
            'invdt_note' => 'STAMPDUTY',
            'costd_id' => '0',
            'inv_id' => '30',
            'meter_id' => NULL
        ]
        ]);
    }
}
