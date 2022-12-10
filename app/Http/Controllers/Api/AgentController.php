<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Rules\AgentRules;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

use App\Libs\RefNoGenerator;
use App\Libs\Response;

use App\Models\Category;
use App\Models\Person;
use App\Models\AgentWorkExperience;

class AgentController extends Controller
{
    use RefNoGenerator;

    public function index(Request $request)
    {
        $agents = Person::where('company_id', auth()->user()->company_id)
            ->whereHas('category', function ($query) {
                $query->where('name', 'agent')
                    ->where('group_by', 'people')
                    ->whereNull('company_id');
            })
            ->get()->toArray();


        return (new Response)->json($agents, 'Agents retrieved successfully');
    }

    public function store(Request $request)
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

        $fields = array_merge($personData, $workExperiencesData);

        $personRules = (new AgentRules)->store($request);
        $workExperiencesRules = (new AgentRules)->workExperiences($request);

        $rules = array_merge($personRules, $workExperiencesRules);

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
        //
    }

    public function destroy($id)
    {
        //
    }
}
