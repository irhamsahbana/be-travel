<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Validation\Rule;

use App\Libs\Response;

use App\Http\Repositories\Finder\PersonFinder;
use App\Http\Repositories\Tutor;
use App\Http\Services\TutorService;

class TutorController extends Controller
{
    public function index(Request $request)
    {
        $finder = new PersonFinder();
        $finder->setAccessControl($this->getAccessControl());
        $finder->setCategory('tutor');

        if (isset($request->order_by))
            $finder->setOrderBy($request->order_by);

        if (isset($request->order_type))
            $finder->setOrderType($request->order_type);

        if (isset($request->paginate)) {
            $finder->usePagination($request->paginate);

            if (isset($request->page))
                $finder->setPage($request->page);

            if (isset($request->per_page))
                $finder->setPerPage($request->per_page);
        }

        $paginator = $finder->get();
        $data = [];

        if (!$finder->isUsePagination()) {
            foreach ($paginator as $item) {
                $data[] = $item;
            }

        } else {
            foreach ($paginator->items() as $item) {
                $data[] = $item;
            }

            foreach ($paginator->toArray() as $key => $value)
                if ($key != 'data')
                    $data['pagination'][$key] = $value;
        }

        $response = new Response;
        return $response->json($data, "success get tutor data");

    }

    public function upsert(Request $request)
    {
        $response = new Response();
        $fields = $request->all();

        $this->filterByAccessControl('tutor-create');

        $rules = [
            'id' => 'required|uuid',
            'name' => 'required|string',
            'city_id' => ['required', 'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('group_by', 'cities');
                }),
            ],
            'address' => 'required|string',
            'phone' => [
                'required',
                'string',
                'min:4',
                Rule::unique('people', 'phone')->ignore($fields['id']),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('people', 'email')->ignore($fields['id']),
            ],
            'bio' => 'required|string',
            'social_medias' => 'nullable|array',
            'course_ids' => ['required', 'array', 'min:1'],
            'course_ids.*' => [
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('group_by', 'courses');
                }),
            ],
            'course_level_ids' =>  ['required', 'array','min:1'],
            'course_level_ids.*' => [
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('group_by', 'course_levels');
                }),
            ],
            'schedules' => 'required|array|min:1',
            'fee' => 'required|numeric',
        ];

        $validator = Validator::make($fields, $rules);

        if ($validator->fails()) {
            return $response->json(
                null,
                $validator->errors(),
                HttpResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $repository = new Tutor();
        $service = new TutorService($repository);
        $data = $service->upsert((object) $fields);

        return $response->json($data, 'ok');
    }

    public function show($id)
    {
        $response = new Response();
        $this->filterByAccessControl('tutor-read');

        $repository = new Tutor();
        $service = new TutorService($repository);
        $data = $service->find($id);

        return $response->json($data, 'success get tutor data');
    }

    public function destroy($id)
    {
        //
    }
}
