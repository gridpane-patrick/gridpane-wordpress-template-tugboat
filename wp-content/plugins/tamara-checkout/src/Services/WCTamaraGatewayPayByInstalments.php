<?php

namespace Tamara\Wp\Plugin\Services;

use Tamara\Wp\Plugin\TamaraCheckout;
use Tamara\Wp\Plugin\Traits\ConfigTrait;
use Tamara\Wp\Plugin\Traits\ServiceTrait;
use Tamara\Wp\Plugin\Traits\WPAttributeTrait;

abstract class WCTamaraGatewayPayByInstalments extends WCTamaraGateway
{
    use ConfigTrait;
    use ServiceTrait;
    use WPAttributeTrait;

    /**
     * Init Payment Type
     * Child class must implement this method
     * Always init $instalmentPeriod in this method
     */
    abstract protected function initPaymentType();

    /**
     * Initialize attributes that are fixed
     */
    protected function initBaseAttributes()
    {
        parent::initBaseAttributes();
        $this->initPaymentType();
        $this->title = __($this->getPaymentTypeTitleMapping($this->instalmentPeriod)[$this->id], $this->textDomain)
                       ?? sprintf(__('Pay In %d with Tamara', $this->textDomain), $this->instalmentPeriod);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Render description for Tamara payment types on checkout
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
            $description .= TamaraCheckout::getInstance()->getServiceView()->render('views/woocommerce/checkout/tamara-gateway-pay-by-instalments-description',
                [
                    'cartTotal' => $cartTotal,
                    'defaultDescription' => $this->populateTamaraDefaultDescription(),
                    'instalmentPeriod' => $this->instalmentPeriod,
                    'inlineType' => TamaraCheckout::TAMARA_INLINE_TYPE_CART_WIDGET_INT,
                ]);
        }

        return $description;
    }
}