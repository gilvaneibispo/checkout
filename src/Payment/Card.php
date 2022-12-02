<?php

class Card implements PaymentInterface
{
    private string $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function pay(): array
    {
        // TODO: Implement pay() method.
        return array();
    }
}