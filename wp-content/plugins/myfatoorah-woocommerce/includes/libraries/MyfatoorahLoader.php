<?php

/**
 * This file is responsible for updating the MyFatoorah Library everyday 
 *
 * MyFatoorah offers a seamless business experience by offering a technology put together by our tech team. This enables smooth business operations involving sales activity, product invoicing, shipping, and payment processing. MyFatoorah invoicing and payment gateway solution trigger your business to greater success at all levels in the new age world of commerce. Leverage your sales and payments at all e-commerce platforms (ERPs, CRMs, CMSs) with transparent and slick applications that are well-integrated into social media and telecom services. For every closing sale click, you make a business function gets done for you, along with generating factual reports and statistics to fine-tune your business plan with no-barrier low-cost.
 * Our technology experts have designed the best GCC E-commerce solutions for the native financial instruments (Debit Cards, Credit Cards, etc.) supporting online sales and payments, for events, shopping, mall, and associated services.
 *
 * Created by MyFatoorah http://www.myfatoorah.com/
 * Developed By tech@myfatoorah.com
 * Date: 02/05/2023
 * Time: 12:00
 *
 * API Documentation on https://myfatoorah.readme.io/docs
 * Library Documentation and Download link on https://myfatoorah.readme.io/docs/php-library
 * 
 * @author    MyFatoorah <tech@myfatoorah.com>
 * @copyright 2021 MyFatoorah, All rights reserved
 * @license   GNU General Public License v3.0
 */
$mfVersion = '2.2';

if (!in_array('curl', get_loaded_extensions())) {
    trigger_error('Kindly install and enable PHP cURL extension in your server.', E_USER_WARNING);
    return;
}

$mfLibFolder = __DIR__;
$mfLibFile   = $mfLibFolder . '/MyfatoorahLibrary.php';
if (!is_writable($mfLibFile)) {
    $mfError = 'To enable MyFatoorah auto-update, kindly give the write/read permissions to the library folder ' . __DIR__ . ' on your server and its files.';
    trigger_error($mfError);
    return;
}

if ((time() - filemtime($mfLibFile)) < 86400) {
    return;
}

touch($mfLibFile);
try {
    $mfCurl = curl_init("https://portal.myfatoorah.com/Files/API/php/library/$mfVersion/MyfatoorahLibrary.txt");
    curl_setopt_array($mfCurl, array(
        CURLOPT_RETURNTRANSFER => true,
    ));

    $mfResponse = curl_exec($mfCurl);
    $mfHttpCode = curl_getinfo($mfCurl, CURLINFO_HTTP_CODE);
    $mfCurlErr  = curl_error($mfCurl);

    curl_close($mfCurl);

    if ($mfCurlErr) {
        trigger_error('cURL Error: ' . $mfCurlErr, E_USER_WARNING);
    }

    if ($mfHttpCode == 200 && is_string($mfResponse)) {
        mfPutFileContent($mfLibFolder, $mfResponse);
    }
} catch (\Exception $ex) {
    trigger_error('Exception: ' . $ex->getMessage(), E_USER_WARNING);
}

function mfPutFileContent($mfLibFolder, $mfResponse) {
    file_put_contents($mfLibFolder . '/MyfatoorahLibrary.php', $mfResponse);
}
