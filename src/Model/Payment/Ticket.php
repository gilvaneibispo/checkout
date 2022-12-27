<?php

namespace App\Model\Payment;

use App\Exceptions\MissingFieldException;
use App\Interfaces\PaymentInterface;

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