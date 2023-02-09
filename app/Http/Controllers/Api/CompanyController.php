<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use App\Libs\Response;
use App\Libs\RefNoGenerator;
use App\Jobs\SendCompanyRegisteredNotificationJob;

use App\Models\Category;
use App\Models\Company;
use App\Models\User;

class CompanyController extends Controller
{
    use RefNoGenerator;

    public function index()
    {
        //
    }

    public function publicIndex()
    {
        $data = Company::with('branches.agents', 'services.packetType', 'services.file')->get()->toArray();

        return (new Response)->json($data, 'Companies retrieved successfully.');
    }

    public function store(Request $request)
    {
        //
    }

    public function register(Request $request)
    {
        $fields = [
            'name' => $request->name,
            'branches' => $request->branches,
            'accounts' => $request->accounts,
            'person' => $request->person
        ];

        $rules = [
            'name' => ['required', 'string', 'max:255', 'unique:companies,name'],
            'branches' => ['required', 'array', 'min:1'],
            'branches.*.name' => ['required', 'string', 'max:255'],
            'accounts' => ['required', 'array', 'min:1'],
            'accounts.*.bank_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('group_by', 'banks');
                })
            ],
            'accounts.*.account_name' => ['required', 'string', 'max:255'],
            'accounts.*.account_number' => ['required', 'max:255'],
            'person.name' => ['required', 'string', 'max:255'],
            'person.username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'person.password' => ['required', 'string', 'min:8'],
            'person.phone' => [
                'required', 'string', 'max:15', 'regex:/^62[0-9]{6,15}$/', // the regex is for Indonesian phone number (62 is the country code, 6-11 is the phone number)
            ],
            'person.wa' => [
                'required', 'string', 'max:15', 'regex:/^62[0-9]{6,15}$/',
                'unique:people,wa'
            ],
            'person.email' => [
                'required', 'string', 'email:rfc,dns', 'max:255',
                'unique:people,email'
            ],
        ];

        $validator = Validator::make($fields, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        DB::beginTransaction();
        try {
            $company = Company::create([
                'ref_no' => $this->generateRefNo('companies', 4, 'CO/', $this->getPostfix()),
                'name' => $request->name,
            ]);

            foreach ($request->branches as $branch) {
                $company->branches()->create([
                    'ref_no' => $this->generateRefNo('branches', 4, 'BR/', $this->getPostfix()),
                    'name' => $branch['name'],
                ]);
            }

            foreach ($request->accounts as $account) {
                $company->accounts()->create([
                    'bank_id' => $account['bank_id'],
                    'account_name' => $account['account_name'],
                    'account_number' => $account['account_number'],
                ]);
            }

            $person = $company->people()->create([
                'category_id' => Category::where('group_by', 'people')->where('name', 'director')->first()->id,
                'ref_no' => $this->generateRefNo('people', 4, 'DR/', $this->getPostfix()),
                'name' => $request->person['name'],
                'phone' => $request->person['phone'],
                'wa' => $request->person['wa'],
                'email' => $request->person['email'],
            ]);

            $user = $person->user()->create([
                'company_id' => $company->id,
                'branch_id' => null,
                'email' => $request->person['email'],
                'username' => $request->person['username'],
                'password' => bcrypt($request->person['password']),
            ]);

            // reload the company with its relations
            $company = Company::with('branches', 'accounts.bank', 'people.user')->find($company->id);
            $this->initializeCompanyUtils($company);

            $job = new SendCompanyRegisteredNotificationJob($company);
            dispatch($job);
            DB::commit();

            return (new Response)->json($company->toArray(), 'Company registered successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return (new Response)->json(null, $e->getMessage(), 500, get_class($e), $e->getFile(), $e->getLine(), $e->getTrace());
        }
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    private function initializeCompanyUtils($company)
    {
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
            'category-packet-types-read',
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
            'category-packet-types-read',
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

        // packet types category
        $packetTypes = Category::where('group_by', 'packet_types')->whereNull('company_id')->get();
        foreach ($packetTypes as $packetType) {
            $pt = new Category;
            $pt->category_id = $packetType->category_id;
            $pt->company_id = $company->id;
            $pt->name = $packetType->name;
            $pt->group_by = $packetType->group_by;
            $pt->label = $packetType->label;
            $pt->notes = $packetType->notes;
            $pt->save();
        }
    }
}
