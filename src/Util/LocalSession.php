<?php

namespace App\Util;

use Symfony\Component\HttpFoundation\Session\Session;
use App\Exceptions\MissingFieldException;

class LocalSession
{

    /**
     * @param array $order
     * @return void
     * @throws MissingFieldException
     */
    public static function saveOrder(array $order)
    {

        if (!isset($order['order_ref'])) {
            throw new MissingFieldException();
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