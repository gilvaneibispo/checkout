<?php

interface PaymentInterface
{
    public function __construct(array $data);

    public function pay(): array;
}