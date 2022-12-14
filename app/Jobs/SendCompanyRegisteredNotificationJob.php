<?php

namespace App\Jobs;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCompanyRegisteredNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?Company $company;
    protected string $message = '';
    protected string $userPassword = '';

    public function __construct(Company $company)
    {
        $this->company = $company;
        $this->message = $this->generateMessage($company);
    }

    public function handle()
    {
        sleep(4);
        $formParams = [
            'token' => config('services.ruangwa.token'),
            'number' => $this->company->people[0]->wa ?? '',
            'message' => $this->message,
        ];

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://app.ruangwa.id/api/send_message', [
            'form_params' => $formParams
        ]);

        // $res = json_decode($response->getBody()->getContents());
    }

    protected function generateMessage($company) : string
    {
        $timestamps = \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, j F Y');

        $accounts = '';

        foreach ($company->accounts as $account) {
            $accounts .= "- {$account->bank->label} {$account->account_number} (a/n {$account->account_name}) \n";
        }

        $message = <<<EOD
        {$timestamps}

        Terima kasih telah melakukan pendaftaran perusahaan. Berikut adalah detail pendaftaran anda:

        Nama Perusahaan: {$company->name}
        No. Referensi: {$company->ref_no}

        Direktur: {$company->people[0]->name}
        Email: {$company->people[0]->email}
        Username: {$company->people[0]->user->username}
        No. HP: {$company->people[0]->phone}

        Akun Bank:
        {$accounts}

        EOD;

        return $message;
    }
}
