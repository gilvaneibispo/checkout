<?php

class Payment
{

    public function paymentHandler(PaymentInterface $pay):array
    {

        return $pay->pay();
    }
}