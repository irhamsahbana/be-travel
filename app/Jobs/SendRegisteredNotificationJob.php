<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendRegisteredNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $waNumber = '';
    protected string $message = '';

    public function __construct(string $waNumber, string $message)
    {
        $this->waNumber = $waNumber;
        $this->message = $message;
    }

    public function handle()
    {
        sleep(5);

        $formParams = [
            'token' => config('services.ruangwa.token'),
            'number' => $this->waNumber,
            'message' => $this->message,
        ];

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://app.ruangwa.id/api/send_message', [
            'form_params' => $formParams
        ]);


    }
}
