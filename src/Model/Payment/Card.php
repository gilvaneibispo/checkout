<?php

namespace App\Model\Payment;

use App\Exceptions\MissingFieldException;
use App\Util\Environment;
use App\Interfaces\PaymentWithSessionInterface;
use \Exception;

class Card implements PaymentWithSessionInterface
{
    const CREDIT_CARD = "CREDIT_CARD";
    const DEBIT_CARD = "DEBIT_CARD";

    private array $seller;
    private array $payer;
    private array $charge;
    private string $token;
    private string $orderRef;
    private bool $isCardToked;

    /**
     * @param $data
     * @throws Exception
     */
    public function __construct($data)
    {

        try {
            if (isset($data['seller']) && isset($data['payer'])) {
                $this->seller = $data['seller'];
                $this->charge = $data['charge'];
                $this->payer = $data['payer'];
                $this->orderRef = $data['order_ref'];
                $this->token = Environment::load("PS_ACCESS_TOKEN");
                $this->isCardToked = $data['is_toked'] ?? false;
            } else {
                throw new MissingFieldException();
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function createSession(): array
    {

        try {

            return PagSeguro::createSession($this->seller['email'], $this->token);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function pay(): array
    {
        try {
            return PagSeguro::chargeExecute($this->buildArrayPSCharge(), $this->token);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function buildArrayPSCharge(): array
    {

        if (!$this->isCardToked) {
            $card = array(
                "store" => true,
                "encrypted" => $this->charge['encrypted_card']
            );
        } else {
            $card = array(
                "id" => $this->charge['toked']
            );
        }

        return array(
            "reference_id" => "Referência {$this->orderRef}",
            "description" => $this->charge['description'],
            "amount" => array(
                "value" => (int)($this->charge['value'] * 100),
                "currency" => "BRL"
            ),
            "payment_method" => array(
                "type" => $this->charge['type'],
                "installments" => $this->charge['installments'],
                "capture" => true,
                "soft_descriptor" => "sellervirtual",
                "card" => $card
            ),
            "notification_urls" => array()
        );
    }
}