<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Person;
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

    protected ?Invoice $invoice;
    protected ?Person $congregation;
    protected string $message = '';

    public function __construct(Invoice $invoice, Person $congregation)
    {
        $this->invoice = $invoice;
        $this->congregation = $congregation;
        $this->message = $this->generateMessage($invoice, $congregation);
    }

    public function handle()
    {
        sleep(4);

        $formParams = [
            'token' => config('services.ruangwa.token'),
            'number' => $this->congregation->wa ?? '',
            'message' => $this->message,
        ];

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://app.ruangwa.id/api/send_message', [
            'form_params' => $formParams
        ]);

        $res = json_decode($response->getBody()->getContents());

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $result = filter_var($res->result, FILTER_VALIDATE_BOOLEAN);
            if ($result) {
                Invoice::where('id', $this->invoice->id)->update([
                    'notification_status' => 'sent',
                ]);
            } else {
                Invoice::where('id', $this->invoice->id)->update([
                    'notification_status' => 'failed',
                ]);
            }
        } else if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 600) {
            Invoice::where('id', $this->invoice->id)->update([
                'notification_status' => 'failed',
            ]);
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

    protected function generateMessage(Invoice $invoice, Person $congregation): string
    {
        // Jumat, 16 Desember 2022 10:00:00
        $timestamps = \Carbon\Carbon::now('Asia/Makassar')->locale('id')->translatedFormat('l, j F Y H:i:s') . ' WITA';

        $departureDate = \Carbon\Carbon::parse(
            $invoice->invoiceDetails[0]->service->departure_date
        )->locale('id')->translatedFormat('l, j F Y');

        $price = number_format($invoice->invoiceDetails[0]->price, 0, ',', '.');
        $price = 'Rp. ' . $price . ',-';

        $accounts = '';
        foreach ($invoice->company->accounts as $account) {
            $accounts .= "- {$account->bank->label} {$account->account_number} (a/n {$account->account_name}) \n";
        }

        $message = <<<EOD
        {$timestamps}

        Terima kasih telah melakukan pendaftaran di {$invoice->company->name}. Berikut adalah detail pendaftaran anda:

        Nama: {$congregation->name}
        No. Invoice: {$invoice->id}
        No. Ref Invoice: {$invoice->ref_no}
        Paket: {$invoice->invoiceDetails[0]->service->packetType->label}
        Keberangkatan: {$departureDate}
        Harga: {$price}

        Silahkan melakukan pembayaran hanya melalui transfer ke rekening berikut:

        {$accounts}
        EOD;

        return $message;
    }
}
