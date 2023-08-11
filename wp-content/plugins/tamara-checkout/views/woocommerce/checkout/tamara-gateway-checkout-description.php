<?php

use Tamara\Wp\Plugin\TamaraCheckout;

$defaultDescription = $viewParams['defaultDescription'] ?? '';
$cartTotal = $viewParams['cartTotal'] ?? 0;
$inlineType = $viewParams['inlineType'] ?? TamaraCheckout::TAMARA_INLINE_TYPE_CART_WIDGET_INT;
$totalToCalculate = TamaraCheckout::getInstance()->getTotalToCalculate($cartTotal);

?>
<div class="tamara-gateway-checkout-description">
    <tamara-widget type="tamara-summary" inline-type="<?php echo $inlineType ?>" amount="<?php echo $totalToCalculate ?>"></tamara-widget>
    <p class="tamara-gateway-checkout-description__default"><?php echo $defaultDescription ?></p>
</div>
