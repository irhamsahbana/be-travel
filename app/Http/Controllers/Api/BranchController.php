<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Libs\Response;
use App\Libs\RefNoGenerator;

use App\Models\Branch;

class BranchController extends Controller
{
    use RefNoGenerator;

    public function index()
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get()->toArray() ?? [];


        return (new Response)->json($branches, 'Branches retrieved successfully.');
    }

    public function store(Request $request)
    {
        $refNo = $this->generateRefNo('branches', 4, 'BR/', $this->getPostfix());

        $fields = [
            'company_id' => auth()->user()->company_id,
            'name' => $request->name,
            'ref_no' => $refNo,
        ];

        $rules = [
            'company_id' => [
                'required',
                'uuid',
                Rule::exists('companies', 'id')->where(function ($query) {
                    $query->where('id', auth()->user()->company_id);
                }),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches')->where(function ($query) use ($fields) {
                    $query->where('company_id', $fields['company_id']);
                }),
            ],
        ];

        $validator = Validator::make($fields, $rules);

        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        $branch = Branch::create($fields);
        $branch->refresh();
        $branch = $branch->toArray();

        return (new Response)->json($branch, 'Branch created successfully.');
    }

    public function show($id)
    {
        $branch = Branch::where('company_id', auth()->user()->company_id)
            ->where('id', $id)
            ->first();

        if (!$branch) return (new Response)->json(null, 'Branch not found.', 404);

        return (new Response)->json($branch, 'Branch retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $fields = [
            'id' => $id,
            'company_id' => auth()->user()->company_id,
            'name' => $request->name,
        ];

        $rules = [
            'id' => [
                'required',
                'uuid',
                Rule::exists('branches', 'id')->where(function ($query) use ($fields) {
                    $query->where('id', $fields['id'])
                        ->where('company_id', $fields['company_id']);
                }),
            ],
            'company_id' => [
                'required',
                'uuid',
                Rule::exists('companies', 'id')->where(function ($query) {
                    $query->where('id', auth()->user()->company_id);
                }),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches')->where(function ($query) use ($fields) {
                    $query->where('company_id', $fields['company_id']);
                })->ignore($fields['id']),
            ],
        ];

        $validator = Validator::make($fields, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        $branch = Branch::where('company_id', auth()->user()->company_id)
            ->where('id', $id)
            ->first();

        if (!$branch) return (new Response)->json(null, 'Branch not found.', 404);

        $branch->update($fields);
        $branch->refresh();
        $branch = $branch->toArray();

        return (new Response)->json($branch, 'Branch updated successfully.');
    }

    public function destroy($id)
    {
        $branch = Branch::where('company_id', auth()->user()->company_id)
            ->where('id', $id)
            ->first();

        if (!$branch) return (new Response)->json(null, 'Branch not found.', 404);

        $data = $branch->toArray();
        $branch->delete();

        return (new Response)->json($data, 'Branch deleted successfully.');
    }
}
