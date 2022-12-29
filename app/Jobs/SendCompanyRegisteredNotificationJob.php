<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Libs\WaGateway\Wablas;

use App\Models\Company;

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

        $waGateway = (new Wablas())
            ->setToken('97f2d9af-6c15-4757-bb35-2562175708b7')
            ->setNumber($this->company->people[0]->wa ?? '')
            ->setMessage($this->message);

        $waGateway->sendMessage();
    }

    protected function generateMessage($company): string
    {
        $timestamps = \Carbon\Carbon::now()->locale('id')->translatedFormat('l, j F Y H:i:s');

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
