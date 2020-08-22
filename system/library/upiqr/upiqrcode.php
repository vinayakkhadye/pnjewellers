<?php

//
// [upiqrcode.php] is for Integrating Open Source Library [ENDROID} to Generate UPI QR CODE as required by
//UPI - [ Unified Payments Interface ] A Service From NPCI - [ National Payments Corporation Of India ]
//as a Payment Gateway Method in OPENCART - 3.0.2.0 - 3.0.3.0 - 3.0.3.1 - 3.0.3.2 - 3.0.3.3
//
//Developed By:
//Mr. TARAKESHWAR GAJAM
//ABCD PAYMENT SERVICES, #4-1-71/416, Sai Durga Gardens, Nacharam Main Road, Medchal-Malkajgiri District, Telangana State - 500076, India.
//URL: https://www.abcd-pay.com , E-Mail : admin@abcd-pay.com, Mobile : 0091-0-8106877688.
//
//File Path = "system/library/upiqr/upiqrcode.php" //i.e. DIR_SYSTEM."library/upiqr/upiqrcode.php";
//NPCI-UPI Logo Image Path = "system/library/upiqr/upilogo/upi-logo.png" //i.e. DIR_SYSTEM."library/upiqr/upilogo/upi-logo.png";
//Generated UPI-QR Images Stored in Directory Path = "image/upiqrimage/" //i.e. DIR_IMAGE."image/upiqrimage/"
//

require_once(__DIR__."/phpqrcode/vendor/autoload.php");

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;

function getupiqrcode($upiid,$upipayee,$amount,$txnremarks,$txnreference,$imageid, bool $addlabeltext){
    $upisyntax1 = "upi://pay?pa=";
    $upisyntax2 = "&pn=";
    $upisyntax3 = "&cu=INR&am=";
    $upisyntax4 = "&tn=";
    $upisyntax5 = "&tr=";

    $text = $upisyntax1.$upiid.$upisyntax2.$upipayee.$upisyntax3.$amount.$upisyntax4.$txnremarks.$upisyntax5.$txnreference;
    $labeltext = "UPI-ID     : ".$upiid."\nPayee      : ".$upipayee."\nAmount  : INR. ".$amount."\nRemarks : ".$txnremarks;
    $logopath = __DIR__."/upilogo/upi-logo.png";

    $imagedir = DIR_IMAGE."upiqrimage";
    if (!file_exists($imagedir)) {
        mkdir($imagedir, 0755, true);
    }
    $imagepath = $imagedir.'/'.$imageid.'.png';

    $qrCode = new QrCode($text);
    $qrCode->setSize(250);
    $qrCode->setWriterByName('png');
    $qrCode->setEncoding('UTF-8');
    $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
    $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
    $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
    $qrCode->setLogoPath($logopath);
    $qrCode->setLogoSize(48);
    if ($addlabeltext == TRUE){
        $qrCode->setMargin(75);
        $qrCode->setLabel($labeltext, 12, null, LabelAlignment::CENTER(),['t' => 0, 'r' => 10, 'b' => 60, 'l' => 10]);
    }
    $qrCode->setRoundBlockSize(true);
    $qrCode->setValidateResult(false);
    $qrCode->setWriterOptions(['exclude_xml_declaration' => true]);
    $qrCode->writeFile($imagepath);
    if (file_exists($imagepath)) {
        chmod($imagepath, 0755);
    }
   
    $response = new QrCodeResponse($qrCode);
    unset($response,$qrCode);
    return $imagepath;
}