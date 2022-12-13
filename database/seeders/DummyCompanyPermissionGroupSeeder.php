<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Category;
use App\Models\Company;
use Illuminate\Support\Str;

class DummyCompanyPermissionGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = Company::all()->first();

        // base permission groups
        $permissionGroups = Category::whereNull('company_id')->where('group_by', 'permission_groups')->get();
        $selectedPermissionGroups = $permissionGroups->whereIn('name', ['director', 'branch-manager', 'agent']);

        foreach ($selectedPermissionGroups as $permissionGroup) {
            $companyPermissionGroup = new Category();
            $companyPermissionGroup->company_id = $company->id;
            $companyPermissionGroup->name = $permissionGroup->name;
            $companyPermissionGroup->label = $permissionGroup->label;
            $companyPermissionGroup->notes = $permissionGroup->notes;
            $companyPermissionGroup->group_by = $permissionGroup->group_by;
            $companyPermissionGroup->save();
        }

        // create company permission groups
        $companyPermissionGroups = Category::where('company_id', $company->id)->where('group_by', 'permission_groups')->get();
        $directorPG = $companyPermissionGroups->where('name', 'director')->first();
        $branchManagerPG = $companyPermissionGroups->where('name', 'branch-manager')->first();
        $agentPG = $companyPermissionGroups->where('name', 'agent')->first();

        // set permissions for company
        // base permissions
        $basePermissions = Category::whereNull('company_id')
            ->where('group_by', 'permissions')
            ->whereNotIn('name', [
                'category-nationalities-create', 'category-nationalities-update', 'category-nationalities-delete',
                'category-cities-create', 'category-cities-update', 'category-cities-delete',
                'category-provinces-create', 'category-provinces-update', 'category-provinces-delete',
                'category-educations-create', 'category-educations-update', 'category-educations-delete',
                'category-marital-statuses-create', 'category-marital-statuses-update', 'category-marital-statuses-delete',
            ])
            ->get();

        // create company permissions
        foreach ($basePermissions as $permission) {
            $companyPermission = new Category();
            $companyPermission->category_id = $permission->category_id;
            $companyPermission->company_id = $company->id;
            $companyPermission->name = $permission->name;
            $companyPermission->label = $permission->label;
            $companyPermission->notes = $permission->notes;
            $companyPermission->group_by = $permission->group_by;
            $companyPermission->save();
        }

        // add permissions to permission group
        $companyPermissions = Category::where('company_id', $company->id)->where('group_by', 'permissions')->get();
        // director
        $directorPermissions = $companyPermissions;
        // branch manager
        $branchManagerPermissions = $companyPermissions->whereIn('name', [
            'congregations-create', 'congregations-create', 'congregations-update', 'congregations-delete',
            'category-nationalities-read',
            'category-cities-read',
            'category-provinces-read',
            'category-educations-read',
            'category-marital-statuses-read',
            'category-payment-methods-read',
            'broadcasts-read', 'broadcasts-create', 'broadcasts-update', 'broadcasts-delete',
            'invoices-read', 'invoices-create', 'invoices-update', 'invoices-delete',
        ]);
        // agent
        $agentPermissions = $companyPermissions->whereIn('name', [
            'congregations-create', 'congregations-create', 'congregations-update', 'congregations-delete',
            'category-nationalities-read',
            'category-cities-read',
            'category-provinces-read',
            'category-educations-read',
            'category-marital-statuses-read',
            'category-payment-methods-read',
            'broadcasts-read',
            'invoices-read',
        ]);

        $permissionGroupPermissions = [
            $directorPG->id => $directorPermissions->pluck('id')->toArray(),
            $branchManagerPG->id => $branchManagerPermissions->pluck('id')->toArray(),
            $agentPG->id => $agentPermissions->pluck('id')->toArray(),
        ];

        // dd($permissionGroupPermissions);

        foreach ($permissionGroupPermissions as $permissionGroupId => $permissionIds) {
            $permissionGroup = Category::find($permissionGroupId);
            $newPermissionIds = [];
            foreach ($permissionIds as $permissionId) {
                $newPermissionIds[$permissionId] = [
                    'company_id' => $company->id,
                    'id' => Str::uuid()->toString(),
                ];
            }
            $permissionGroup->permissions()->sync($newPermissionIds);
        }
    }
}
