<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(MsCompanySeeder::class);
        $this->call(MsTenantSeeder::class);
        $this->call(TrContractSeeder::class);
        $this->call(TrContractInvoiceSeeder::class);
        $this->call(MsCostItemSeeder::class);
        $this->call(MsUnitSeeder::class);
        $this->call(MsCostDetailSeeder::class);
        $this->call(MsVirtualAccountSeeder::class);
        $this->call(TrPeriodMeterSeeder::class);
        $this->call(MsFloorSeeder::class);
        $this->call(MsUnitTypeSeeder::class);
        $this->call(MsTenantTypeSeeder::class);
        $this->call(MsInvoiceTypeSeeder::class);
        $this->call(MsMarketingAgentSeeder::class);
        $this->call(TrMeterSeeder::class);
        $this->call(MsSupplierSeeder::class);
        $this->call(MsDepartmentSeeder::class);
        $this->call(MsPaymentTypeSeeder::class);
        $this->call(TrInvoiceSeeder::class);
        $this->call(TrInvoiceDetailSeeder::class);
        $this->call(MsJournalTypeSeeder::class);
        $this->call(MsCurrencySeeder::class);
        $this->call(TrCurrencyRateSeeder::class);
        $this->call(MsCashBankSeeder::class);
        $this->call(TrInvoicePaymhdrSeeder::class);
        $this->call(TrInvoicePaymdtlSeeder::class);
    }
}
