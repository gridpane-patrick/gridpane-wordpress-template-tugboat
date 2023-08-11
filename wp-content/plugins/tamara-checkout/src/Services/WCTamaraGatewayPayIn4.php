<?php

namespace Tamara\Wp\Plugin\Services;

use Tamara\Wp\Plugin\TamaraCheckout;
use Tamara\Wp\Plugin\Traits\ConfigTrait;
use Tamara\Wp\Plugin\Traits\ServiceTrait;
use Tamara\Wp\Plugin\Traits\WPAttributeTrait;

class WCTamaraGatewayPayIn4 extends WCTamaraGatewayPayByInstalments
{
    use ConfigTrait;
    use ServiceTrait;
    use WPAttributeTrait;

    /**
     * Init $id, $paymentType and $instalmentPeriod
     */
    protected function initPaymentType()
    {
        $this->id = TamaraCheckout::TAMARA_GATEWAY_PAY_IN_4;
        $this->paymentType = static::PAYMENT_TYPE_PAY_BY_INSTALMENTS;
        $this->instalmentPeriod = 4;
    }
}