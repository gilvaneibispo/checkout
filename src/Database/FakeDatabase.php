<?php

class FakeDatabase
{

    public static function getDataSeller(): array
    {
        # dados fakes...
        # apenas para exemplo...
        return array(
            "name" => "Marta Maria Maju",
            "cpf" => "88067118060",
            "email" => "m.maju@mail.com",
            "city" => "Salvador",
            "key_pix" => "c329e315-1471-0001-t56h-2k1ei8feli92"
        );
    }

    public static function getDataPayer(): array
    {
        # dados fakes...
        # apenas para exemplo...
        return array(
            "name" => "João José Maré",
            "cpf" => "69395172002",
            "email" => "joao.jose@mail.com",
            "city" => "Feira de Santana",
            "key_pix" => "69395172002"
        );
    }

    public static function getDataProduct01(): array
    {
        return array(
            "title" => "Camisa Apoluh Azul - Tam: M",
            "desc" => "Camisa de algodão da Marcopolus na cor cinza.",
            "price" => 0.53,
            "ref" => "PROD00001"
        );
    }

    public static function getDataProduct02(): array
    {
        return array(
            "title" => "Máscara Mahalo - Branca",
            "desc" => "Máscara de proteção Covid-19 Mahaluh na branca",
            "price" => 0.7,
            "ref" => "PROD00002"
        );
    }

    public static function getDataPurchase(): array
    {

        $prod01 = self::getDataProduct01();
        $prod02 = self::getDataProduct02();

        # valor em porcentagem
        $offPercent = 15;

        $subtotal = $prod01['price'] + $prod02['price'];
        $off = ($subtotal * $offPercent) / 100;
        $off = (float)number_format($off, 2);

        return array(
            "items_num" => 2,
            "items" => array(
                $prod01,
                $prod02
            ),
            "order_ref" => self::makeOrderId(),
            "value" => array(
                "subtotal" => $subtotal,
                "off" => array(
                    'value' => $off,
                    'code' => "SUPERFIVE"
                ),
                "total" => $subtotal - $off,
            ),
            "seller" => self::getDataSeller(),
            "payer" => self::getDataPayer()
        );
    }

    public static function makeOrderId(): string
    {

        return "ORD" . str_pad(time(), 25, '0', STR_PAD_LEFT);
    }
}