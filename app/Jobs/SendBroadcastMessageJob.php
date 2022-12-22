<?php

namespace App\Jobs;

use App\Libs\Logger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\BroadcastMessage;
use App\Models\BroadcastMessageRecipient;
use App\Models\ApiToken;

class SendBroadcastMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected BroadcastMessage $bm;
    protected BroadcastMessageRecipient $br;

    public function __construct(BroadcastMessage $bm, BroadcastMessageRecipient $br)
    {
        $this->bm = $bm->load(['company' => fn ($q) => $q->select(['id', 'name'])]);
        $this->br = $br->load(['person' => fn ($q) => $q->select(['id', 'name', 'wa'])]);

        // dump($this->bm->toArray(), $this->br->toArray());
    }

    public function handle()
    {
        sleep(4);
        $token = ApiToken::where('company_id', $this->bm->company_id)->first()?->token;
        $number = $this->br->person->wa ?? '';
        $message = $this->generateMessage();

        $formParams = [
            'token' => $token,
            'number' => $number,
            'message' => $message,
        ];

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://app.ruangwa.id/api/send_message', [
            'form_params' => $formParams
        ]);

        $res = json_decode($response->getBody()->getContents());

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $result = filter_var($res->result, FILTER_VALIDATE_BOOLEAN);

            if ($result) BroadcastMessageRecipient::where('id', $this->br?->id)?->update(['status' => 'sent']);
            else BroadcastMessageRecipient::where('id', $this->br?->id)?->update(['status' => 'failed']);
        } else {
            BroadcastMessageRecipient::where('id', $this->br?->id)?->update(['status' => 'failed']);

            Logger::log(
                null,
                Logger::LEVEL_ERROR,
                Logger::ACTION_SEND_BROADCAST_MESSAGE,
                Logger::TABLE_BROADCAST_MESSAGE_RECIPIENT,
                $this->br?->id,
                $this->bm?->company?->id,
                null,
                null,
                json_encode($formParams)
            );

            $this->fail(new \Exception('Failed to send broadcast message'));
        }
    }

    protected function generateMessage(): string
    {
        $bm = $this->bm;

        $timestamps = \Carbon\Carbon::now()->locale('id')->translatedFormat('l, j F Y H:i:s');

        $message = <<<EOD
        {$timestamps}

        {$bm->title}

        {$bm->message}

        Terima kasih.
        {$bm->company->name}
        EOD;

        return $message;
    }
}
