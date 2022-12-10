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
        // companies table
        $company = new Company();
        $company->id = '97f2d9af-6c15-4757-bb35-2562175708b7';
        $company->name = 'PT. Testing Travel';
        $company->save();

        // categories table
        // PG = Permission Group

        // create director permission group
        $baseDirectorPG = Category::where([
            'name' => 'director',
            'group_by' => 'permission_groups',
        ])
        ->whereNull('company_id')
        ->first();

        $directorPG = new Category();
        $directorPG->company_id = $company->id;
        $directorPG->name = $baseDirectorPG->name;
        $directorPG->label = $baseDirectorPG->label;
        $directorPG->notes = $baseDirectorPG->notes;
        $directorPG->group_by = $baseDirectorPG->group_by;
        $directorPG->save();

        // $directorPG = Category::where([
        //     'company_id' => $company->id,
        //     'name' => 'director',
        //     'group_by' => 'permission_groups',
        // ])->first();

        $basePermissions = Category::whereNull('company_id')->where('group_by', 'permissions')->get();

        foreach ($basePermissions as $permission) {
            $companyPermission = new Category();
            $companyPermission->category_id = $permission->category_id;
            $companyPermission->company_id = $company->id;
            $companyPermission->name = $permission->name;
            $companyPermission->label = $permission->label;
            $companyPermission->notes = $permission->notes;
            $companyPermission->group_by = $permission->group_by;
            $companyPermission->save();

            PermissionGroupPermission::create([
                'permission_group_id' => $directorPG->id,
                'permission_id' => $companyPermission->id,
            ]);
        }

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

        // people table
        $person = new Person();
        $person->company_id = $company->id;
        $person->category_id = Category::where('group_by', 'people')->where('name', 'director')->first()->id;
        $person->name = 'Testing Director';
        $person->save();

        // users table
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
