<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Category;
use App\Models\Company;
use App\Models\Person;

class DummyPersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = Company::all()->first();

        $person = new Person();
        $person->company_id = $company->id;
        $person->category_id = Category::where('group_by', 'people')->where('name', 'director')->first()->id;
        $person->name = 'Testing Director';
        $person->save();

        $person = new Person();
        $person->company_id = $company->id;
        $person->category_id = Category::where('group_by', 'people')->where('name', 'branch-manager')->first()->id;
        $person->name = 'Testing Branch Manager';
        $person->save();

        $person = new Person();
        $person->company_id = $company->id;
        $person->category_id = Category::where('group_by', 'people')->where('name', 'agent')->first()->id;
        $person->name = 'Testing Agent';
        $person->save();
    }
}
