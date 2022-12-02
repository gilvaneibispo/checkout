<?php

class FakeDatabase
{

    public static function getDataSeller(): array
    {
        return array(
            "name" => "Gilvanei Pereira Bispo",
            "email" => "gilvanei.pb@gmail.com",
            "city" => "Feira de Santana",
            "key_pix" => "gilvanei.pb@gmail.com"
        );
    }

    public static function getDataProduct(): array{
        return array(
            "title" => "Camisa Apolo Azul - Tam: M",
            "price" => 56.90,
            "ref" => "PROD00001"
        );
    }
}