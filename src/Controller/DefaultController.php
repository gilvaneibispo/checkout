<?php

namespace App\Controller;

use PixPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="app_index")
     */
    public function index(): Response
    {

        ####### START SERVER #############
        ## symfony server:start

        try {

            $pay = new \Payment();

        } catch (\Exception $e) {

            throw $e;
        }
    }

    public function staticPix(): Response
    {

        $pay = new \Payment();
        $seller = \FakeDatabase::getDataSeller();
        $charge = \FakeDatabase::getDataProduct();
        $data = $pay->paymentHandler(new \Pix($seller, $charge));

        return $this->json($data);
    }
}