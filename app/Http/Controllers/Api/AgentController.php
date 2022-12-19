<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Rules\AgentRules;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

use App\Libs\RefNoGenerator;
use App\Libs\Response;

use App\Models\AgentWorkExperience;
use App\Models\Category;
use App\Models\Person;
use App\Models\User;

class AgentController extends Controller
{
    use RefNoGenerator;

    public function index(Request $request)
    {
        $user = $this->getUser();
        $paginate = isset($request->paginate) ? (bool) $request->paginate : true;

        $data = Person::with([
            'category' => fn ($query) => $query->select('id', 'label'),
            'branch' => fn ($query) => $query->select('id', 'name'),
        ])
            ->select(['id', 'branch_id', 'category_id', 'ref_no', 'name', 'wa', 'email'])
            ->where('company_id', $user->person->company_id)
            ->whereHas('category', function ($query) {
                $query->where('name', 'agent')
                    ->where('group_by', 'people')
                    ->whereNull('company_id');
            });

        if ($user->person->category->name == 'director') {
            // filters
            if (!empty($request->branch_id)) $data = $data->where('branch_id', $request->branch_id);
        } else if ($user->person->category->name == 'branch-manager') {
            $data = $data->where('branch_id', $user->person->branch_id);
        } else {
            return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);
        }

        if ($paginate) {
            $data = $data->paginate((int) $request->per_page ?? 15)->toArray();

            $pagination = $data;
            unset($pagination['data']);

            $data = $data['data'];
            $data['pagination'] = $pagination;
        } else {
            $data = $data->get()->toArray();
        }

