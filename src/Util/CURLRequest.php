<?php

class CURLRequest
{

    /**
     * @return string[]
     * @throws Exception
     */
    private static function buildHeardAuth($token): array
    {
        return array(
            "Authorization: " . $token,
            "Content-Type: application/json",
            "x-api-version: 1.0",
        );
    }

    public static function post($url, $token, $data)
    {

        $headers = @self::buildHeardAuth($token);
        $curl = curl_init();


        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_PROXY => 'proxy.uefs.br:3128',
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        ));

        $response = curl_exec($curl);

        if((substr($response, 0, 5) == "<?xml")){
            $xml = simplexml_load_string($response);
            $response = json_encode($xml);
        }

        curl_close($curl);

        return (array) json_decode($response);


        #var_dump($response);





        $headers = self::buildHeardAuth();
        $curl = curl_init($url);
        #curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if (is_array($data))
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

        curl_setopt($curl, CURLOPT_PROXY, 'proxy.uefs.br:3128');
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = (string)curl_exec($curl);
        curl_close($curl);

        var_dump($response);
        die();
    }


    public static function get($url, $token)
    {
        $headers = array(
            "Authorization: {$token}",
            "Content-Type: application/json",
            "x-api-version: 1.0",
        );

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_PROXY, 'proxy.uefs.br:3128');
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response);
    }
}