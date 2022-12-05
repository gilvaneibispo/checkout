<?php

use Symfony\Component\HttpFoundation\Session\Session;

class LocalSession
{

    public static function saveOrder(array $order)
    {

        if (!isset($order['order_ref'])) {
            throw new \App\Exceptions\MissingFieldException();
        }

        $session = new Session();
        $session->set($order['order_ref'], $order);
    }

    public static function getOrder($orderRef)
    {

        $session = new Session();
        $order = $session->get($orderRef);

        if ($order != NULL) {
            return $order;
        }

        return false;
    }
}