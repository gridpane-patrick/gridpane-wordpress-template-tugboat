<?php

namespace Tamara\Wp\Plugin\Services;

use Tamara\Wp\Plugin\TamaraCheckout;
use Tamara\Wp\Plugin\Traits\ConfigTrait;
use Tamara\Wp\Plugin\Traits\ServiceTrait;
use Tamara\Wp\Plugin\Traits\WPAttributeTrait;

class WCTamaraGatewayPayIn3 extends WCTamaraGatewayPayByInstalments
{
    use ConfigTrait;
    use ServiceTrait;
    use WPAttributeTrait;

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Render description for Tamara Pay In 3 on checkout
     *
     * @param $description
     * @param $gatewayId
     *
     * @return string
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function renderPaymentTypeDescription($description, $gatewayId)
    {
        if ($this->id === $gatewayId) {
            $cartTotal = WC()->cart->total;
            $description .= TamaraCheckout::getInstance()->getServiceView()->render('views/woocommerce/checkout/tamara-gateway-pay-by-3-description',
                [
                    'cartTotal' => $cartTotal,
                    'defaultDescription' => $this->populateTamaraDefaultDescription(),
                    'instalmentPeriod' => $this->instalmentPeriod,
                    'inlineType' => TamaraCheckout::TAMARA_INLINE_TYPE_CART_WIDGET_INT,
                ]);
        }

        return $description;
    }

    /**
     * Init $id, $paymentType and $instalmentPeriod
     */
    protected function initPaymentType()
    {
        $this->id = TamaraCheckout::TAMARA_GATEWAY_PAY_IN_3;
        $this->paymentType = static::PAYMENT_TYPE_PAY_BY_INSTALMENTS;
        $this->instalmentPeriod = 3;
    }

    /**
     * @param $cartTotal
     *
     * @return bool
     */
    protected function isCartTotalValid($cartTotal)
    {
        $countryCode = $this->getCurrentCountryCode();
        // Force pull country payment types from remote api
        $countryPaymentTypes = $this->getCountryPaymentTypes();
        $paymentTypes = $this->getPaymentTypes();
        
        $getPayIn3MinAmount = TamaraCheckout::getInstance()->populateInstalmentPayInXMinLimit($this->instalmentPeriod, $countryCode) ?? null;
        $getPayIn3MaxAmount = TamaraCheckout::getInstance()->populateInstalmentPayInXMaxLimit($this->instalmentPeriod, $countryCode) ?? null;
        $getPayByInstalmentsMinAmount = TamaraCheckout::getInstance()->populateInstalmentsMinLimit() ?? null;
        $getPayByInstalmentsMaxAmount = TamaraCheckout::getInstance()->populateInstalmentsMaxLimit() ?? null;
        $payIn3MinAmount = '';
        $payIn3MaxAmount = '';
        
        if (TamaraCheckout::getInstance()->isPayByInstalmentsEnabled()) {
            $payIn3MinAmount = $getPayByInstalmentsMinAmount;
            $payIn3MaxAmount = $getPayByInstalmentsMaxAmount;
        } elseif (TamaraCheckout::getInstance()->isPayInXEnabled($this->instalmentPeriod, $countryCode)) {
            $payIn3MinAmount = $getPayIn3MinAmount;
            $payIn3MaxAmount = $getPayIn3MaxAmount;
        }

        return ($payIn3MinAmount <= $cartTotal && $payIn3MaxAmount >= $cartTotal);
    }
}