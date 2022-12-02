<?php

namespace App\Util;

use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;
use Mpdf\QrCode\QrCodeException;

class QRCodeGeneration
{

    const IMG_SIZE_MIN = 280;
    const IMG_SIZE_MEDIUM = 420;
    const IMG_SIZE_LARGE = 840;

    /**
     * @param string $payload
     * @param int $size
     * @return string
     * @throws QrCodeException
     */
    private static function buildImage(string $payload, int $size): string
    {

        try {
            $qrCode = new QrCode($payload);
            $outPng = new Output\Png();
            return $outPng->output($qrCode, $size);
        } catch (QrCodeException $e) {
            throw $e;
        }
    }

    /**
     * @throws QrCodeException
     */
    public static function getBase64(string $payload, $size): string
    {

        try {
            $base64 = "data:image/png;base64,";
            $base64 .= base64_encode(self::buildImage($payload, $size));
            return $base64;
        } catch (QrCodeException $e) {
            throw $e;
        }
    }
}