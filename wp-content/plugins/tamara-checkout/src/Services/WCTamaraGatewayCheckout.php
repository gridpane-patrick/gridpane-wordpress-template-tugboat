<?php

namespace Tamara\Wp\Plugin\Services;

use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Money;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Checkout\PaymentOptionsAvailability;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Checkout\CheckPaymentOptionsAvailabilityRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Checkout\CheckPaymentOptionsAvailabilityResponse;
use Tamara\Wp\Plugin\TamaraCheckout;
use Tamara\Wp\Plugin\Traits\ConfigTrait;
use Tamara\Wp\Plugin\Traits\ServiceTrait;
use Tamara\Wp\Plugin\Traits\WPAttributeTrait;

class WCTamaraGatewayCheckout extends WCTamaraGateway
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
        $this->id = TamaraCheckout::TAMARA_GATEWAY_CHECKOUT_ID;
        $this->title = __(sprintf('Tamara: Split in %d, interest-free', 4), $this->textDomain);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Render description for Tamara Single Checkout
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
            $description .= TamaraCheckout::getInstance()->getServiceView()->render('views/woocommerce/checkout/tamara-gateway-checkout-description',
                [
                    'cartTotal' => $cartTotal,
                    'defaultDescription' => $this->populateTamaraDefaultDescription(),
                    'inlineType' => TamaraCheckout::TAMARA_INLINE_TYPE_CART_WIDGET_INT,
                ]);
        }

        return $description;
    }

    /**
     * Hide Tamara Payment Gateway on checkout if total value of order is under/over limit
     * and the shipping country is different than countries set in Tamara payment settings
     *
     * @param WC_Payment_Gateway $availableGateways
     *
     * @return WC_Payment_Gateway $availableGateways
     *
     * @throws Exception
     */
    public function adjustTamaraGatewayOnCheckout($availableGateways)
    {
        if (is_checkout()) {
            $cartTotal = TamaraCheckout::getInstance()->getTotalToCalculate(WC()->cart->total);
            $currentCountryCode = $this->getCurrencyToCountryMapping()[get_woocommerce_currency()];
            $tamaraExcludedProductItems = TamaraCheckout::getInstance()->getExcludedProductIds() ?? null;
            $tamaraExcludedProductCategories = TamaraCheckout::getInstance()->getExcludedProductCategoryIds() ?? null;
            $cartItemIds = TamaraCheckout::getInstance()->getAllProductIdsInCart();
            $cartItemCategoryIds = TamaraCheckout::getInstance()->getAllProductCategoryIdsInCart();
            $tamaraExcludedProductItemsInCart = (count(array_intersect(
                $cartItemIds, $tamaraExcludedProductItems))) ? true : false;
            $tamaraExcludedProductCategoriesInCart = (count(array_intersect(
                $cartItemCategoryIds, $tamaraExcludedProductCategories))) ? true : false;
            $customerPhone = TamaraCheckout::getInstance()->getCustomerPhoneNumber() ?? WC()->customer->get_billing_phone();

            if (!TamaraCheckout::getInstance()->hasAvailablePaymentOptions($cartTotal, $customerPhone, $currentCountryCode)
                || $tamaraExcludedProductItemsInCart || $tamaraExcludedProductCategoriesInCart) {
                unset($availableGateways[$this->id]);
            } else {
                $getAvailableMethod = $this->isMethodAvailableFromRemote($cartTotal, $customerPhone, $currentCountryCode);
                $countPaymentOption = $this->isMethodAvailableFromRemote($cartTotal, $customerPhone, $currentCountryCode);
                $siteLocale = substr(get_locale(), 0, 2) ?? 'en';
                if ('ar' === $siteLocale) {
                    $this->title = $getAvailableMethod['descriptionAr'];
                } else {
                    $this->title = $getAvailableMethod['descriptionEn'];
                }
            }
        }

        return $availableGateways;
    }

    /**
     * Check if a payment method is available from remote and get full description
     *
     * @param $cartTotal
     * @param $customerPhone
     * @param $countryCode
     *
     * @return array
     */
    protected function isMethodAvailableFromRemote($cartTotal, $customerPhone, $countryCode)
    {
        $paymentOptions = TamaraCheckout::getInstance()->getPaymentOptions($cartTotal, $customerPhone, $countryCode) ?? [];
        $descriptionEn = '';
        $descriptionAr = '';
        if (!empty($paymentOptions)) {
            foreach ($paymentOptions as $payment_option) {
                $descriptionEn = $payment_option['description_en'];
                $descriptionAr = $payment_option['description_ar'];
                break;
            }
        }
        return [
            'descriptionEn' => $descriptionEn,
            'descriptionAr' => $descriptionAr
        ];
    }
}