<?php

namespace App\Jobs;

use App\Libs\WaGateway\Wablas;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Invoice;
use App\Models\Person;

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
        $companyId = $this->invoice?->company_id;
        $waGateway = (new Wablas)
            ->setToken($companyId)
            ->setNumber($this->congregation->wa ?? '')
            ->setMessage($this->message);

        $response = $waGateway->sendMessage();
    }

    protected function generateMessage(Invoice $invoice, Person $congregation): string
    {
        // Jumat, 16 Desember 2022 10:00:00
        $timestamps = \Carbon\Carbon::now()->locale('id')->translatedFormat('l, j F Y H:i:s') . ' WITA';

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
