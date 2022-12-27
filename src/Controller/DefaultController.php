<?php

namespace App\Controller;

# Objetos Symfony
use App\Exceptions\MissingFieldException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

# Classes Utils
use App\Util\Method;
use App\Util\LocalSession;
use App\Util\JsonView;

# Banco de dados
# Remover se usar DB real
use App\Database\FakeDatabase;

# Interface de pagamento...
use App\Model\Payment\Payment;

# Modos de pagamento...
use App\Model\Payment\Card;
use App\Model\Payment\Pix;
use App\Model\Payment\GnPix;
use App\Model\Payment\Ticket;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="app_index")
     */
    public function index(): Response
    {

        ####### START SERVER #############
        ## symfony server:start

        /* Inicialmente estou reaproveitando o front de outro projeto com jQuery, mas pretendo
         * reconstrui-lo com VueJS. Por isso estou itilizando a versão minima do Symfony, que
         * não contem o render, o que justifica a adaptação abaixo!
         */
        $html = file_get_contents(realpath(__DIR__ . '/../../html/index.html'));
        return new Response($html);
    }

    /**
     * @Route ("/shopping-cart", name="app_cart")
     * @return Response
     */
    public function shoppingCart(): Response
    {
        try {
            # recupera os dados do carrinho do banco de dados.
            $data = FakeDatabase::getDataPurchase();

            # salva o carrinho em sessão.
            LocalSession::saveOrder($data);

            # gera o json com o carrinho.
            return $this->json($data);
        } catch (\Exception $e) {
            return $this->json(JsonView::error($e));
        }
    }

    /**
     * @Route ("/pay/pix/static/{order_ref}", name="app_static_pix")
     * @param $order_ref
     * @return Response
     */
    public function staticPix($order_ref): Response
    {

        try {

            $data = LocalSession::getOrder($order_ref);

            # gerando o QRCode do Pix.
            $pay = new Payment(new Pix($data));
            $data = $pay->paymentHandler();

            return $this->json($data);

        } catch (\Exception $e) {
            return $this->json(JsonView::error($e));
        }
    }

    /**
     * @Route ("/pay/pix/dynamic/{order_ref}", name="app_dynamic_pix")
     * @param $order_ref
     * @return Response
     */
    public function dynamicPix($order_ref): Response
    {

        try {

            # recuperando os dados do DB.
            $data = LocalSession::getOrder($order_ref);

            # gerando o QRCode do Pix.
            $pay = new Payment(new GnPix($data));
            $data = $pay->paymentHandler();

            return $this->json($data);

        } catch (\Exception $e) {
            return $this->json(JsonView::error($e));
        }
    }

    /**
     * @Route ("/pay/card/start", name="app_credit_pay_start")
     * is_debit 1 ou 0 | true ou false | qualquer outro valor resulta em false.
     * @return void
     */
    public function creditPayStart(): Response
    {

        try {

            $data = FakeDatabase::getDataPurchase();
            $data['charge'] = array();

            # gerando o QRCode do Pix.
            $pay = new Card($data);
            $data = $pay->createSession();

            return $this->json($data);

        } catch (\Exception $e) {
            return $this->json(JsonView::error($e));
        }
    }

    /**
     * @Route ("/pay/card/confirm", name="app_credit_pay_confirm")
     * is_debit 1 ou 0 | true ou false | qualquer outro valor resulta em false.
     * @return void
     */
    public function creditPayConfirm(): Response
    {

        try {

            if ($data = Method::post()) {

                # verifica e trata os dados recebidos via POST.
                $data = self::postedDataValidate($data);

                # recupera os dados da venda.
                $order = LocalSession::getOrder($data['order_ref']);

                # diz se é uma cobrança com cartão salvo.
                $order['is_toked'] = false;

                # monta valores adicionais necessários para a cobrança
                $temp = array(
                    "value" => $order['value']['total'],
                    "description" => "Campra de {$order['items_num']} item(ns) em {$order['seller']['name']}.",
                    "toked" => $order['card']['toked'],
                );

                # cria um array com todos os valores necessários para a cobrança.
                $order['charge'] = array_merge($temp, $data);

                # Realiza a cobrança.
                $pay = new Payment(new Card($order));
                $resp = $pay->paymentHandler();

                if ($resp) {
                    if ($data['save_card']) {

                        $respPay = (array)$resp['payment_method'];
                        $respPay = (array)$respPay['card'];

                        if (FakeDatabase::saveCard($respPay)) {

                            return $this->json(
                                JsonView::show($respPay, 201),
                                201
                            );
                        }
                    }
                    return new Response();
                } else {
                    return $this->json(
                        JsonView::error(
                            new \Exception("Erro interno: Tente mais tarde!", 500)
                        )
                    );
                }

            } else {
                throw new \Exception("Method not alone!");
            }

        } catch (\Exception $e) {
            return $this->json(JsonView::error($e));
        }
    }


    /**
     * @Route ("/pay/card/saved", name="app_credit_pay_saved")
     * @return void
     */
    public function creditSavedPay(): Response
    {

        try {

            if ($data = Method::post()) {

                # verifica e trata os dados recebidos via POST.
                $data = self::postedDataValidate($data);

                # recupera os dados da venda.
                $order = LocalSession::getOrder($data['order_ref']);

                # diz se é uma cobrança com cartão salvo.
                $order['is_toked'] = false;

                # monta valores adicionais necessários para a cobrança
                $temp = array(
                    "value" => $order['value']['total'],
                    "description" => "Campra de {$order['items_num']} item(ns) em {$order['seller']['name']}.",
                    "toked" => $order['card']['toked'],
                );

                # cria um array com todos os valores necessários para a cobrança.
                $order['charge'] = array_merge($temp, $data);

                # Realiza a cobrança.
                $pay = new Payment(new Card($order));
                $resp = (array)$pay->paymentHandler();

                var_dump((array)((array)$resp['payment_method'])['card']);
                return new Response();
            } else {
                throw new \Exception("Method not alone!");
            }

        } catch (\Exception $e) {
            return $this->json(JsonView::error($e));
        }
    }

    /**
     * @param array $data
     * @return array
     * @throws MissingFieldException
     */
    private static function postedDataValidate(array $data): array
    {

        $installment = filter_var($data['installment'], FILTER_VALIDATE_INT);
        $orderRef = filter_var($data['order_ref'], FILTER_SANITIZE_STRING);
        $encryptedCard = filter_var($data['encrypted_card'], FILTER_SANITIZE_STRING);
        $toSaveCard = filter_var($data['save_card'], FILTER_VALIDATE_BOOL);
        $type = filter_var($data['type'] ?? false, FILTER_SANITIZE_STRING);

        if ($type == "DEBIT" || $type == "CREDIT") {
            $type = mb_strtoupper($data['type']) == "DEBIT" ? Card::DEBIT_CARD : Card::CREDIT_CARD;
        } else {
            $type = false;
        }

        if (!$installment || !$orderRef || !$encryptedCard || !is_bool($toSaveCard) || !$type) {
            throw new MissingFieldException();
        }

        return array(
            'installments' => $installment,
            'order_ref' => $orderRef,
            'encrypted_card' => $encryptedCard,
            'save_card' => $toSaveCard,
            'type' => $type
        );
    }
}