<?php

use App\Exceptions\MissingFieldException;

class Ticket implements PaymentInterface
{

    private array $seller;
    private array $charge;

    public function __construct(array $data)
    {
        if (isset($data['seller']) && isset($data['payer'])) {
            $this->seller = $data['seller'];
            $this->charge = $data['payer'];
        } else {
            throw new MissingFieldException();
        }
    }

    public function pay(): array
    {
        // TODO: Implement pay() method.
        return array();
    }
}