<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

use App\Models\{
    Category,
    Company,
    User,
    Meta,
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
        $permissions = Category::where('group_by', 'permissions')->get();

        $company = new Company();
        $company->name = 'PT. Testing Travel';
        $company->save();

        $person = new Person();
        $person->company_id = $company->id;
        $person->category_id = Category::where('group_by', 'people')->where('name', 'director')->first()->id;
        $person->name = 'Testing Director';
        $person->save();

        $user = new User();
        $user->person_id = $person->id;
        $user->email = 'director@director';
        $user->username = 'director';
        $user->password = bcrypt('director');
        $user->save();

        $adminPermissionGroup = Category::where([
            'name' => 'director',
            'group_by' => 'permission_groups',
        ])->first();

        // generate full access for administrator groups
        foreach ($permissions as $permission) {
            $meta = new Meta();
            $meta->fk_id = $adminPermissionGroup->id;
            $meta->table_name = $adminPermissionGroup->getTable();
            $meta->key = 'permission_id';
            $meta->value = $permission->id;
            $meta->save();
        }

        // create admin user his permission group
        Meta::create([
            'fk_id' => $user->id,
            'table_name' => $user->getTable(),
            'key' => 'permission_group_id',
            'value' => $adminPermissionGroup->id,
        ]);
    }
}
