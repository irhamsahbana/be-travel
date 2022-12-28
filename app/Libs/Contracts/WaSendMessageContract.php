<?php

namespace App\Libs\Contracts;

interface WaSendMessageContract
{
    public function setToken(string $companyId) : WaSendMessageContract;
    public function setMessage(string $message) : WaSendMessageContract;
    public function setNumber(string $number) : WaSendMessageContract;
    public function sendMessage() : object;

}
