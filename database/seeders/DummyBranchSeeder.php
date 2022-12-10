<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Libs\RefNoGenerator;

class DummyBranchSeeder extends Seeder
{
    use RefNoGenerator;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = Company::all();
        $refNo = $this->generateRefNo('branches', 4, 'BR/', $this->getPostfix());
        foreach ($companies as $company) {
            $company->branches()->create([
                'id' => '97f2d9b0-005a-443c-9183-93e9ca910ceb',
                'name' => 'Dummy Branch',
                'ref_no' => $refNo,
            ]);
        }
    }
}
