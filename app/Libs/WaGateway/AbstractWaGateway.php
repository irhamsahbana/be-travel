<?php

namespace App\Libs\WaGateway;

use App\Libs\Contracts\WaSendMessageContract;

abstract class AbstractWaGateway implements WaSendMessageContract
{
    protected string $companyId = '';
    protected string $token = '';
    protected string $message = '';
    protected string $number = '';

    public function setMessage(string $message): WaSendMessageContract
    {
        $this->message = $message;
        return $this;
    }

    public function setNumber(string $number): WaSendMessageContract
    {
        $this->number = $number;
        return $this;
    }

    abstract public function setToken(string $companyId): WaSendMessageContract;
    abstract public function sendMessage(): object;
}
