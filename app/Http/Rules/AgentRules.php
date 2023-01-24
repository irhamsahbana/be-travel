<?php

namespace App\Http\Rules;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\Category;

class AgentRules extends PeopleRules
{
    public function workExperiences(): array
    {
        return [
            'work_experiences' => ['array', 'min:0'],
            'work_experiences.*.id' => [
                'nullable',
                'uuid',
            ],
            'work_experiences.*.company_name' => ['required', 'string', 'max:255'],
            'work_experiences.*.role' => ['required', 'string', 'max:255'],
            'work_experiences.*.start_date' => ['required', 'date_format:Y-m-d'],
            'work_experiences.*.end_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function update(Request $request): array
    {
        $parent = parent::store($request);

        $rules = array_merge($parent, [
            'id' => [
                'required',
                'uuid',
                Rule::exists('people', 'id')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id)
                        ->where('branch_id', $request->branch_id)
                        ->where('category_id', Category::where('name', 'agent')->where('group_by', 'people')->first()->id ?? null);
                })
            ],
        ]);

        return $rules;
    }

    public function user(): array
    {
        $parent = parent::user();

        return array_merge($parent, [
            'permission_group_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('group_by', 'permission_groups')
                        ->where('company_id', request()->company_id);
                })
            ],
        ]);
    }

    public function password(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ];
    }
}
