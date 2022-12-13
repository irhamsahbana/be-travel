<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

use App\Models\{
    Category,
    Company,
    User,
    PermissionGroupPermission,
    Person,
};

class DummyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // companies table
        $company = Company::all()->first();

        // company permission groups
        $companyPGs = Category::where('company_id', $company->id)->where('group_by', 'permission_groups')->get();
        $directorPG = $companyPGs->where('name', 'director')->first();
        $branchManagerPG = $companyPGs->where('name', 'branch-manager')->first();
        $agentPG = $companyPGs->where('name', 'agent')->first();

        // people table
        $people = Person::with('category')
            ->where('company_id', $company->id)
            ->get();
        $director = $people->where('category.name', 'director')->first();
        $branchManager = $people->where('category.name', 'branch-manager')->first();
        $agent = $people->where('category.name', 'agent')->first();

        // users table
        $user = new User();
        $user->person_id = $director->id;
        $user->company_id = $company->id;
        $user->permission_group_id = $directorPG->id;
        $user->email = 'director@director';
        $user->username = 'director';
        $user->password = bcrypt('director');
        $user->save();

        $user = new User();
        $user->person_id = $branchManager->id;
        $user->company_id = $company->id;
        $user->permission_group_id = $branchManagerPG->id;
        $user->email = 'branch-manager@branch-manager';
        $user->username = 'branch-manager';
        $user->password = bcrypt('branch-manager');
        $user->save();

        $user = new User();
        $user->person_id = $agent->id;
        $user->company_id = $company->id;
        $user->permission_group_id = $agentPG->id;
        $user->email = 'agent@agent';
        $user->username = 'agent';
        $user->password = bcrypt('agent');
        $user->save();
    }
}
