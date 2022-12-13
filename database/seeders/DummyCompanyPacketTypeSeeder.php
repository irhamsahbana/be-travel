<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Category;
use App\Models\Company;

class DummyCompanyPacketTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = Company::all()->first();

        $basePacketTypes = Category::whereNull('company_id')->where('group_by', 'packet_types')->get();

        foreach ($basePacketTypes as $packetType) {
            $companyPacketType = new Category();
            $companyPacketType->category_id = $packetType->category_id;
            $companyPacketType->company_id = $company->id;
            $companyPacketType->name = $packetType->name;
            $companyPacketType->label = $packetType->label;
            $companyPacketType->notes = $packetType->notes;
            $companyPacketType->group_by = $packetType->group_by;
            $companyPacketType->save();
        }
    }
}
