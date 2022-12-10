<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\Response;
use Illuminate\Http\Request;

use App\Models\Company;

class CompanyController extends Controller
{
    public function index()
    {
        //
    }

    public function publicIndex()
    {
        $data = Company::with('branches', 'services.packetType')->get()->toArray();

        return (new Response)->json($data, 'Companies retrieved successfully.');
    }

    public function store(Request $request)
    {
        //
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
