<?php

namespace App\Util;

class Method
{

    public static function del(){

        if ($_SERVER['REQUEST_METHOD'] === "DELETE") {

            $_DEL = self::getPhpInputFile();

            return (array) json_decode($_DEL);
        }

        return false;
    }

    public static function put(){

        if ($_SERVER['REQUEST_METHOD'] === "PUT")
        {
            $_PUT = self::getPhpInputFile();

            return (array) json_decode($_PUT);
        }

        return false;
    }

    public static function post(){

        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            $_POST = self::getPhpInputFile();

            return (array) json_decode($_POST);
        }

        return false;
    }

    public static function header(){

        if ($_SERVER['REQUEST_METHOD'] === "HEADER") {

            $_HEADER = self::getPhpInputFile();

            return (array) json_decode($_HEADER);
        }

        return false;
    }

    private static function getPhpInputFile(){

        return file_get_contents(
            'php://input',
            false,
            null,
            0,
            $_SERVER['CONTENT_LENGTH']
        );
    }
}
