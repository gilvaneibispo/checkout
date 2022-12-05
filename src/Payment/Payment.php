<?php

class Payment
{

    private PaymentInterface $payment;

    public function __construct(PaymentInterface $payment)
    {
        $this->payment = $payment;
    }

    public function paymentHandler():array
    {

        return $this->payment->pay();
    }
}