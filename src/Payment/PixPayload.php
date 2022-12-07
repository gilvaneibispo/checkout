<?php

/*
 * Esta classe constroi o payload de um pix estático, que pode ser usado na função
 * capia e cola dos APPs de bancos, seguindo a documentação do PIX do Banco Central.
 * O payload do Pix é uma string longa com varios conjuntos de dados concatenados,
 * onde cada conjunto é composto por ID, tamanho e valor (ID e tamanho conterá sempre
 * dois caracteres). Ex.: O conjunto 'Payload Format Indicator' possui ID = '00',
 * Size = 2 e Val = '01', logo seu conjunto seria 000201
 */
class PixPayload
{

    private string $key;
    private string $description;
    private string $sellerName;
    private string $sellerCity;
    private string $txId;
    private float $amount;
    private $paymentUniq = false;
    private string $url;

    public function __construct()
    {
        $this->description = "";
    }

    /**
     * Cria uma string com 2 caracteres apartir do valor fornecido preenchendo
     * com zeros os caracteres ausentes. Depois concatena com o $id.
     * Ex. 01: $val = 3 -> "03";
     * Ex. 02: $val = 21 -> "21"
     * @param string $id
     * @param string $val
     * @return string $id.$size.$val
     */
    private static function buildDataset(string $id, string $val): string
    {
        # Preenche a string $val para o tamanho 2 com zero a esquerda, caso ela
        # tenha tamanho menor que 2;
        $val_padded = str_pad(mb_strlen($val), 2, '0', STR_PAD_LEFT);

        # retorna a composição determinada pelo BC.
        return $id . $val_padded . $val;
    }

    /**
     *
     * @return string
     */
    private static function payloadFormatIndicator(): string
    {

        # O conjunto de dados Payload Format Indicator é definido pelo B. Cebtral, com:
        # ID = '00';
        # Tam = 2;
        # Val = '01';
        return self::buildDataset(PixCodes::PAYLOAD_FORMAT_INDICATOR, '01');
    }

    private function merchantAccountInformation(): string
    {
        # O conjunto de dados Merchant Account Information é compostos por dois subconjuntos
        # obrigatórios, GUI e chave, e dois opcionais, infoAdicional e fss.
        # Os obrigatórios são definidos como segue:
        # ID = '26';
        # Tam = 99;
        # Val = [GUI ou chave];
        # - - - - - - - - - - - - - - - - -
        # Definições do subconjunto GUI:
        # ID = '00';
        # Tam = 14;
        # Val = 'br.gov.bcb.pix';
        $gui = self::buildDataset(PixCodes::MERCHANT_ACCOUNT_INFORMATION_GUI, 'br.gov.bcb.pix');

        # Definições do subconjunto chave:
        # ID = '01';
        # Tam = [len(val)];
        # Val = [pix key merchant];
        $key = self::buildDataset(PixCodes::MERCHANT_ACCOUNT_INFORMATION_KEY, $this->key);
        $key = (mb_strlen($this->key) > 0 ? $key : '');

        # Definições do subconjunto adicional (opcional):
        # ID = '02';
        # Tam = [99 - (len(gui) + len(key))];
        # Val = [description];
        $addMaxSize = 99 - (strlen($key) + strlen($gui) + 4);

        # Trunca a informação adicional se ela for mais que os suportado.
        if(strlen($this->description) > $addMaxSize){
            $this->description = substr($this->description, 0, $addMaxSize-1);
        }

        $infoAdditional = self::buildDataset(PixCodes::MERCHANT_ACCOUNT_INFORMATION_ADDITIONAL, $this->description);
        $infoAdditional = (mb_strlen($this->description) > 0 ? $infoAdditional : '');

        //$url = (mb_strlen($this->url) > 0 ?
        //    self::getRealValue(PixCodes::MERCHANT_ACCOUNT_INFORMATION_URL, $this->url) : '');
        //$url = str_replace('https://', '', $url);

        return self::buildDataset(PixCodes::MERCHANT_ACCOUNT_INFORMATION, $gui . $key . $infoAdditional);
    }

    private function getAdditionalField()
    {

        $id = self::buildDataset(PixCodes::ADDITIONAL_DATA_FIELD_TEMPLATE_TXID, $this->txId);
        return  self::buildDataset(PixCodes::ADDITIONAL_DATA_FIELD_TEMPLATE, $id);
    }

    # pode ir para outra classe específica...
    private function getCRC16($payload)
    {
        //ADICIONA DADOS GERAIS NO PAYLOAD
        $payload .= PixCodes::CRC16 . '04';

        //DADOS DEFINIDOS PELO BACEN
        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        //CHECKSUM
        if (($length = mb_strlen($payload)) > 0) {
            for ($offset = 0; $offset < $length; $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) $resultado ^= $polinomio;
                    $resultado &= 0xFFFF;
                }
            }
        }

        //RETORNA CÓDIGO CRC16 DE 4 CARACTERES
        return PixCodes::CRC16 . '04' . strtoupper(dechex($resultado));
    }

    private function getUniqPayment(): string
    {
        return (mb_strlen($this->paymentUniq) > 0 ? self::buildDataset(PixCodes::POINT_INIT_METHOD, '12') : '');
    }

    public function createPayload(): string
    {

        $payload = self::payloadFormatIndicator();
        $payload .= $this->getUniqPayment();
        $payload .= $this->merchantAccountInformation();
        $payload .= self::buildDataset(PixCodes::MERCHANT_CATEGORY_CODE, '0000');
        $payload .= self::buildDataset(PixCodes::TRANSACTION_CURRENCY, '986');
        $payload .= self::buildDataset(PixCodes::TRANSACTION_AMOUNT, $this->amount);
        $payload .= self::buildDataset(PixCodes::COUNTRY_CODE, 'BR');
        $payload .= self::buildDataset(PixCodes::MERCHANT_NAME, $this->sellerName);
        $payload .= self::buildDataset(PixCodes::MERCHANT_CITY, $this->sellerCity);
        $payload .= $this->getAdditionalField();
        $payload .= $this->getCRC16($payload);

        return $payload;
    }



    ################################
    ##  SETTERS
    ################################


    public function setPixKey($key): PixPayload
    {
        $this->key = $key;
        return $this;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function setPaymentUniq($uniqPay)
    {
        $this->paymentUniq = $uniqPay;
        return $this;
    }

    public function setDescription(string $description): PixPayload
    {
        $this->description = $description;
        return $this;
    }

    public function setSellerName(string $sellerName): PixPayload
    {
        $this->sellerName = $sellerName;
        return $this;
    }

    public function setSellerCity(string $sellerCity): PixPayload
    {
        $this->sellerCity = $sellerCity;
        return $this;
    }

    public function setTxId(string $id): PixPayload
    {
        $this->txId = $id;
        return $this;
    }

    public function setAmount(float $amount): PixPayload
    {
        $this->amount = (string)number_format($amount, 2, '.', '');
        return $this;
    }
}