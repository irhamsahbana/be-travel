<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Http\Repositories\Finder\CategoryFinder;

use App\Libs\Response;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        $finder = new CategoryFinder();
        $finder->setAccessControl($this->getAccessControl());

        if (isset($request->group_by))
            $finder->setGroup($request->group_by);

        if (isset($request->group_by) && in_array($request->group_by, ['packet_types']))
            $finder->setCompany($user?->company_id);

        if (isset($request->order_by))
            $finder->setOrderBy($request->order_by);

        if (isset($request->order_type) && !empty($request->order_type))
            $finder->setOrderType($request->order_type);

        if (isset($request->parent_id) && !empty($request->parent_id))
            $finder->setParentId($request->parent_id);
        else
            $finder->setParentId(null);

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
            foreach ($paginator as $item)
                $data[] = $item;
        } else {
            foreach ($paginator->items() as $item)
                $data[] = $item;

            foreach ($paginator->toArray() as $key => $value)
                if ($key != 'data')
                    $data['pagination'][$key] = $value;
        }

        $response = new Response;
        return $response->json($data, "success get {$request->group_by} data");
    }

    public function public(Request $request)
    {
        $request->validate([
            'group_by' => [
                'string',
                'required',
                Rule::in([
                    'genders', 'marital_statuses',
                    'educations', 'nationalities',
                    'banks', 'provinces', 'cities',
                    'nationalities',
                ]),
            ],
        ]);
        $finder = new CategoryFinder();

        if (isset($request->group_by))
            $finder->setGroup($request->group_by);

        if (isset($request->order_by))
            $finder->setOrderBy($request->order_by);

        if (isset($request->order_type))
            $finder->setOrderType($request->order_type);

        if (isset($request->parent_id))
            $finder->setParentId($request->parent_id);
        else
            $finder->setParentId(null);

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
            foreach ($paginator as $item)
                $data[] = $item;
        } else {
            foreach ($paginator->items() as $item)
                $data[] = $item;

            foreach ($paginator->toArray() as $key => $value)
                if ($key != 'data')
                    $data['pagination'][$key] = $value;
        }

        return (new Response)->json($data, "success get {$request->group_by} data");
    }
}
