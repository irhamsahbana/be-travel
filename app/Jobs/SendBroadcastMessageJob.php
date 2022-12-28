<?php

namespace App\Jobs;

use App\Libs\Logger;
use App\Libs\WaGateway\Wablas;
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
        $companyId = $this->bm->company_id;
        $number = $this->br->person->wa ?? '';
        $message = $this->generateMessage();

        $waGateway = (new Wablas)
            ->setToken($companyId)
            ->setNumber($number)
            ->setMessage($message);

        $response = $waGateway->sendMessage();
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
