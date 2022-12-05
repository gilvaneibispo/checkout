<?php

use App\Exceptions\MissingFieldException;
use Gerencianet\Exception\GerencianetException;
use Gerencianet\Gerencianet;

class GnPix implements PaymentInterface
{
    private array $seller;
    private array $payer;
    private array $charge;
    private string $orderRef;

    public function __construct($data)
    {
        if (isset($data['seller']) && isset($data['payer'])) {
            $this->seller = $data['seller'];
            $this->payer = $data['payer'];
            $this->charge = $data['value'];
            $this->orderRef = $data['order_ref'];
        } else {
            throw new MissingFieldException();
        }
    }

    public static function getInitialData(): array
    {
        return array(
            "client_id" => "Client_Id_55730937ab7b3c9bef234074e91adcc64953bca0",
            "client_secret" => "Client_Secret_565b238d2af25ff5eb852c06d4c87dfb3873d921",
            "certificate" => realpath(dirname(__DIR__, 2) . '/var/cert/exdev_pro.pem'),//realpath(__DIR__ . "/productionCertificate.p12"), // Absolute path to the certificate in .pem or .p12 format
            "sandbox" => false,
            "debug" => false,
            "timeout" => 30
        );
    }

    public function pay(): array
    {
        $params = array(
            "txid" => $this->orderRef
        );

        # constroi o array de dados solicitado pela Gerencianet.
        $body = $this->arrDataBuild();

        try {

            $api = Gerencianet::getInstance(self::getInitialData());

            $pix = $api->pixCreateCharge($params, $body);

            if ($pix["txid"]) {

                $params = [
                    "id" => $pix["loc"]["id"]
                ];

                $qrcode = $api->pixGenerateQRCode($params);

                return array(
                    "payload" => $qrcode['qrcode'],
                    "location" => $qrcode['imagemQrcode']
                );
            } else {
                echo "<pre>" . json_encode($pix, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</pre>";
                return array();
            }
        } catch (GerencianetException|Exception $e) {
            throw $e;
        }
    }

    private function arrDataBuild(): array
    {
        $calendar = array(
            # Vida útil da carga especificada em segundos a partir da data de criação.
            "expiracao" => 3600
        );

        # passar a receber por parâmetro
        $payer = array(
            "cpf" => $this->payer['cpf'],
            "nome" => $this->payer['name']
        );

        $value = array(
            "original" => (string)$this->charge['total']
        );

        # A chave pix precisa ser a mesma registrado na Gerencianet.
        return array(
            "calendario" => $calendar,
            "devedor" => $payer,
            "valor" => $value,
            "solicitacaoPagador" => "Referência " . $this->orderRef,
            "chave" => $this->seller['key_pix'],
        );
    }
}