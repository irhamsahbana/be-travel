<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Libs\RefNoGenerator;
use App\Libs\Response;

use App\Http\Rules\CongregationRules;
use App\Jobs\SendRegisteredNotificationJob;
use App\Models\{Company, CongregationDetail, Service, Category, Person, Invoice, InvoiceDetail};

class CongregationController extends Controller
{
    use RefNoGenerator;

    public function index(Request $request)
    {
        //
    }

    public function store(Request $request)
    {
        $refNo = $this->generateRefNo('people', 4, 'CG/', $this->getPostfix());

        $personData = [
            'category_id' => Category::where('name', 'congregation')->where('group_by', 'people')->first()->id ?? null,
            'company_id' => $request->company_id,
            'branch_id' => $request->branch_id,
            'agent_id' => $request->agent_id,
            'ref_no' => $refNo,
            'name' => $request->name,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'place_of_birth' => $request->place_of_birth,
            'date_of_birth' => $request->date_of_birth,
            'sex' => $request->sex,
            'national_id' => $request->national_id,
            'address' => $request->address,
            'city_id' => $request->city_id,
            'nationality_id' => $request->nationality_id,
            'phone' => $request->phone,
            'wa' => $request->wa,
            'email' => $request->email,
            'education_id' => $request->education_id,
            'profession' => $request->profession,
            'marital_status_id' => $request->marital_status_id,
            'account_name' => $request->account_name,
            'bank_id' => $request->bank_id,
            'account_number' => $request->account_number,
            'emergency_name' => $request->emergency_name,
            'emergency_address' => $request->emergency_address,
            'emergency_home_phone' => $request->emergency_home_phone,
            'emergency_phone' => $request->emergency_phone,
            'notes' => $request->notes,
        ];

        $serviceData = [
            'service_id' => $request->service_id,
        ];

        $congregationDetailData = [
            'is_has_meningitis_vaccinated' => $request->is_has_meningitis_vaccinated,
            'is_has_family_card' => $request->is_has_family_card,
            'is_has_photo' => $request->is_has_photo,
            'is_has_mahram' => $request->is_has_mahram,
            'is_airport_handling' => $request->is_airport_handling,
            'is_equipment' => $request->is_equipment,
            'is_single_mahram' => $request->is_single_mahram,
            'is_double_mahram' => $request->is_double_mahram,
            'is_pusher_guide' => $request->is_pusher_guide,
            'is_special_guide' => $request->is_special_guide,
            'is_manasik' => $request->is_manasik,
            'is_domestic_ticket' => $request->is_domestic_ticket,
            'blood_type' => $request->blood_type,
            'chronic_disease' => $request->chronic_disease,
            'allergy' => $request->allergy,
            'passport_number' => $request->passport_number,
            'passport_issued_in' => $request->passport_issued_in,
            'passport_issued_at' => $request->passport_issued_at,
            'passport_expired_at' => $request->passport_expired_at,
            'passport_name' => $request->passport_name,
        ];


        $fields = array_merge($personData, $serviceData, $congregationDetailData);

        $personRules = (new CongregationRules)->store($request);
        $congregationDetailRules = (new CongregationRules)->congregationDetail();
        $serviceRules = (new CongregationRules)->service($request);

        $rules = array_merge($personRules, $serviceRules, $congregationDetailRules);

        $validator = Validator::make($fields, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        DB::beginTransaction();
        try {
            // people table
            $person = Person::create($personData);

            // congregation_details table
            $congregationDetailData['person_id'] = $person->id;
            CongregationDetail::create($congregationDetailData);

            // invoices table
            $service = Service::find($request->service_id);
            $invoiceData = [
                'company_id' => $request->company_id,
                'congregation_id' => $person->id,
                'amount' => $service->price,
                'paid' => 0,
            ];
            $invoice = Invoice::create($invoiceData);

            // invoice_details table
            $invoiceDetail = [
                'invoice_id' => $invoice->id,
                'service_id' => $request->service_id,
                'quantity' => 1,
                'price' => $service->price,
            ];
            InvoiceDetail::create($invoiceDetail);

            $person = Person::with([
                'congregationDetail'
            ])->find($person->id);

            $invoice = Invoice::with([
                'invoiceDetails.service.packetType',
                'company.accounts.bank',
            ])
                ->find($invoice->id);

            $p = $person->toArray();
            $inv = ['invoice' => $invoice->toArray()];
            $response = array_merge($p, $inv);

            $job = new SendRegisteredNotificationJob($invoice, $person);
            $this->dispatch($job);

            DB::commit();

            return (new Response)->json($response, 'success to create congregation', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return (new Response)->json(null, $e->getMessage(), 500, get_class($e), $e->getFile(), $e->getLine(), $e->getTrace());
        }
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
