<?php

namespace Tamara\Wp\Plugin\Services;

use Tamara\Wp\Plugin\TamaraCheckout;
use Tamara\Wp\Plugin\Traits\ConfigTrait;
use Tamara\Wp\Plugin\Traits\ServiceTrait;
use Tamara\Wp\Plugin\Traits\WPAttributeTrait;

class WCTamaraGatewayPayNextMonth extends WCTamaraGateway
{
    use ConfigTrait;
    use ServiceTrait;
    use WPAttributeTrait;

    /**
     * Initialize attributes that are fixed
     */
    protected function initBaseAttributes()
    {
        parent::initBaseAttributes();
        $this->id = TamaraCheckout::TAMARA_GATEWAY_PAY_NEXT_MONTH;
        $this->paymentType = static::PAYMENT_TYPE_PAY_NEXT_MONTH;
        $this->title = __(static::TAMARA_GATEWAY_PAY_NEXT_MONTH_DEFAULT_TITLE, $this->textDomain);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Render description for Tamara Pay Next Month
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
            $description .= TamaraCheckout::getInstance()->getServiceView()->render('views/woocommerce/checkout/tamara-gateway-pay-next-month-description',
                [
                    'defaultDescription' => $this->populateTamaraDefaultDescription(),
                ]);
        }

        return $description;
    }
}
