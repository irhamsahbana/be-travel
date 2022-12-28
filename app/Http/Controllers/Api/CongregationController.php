<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        $paginate = isset($request->paginate) ? (bool) $request->paginate : true;

        $data = Person::with([
            'category' => fn ($query) => $query->select('id', 'label'),
            'branch' => fn ($query) => $query->select('id', 'name'),
        ])
            ->select(['id', 'branch_id', 'category_id', 'ref_no', 'name', 'wa', 'email'])
            ->where('company_id', $user->person->company_id)
            ->whereHas('category', function ($query) {
                $query->where('name', 'congregation')
                    ->where('group_by', 'people')
                    ->whereNull('company_id');
            });

        if ($userCategory == 'director') {
            // filters
            if (!empty($request->branch_id)) $data = $data->where('branch_id', $request->branch_id);
            if (!empty($request->agent_id)) $data = $data->where('agent_id', $request->agent_id);
            if (!empty($request->congregation_id)) $data = $data->where('congregation_id', $request->congregation_id);
        } else if ($userCategory == 'branch-manager') {
            $data = $data->where('branch_id', $user->person->branch_id);

            // filters
            if (!empty($request->agent_id)) $data = $data->where('agent_id', $request->agent_id);
            if (!empty($request->congregation_id)) $data = $data->where('congregation_id', $request->congregation_id);
        } else if ($userCategory == 'agent') {
            $data = $data->where('branch_id', $user->person->branch_id)->where('agent_id', $user->person->id);

            // filters
            if (!empty($request->congregation_id)) $data = $data->where('congregation_id', $request->congregation_id);
        } else {
            return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);
        }

        if ($paginate) {
            $data = $data->paginate((int) $request->per_page ?? 15)->toArray();

            $pagination = $data;
            unset($pagination['data']);

            $data = $data['data'];
            $data['pagination'] = $pagination;
        } else {
            $data = $data->get()->toArray();
        }

        return (new Response)->json($data, 'Congregations retrieved successfully');
    }

    public function check($identifier)
    {
        $congregation = Person::whereNotNull('wa')
            ->whereNotNull('phone')
            ->whereNotNull('national_id')
            ->where(function ($query) use ($identifier) {
                $query->where('wa', $identifier)
                    ->orWhere('phone', $identifier)
                    ->orWhere('national_id', $identifier);
            })
            ->first();

        if ($congregation) {
            $congregation->load([
                'company',
                'branch',
                'agent' => fn ($query) => $query->select('id', 'ref_no', 'name', 'phone', 'wa'),
                'city' => fn ($query) => $query->select('id', 'label', 'group_by'),
                'congregationDetail',
                'congregationInvoices.invoiceDetails'
            ]);

            return (new Response)->json($congregation->toArray(), 'Congregation retrieved successfully.');
        } else {
            return (new Response)->json(null, 'Congregation not found.', 404);
        }
    }

    public function store(Request $request)
    {
        //
    }

    public function register(Request $request)
    {

        $refNo = $this->generateRefNo('people', 4, 'CG/', $this->getPostfix());
        $refNoInvoice = $this->generateRefNo('invoices', 4, 'INV/', $this->getPostfix());

        $personData = [
            'category_id' => Category::where('name', 'congregation')->where('group_by', 'people')->first()->id ?? null,
            'company_id' => $request->company_id,
            'branch_id' => $request->branch_id,
            'agent_id' => $request->agent_id,
            'congregation_id' => $request->congregation_id,
            'ref_no' => $refNo,
            'name' => $request->name,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'place_of_birth' => $request->place_of_birth,
            'date_of_birth' => $request->date_of_birth,
            'sex' => $request->sex,
            'national_id' => $request->national_id,
            'address' => $request->address,
            'province_id' => $request->province_id,
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
                'branch_id' => $request->branch_id,
                'congregation_id' => $person->id,
                'agent_id' => $request->agent_id,
                'ref_no' => $refNoInvoice,
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
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        $data = Person::with([
            'congregationDetail',
            'congregationInvoices.invoiceDetails.service.packetType',
        ])->where('id', $id)->where('company_id', $user->company_id);

        if ($userCategory == 'branch-manager') $data = $data->where('branch_id', $user->branch_id);
        else if ($userCategory == 'agent') $data = $data->where('branch_id', $user->branch_id)->where('agent_id', $user->id);
        else return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);

        $data = $data->first();

        if (!$data) return (new Response)->json(null, 'congregation not found', 404);
        return (new Response)->json($data, 'success to get congregation', 200);
    }

    public function update(Request $request, $id)
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        $person = Person::where('id', $id)->where('company_id', $user->company_id);
        if ($userCategory == 'director') $person = $person->first();
        else if ($userCategory == 'branch-manager') $person = $person->where('branch_id', $user->branch_id)->first();
        else return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);

        if (!$person) return (new Response)->json(null, 'congregation not found', 404);

        // add id to request
        $request->merge(['id' => $id]);

        $personData = [
            'id' => $request->id,
            'category_id' => $person->category_id,
            'company_id' => $person->company_id,
            'branch_id' => $userCategory == 'director' ? $request->branch_id : $person->branch_id,
            'agent_id' => $request->agent_id,
            'congregation_id' => $request->congregation_id,
            'ref_no' => $person->ref_no,
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

        $personRules = (new CongregationRules)->update($request);
        $congregationDetailRules = (new CongregationRules)->congregationDetail();

        $fields = array_merge($personData, $congregationDetailData);
        $rules = array_merge($personRules, $congregationDetailRules);

        $validator = Validator::make($fields, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        $person = Person::find($id);
        if (!$person) return (new Response)->json(null, 'congregation not found', 404);

        DB::beginTransaction();
        try {
            $person->update($personData);
            $person->congregationDetail->updateOrCreate(['person_id' => $person->id], $congregationDetailData);

            $person = Person::with([
                'congregationDetail',
                'congregationInvoices.invoiceDetails.service.packetType',
            ])->find($person->id)?->toArray();

            DB::commit();
            return (new Response)->json($person, 'success to update congregation', 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        $person = Person::where('id', $id)->where('company_id', $user->company_id);

        if ($userCategory == 'director') $person = $person->first();
        else if ($userCategory == 'branch-manager') $person = $person->where('branch_id', $user->branch_id)->first();
        else return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);

        if (!$person) return (new Response)->json(null, 'congregation not found', 404);

        $fields = ['invoices' => $id];
        $rules = ['invoices' => Rule::unique('invoices', 'congregation_id')];
        $messages = ['invoices.unique' => 'jamaah tidak bisa dihapus karena memiliki data invoice'];

        $validator = Validator::make($fields, $rules, $messages);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        DB::beginTransaction();
        try {
            $person->load('congregationDetail');
            $person->delete();

            DB::commit();
            return (new Response)->json($person->toArray(), 'success to delete congregation', 200);
        } catch (\Throwable $th) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            throw $th;
        }
    }
}
