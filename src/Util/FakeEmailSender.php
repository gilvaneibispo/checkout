<?php

namespace App\Util;

class FakeEmailSender
{

    public static function send($email, $msg, $title): bool
    {
        # aqui pode ser implementado um método real e enviou de email,
        # usando o PHPMailer, por exemplo!
        return true;
    }
}