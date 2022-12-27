<?php

namespace App\Model\Payment;

use App\Util\CURLRequest;
use \Exception;

class PagSeguro
{

    const URL_CHARGES = "https://sandbox.api.pagseguro.com/charges";
    const URL_PUBLIC_KEY = "https://sandbox.api.pagseguro.com/public-keys";
    const URL_SESSION_START = "https://ws.sandbox.pagseguro.uol.com.br/v2/sessions?email={{email}}&token={{token}}";

    /**
     * @param $sellerEmail
     * @param $token
     * @return array
     * @throws Exception
     */
    public static function createSession($sellerEmail, $token): array
    {
        try {
            $publicKey = CURLRequest::post(self::URL_PUBLIC_KEY, $token, array("type" => "card"));

            //$url = str_replace("{{email}}", $sellerEmail, self::URL_SESSION_START);
            //$url = str_replace("{{token}}", $token, $url);
            $response = CURLRequest::post(
                self::urlDataReplace(array(
                    "email" => $sellerEmail,
                    "token" => $token
                )), $token, null);

            if (isset($publicKey['public_key']) && isset($response['id'])) {

                return array(
                    "public_key" => $publicKey['public_key'],
                    "key_created_at" => $publicKey['created_at'],
                    "session_id" => $response['id']
                );
            } else {
                throw new Exception("PagSeguro does not respond at the moment");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    private static function urlDataReplace(array $data): string
    {


        if (count($data) > 0) {

            $url = self::URL_SESSION_START;

            foreach ($data as $key => $val) {
                $url = str_replace("{{" . $key . "}}", $val, $url);
            }

            return $url;
        }

        return "";
    }

    /**
     * @return void
     * @throws Exception
     */
    public static function chargeExecute($data, $token): array
    {

        try {
            $publicKey = CURLRequest::post(self::URL_CHARGES, $token, $data);

            return $publicKey;
        } catch (Exception $e) {
            throw $e;
        }
    }
}