<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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

            # recumpera os dados do carrinho do banco de dados.
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
     * @Route ("/pix/static/{order_ref}", name="app_static_pix")
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
     * @Route ("/pix/dynamic/{order_ref}", name="app_dynamic_pix")
     * @param $order_ref
     * @return Response
     */
    public function dynamicPix($order_ref): Response
    {

        try {
            # recumperando os dados do DB.
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
}