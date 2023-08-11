<?php
use Tamara\Wp\Plugin\TamaraCheckout;

$defaultDescription = $viewParams['defaultDescription'] ?? '';
$countryCode = TamaraCheckout::getInstance()->getWCTamaraGatewayService()->getCurrencyToCountryMapping()[$cartCurrency];
$siteLocale = substr(get_locale(), 0, 2) ?? 'en';

?>
<div class="tamara-gateway-paynow-description">
    <div class="tamara-gateway-description">
		<tamara-widget type="tamara-card-snippet" lang="<?php echo esc_attr($siteLocale) ?>" country="<?php echo esc_attr($countryCode) ?>"></tamara-widget>
        <p class="tamara-gateway-description__default"><?php echo $defaultDescription ?></p>
    </div>
</div>
