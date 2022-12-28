<?php

namespace App\Libs\WaGateway;

use App\Libs\Contracts\WaSendMessageContract;
use Illuminate\Support\Facades\Log as FacadesLog;

use App\Models\ApiToken;
use App\Models\Log;

class RuangWa extends AbstractWaGateway
{
    public function setToken(string $companyId): WaSendMessageContract
    {
        $token = ApiToken::where('company_id', $companyId)->where('name', 'ruang_wa')->first()?->token ?? '';
        $this->token = $token;

        return $this;
    }

    public function sendMessage(): object
    {
        $obj = new \stdClass();
        $client = new \GuzzleHttp\Client();
        $formParams = [
            'token' => $this->token,
            'number' => $this->number,
            'message' => $this->message
        ];

        try {
            $response = $client->request('POST', 'https://app.ruangwa.id/api/send_message', [
                'form_params' => $formParams
            ]);

            $res = json_decode($response->getBody()->getContents());
            $obj->body = $res;
            $obj->statusCode = $response->getStatusCode();
        } catch (\Exception $e) {
            $data = [
                'token' => $this->token,
                'number' => $this->number,
                'message' => $this->message
            ];
            $data = json_encode($data);

            Log::create([
                'company_id' => $this->companyId,
                'table' => 'invoices',
                'action' => 'send_congregation_registered_notification',
                'data' => $data,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            FacadesLog::error($e->getMessage(), $e->getTrace());

            $obj->body = $e->getMessage();
            $obj->statusCode = 500;
        }

        return $obj;
    }
    // array:2 [ // app/Jobs/SendRegisteredNotificationJob.php:85
    //     "result" => "false"
    //     "message" => "Tidak ada data!"
    //   ]

    // {#1805 // app/Jobs/SendRegisteredNotificationJob.php:77
    // +"result": "true"
    // +"id": "BAE5250D11A36D52"
    // +"number": "6282188449289"
    // +"message": "Kirim pesan sukses!"
    // +"status": "sent"
    // }

    // sample response
}
