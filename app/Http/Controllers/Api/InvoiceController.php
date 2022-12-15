<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Libs\Response;

use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->getUser();

        $invoices = Invoice::select('*')
            ->with([
                'branch' => fn ($query) => $query->select('id', 'name'),
                'congregation' => fn ($query) => $query->select('id', 'ref_no', 'name'),
                'agent' => fn ($query) => $query->select('id', 'ref_no', 'name'),
            ])
            ->where('company_id', $user->person->company_id);

        if ($user->person->category->name === 'director') {
            if (!empty($request->branch_id)) $invoices->where('branch_id', $request->branch_id);
            if (!empty($request->agent_id)) $invoices->where('agent_id', $request->agent_id);
            if (!empty($request->congregation_id)) $invoices->where('congregation_id', $request->congregation_id);
        } else if ($user->person->category->name === 'branch-manager') {
            $invoices->where('branch_id', $user->person->branch_id);

            if (!empty($request->agent_id)) $invoices->where('agent_id', $request->agent_id);
            if (!empty($request->congregation_id)) $invoices->where('congregation_id', $request->congregation_id);
        } else if ($user->person->category->name === 'agent') {
            $invoices->where('agent_id', $user->person->id);
            if (!empty($request->congregation_id)) $invoices->where('congregation_id', $request->congregation_id);
        }

        $invoices = $invoices->paginate((int) $request->per_page ?? 15)->toArray();

        $pagination = $invoices;
        unset($pagination['data']);

        $invoices = $invoices['data'];
        $invoices['pagination'] = $pagination;

        return (new Response)->json($invoices, 'success get invoices');
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        $user = $this->getUser();

        if ($user->person->category->name == 'director') {
            $data = Invoice::with(['invoiceDetails'])->where('id', $id)
                ->where('company_id', $user->person->company_id)
                ->first();
        } else if ($user->person->category->name == 'branch-manager') {
            $data = Invoice::with(['invoiceDetails'])->where('id', $id)
                ->where('branch_id', $user->person->branch_id)
                ->first();
        } else {
            return (new Response)->json(null, 'You are not authorized to access this resource', 403);
        }

        if (!$data) return (new Response)->json(null, 'Invoice not found', 404);
        return (new Response)->json($data?->toArray(), 'success get invoice');
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        $user = $this->getUser();

        DB::beginTransaction();
        try {
            if ($user->person->category->name == 'director') {
                $invoice = Invoice::with(['invoiceDetails'])->where('id', $id)
                    ->where('company_id', $user->person->company_id)
                    ->first();

                $invoice?->delete(); // invoice details will be deleted by cascade
            } else if ($user->person->category->name == 'branch-manager') {
                $invoice = Invoice::with(['invoiceDetails'])->where('id', $id)
                    ->where('branch_id', $user->person->branch_id)
                    ->first();

                $invoice?->delete();
            } else {
                return (new Response)->json(null, 'You are not authorized to access this resource', 403);
            }
            if (!$invoice) return (new Response)->json(null, 'Invoice not found', 404);
            DB::commit();
            return (new Response)->json($invoice?->toArray(), 'success delete invoice');
        } catch (\Exception $e) {
            DB::rollBack();
            return (new Response)->json(null, $e->getMessage(), 500, get_class($e), $e->getFile(), $e->getLine(), $e->getTrace());
        }
    }
}
