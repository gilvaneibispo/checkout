<?php

use App\Exceptions\MissingFieldException;

class Card implements PaymentWithSessionInterface
{

    private array $seller;
    private array $charge;

    public function __construct($data)
    {
        if (isset($data['seller']) && isset($data['payer'])) {
            $this->seller = $data['seller'];
            $this->charge = $data['payer'];
        } else {
            throw new MissingFieldException();
        }
    }

    public function createSession(): array
    {
        // TODO: Implement createSession() method.
        return array();
    }

    public function pay(): array
    {
        // TODO: Implement pay() method.
        return array();
    }


}