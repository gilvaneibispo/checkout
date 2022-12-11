<?php

namespace App\Controller;

use app\src\Method;
use App\Util\Environment;
use CURLRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use \LocalSession;
use \FakeDatabase;

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
            return $this->json(array(
                "error" => $e->getMessage()
            ));
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
            $pay = new \Payment(new \Pix($data));
            $data = $pay->paymentHandler();

            return $this->json($data);

        } catch (\Exception $e) {
            return $this->json(array(
                "error" => $e->getMessage()
            ));
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
            $pay = new \Payment(new \GnPix($data));
            $data = $pay->paymentHandler();

            return $this->json($data);

        } catch (\Exception $e) {
            return $this->json(array(
                "error" => $e->getMessage()
            ));
        }
    }

    /**
     * @Route ("/pay/card/start", name="app_credit_pay_start")
     * @param $type
     * is_debit 1 ou 0 | true ou false | qualquer outro valor resulta em false.
     * @return void
     */
    public function creditPayStart($type): Response
    {

        $type = filter_var($type, FILTER_SANITIZE_STRING);

        try {

            $data = FakeDatabase::getDataPurchase();

            # gerando o QRCode do Pix.
            $pay = new \Card($data);
            $data = $pay->createSession();

            return $this->json($data);

        } catch (\Exception $e) {
            return $this->json(array(
                "error" => $e->getMessage()
            ));
        }
    }

    /**
     * @Route ("/pay/card/confirm", name="app_credit_pay_confirm")
     * @param $type
     * is_debit 1 ou 0 | true ou false | qualquer outro valor resulta em false.
     * @return void
     */
    public function creditPayConfirm($type): Response
    {

        try {

            $type = filter_var($type, FILTER_SANITIZE_STRING);

            if (!(mb_strtoupper($type) == "DEBIT" || mb_strtoupper($type) == "CREDIT")) {
                throw new \Exception("The card type entered is not valid [Req.: credit | debit]!");
            }

            if ($data = Method::post()) {

                $installment = filter_var($data['installment'], FILTER_VALIDATE_INT);
                $orderRef = filter_var($data['order_ref'], FILTER_SANITIZE_STRING);
                $encryptedCard = filter_var($data['encrypted_card'], FILTER_SANITIZE_STRING);

                $order = LocalSession::getOrder($orderRef);

                $type = mb_strtoupper($type) == "DEBIT" ? \Card::DEBIT_CARD : \Card::CREDIT_CARD;

                $order['charge'] = array(
                    "installments" => $installment,
                    "value" => $order['value']['total'],
                    "description" => "Campra de {$order['items_num']} item(ns) em {$order['seller']['name']}.",
                    "encrypted_card" => $encryptedCard,
                    "type" => $type
                );

                $pay = new \Payment(new \Card($order));
                $resp = $pay->paymentHandler();

                var_dump($resp);
                return new Response();
            } else {
                throw new \Exception("Method not alone!");
            }

        } catch (\Exception $e) {
            return $this->json(array(
                "error" => $e->getMessage()
            ));
        }
    }
}