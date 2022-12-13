<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\Response;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user()->load([
            'person' => function ($query) {
                $query->select('id', 'name', 'company_id', 'branch_id', 'category_id');
            },
            'person.category' => function ($query) {
                $query->select('id', 'name');
            },
        ]);

        $invoices = Invoice::select('*')
            ->with([
                'congregation' => function ($query) {
                    $query->select('id', 'name');
                },
                'invoiceDetails'
            ])
            ->where('company_id', $user->person->company_id);

        if ($user->person->category->name === 'director') {
        } else if ($user->person->category->name === 'branch-manager') {
            $invoices->where('branch_id', $user->person->branch_id);
        } else if ($user->person->category->name === 'agent') {
            $invoices->where('agent_id', $user->person->id);
        }

        $invoices = $invoices->get()->toArray();

        // return (new Response)->json($invoices, 'success get invoices');

        return (new Response)->json($user->toArray(), 'success get user');
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
