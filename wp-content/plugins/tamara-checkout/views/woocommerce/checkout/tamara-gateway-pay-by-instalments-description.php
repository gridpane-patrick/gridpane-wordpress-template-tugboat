<?php

use Tamara\Wp\Plugin\TamaraCheckout;

$defaultDescription = $viewParams['defaultDescription'] ?? '';
$cartTotal = $viewParams['cartTotal'] ?? 0;
$instalmentPeriod = $viewParams['instalmentPeriod'] ?? '';
$publicKey = TamaraCheckout::getInstance()->getWCTamaraGatewayService()->getPublicKey() ?? '';
$cartCurrency = get_woocommerce_currency();
$countryCode = TamaraCheckout::getInstance()->getWCTamaraGatewayService()->getCurrencyToCountryMapping()[$cartCurrency];
$siteLocale = substr(get_locale(), 0, 2) ?? 'en';
$totalToCalculate = TamaraCheckout::getInstance()->getTotalToCalculate($cartTotal);

?>

<div class="payment_method_tamara-gateway-pay-by-instalments-<?php echo $instalmentPeriod ?>">
    <div class="tamara-gateway-description">
        <div
            class="tamara-installment-plan-widget"
            data-lang="<?php echo esc_attr($siteLocale) ?>"
            data-price="<?php echo esc_attr($totalToCalculate) ?>"
            data-number-of-installments="<?php echo esc_attr($instalmentPeriod) ?>"
            data-public-key="<?php echo esc_attr($publicKey) ?>"
            data-currency="<?php echo esc_attr($cartCurrency) ?>"
            data-inject-template="false"
            data-disable-installment="false"
            data-country-code="<?php echo esc_attr($countryCode) ?>"
        >
        </div>
        <p class="tamara-gateway-description__default"><?php echo $defaultDescription ?></p>
    </div>
</div>