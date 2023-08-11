<?php
use Tamara\Wp\Plugin\TamaraCheckout;

$defaultDescription = $viewParams['defaultDescription'] ?? '';

?>
<div class="tamara-gateway-paynow-description">
    <p class="tamara-gateway-paynow-description__default"><?php echo $defaultDescription ?></p>
</div>
