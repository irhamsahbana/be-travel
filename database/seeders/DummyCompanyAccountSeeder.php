<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// add faker
use Faker\Factory as Faker;

class DummyCompanyAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        $companies = \App\Models\Company::all();

        foreach ($companies as $company) {
            $companyAccount = new \App\Models\CompanyAccount();
            $companyAccount->company_id = $company->id;
            $companyAccount->bank_id = \App\Models\Category::where('group_by', 'banks')->first()->id ?? null;
            $companyAccount->account_name = $company->name;
            $companyAccount->account_number = $this->faker->bankAccountNumber;
            $companyAccount->save();
        }
    }
}
