<?php

namespace App\Libs\WaGateway;

use App\Libs\Contracts\WaSendMessageContract;
use App\Libs\Logger;
use Illuminate\Support\Facades\Log as FacadesLog;

use App\Models\ApiToken;
use App\Models\Log;

class Wablas extends AbstractWaGateway
{
    public function setToken(string $companyId): WaSendMessageContract
    {
        // $token = ApiToken::where('company_id', $companyId)->where('name', 'wablas')->first()?->token ?? '';
        // $this->token = $token;
        // $this->company = $companyId;

        $this->token = 'n6x6vN9rmJeLNYDyXTQ394X0fSin8O12s2nkTAuZRm3RFsnVcxEyrNTpS28bBRQT';
        $this->company = $companyId;
        return $this;
    }

    public function sendMessage(): object
    {
        $obj =  new \StdClass();
        $client = new \GuzzleHttp\Client();

        $headers = ['Authorization' => $this->token];

        $requestData = [
            [
                'name' => 'phone',
                'contents' => $this->number
            ],
            [
                'name' => 'message',
                'contents' => $this->message
            ],
            [
                'name' => 'spintax',
                'contents' => 'true'
            ],
            [
                'name' => 'priority',
                'contents' => 'true'
            ]
        ];

        try {
            $response = $client->request('POST', 'https://jogja.wablas.com/api/send-message', [
                'headers' => $headers,
                'multipart' => $requestData
            ]);

            $res = json_decode($response->getBody()->getContents());
            $obj->body = $res;
            $obj->statusCode = $response->getStatusCode();

            $data = json_encode([
                'token' => $this->token,
                'number' => $this->number,
                'message' => $this->message
            ]);

            $obj->response = $response;
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $response = $e?->getResponse()?->getBody()?->getContents() ?? null;

            Logger::log(
                e: $e,
                level: Logger::LEVEL_ERROR,
                data: $data,
                response: $response,
            );

            $obj->body = $e->getMessage();
            $obj->statusCode = 500;
        } catch (\Exception $e) {
            Logger::log(
                e: $e,
                level: Logger::LEVEL_ERROR,
                companyId: $this->companyId,
                table: Logger::TABLE_COMPANY,
                data: $data,
            );
        }

        return $obj;
    }
    // example of data $res:
    // {#1807
    //     +"status": true
    //     +"message": "Message is pending and waiting to be processed"
    //     +"data": {#1805
    //     +"device_id": "2TCVPF"
    //     +"quota": 194
    //     +"messages": array:1 [
    //         0 => {#1772
    //         +"id": "2632d308-2e1f-4be7-a8e1-a59b964957c9"
    //         +"phone": "6282188449289"
    //         +"message": """
    //             Rabu, 28 Desember 2022 11:12:42\n
    //             \n
    //             Terima kasih telah melakukan pendaftaran perusahaan. Berikut adalah detail pendaftaran anda:\n
    //             \n
    //             Nama Perusahaan: PT. Coba - Coba\n
    //             No. Referensi: CO/0002/12.22\n
    //             \n
    //             Direktur: Irham Sahbana\n
    //             Email: irhamsahbana@gmail.com\n
    //             Username: irhamsahbana\n
    //             No. HP: 6282188449289\n
    //             \n
    //             Akun Bank:\n
    //             - BCA Syariah 52976492 (a/n Irham Sahbana)
    //             """
    //         +"status": "pending"
    //         +"ref_id": null
    //         }
    //     ]
    //     }
    // }
}
