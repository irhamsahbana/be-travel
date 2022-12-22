<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ApiToken;
use App\Models\Company;

class DummyCompanyApiTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = Company::all()->first();

        $api = new ApiToken();
        $api->company_id = $company->id;
        $api->token = config('services.ruangwa.token');
        $api->name = 'ruang_wa';
        $api->save();
    }
}
