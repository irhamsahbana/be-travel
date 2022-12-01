<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

use App\Libs\RefNoGenerator;
use App\Libs\Response;
use App\Libs\FileSaver;
use App\Libs\Dumper;

use App\Models\Category;
use App\Models\Person;
use App\Models\AgentWorkExperience;

class AgentController extends Controller
{
    use RefNoGenerator, FileSaver, Dumper;

    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $refNo = $this->generateRefNo('people', 4, 'AG/', $this->getPostfix());

        $fields = [
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
            'work_experiences' => $request->work_experiences,
            'notes' => $request->notes,
            // 'file' => $file
        ];

        $rules = [
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'branch_id' => [
                'required',
                'uuid',
               Rule::exists('branches', 'id')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                })
            ],

            'ref_no' => ['required', 'string', 'max:255', 'unique:people'],
            'name' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'place_of_birth' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date_format:Y-m-d'],
            'sex' => ['required', 'in:male,female'],
            'national_id' => ['required','string','max:30', 'unique:people'],
            'address' => ['required', 'string', 'max:255'],
            'city_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('group_by', 'cities');
                })
            ],
            'nationality_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('group_by', 'nationalities');
                })
            ],
            'phone' => ['required', 'string', 'max:15'],
            'wa' => ['required', 'string', 'max:15'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'education_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('group_by', 'educations');
                })
            ],
            'profession' => ['required', 'string', 'max:255'],
            'marital_status_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('group_by', 'marital_statuses');
                })
            ],
            'account_name' => ['required', 'string', 'max:255'],
            'bank_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('group_by', 'banks');
                })
            ],
            'account_number' => ['required', 'string', 'max:25'],
            'emergency_name' => ['required', 'string', 'max:255'],
            'emergency_address' => ['required', 'string', 'max:255'],
            'emergency_home_phone' => ['required', 'string', 'max:15'],
            'emergency_phone' => ['required', 'string', 'max:15'],

            'work_experiences' => ['array', 'min:0'],
            'work_experiences.*.company_name' => ['required', 'string', 'max:255'],
            'work_experiences.*.role' => ['required', 'string', 'max:255'],
            'work_experiences.*.start_date' => ['required', 'date_format:Y-m-d'],
            'work_experiences.*.end_date' => ['nullable', 'date_format:Y-m-d'],

            'notes' => ['nullable', 'string', 'max:255'],
        ];

        $validator = Validator::make($fields, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        DB::beginTransaction();
        try {
            $workExperiences = (array) $fields['work_experiences'];

            unset($fields['work_experiences']);
            unset($fields['file']);
            $person = Person::create($fields);

            foreach ($workExperiences as $key => $value) {
                $workExperiences[$key]['person_id'] = $person->id;
                $workExperiences[$key]['id'] = (string) Str::uuid();
            }

            AgentWorkExperience::insert($workExperiences);
            $person = Person::find($person->id)->load('agentWorkExperiences')->toArray() ?? null;

            DB::commit();
            return (new Response)->json($person, 'success to create agent', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return (new Response)->json(null, $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $person = Person::find($id)->load('agentWorkExperiences', 'category', 'file')->toArray() ?? null;

        if (!$person) return (new Response)->json(null, 'agent not found', 404);
        return (new Response)->json($person, 'success to get agent', 200);
    }

    public function downloadAttachments($id)
    {
        $person = Person::find($id)->load('file')->toArray() ?? null;

        if (!$person) return (new Response)->json(null, 'agent not found', 404);

        $file = $person['file'] ?? null;
        if (!$file) return (new Response)->json(null, 'file not found', 404);

        $path = storage_path('app/' . $file['path']);
        if (!File::exists($path)) return (new Response)->json(null, 'file not found', 404);

        return response()->download($path, $file['name']);
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
