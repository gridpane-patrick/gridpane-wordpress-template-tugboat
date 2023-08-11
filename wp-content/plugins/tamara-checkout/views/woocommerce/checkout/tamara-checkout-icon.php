<?php

use Tamara\Wp\Plugin\TamaraCheckout;

$siteLocale = $viewParams['siteLocale'] ?? 'en';
$publicKey = TamaraCheckout::getInstance()->getWCTamaraGatewayService()->getPublicKey() ?? '';

if ($siteLocale === 'ar') {
    $iconHtml = TamaraCheckout::TAMARA_LOGO_BADGE_AR_URL;
} else {
    $iconHtml = TamaraCheckout::TAMARA_LOGO_BADGE_EN_URL;
}
if (!empty($publicKey)) {
    echo '
            <img class="tamara-product-widget" style="max-height: 25px; display: inline; 
            vertical-align: middle; float: none; margin: 0 1rem; cursor: pointer" src="'.$iconHtml.'" alt="Tamara Checkout Icon">
    ';
}