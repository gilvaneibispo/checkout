<?php

interface PaymentWithSessionInterface extends PaymentInterface
{

    public function createSession(): array;
}