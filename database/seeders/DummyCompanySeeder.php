<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Company;

class DummyCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = new Company();
        $company->id = '97f2d9af-6c15-4757-bb35-2562175708b7';
        $company->name = 'PT. Testing Travel';
        $company->save();
    }
}
