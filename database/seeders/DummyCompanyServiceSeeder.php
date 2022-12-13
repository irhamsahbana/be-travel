<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Category;
use App\Models\Company;
use App\Models\Service;

class DummyCompanyServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = Company::all()->first();

        $category = new Category();
        $packetTypes = $category->packetTypes($company->id)->get();

        $uuids = [
            '97f2dc11-0f70-4dc8-aa58-d0a6dd01ad85',
            '97f2dc11-1099-48e5-8680-7d8826326488',
            '97f2dc11-1156-4b25-86cc-df30c48355e0'
        ];

        foreach ($packetTypes as $key => $packetType) {
            $dummyService = new Service();
            $dummyService->id = $uuids[$key] ?? null;
            $dummyService->company_id = $company->id;
            $dummyService->packet_type_id = $packetType->id;
            $dummyService->name = 'Dummy Service ' . $key;
            $dummyService->price = 1_000_000 + $key;
            $dummyService->departure_date = now()->addDays($key + 7);
            $dummyService->save();
        }
    }
}
