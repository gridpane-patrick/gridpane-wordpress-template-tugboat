<?php

use Tamara\Wp\Plugin\TamaraCheckout;

$dataPrice = $viewParams['dataPrice'] ?? 0;
$inlineType = $viewParams['inlineType'] ?? TamaraCheckout::TAMARA_INLINE_TYPE_PRODUCT_WIDGET_INT;
$publicKey = TamaraCheckout::getInstance()->getWCTamaraGatewayService()->getPublicKey() ?? '';
if (!empty($publicKey)) {
    echo '
        <tamara-widget type="tamara-summary" inline-type="'.$inlineType.'" amount="'.$dataPrice.'"></tamara-widget>
    ';
}