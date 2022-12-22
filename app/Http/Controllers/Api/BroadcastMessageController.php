<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\Logger;
use App\Libs\Response;
use App\Models\BroadcastMessage;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BroadcastMessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        if ($userCategory != 'director') return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);

        $data = BroadcastMessage::select(['id', 'company_id', 'title', 'message', 'scheduled_date', 'scheduled_time'])
            ->where('company_id', $user->company_id);

        if ($request->branch_id) {
            $data = $data->whereHas('BroadcastMessageRecipients', function ($query) use ($request) {
                return $query->where('person_id', $request->branch_id);
            });
        }

        $data = $data->paginate((int) $request->per_page ?? 15)->toArray();

        $pagination = $data;
        unset($pagination['data']);

        $data = $data['data'];
        $data['pagination'] = $pagination;

        return (new Response)->json($data, 'broadcast messages retrieve successfully.');
    }

    public function store(Request $request)
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        if ($userCategory != 'director') return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);
        $nowDate = now()->format('Y-m-d');

        $fields = [
            'company_id' => $user->company_id,
            'person_id' => $user->person->id,
            'title' => $request->title,
            'message' => $request->message,
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time' => $request->scheduled_time,
            'recipients' => $request->recipients,
        ];

        $rules = [
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'person_id' => ['required', 'uuid', 'exists:people,id'],
            'title' => ['required', 'max:255'],
            'message' => ['required', 'max:10000'],
            'scheduled_date' => ['required', 'date', "after_or_equal:{$nowDate}", 'date_format:Y-m-d'],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'recipients' => ['nullable', 'array'],
            'recipients.*' => [
                'nullable',
                'uuid',
                Rule::exists('people', 'id')->where(function ($query) use ($fields) {
                    return $query->where('company_id', $fields['company_id']);
                }),
            ],
        ];

        $validator = Validator::make($fields, $rules);

        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        DB::beginTransaction();
        try {
            // broadcast messages table
            $bcm = new \App\Models\BroadcastMessage;
            $bcm->company_id = $fields['company_id'];
            $bcm->person_id = $fields['person_id'];
            $bcm->title = $fields['title'];
            $bcm->message = $fields['message'];
            $bcm->scheduled_date = $fields['scheduled_date'];
            $bcm->scheduled_time = $fields['scheduled_time'];
            $bcm->save();

            // broadcast message recipients table
            foreach ($fields['recipients'] as $recipient) {
                $bcmr = new \App\Models\BroadcastMessageRecipient;
                $bcmr->broadcast_message_id = $bcm->id;
                $bcmr->person_id = $recipient;
                $bcmr->save();
            }

            DB::commit();
            $data = $bcm->load([
                'broadcastMessageRecipients' => fn ($q) => $q->select('id', 'broadcast_message_id', 'person_id'),
            ])->toArray();

            return (new Response)->json($data, 'Broadcast message successfully created.', 201);
        } catch (\Throwable $th) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            Logger::log(
                $th,
                Logger::LEVEL_ERROR,
                Logger::ACTION_CREATE,
                'broadcast_messages',
                null,
                $user->company_id,
                $user->branch_id,
                $user->id,
                json_encode($fields)
            );
            throw $th;
        }
    }

    public function show($id)
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        if ($userCategory != 'director') return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);

        $data = BroadcastMessage::with([
            'BroadcastMessageRecipients',
            'BroadcastMessageRecipients.person' => fn ($q) => $q->select('id', 'ref_no', 'national_id', 'name', 'wa')
        ])->where('id', $id)->first();
        if (!$data) return (new Response)->json(null, 'Broadcast message not found.', 404);

        return (new Response)->json($data->toArray(), 'broadcast message retrieve successfully.');
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        if ($userCategory != 'director') return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);

        $bcm = BroadcastMessage::where('id', $id)->first();
        if (!$bcm) return (new Response)->json(null, 'Broadcast message not found.', 404);

        $bcm->delete();

        return (new Response)->json($bcm->toArray(), 'Broadcast message successfully deleted.');
    }
}
