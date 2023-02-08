<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Libs\Response;

use App\Models\Service;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;
        $data = null;

        switch ($userCategory) {
            case 'director':
                $data = Service::where('company_id', $user->company_id);
                break;
            case 'manager':
                $data = Service::where('company_id', $user->company_id);
                break;
            case 'agent':
                $data = Service::where('company_id', $user->company_id);
                break;
            default:
                return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);
                break;
        }

        $data = $data->with([
            'packetType' => fn ($query) => $query->select('id', 'label'),
            'file'
        ])->get();

        return (new Response)->json($data->toArray(), 'services retrieved successfully');
    }

    public function store(Request $request)
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        $fields = [
            'company_id' => $user->company_id,
            'packet_type_id' => $request->packet_type_id,
            'name' => $request->name,
            'price' => $request->price,
            'departure_date' => $request->departure_date,
        ];

        $rules = [
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'packet_type_id' => [
                'required', 'uuid',
                Rule::exists('categories', 'id')->where(function ($query) use ($fields){
                    $query->where('group_by', 'packet_types')
                        ->where('company_id', $fields['company_id']);
                })
            ],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'departure_date' => ['required', 'date', 'date_format:Y-m-d']
        ];

        $validator = Validator::make($fields, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        switch ($userCategory) {
            case 'director':
                $data = Service::create([
                    'company_id' => $user->company_id,
                    'packet_type_id' => $request->packet_type_id,
                    'name' => $request->name,
                    'price' => $request->price,
                    'departure_date' => $request->departure_date,
                ]);
                break;
            default:
                return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);
                break;
        }

        $data = $data->with([
            'packetType' => fn ($query) => $query->select('id', 'label'),
        ])->first();

        return (new Response)->json($data->toArray(), 'service created successfully');
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
}
