<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Libs\RefNoGenerator;

use App\Models\Category;
use App\Models\Company;
use App\Models\Person;

class DummyPersonSeeder extends Seeder
{
    use RefNoGenerator;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = Company::with('branches')->first();
        $personCategories = Category::where('group_by', 'people')->get();

        $person = new Person();
        $person->id = '5f6108f0-36f1-4381-b87d-e699e36e9c1b';
        $person->company_id = $company->id;
        $person->category_id = $personCategories->where('name', 'director')->first()->id;
        $person->ref_no = $this->generateRefNo('people', 4, 'DR/', $this->getPostfix());
        $person->name = 'Testing Director';
        $person->save();

        $person = new Person();
        $person->id = '15e69304-6550-49ed-8f31-911ae8c0f15c';
        $person->company_id = $company->id;
        $person->branch_id = $company->branches->first()->id;
        $person->category_id = $personCategories->where('name', 'branch-manager')->first()->id;
        $person->ref_no = $this->generateRefNo('people', 4, 'BM/', $this->getPostfix());
        $person->name = 'Testing Branch Manager';
        $person->save();

        $person = new Person();
        $person->id = '96881465-455e-4322-8228-039564b74609';
        $person->company_id = $company->id;
        $person->branch_id = $company->branches->first()->id;
        $person->category_id = $personCategories->where('name', 'agent')->first()->id;
        $person->ref_no = $this->generateRefNo('people', 4, 'AG/', $this->getPostfix());
        $person->name = 'Testing Agent';
        $person->save();
    }
}
