<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Libs\Response;

use App\Models\Invoice;


class PaymentController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $user = $this->getUser();
        if ($user->person->category->name != 'director') return (new Response)->json(null, 'you not authorized to access this resource', 403);

        $invoice = Invoice::where('id', $request->invoice_id)
            ->where('company_id', $user->company_id)
            ->first();

        $totalAmount = (float) $invoice?->amount;
        $totalPayments = 0;
        foreach ($request->payments as $payment) $totalPayments += (float) $payment['amount'];

        $fields = [
            'invoice_id' => $request->invoice_id,
            'payments' => $request->payments,
            'total_amount' => $totalAmount,
            'total_payments' => $totalPayments,
        ];

        $rules = [
            'invoice_id' => ['required', 'exists:invoices,id'],
            'payments' => ['required', 'array'],
            'payments.*.id' => [
                'nullable',
                'uuid',
                Rule::exists('payments', 'id')->where(function ($query) use ($invoice) {
                    $query->where('invoice_id', $invoice?->id);
                })
            ],
            'payments.*.payment_method_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('group_by', 'payment_methods');
                })
            ],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
            'payments.*.created' => ['required', 'date_format:Y-m-d H:i:s'],
            'payments.*.notes' => ['nullable', 'string', 'max:255'],

            'total_amount' => ['required', 'numeric', 'min:0'],
            'total_payments' => ['required', 'numeric', 'min:0', 'lte:total_amount'],
        ];

        $messages = [
            'total_payments.lte' => "total pembayaran harus lebih kecil atau sama dengan jumlah invoice ($totalAmount), saat ini pembayaran :input."
        ];

        $validator = Validator::make($fields, $rules, $messages);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        // update payment if exist else create new payment
        DB::beginTransaction();
        try {
            $invoice->payments()->delete();
            foreach ($request->payments as $payment) {
                $invoice->payments()->create([
                    'id' => $payment['id'],
                    'payment_method_id' => $payment['payment_method_id'],
                    'amount' => $payment['amount'],
                    'created' => $payment['created'],
                    'notes' => $payment['notes'],
                ]);
            }

            // update paid amount in invoice
            $invoice->paid = $totalPayments;
            $invoice->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return (new Response)->json(null, $e->getMessage(), 500, get_class($e), $e->getFile(), $e->getLine(), $e->getTrace());
        }

        $data = $invoice->load([
            'payments' => fn ($q) => $q->orderBy('created', 'asc'),
            'payments.paymentMethod' => fn ($query) => $query->select('id', 'label'),
        ])->toArray();

        return (new Response)->json($data, 'success create payment');
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
