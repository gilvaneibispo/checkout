<?php

namespace App\Interfaces;

interface PaymentInterface
{
    public function __construct(array $data);

    public function pay(): array;
}