        return (new Response)->json($data, 'Agents retrieved successfully');
    }

    public function store(Request $request)
    {
        //
    }

    public function register(Request $request)
    {
        $refNo = $this->generateRefNo('people', 4, 'AG/', $this->getPostfix());

        $personData = [
            'category_id' => Category::where('name', 'agent')->where('group_by', 'people')->first()->id ?? null,
            'company_id' => $request->company_id,
            'branch_id' => $request->branch_id,
            'ref_no' => $refNo,
            'name' => $request->name,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'place_of_birth' => $request->place_of_birth,
            'date_of_birth' => $request->date_of_birth,
            'sex' => $request->sex,
            'national_id' => $request->national_id,
            'address' => $request->address,
            'city_id' => $request->city_id,
            'nationality_id' => $request->nationality_id,
            'phone' => $request->phone,
            'wa' => $request->wa,
            'email' => $request->email,
            'education_id' => $request->education_id,
            'profession' => $request->profession,
            'marital_status_id' => $request->marital_status_id,
            'account_name' => $request->account_name,
            'bank_id' => $request->bank_id,
            'account_number' => $request->account_number,
            'emergency_name' => $request->emergency_name,
            'emergency_address' => $request->emergency_address,
            'emergency_home_phone' => $request->emergency_home_phone,
            'emergency_phone' => $request->emergency_phone,
            'notes' => $request->notes,
        ];

        $workExperiencesData = [
            'work_experiences' => $request->work_experiences,
        ];

        $userData = [
            'username' => $request->username,
            'permission_group_id' => Category::where('name', 'agent')
                ->where('group_by', 'permission_groups')
                ->where('company_id', $request->company_id)->first()->id ?? null,
        ];

        $fields = array_merge($personData, $workExperiencesData, $userData);

        $personRules = (new AgentRules)->store($request);
        $workExperiencesRules = (new AgentRules)->workExperiences($request);
        $userRules = (new AgentRules)->user();

        $rules = array_merge($personRules, $workExperiencesRules, $userRules);

        $validator = Validator::make($fields, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        DB::beginTransaction();
        try {
            $person = Person::create($personData);
            $workExperiences = (array) $workExperiencesData['work_experiences'];

            foreach ($workExperiences as $key => $value) {
                $workExperiences[$key]['person_id'] = $person->id;
                $workExperiences[$key]['id'] = (string) Str::uuid();
            }

            AgentWorkExperience::insert($workExperiences);
            User::create([
                'person_id' => $person->id,
                'company_id' => $request->company_id,
                'branch_id' => $request->branch_id,
                'email' => $request->email,
                'username' => $request->username,
                'password' => bcrypt(Str::random(8)),
                'permission_group_id' => Category::where('name', 'agent')
                    ->where('group_by', 'permission_groups')
                    ->where('company_id', $request->company_id)->first()->id ?? null,
            ]);
            $person = Person::find($person->id)->load('agentWorkExperiences')->toArray();

            DB::commit();
            return (new Response)->json($person, 'success to create agent', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return (new Response)->json(null, $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        $data = Person::with([
            'category',
            'agentWorkExperiences',
            'registeredCongregations' => fn ($q) => $q->select('id', 'agent_id', 'congregation_id', 'ref_no', 'name'),
            'file'
        ])->where('id', $id)->where('company_id', $user?->company_id);

        if ($userCategory == 'director') $data = $data->first()?->toArray();
        else if ($userCategory == 'branch-manager') $data = $data->where('branch_id', $user->branch_id)->first()?->toArray();
        else return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);

        if (!$data) return (new Response)->json(null, 'agent not found', 404);
        return (new Response)->json($data, 'success to get agent', 200);
    }

    public function downloadAttachments($id)
    {
        $person = Person::where('id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->with(['file'])
            ->first();

        if (!$person) return (new Response)->json(null, 'agent not found', 404);

        $file = $person['file'] ?? null;
        if (!$file) return (new Response)->json(null, 'file not found', 404);

        $path = storage_path('app/' . $file['path']);
        if (!File::exists($path)) return (new Response)->json(null, 'file not found', 404);

        return response()->download($path, $file['name']);
    }

    public function update(Request $request, $id)
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        $person = Person::where('id', $id)->where('company_id', $user->company_id);
        if ($userCategory == 'director') $person = $person->first();
        else if ($userCategory == 'branch-manager') $person = $person->where('branch_id', $user->branch_id)->first();
        else return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);

        if (!$person) return (new Response)->json(null, 'agent not found', 404);

        $branchId = '';
        if ($userCategory == 'director') $branchId = $request->branch_id ? $request->branch_id : $person->branch_id;
        else if ($userCategory == 'branch-manager') $branchId = $person->branch_id;


        $request->merge([
            'id' => $id,
            'company_id' => $user->company_id,
        ]);
        $personData = [
            'id' => $request->id,
            'company_id' => $person->company_id,
            'branch_id' => $branchId,
            'category_id' => $person->category_id,
            'ref_no' => $person->ref_no,
            'name' => $request->name,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'place_of_birth' => $request->place_of_birth,
            'date_of_birth' => $request->date_of_birth,
            'sex' => $request->sex,
            'national_id' => $request->national_id,
            'address' => $request->address,
            'city_id' => $request->city_id,
            'nationality_id' => $request->nationality_id,
            'phone' => $request->phone,
            'wa' => $request->wa,
            'email' => $request->email,
            'education_id' => $request->education_id,
            'profession' => $request->profession,
            'marital_status_id' => $request->marital_status_id,
            'account_name' => $request->account_name,
            'bank_id' => $request->bank_id,
            'account_number' => $request->account_number,
            'emergency_name' => $request->emergency_name,
            'emergency_address' => $request->emergency_address,
            'emergency_home_phone' => $request->emergency_home_phone,
            'emergency_phone' => $request->emergency_phone,
            'notes' => $request->notes,
        ];

        $workExperiencesData = [
            'work_experiences' => $request->work_experiences,
        ];

        $personRules = (new AgentRules)->update($request);
        $workExperiencesRules = (new AgentRules)->workExperiences($request);

        $fields = array_merge($personData, $workExperiencesData);
        $rules = array_merge($personRules, $workExperiencesRules);

        $validator = Validator::make($fields, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        DB::beginTransaction();
        try {
            // person
            $person = Person::find($request->id);
            unset($personData['id']);
            $person->update($personData);

            // work experiences
            $workExperiences = collect($request->work_experiences);
            $oldWorkExperiences = $workExperiences->filter(fn ($workExperience) => isset($workExperience['id']));
            $newWorkExperiences = $workExperiences->filter(fn ($workExperience) => !isset($workExperience['id']));

            $person->agentWorkExperiences()->whereNotIn('id', $oldWorkExperiences->pluck('id'))->delete();
            $person->agentWorkExperiences()->createMany($newWorkExperiences->toArray());

            $person = Person::find($request->id)?->load([
                'category',
                'agentWorkExperiences',
                'registeredCongregations' => fn ($q) => $q->select('id', 'agent_id', 'congregation_id', 'ref_no', 'name'),
                'file'
            ])->toArray();

            DB::commit();
            return (new Response)->json($person, 'success to update agent', 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        //
    }
}
