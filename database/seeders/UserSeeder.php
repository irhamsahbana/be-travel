<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

use App\Models\{
    Category,
    Company,
    User,
    Meta,
    PermissionGroupPermission,
    Person,
};

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = new Company();
        $company->name = 'PT. Testing Travel';
        $company->save();

        // create director permission group
        // PG = Permission Group
        $baseDirectorPG = Category::whereNull('company_id')
            ->where([
                'name' => 'director',
                'group_by' => 'permission_groups',
            ])
            ->first();

        $directorPG = new Category();
        $directorPG->company_id = $company->id;
        $directorPG->name = $baseDirectorPG->name;
        $directorPG->label = $baseDirectorPG->label;
        $directorPG->notes = $baseDirectorPG->notes;
        $directorPG->group_by = $baseDirectorPG->group_by;
        $directorPG->save();

        $directorPG = Category::where([
            'company_id' => $company->id,
            'name' => 'director',
            'group_by' => 'permission_groups',
        ])->first();

        $basePermissions = Category::whereNull('company_id')->where('group_by', 'permissions')->get();

        foreach ($basePermissions as $permission) {
            $category = new Category();
            $category->category_id = $permission->category_id;
            $category->company_id = $company->id;
            $category->name = $permission->name;
            $category->label = $permission->label;
            $category->notes = $permission->notes;
            $category->group_by = $permission->group_by;
            $category->save();

            PermissionGroupPermission::create([
                'permission_group_id' => $directorPG->id,
                'permission_id' => $category->id,
            ]);
        }

        $person = new Person();
        $person->company_id = $company->id;
        $person->category_id = Category::where('group_by', 'people')->where('name', 'director')->first()->id;
        $person->name = 'Testing Director';
        $person->save();

        $user = new User();
        $user->person_id = $person->id;
        $user->company_id = $company->id;
        $user->permission_group_id = $directorPG->id;
        $user->email = 'director@director';
        $user->username = 'director';
        $user->password = bcrypt('director');
        $user->save();
    }
}
