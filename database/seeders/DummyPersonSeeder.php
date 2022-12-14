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
        $company = Company::all()->first();
        $personCategories = Category::where('group_by', 'people')->get();

        $person = new Person();
        $person->company_id = $company->id;
        $person->category_id = $personCategories->where('name', 'director')->first()->id;
        $person->ref_no = $this->generateRefNo('people', 4, 'DR/', $this->getPostfix());
        $person->name = 'Testing Director';
        $person->save();

        $person = new Person();
        $person->company_id = $company->id;
        $person->category_id = $personCategories->where('name', 'branch-manager')->first()->id;
        $person->ref_no = $this->generateRefNo('people', 4, 'BM/', $this->getPostfix());
        $person->name = 'Testing Branch Manager';
        $person->save();

        $person = new Person();
        $person->company_id = $company->id;
        $person->category_id = $personCategories->where('name', 'agent')->first()->id;
        $person->ref_no = $this->generateRefNo('people', 4, 'AG/', $this->getPostfix());
        $person->name = 'Testing Agent';
        $person->save();
    }
}
