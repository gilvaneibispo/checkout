<?php

namespace App\Interfaces;

interface PaymentWithSessionInterface extends PaymentInterface
{

    public function createSession(): array;
}