<?php
use \App\Util\QRCodeGeneration;
class Pix implements PaymentInterface
{
    private array $seller;
    private array $charge;

    public function __construct(array $seller, array $charge)
    {
        $this->seller = $seller;
        $this->charge = $charge;
    }

    public function pay(): array
    {
        try {
            $payload = new PixPayload();

            $payload->setPixKey($this->seller['email'])
                //->setDescription("Compra com ")
                ->setSellerName($this->seller['name'])
                ->setSellerCity($this->seller['city'])
                ->setTxId($this->charge['ref'])
                ->setAmount($this->charge['price']);

            $strPay = $payload->createPayload();
            $qrcode = QRCodeGeneration::getBase64($strPay, QRCodeGeneration::IMG_SIZE_MEDIUM);

            return array(
                "payload" => $strPay,
                "location" => $qrcode
            );
        }catch (\Exception $e){
            throw $e;
        }
    }
}