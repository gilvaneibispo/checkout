<?php

use App\Exceptions\MissingFieldException;
use App\Util\Environment;
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
        # carrega apenas as informações necessárias para a implementação da classe.
        if (isset($data['seller']) && isset($data['payer'])) {
            $this->seller = $data['seller'];
            $this->payer = $data['payer'];
            $this->charge = $data['value'];
            $this->orderRef = $data['order_ref'];
        } else {
            throw new MissingFieldException();
        }
    }

    /**
     * Recumpera o caminho completo do certificado da Gerencianet informado
     * no .env!
     * @return string
     * @throws Exception
     */
    private static function getCertPath(): string
    {

        try {

            # recumpera o caminho do certificado nas variáveis de ambiente.
            $path = Environment::load("GN_CERT_PATH");
            return realpath(dirname(__DIR__, 2) . $path);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Constroi o array de configuração para a Gerencianet.
     * @return array
     * @throws Exception
     */
    private static function getInitialData(): array
    {
        try {

            return array(
                "client_id" => Environment::load("GN_CLIENT_ID"),
                "client_secret" => Environment::load("GN_SECRET_KEY"),
                "certificate" => self::getCertPath(),
                "sandbox" => false,
                "debug" => false,
                "timeout" => 30
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Implementa a função de pagamento da Interface para os casos de pagamentos via Pix
     * dinâminco utilizando a API da Gerencianet.
     * @return array
     * @throws GerencianetException|Exception
     */
    public function pay(): array
    {
        try {

            # define a ref da compra como o txid do Pix.
            $params = array(
                "txid" => $this->orderRef
            );

            # constroi o array de dados solicitado pela Gerencianet.
            $body = $this->arrDataBuild();

            # recumpera os dados de configuração da conexão com o Gerencianet.
            $config = self::getInitialData();

            # cria uma instância da API da Gerencianet
            $api = Gerencianet::getInstance($config);

            # cria uma cobraça via pix com a API da Gerencianet.
            $pix = $api->pixCreateCharge($params, $body);

            # em caso de sucesso...
            if ($pix["txid"]) {

                $params = [
                    "id" => $pix["loc"]["id"]
                ];

                # ... cria o QRCode da cobrança.
                $qrcode = $api->pixGenerateQRCode($params);

                # retorna o array padrão da interface.
                return array(
                    "payload" => $qrcode['qrcode'],
                    "location" => $qrcode['imagemQrcode']
                );
            } else {
                throw new Exception("Erro na solicitação com a Gerencianet!");
            }

        } catch (GerencianetException|Exception $e) {
            throw $e;
        }
    }

    /**
     * Constroi um arrau com os dados solicitados pela Gerencianet.
     * @return array
     * @throws Exception
     */
    private function arrDataBuild(): array
    {

        if (
            !isset($this->payer['cpf']) ||
            !isset($this->payer['name']) ||
            !isset($this->charge['total']) ||
            !isset($this->seller['key_pix'])
        ) {


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

        } else {
            throw new MissingFieldException();
        }
    }
}