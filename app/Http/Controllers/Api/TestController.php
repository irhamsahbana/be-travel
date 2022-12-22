<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class TestController extends Controller
{
    public function deleteNonDummy()
    {
        DB::beginTransaction();
        try {

            // delete invoices
            $invoices = \App\Models\Invoice::all();

            foreach ($invoices as $invoice) {
                $invoice->invoiceDetails()->delete();
                $invoice->delete();
            }

            // delete broadcast messages
            $broadcastMessages = \App\Models\BroadcastMessage::all();
            foreach ($broadcastMessages as $broadcastMessage) {
                $broadcastMessage->delete();
            }

            // delete people
            $people = \App\Models\Person::with(['category'])->whereNotIn('id', [
                '5f6108f0-36f1-4381-b87d-e699e36e9c1b',
                '15e69304-6550-49ed-8f31-911ae8c0f15c',
                '96881465-455e-4322-8228-039564b74609',
            ])->get();
            foreach ($people->where('category.name', 'congregation') as $person) {
                $person->congregationDetail()->delete();
                $person->user()->delete();
                $person->delete();
            }
            foreach ($people->where('category.name', 'agent') as $person) {
                $person->agentWorkExperiences()->delete();
                $person->user()->delete();
                $person->delete();
            }

            // delete companies permission groups and its permissions
            $permissionGroups = \App\Models\PermissionGroupPermission::where('company_id', '!=', '97f2d9af-6c15-4757-bb35-2562175708b7')->delete();

            // delete companies services
            $companiesServices = \App\Models\Service::whereNotIn('company_id', ['97f2d9af-6c15-4757-bb35-2562175708b7'])->forceDelete();

            // delete companies categories
            $companiesCategories = \App\Models\Category::whereNotIn('company_id', [null, '97f2d9af-6c15-4757-bb35-2562175708b7'])->forceDelete();

            // delete branches
            $branches = \App\Models\Branch::where('id', '!=', '97f2d9b0-005a-443c-9183-93e9ca910ceb')->delete();

            // delete companies
            $companies = \App\Models\Company::where('id', '!=', '97f2d9af-6c15-4757-bb35-2562175708b7')->delete();
            DB::commit();
            return (new Response)->json([], 'success');
        } catch (\Throwable $th) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            throw $th;
        }
    }
}
