<?php

namespace App\Model\Payment;

use App\Exceptions\MissingFieldException;
use App\Util\QRCodeGeneration;
use App\Interfaces\PaymentInterface;

/*
 * https://www.bcb.gov.br/content/estabilidadefinanceira/pix/Regulamento_Pix/II_ManualdePadroesparaIniciacaodoPix.pdf
 */

class Pix implements PaymentInterface
{
    private array $seller;
    private string $orderRef;
    private array $charge;

    public function __construct(array $data)
    {
        if (isset($data['seller']) && isset($data['payer'])) {
            $this->seller = $data['seller'];
            $this->charge = $data['value'];
            $this->orderRef = $data['order_ref'];
        } else {
            throw new MissingFieldException();
        }
    }

    /**
     * @return array
     * @throws \Mpdf\QrCode\QrCodeException
     * @throws \Exception;
     */
    public function pay(): array
    {
        try {
            $payload = new PixPayload();

            if (
                !isset($this->seller['name']) ||
                !isset($this->seller['key_pix']) ||
                !isset($this->seller['city']) ||
                !isset($this->orderRef) ||
                !isset($this->charge['total'])
            ) {
                throw new MissingFieldException();
            }

            $payload->setSellerName($this->seller['name'])

                //->setDescription("Compra com ")

                # existe um erro quando a chave é um CPF
                ->setPixKey($this->seller['key_pix'])
                ->setSellerCity($this->seller['city'])
                ->setTxId($this->orderRef)
                ->setAmount($this->charge['total']);

            $strPay = $payload->createPayload();
            $qrcode = QRCodeGeneration::getBase64($strPay, QRCodeGeneration::IMG_SIZE_MEDIUM);

            return array(
                "payload" => $strPay,
                "location" => $qrcode
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}