<?php

namespace App\Http\Rules;

use Illuminate\Http\Request;

class AgentRules extends PeopleRules
{
    public function store(Request $request) : array
    {
        return parent::store($request);
    }

    public function agent(): array
    {
        return [
            'work_experiences' => ['array', 'min:0'],
            'work_experiences.*.company_name' => ['required', 'string', 'max:255'],
            'work_experiences.*.role' => ['required', 'string', 'max:255'],
            'work_experiences.*.start_date' => ['required', 'date_format:Y-m-d'],
            'work_experiences.*.end_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function workExperiences(): array
    {
        return [
            'work_experiences' => ['required', 'array'],
            'work_experiences.*.company_name' => ['required', 'string', 'max:255'],
            'work_experiences.*.position' => ['required', 'string', 'max:255'],
            'work_experiences.*.start_date' => ['required', 'date_format:Y-m-d'],
            'work_experiences.*.end_date' => ['required', 'date_format:Y-m-d'],
            'work_experiences.*.description' => ['required', 'string', 'max:255'],
        ];
    }
}
