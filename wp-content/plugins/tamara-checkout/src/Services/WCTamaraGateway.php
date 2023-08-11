<?php

namespace Tamara\Wp\Plugin\Services;

use DateTimeImmutable;
use Exception;
use Tamara\Wp\Plugin\Dependencies\Tamara\Client;
use Tamara\Wp\Plugin\Dependencies\Tamara\Configuration;
use Tamara\Wp\Plugin\Dependencies\Tamara\HttpClient\NyholmHttpAdapter;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Checkout\PaymentOptionsAvailability;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Checkout\PaymentType;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Merchant;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Money;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Order\Address;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Order\Consumer;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Order\Discount;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Order\MerchantUrl;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Order\Order;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Order\OrderItem;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Order\OrderItemCollection;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Order\RiskAssessment;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Payment\Capture;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\ShippingInfo;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Checkout\CheckPaymentOptionsAvailabilityRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Checkout\CreateCheckoutRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Checkout\GetPaymentTypesV2Request;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Merchant\GetDetailsInfoRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Order\CancelOrderRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Order\GetOrderByReferenceIdRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Payment\CaptureRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Webhook\RegisterWebhookRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Webhook\RemoveWebhookRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Checkout\GetPaymentTypesResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Payment\CaptureResponse;
use Tamara\Wp\Plugin\Helpers\MoneyHelper;
use Tamara\Wp\Plugin\TamaraCheckout;
use Tamara\Wp\Plugin\Traits\ConfigTrait;
use Tamara\Wp\Plugin\Traits\ServiceTrait;
use Tamara\Wp\Plugin\Traits\WPAttributeTrait;
use WC_Admin_Settings;
use WC_Order;
use WC_Order_Refund;
use WC_Payment_Gateway;
use WC_Product;
use WP_REST_Response;

/**
 * Class WCTamaraGateway
 * @package Tamara\Wp\Plugin\Services
 * @method \Tamara\Wp\Plugin\TamaraCheckout getContainer()
 */
class WCTamaraGateway extends WC_Payment_Gateway
{
    use ConfigTrait;
    use ServiceTrait;
    use WPAttributeTrait;

    public const
        DEFAULT_COUNTRY_CODE = 'SA',
        IPN_SLUG = 'tamara-ipn',
        WEBHOOK_SLUG = 'tamara-webhook',
        TAMARA_CHECKOUT = 'tamara-checkout',
        PAYMENT_SUCCESS_SLUG = 'tamara-payment-success',
        PAYMENT_CANCEL_SLUG = 'tamara-payment-cancel',
        PAYMENT_FAIL_SLUG = 'tamara-payment-fail',
        PAYMENT_TYPE_PAY_BY_LATER = 'PAY_BY_LATER',
        PAYMENT_TYPE_PAY_NOW = 'PAY_NOW',
        PAYMENT_TYPE_PAY_BY_INSTALMENTS = 'PAY_BY_INSTALMENTS',
        PAYMENT_TYPE_PAY_NEXT_MONTH = 'PAY_NEXT_MONTH',
        REGISTERED_WEBHOOKS = [
        'order_expired',
        'order_declined',
    ],
        TAMARA_POPUP_WIDGET_POSITIONS = [
        'woocommerce_single_product_summary' => 'woocommerce_single_product_summary',
        'woocommerce_after_single_product_summary' => 'woocommerce_after_single_product_summary',
        'woocommerce_after_add_to_cart_form' => 'woocommerce_after_add_to_cart_form',
        'woocommerce_before_add_to_cart_form' => 'woocommerce_before_add_to_cart_form',
        'woocommerce_product_meta_end' => 'woocommerce_product_meta_end',
    ],
        TAMARA_CART_POPUP_WIDGET_POSITIONS = [
        'woocommerce_before_cart' => 'woocommerce_before_cart',
        'woocommerce_after_cart_table' => 'woocommerce_after_cart_table',
        'woocommerce_cart_totals_before_order_total' => 'woocommerce_cart_totals_before_order_total',
        'woocommerce_proceed_to_checkout' => 'woocommerce_proceed_to_checkout',
        'woocommerce_after_cart_totals' => 'woocommerce_after_cart_totals',
        'woocommerce_after_cart' => 'woocommerce_after_cart',
    ],
        ENVIRONMENT_LIVE_MODE = 'live_mode',
        ENVIRONMENT_SANDBOX_MODE = 'sandbox_mode',
        LIVE_API_URL = 'https://api.tamara.co',
        SANDBOX_API_URL = 'https://api-sandbox.tamara.co',

        TAMARA_GATEWAY_DEFAULT_TITLE = 'Pay Later with Tamara',
        TAMARA_GATEWAY_PAY_NOW_DEFAULT_TITLE= 'Tamara pay now using Mada, Apple Pay, or credit card',
        TAMARA_GATEWAY_PAY_NEXT_MONTH_DEFAULT_TITLE  = 'Pay Next Month with Tamara',
        TAMARA_GATEWAY_PAY_IN_X_DEFAULT_TITLE = 'Pay in ',
        PAYMENT_TYPE_PAY_BY_LATER_DEFAULT_TITLE = 'Pay in 30 days without fees with Tamara',
        PAYMENT_TYPE_PAY_BY_LATER_DEFAULT_TITLE_AR = 'اطلب الآن وادفع خلال 30 یوم مع تمارا. بدون رسوم',
        PAYMENT_TYPE_PAY_BY_INSTALMENTS_DEFAULT_TITLE = 'Split into 3 payments, without fees with Tamara',
        PAYMENT_TYPE_PAY_BY_INSTALMENTS_DEFAULT_TITLE_AR = 'قسّمها على 3 دفعات بدون رسوم مع تمارا';

    public $payByLaterEnabled;
    public $payByInstalmentsEnabled;
    public $environment;
    public $apiUrl;
    public $apiToken;
    public $notificationToken;

    /**
     * @var Client $tamaraClient
     */
    public $tamaraClient;
    public $tamaraStatus = [];
    public $webhookEnabled;
    public $beautifyMerchantUrlsEnabled;
    public $customLogMessageEnabled;
    public $popupWidgetPosition;

    protected $webhookId;
    protected $errorMap;
    protected $paymentType;
    protected $instalmentPeriod = null;
    protected $paymentTypes = [];
    protected $publicKey;

    /**
     * WCTamaraGateway constructor.
     */
    public function __construct()
    {
        $this->initBaseAttributes();
        $this->initSettingAttributes();
    }

    /**
     * Handle remote request after saving
     *
     * @param $settings
     */
    public function onSaveSettings($settings)
    {
        $this->refreshPaymentTypeCache();
//        $this->refreshPaymentTypeCacheV2();

        if ($this->validateRequiredFields()) {
            $this->initTamaraClient();
            $this->settings['country_payment_types'] = $this->getCountryPaymentTypes();
            $paymentTypes = $this->getPaymentTypes();
            $this->settings['webhook_id'] = $this->populateTamaraWebhook();
            $this->settings['min_limit'] = $this->populateMinLimit();
            $this->settings['max_limit'] = $this->populateMaxLimit();
        }

        // Remove old pay by instalment setting
        unset($this->settings['pay_by_instalments_enabled']);

        $this->processRewriteRules();
        $this->initSettingAttributes();

        $this->initFormFields();
        $postData = $this->get_post_data();
        foreach ($this->get_form_fields() as $key => $field) {
            if ('title' !== $this->get_field_type($field)) {
                try {
                    $this->settings[$key] = $this->get_field_value($key, $field, $postData);
                } catch (Exception $e) {
                    $this->add_error($e->getMessage());
                }
            }
        }
        if (empty($this->settings['country_payment_types'])) {
            $this->settings['country_payment_types'] = $this->getCountryPaymentTypes();
        }

        if (!$this->validateRequiredFields()) {
            $this->raiseAdminError();
            $this->settings['enabled'] = 'no';
            $this->settings['webhook_enabled'] = 'no';
            $this->settings['webhook_id'] = null;
            $this->settings['min_limit'] = null;
            $this->settings['max_limit'] = null;
            $this->settings['country_payment_types'] = [];
        }

        $this->updateThisSettingsToOptions();
        $this->initFormFields();
    }

    public function init()
    {
        // Clear Tamara payment types cache whenever user is on Settings screen.
        if (TamaraCheckout::getInstance()->isTamaraAdminSettingsScreen()) {
            $this->refreshPaymentTypeCache();
//            $this->refreshPaymentTypeCacheV2();
            $this->settings['country_payment_types'] = $this->getCountryPaymentTypes();
            $this->updateThisSettingsToOptions();
            // Load the settings form.
            $this->initFormFields();
        }

        // Process admin options
        add_action('woocommerce_update_options_payment_gateways_'.$this->id, [$this, 'processAdminOptions']);

        // Add Tamara Icon on Checkout
        add_filter('woocommerce_gateway_icon', [$this, 'setTamaraIconForPaymentGateway'], 10, 2);

        // Tamara Capture payment
        add_action('woocommerce_order_status_changed', [$this, 'tamaraCapturePayment'], 10, 4);

        // Handle cancel order before capturing
        add_action('woocommerce_order_status_changed', [$this, 'tamaraCancelOrder'], 10, 4);

        // Invoke a filter to hide Tamara Gateway on checkout
        add_filter('woocommerce_available_payment_gateways', [$this, 'adjustTamaraGatewayOnCheckout'], 9999, 1);

        // Invoke a filter to update description for Tamara Gateway on checkout
        add_filter('woocommerce_gateway_description', [$this, 'renderPaymentTypeDescription'], 9999, 2);

        if (is_order_received_page()) {
            $this->handleTamaraSuccessOrderReceivedPage();
        }

    }

    public function getPayByLaterTitle()
    {
        return static::PAYMENT_TYPE_PAY_BY_LATER_DEFAULT_TITLE;
    }

    public function getPayByLaterTitleAr()
    {
        return static::PAYMENT_TYPE_PAY_BY_LATER_DEFAULT_TITLE_AR;
    }

    public function getPayInXTitle($instalment)
    {
        return $this->populateDefaultPayInXTitles($instalment)['instalmentDefaultEnTitle'];
    }

    public function getPayInXTitleAr($instalment)
    {
        return $this->populateDefaultPayInXTitles($instalment)['instalmentDefaultArTitle'];
    }

    public function getStoreBaseCountryCode()
    {
        return !empty(WC()->countries->get_base_country()) ? WC()->countries->get_base_country() : static::DEFAULT_COUNTRY_CODE;
    }

    public function getDefaultBillingCountryCode()
    {
        return !empty($this->getCurrencyToCountryMapping()[get_woocommerce_currency()]) ? $this->getCurrencyToCountryMapping()[get_woocommerce_currency()]
            : $this->getStoreBaseCountryCode();
    }

    public function isBeautifyMerchantUrlsEnabled()
    {
        return ($this->beautifyMerchantUrlsEnabled === 'yes' && $this->enabled === 'yes');
    }

    public function isWebhookEnabled()
    {
        return ($this->webhookEnabled === 'yes');
    }

    public function isSandboxMode()
    {
        return (static::ENVIRONMENT_SANDBOX_MODE === $this->environment);
    }

    public function isLiveMode()
    {
        return (static::ENVIRONMENT_LIVE_MODE === $this->environment);
    }

    public function initSettings()
    {
        parent::init_settings();
    }

    public function processAdminOptions()
    {
        parent::process_admin_options();
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
            $description .= TamaraCheckout::getInstance()->getServiceView()->render('views/woocommerce/checkout/tamara-gateway-description',
                [
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
            $getAvailableMethod = $this->isMethodAvailableFromRemote($cartTotal, $customerPhone, $currentCountryCode);

            if (!$getAvailableMethod['isMethodAvailable']
                || $tamaraExcludedProductItemsInCart || $tamaraExcludedProductCategoriesInCart) {
                unset($availableGateways[$this->id]);
            } else {
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

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Return the Tamara gateway's icon on checkout
     *
     * @param $iconHtml
     * @param $gatewayId
     *
     * @return string
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function setTamaraIconForPaymentGateway($iconHtml, $gatewayId)
    {
        if ($this->id === $gatewayId && null == $this->icon) {
            $iconHtml = $this->getContainer()->getServiceView()->render('views/woocommerce/checkout/tamara-checkout-icon',
                [
                    'siteLocale' => substr(get_locale(), 0, 2) ?? 'en',
                ]);
        }

        return $iconHtml;
    }

    /**
     * Create Tamara Checkout session, if failed, put errors to `wc_notices`
     *
     * @param $orderId
     *
     * @return array|bool
     */
    public function tamaraCheckoutSession($orderId)
    {
        $wcOrder = wc_get_order($orderId);
        $instalmentPeriod = null;

        if (TamaraCheckout::isRestRequest()) {
            $restApiRequest = TamaraCheckout::getInstance()->getRestApiRequest();
            $paymentMethod = $restApiRequest->get_param('payment_method');
            if (TamaraCheckout::TAMARA_GATEWAY_ID === $paymentMethod) {
                $checkoutPaymentType = $this->getPaymentTypeMapping()[TamaraCheckout::TAMARA_GATEWAY_ID];
            } elseif (TamaraCheckout::TAMARA_GATEWAY_CHECKOUT_ID === $paymentMethod) {
                $checkoutPaymentType = $this->getPaymentTypeMapping()[TamaraCheckout::TAMARA_GATEWAY_CHECKOUT_ID];
            } elseif (TamaraCheckout::TAMARA_GATEWAY_PAY_NOW === $paymentMethod) {
                $checkoutPaymentType = $this->getPaymentTypeMapping()[TamaraCheckout::TAMARA_GATEWAY_PAY_NOW];
            } else {
                $checkoutPaymentType = $this->getPaymentTypeMapping()[$paymentMethod];
                $instalmentPeriod = $restApiRequest->get_param('tamara_instalment_period') ?? null;
            }
        } else {
            $checkoutPaymentType = $this->paymentType;
            $instalmentPeriod = $this->instalmentPeriod;
        }

        try {
            $checkoutResponse = $this->createTamaraCheckoutSession($wcOrder, $checkoutPaymentType, $instalmentPeriod);
            TamaraCheckout::getInstance()->logMessage(sprintf("Tamara Checkout Session Response Data: %s", print_r($checkoutResponse, true)));
        } catch (Exception $tamaraCheckoutException) {
            if (!TamaraCheckout::isRestRequest()) {
                $errorMessage = __("Tamara Service unavailable! Please try again later.", $this->textDomain);
                if (function_exists('wc_add_notice')) {
                    wc_add_notice($errorMessage, 'error');
                }
            }

            // Log if service unavailable
            TamaraCheckout::getInstance()->logMessage("Tamara Checkout Session cannot be created."."\n");
            TamaraCheckout::getInstance()->logMessage(
                sprintf(
                    "Error message: '%s'.\nTrace: %s",
                    $tamaraCheckoutException->getMessage(),
                    $tamaraCheckoutException->getTraceAsString()
                )
            );
        }

        if (isset($checkoutResponse) && $checkoutResponse->isSuccess()) {
            $tamaraCheckoutUrl = $checkoutResponse->getCheckoutResponse()->getCheckoutUrl();
            $tamaraCheckoutSessionId = $checkoutResponse->getCheckoutResponse()->getCheckoutId();

            update_post_meta($orderId, 'tamara_checkout_session_id', $tamaraCheckoutSessionId);
            update_post_meta($orderId, 'tamara_checkout_url', $tamaraCheckoutUrl);
            update_post_meta($orderId, 'tamara_payment_type', $checkoutPaymentType);
            if ($checkoutPaymentType === static::PAYMENT_TYPE_PAY_BY_INSTALMENTS && !empty($instalmentPeriod)) {
                update_post_meta($orderId, 'tamara_payment_type_instalment', $instalmentPeriod);
            }

            return [
                'result' => 'success',
                'redirect' => $tamaraCheckoutUrl,
                'tamaraCheckoutUrl' => $tamaraCheckoutUrl,
                'tamaraCheckoutSessionId' => $tamaraCheckoutSessionId,
            ];
        }

        if (isset($checkoutResponse) && !$checkoutResponse->isSuccess()) {
            $errorMap = $this->errorMap;

            $tamaraErrors = $checkoutResponse->getErrors();
            $errors = [];
            if (!empty($tamaraErrors) && is_array($tamaraErrors)) {
                foreach ($tamaraErrors as $tmpKey => $tamaraError) {
                    $errorCode = $tamaraError['error_code'] ?? null;
                    if ($errorCode && isset($errorMap[$errorCode])) {
                        $errors[] = $errorMap[$errorCode];
                    }
                }
            }
            if (empty($errors)) {
                $errorCode = $checkoutResponse->getMessage();
                if ($errorCode && isset($errorMap[$errorCode])) {
                    $errors[] = $errorMap[$errorCode];
                }

                if (!$checkoutResponse->isSuccess() && empty($errors)) {
                    $errors[] = $errorMap['tamara_disabled'];
                }
            }

            if (TamaraCheckout::isRestRequest()) {
                add_filter('rest_post_dispatch', function ($result, $rest_api_server, $request) use ($orderId, $errors) {
                    $httpStatusCode = 400;
                    /** @var \WP_HTTP_Response $result */
                    $result->set_status($httpStatusCode);
                    $result->set_data([
                        'code' => 'tamara_checkout_session_error',
                        'message' => '<p>'.implode('<br />', $errors).'</p>',
                        'data' => [
                            'order_id' => $orderId,
                            'status' => $httpStatusCode,
                        ],
                        'additional_errors' => $errors,
                    ]);

                    return $result;
                }, 100, 3);
            } else {
                if (function_exists('wc_add_notice')) {
                    foreach ($errors as $error) {
                        wc_add_notice($error, 'error');
                    }
                }
            }
        }

        // If this is the failed process, return false instead of ['result' => 'success']
        return false;
    }

    /**
     * Filling all needed data for a Tamara Order used for Pay By Later
     *
     * @param WC_Order $wcOrder
     * @param string $paymentType | null
     * @param string $instalmentPeriod null
     *
     * @return Order
     * @throws Exception
     */
    public function populateTamaraOrder($wcOrder, $paymentType = null, $instalmentPeriod = null)
    {
        if (empty($paymentType)) {
            throw new Exception('Error! No Payment Type specified');
        }
        $usedCouponsStr = !empty($wcOrder->get_coupon_codes()) ? implode(",", $wcOrder->get_coupon_codes()) : '';
        $order = new Order();

        $order->setOrderReferenceId($wcOrder->get_id());
        $order->setLocale(get_locale());
        $order->setCurrency($wcOrder->get_currency());
        $order->setTotalAmount(new Money(MoneyHelper::formatNumber($wcOrder->get_total()), $order->getCurrency()));
        $order->setCountryCode(!empty($wcOrder->get_billing_country()) ? $wcOrder->get_billing_country() : $this->getDefaultBillingCountryCode());
        $order->setPaymentType($paymentType);
        $order->setInstalments($instalmentPeriod);
        $order->setPlatform(sprintf('WordPress %s, WooCommerce %s, Tamara Checkout %s', $GLOBALS['wp_version'], $GLOBALS['woocommerce']->version, TamaraCheckout::getInstance()->version));
        $order->setDescription(__('Use Tamara Gateway with WooCommerce', $this->textDomain));
        $order->setTaxAmount(new Money(MoneyHelper::formatNumber($wcOrder->get_total_tax()), $order->getCurrency()));
        $order->setShippingAmount(new Money(MoneyHelper::formatNumber($wcOrder->get_shipping_total()), $order->getCurrency()));
        $order->setDiscount(new Discount($usedCouponsStr, new Money(MoneyHelper::formatNumber($wcOrder->get_discount_total()), $order->getCurrency())));
        $order->setMerchantUrl($this->populateTamaraMerchantUrl($wcOrder));
        $order->setBillingAddress($this->populateTamaraBillingAddress($wcOrder));
        $order->setShippingAddress($this->populateTamaraShippingAddress($wcOrder));
        $order->setConsumer($this->populateTamaraConsumer($wcOrder));

        $order->setItems($this->populateTamaraOrderItems($wcOrder));

        return $order;
    }

    /**
     * @inheritDoc
     */
    public function process_payment($orderId)
    {
        return $this->tamaraCheckoutSession($orderId);
    }

    /**
     * Set Tamara Items Detail
     *
     * @param WC_Order $wcOrder
     *
     * @return OrderItemCollection
     */
    public function populateTamaraOrderItems($wcOrder)
    {
        $wcOrderItems = $wcOrder->get_items();
        $orderItemCollection = new OrderItemCollection();

        foreach ($wcOrderItems as $itemId => $wcOrderItem) {
            $orderItem = new OrderItem();
            /** @var WC_Product $wcOrderItemProduct */
            $wcOrderItemProduct = $wcOrderItem->get_product();
            if ($wcOrderItemProduct) {
                $wcOrderItemName = strip_tags($wcOrderItem->get_name());
                $wcOrderItemQuantity = $wcOrderItem->get_quantity();
                $wcOrderItemSku = $wcOrderItemProduct->get_sku() ?: 'N/A';
                $wcOrderItemTotalTax = $wcOrderItem->get_total_tax();
                $wcOrderItemTotal = $wcOrderItem->get_total() + $wcOrderItemTotalTax;
                $wcOrderItemCategories = strip_tags(wc_get_product_category_list($wcOrderItemProduct->get_id())) ?: 'N/A';
                $wcOrderItemRegularPrice = $wcOrderItemProduct->get_regular_price();
                $wcOrderItemSalePrice = $wcOrderItemProduct->get_sale_price();
                $itemPrice = $wcOrderItemSalePrice ?: $wcOrderItemRegularPrice;
                $wcOrderItemDiscountAmount = (int)$itemPrice * $wcOrderItemQuantity - ((int)$wcOrderItemTotal - (int)$wcOrderItemTotalTax);
                $orderItem->setName($wcOrderItemName);
                $orderItem->setQuantity($wcOrderItemQuantity);
                $orderItem->setUnitPrice(new Money(MoneyHelper::formatNumber($itemPrice), $wcOrder->get_currency()));
                $orderItem->setType($wcOrderItemCategories);
                $orderItem->setSku($wcOrderItemSku);
                $orderItem->setTotalAmount(new Money(MoneyHelper::formatNumber($wcOrderItemTotal),
                    $wcOrder->get_currency()));
                $orderItem->setTaxAmount(new Money(MoneyHelper::formatNumber($wcOrderItemTotalTax),
                    $wcOrder->get_currency()));
                $orderItem->setDiscountAmount(new Money(MoneyHelper::formatNumber($wcOrderItemDiscountAmount),
                    $wcOrder->get_currency()));
                $orderItem->setReferenceId($itemId);
                $orderItem->setImageUrl(wp_get_attachment_url($wcOrderItemProduct->get_image_id()));
            } else {
                $wcOrderItemProduct = $wcOrderItem->get_data();
                $wcOrderItemName = strip_tags($wcOrderItemProduct['name']) ?? 'N/A';
                $wcOrderItemQuantity = $wcOrderItemProduct['quantity'] ?? 1;
                $wcOrderItemSku = $wcOrderItemProduct['sku'] ?? 'N/A';
                $wcOrderItemTotalTax = $wcOrderItemProduct['total_tax'] ?? 0;
                $wcOrderItemTotal = $wcOrderItemProduct['total'] ?? 0;
                $wcOrderItemCategories = $wcOrderItemProduct['category'] ?? 'N/A';
                $itemPrice = $wcOrderItemProduct['subtotal'] ?? 0;
                $wcOrderItemDiscountAmount = (int)$itemPrice * $wcOrderItemQuantity - ((int)$wcOrderItemTotal - (int)$wcOrderItemTotalTax);
                $orderItem->setName($wcOrderItemName);
                $orderItem->setQuantity($wcOrderItemQuantity);
                $orderItem->setUnitPrice(new Money(MoneyHelper::formatNumber($itemPrice), $wcOrder->get_currency()));
                $orderItem->setType($wcOrderItemCategories);
                $orderItem->setSku($wcOrderItemSku);
                $orderItem->setTotalAmount(new Money(MoneyHelper::formatNumber($wcOrderItemTotal),
                    $wcOrder->get_currency()));
                $orderItem->setTaxAmount(new Money(MoneyHelper::formatNumber($wcOrderItemTotalTax),
                    $wcOrder->get_currency()));
                $orderItem->setDiscountAmount(new Money(MoneyHelper::formatNumber($wcOrderItemDiscountAmount),
                    $wcOrder->get_currency()));
                $orderItem->setReferenceId($itemId);
                $orderItem->setImageUrl('N/A');
            }

            $orderItemCollection->append($orderItem);
        }

        return $orderItemCollection;
    }

    /**
     * @param WC_Order_Refund $wcOrderRefund
     *
     * @return OrderItemCollection
     */
    public function populateTamaraRefundOrderItems($wcOrderRefund)
    {
        $wcOrderItems = $wcOrderRefund->get_items();
        $orderItemCollection = new OrderItemCollection();

        foreach ($wcOrderItems as $itemId => $wcOrderItem) {
            $orderItem = new OrderItem();
            /** @var WC_Product $wcOrderItemProduct */
            $wcOrderItemProduct = $wcOrderItem->get_product();
            if ($wcOrderItemProduct) {
                $wcOrderItemName = strip_tags($wcOrderItem->get_name());
                $wcOrderItemQuantity = abs($wcOrderItem->get_quantity());
                $wcOrderItemSku = $wcOrderItemProduct->get_sku() ?: 'N/A';
                $wcOrderItemTotalTax = abs($wcOrderItem->get_total_tax());
                $wcOrderItemTotal = abs($wcOrderItem->get_total()) + $wcOrderItemTotalTax;
                $wcOrderItemCategories = strip_tags(wc_get_product_category_list($wcOrderItemProduct->get_id())) ?: 'N/A';
                $wcOrderItemRegularPrice = $wcOrderItemProduct->get_regular_price();
                $wcOrderItemSalePrice = $wcOrderItemProduct->get_sale_price();
                $itemPrice = $wcOrderItemSalePrice ?: $wcOrderItemRegularPrice;
                $wcOrderItemDiscountAmount = $itemPrice * $wcOrderItemQuantity - ($wcOrderItemTotal - $wcOrderItemTotalTax);
                $orderItem->setName($wcOrderItemName);
                $orderItem->setQuantity($wcOrderItemQuantity);
                $orderItem->setUnitPrice(new Money(MoneyHelper::formatNumber($itemPrice), $wcOrderRefund->get_currency()));
                $orderItem->setType($wcOrderItemCategories);
                $orderItem->setSku($wcOrderItemSku);
                $orderItem->setTotalAmount(new Money(MoneyHelper::formatNumber($wcOrderItemTotal), $wcOrderRefund->get_currency()));
                $orderItem->setTaxAmount(new Money(MoneyHelper::formatNumber($wcOrderItemTotalTax), $wcOrderRefund->get_currency()));
                $orderItem->setDiscountAmount(new Money(MoneyHelper::formatNumber($wcOrderItemDiscountAmount), $wcOrderRefund->get_currency()));
                $orderItem->setReferenceId($itemId);
                $orderItem->setImageUrl(wp_get_attachment_url($wcOrderItemProduct->get_image_id()));
            } else {
                $wcOrderItemProduct = $wcOrderItem->get_data();
                $wcOrderItemName = strip_tags($wcOrderItemProduct['name']) ?? 'N/A';
                $wcOrderItemQuantity = abs($wcOrderItemProduct['quantity']) ?? 1;
                $wcOrderItemSku = $wcOrderItemProduct['sku'] ?? 'N/A';
                $wcOrderItemTotalTax = abs($wcOrderItemProduct['total_tax']) ?? 0;
                $wcOrderItemTotal = abs($wcOrderItemProduct['total']) ?? 0;
                $wcOrderItemCategories = $wcOrderItemProduct['category'] ?? 'N/A';
                $itemPrice = abs($wcOrderItemProduct['subtotal']) ?? 0;
                $wcOrderItemDiscountAmount = (int)$itemPrice * $wcOrderItemQuantity - ((int)$wcOrderItemTotal - (int)$wcOrderItemTotalTax);
                $orderItem->setName($wcOrderItemName);
                $orderItem->setQuantity($wcOrderItemQuantity);
                $orderItem->setUnitPrice(new Money(MoneyHelper::formatNumber($itemPrice), $wcOrderRefund->get_currency()));
                $orderItem->setType($wcOrderItemCategories);
                $orderItem->setSku($wcOrderItemSku);
                $orderItem->setTotalAmount(new Money(MoneyHelper::formatNumber($wcOrderItemTotal),
                    $wcOrderRefund->get_currency()));
                $orderItem->setTaxAmount(new Money(MoneyHelper::formatNumber($wcOrderItemTotalTax),
                    $wcOrderRefund->get_currency()));
                $orderItem->setDiscountAmount(new Money(MoneyHelper::formatNumber($wcOrderItemDiscountAmount),
                    $wcOrderRefund->get_currency()));
                $orderItem->setReferenceId($itemId);
                $orderItem->setImageUrl('N/A');
            }

            $orderItemCollection->append($orderItem);
        }

        return $orderItemCollection;
    }

    /**
     * Fill up date for Tamara Merchant Url for Tamara to redirect on corresponding order status
     *
     * @param WC_Order $wcOrder
     *
     * @return MerchantUrl
     */
    public function populateTamaraMerchantUrl($wcOrder)
    {
        $merchantUrl = new MerchantUrl();

        $orderId = $wcOrder->get_id();

        $tamaraSuccessUrl = $this->getTamaraSuccessUrl($wcOrder, [
            'wcOrderId' => $orderId,
            'paymentMethod' => static::TAMARA_CHECKOUT,
        ]);
        $tamaraCancelUrl = $this->getTamaraCancelUrl([
            'wcOrderId' => $orderId,
        ]);
        $tamaraFailureUrl = $this->getTamaraFailureUrl([
            'wcOrderId' => $orderId,
        ]);

        $merchantUrl->setSuccessUrl($tamaraSuccessUrl);
        $merchantUrl->setFailureUrl($tamaraFailureUrl);
        $merchantUrl->setCancelUrl($tamaraCancelUrl);
        $merchantUrl->setNotificationUrl($this->getTamaraIpnUrl());

        return $merchantUrl;
    }

    /**
     * @param $wcAddress
     *
     * @return Address
     */
    public function populateTamaraAddress($wcAddress)
    {
        $firstName = !empty($wcAddress['first_name']) ? $wcAddress['first_name'] : 'N/A';
        $lastName = !empty($wcAddress['last_name']) ? $wcAddress['first_name'] : 'N/A';
        $address1 = !empty($wcAddress['address_1']) ? $wcAddress['first_name'] : 'N/A';
        $address2 = !empty($wcAddress['address_2']) ? $wcAddress['first_name'] : 'N/A';
        $city = !empty($wcAddress['city']) ? $wcAddress['first_name'] : 'N/A';
        $state = !empty($wcAddress['state']) ? $wcAddress['first_name'] : 'N/A';
        $phone = !empty($wcAddress['phone']) ? $wcAddress['phone'] : null;
        $country = !empty($wcAddress['country']) ? $wcAddress['country'] : $this->getDefaultBillingCountryCode();

        $tamaraAddress = new Address();
        $tamaraAddress->setFirstName((string)$firstName);
        $tamaraAddress->setLastName((string)$lastName);
        $tamaraAddress->setLine1((string)$address1);
        $tamaraAddress->setLine2((string)$address2);
        $tamaraAddress->setCity((string)$city);
        $tamaraAddress->setRegion((string)$state);
        $tamaraAddress->setPhoneNumber((string)$phone);
        $tamaraAddress->setCountryCode((string)$country);

        return $tamaraAddress;
    }

    /**
     * Set Tamara Order Billing Addresses
     *
     * @param WC_Order $wcOrder
     *
     * @return Address
     */
    public function populateTamaraBillingAddress($wcOrder)
    {
        $wcBillingAddress = $wcOrder->get_address('billing');

        return $this->populateTamaraAddress($wcBillingAddress);
    }

    /**
     * Set Order Shipping Addresses
     *
     * @param WC_Order $wcOrder
     *
     * @return Address
     */
    public function populateTamaraShippingAddress($wcOrder)
    {
        $wcShippingAddress = $wcOrder->get_address('shipping');
        $wcBillingAddress = $wcOrder->get_address('billing');

        $wcShippingAddress['first_name'] = !empty($wcShippingAddress['first_name']) ? $wcShippingAddress['first_name'] : (!empty($wcBillingAddress['first_name']) ? $wcBillingAddress['first_name'] : null);
        $wcShippingAddress['last_name'] = !empty($wcShippingAddress['last_name']) ? $wcShippingAddress['last_name'] : (!empty($wcBillingAddress['last_name']) ? $wcBillingAddress['last_name'] : null);
        $wcShippingAddress['address_1'] = !empty($wcShippingAddress['address_1']) ? $wcShippingAddress['address_1'] : (!empty($wcBillingAddress['address_1']) ? $wcBillingAddress['address_1'] : null);
        $wcShippingAddress['address_2'] = !empty($wcShippingAddress['address_2']) ? $wcShippingAddress['address_2'] : (!empty($wcBillingAddress['address_2']) ? $wcBillingAddress['address_2'] : null);
        $wcShippingAddress['city'] = !empty($wcShippingAddress['city']) ? $wcShippingAddress['city'] : (!empty($wcBillingAddress['city']) ? $wcBillingAddress['city'] : null);
        $wcShippingAddress['state'] = !empty($wcShippingAddress['state']) ? $wcShippingAddress['state'] : (!empty($wcBillingAddress['state']) ? $wcBillingAddress['state'] : null);
        $wcShippingAddress['country'] = !empty($wcShippingAddress['country']) ? $wcShippingAddress['country'] : (!empty($wcBillingAddress['country']) ? $wcBillingAddress['country'] : $this->getDefaultBillingCountryCode());
        $wcShippingAddress['phone'] = !empty($wcShippingAddress['phone']) ? $wcShippingAddress['phone'] : (!empty($wcBillingAddress['phone']) ? $wcBillingAddress['phone'] : null);

        return $this->populateTamaraAddress($wcShippingAddress);
    }

    /**
     * Set Tamara Consumer
     *
     * @param WC_Order $wcOrder
     *
     * @return Consumer
     */
    public function populateTamaraConsumer($wcOrder)
    {
        $wcBillingAddress = $wcOrder->get_address('billing');

        $firstName = $wcBillingAddress['first_name'] ?? 'N/A';
        $lastName = $wcBillingAddress['last_name'] ?? 'N/A';
        $email = $wcBillingAddress['email'] ?? 'notavailable@email.com';
        $phone = $wcBillingAddress['phone'] ?? 'N/A';

        $consumer = new Consumer();
        $consumer->setFirstName($firstName);
        $consumer->setLastName($lastName);
        $consumer->setEmail($email);
        $consumer->setPhoneNumber($phone);

        return $consumer;
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Create Tamara Checkout Session Request
     *
     * @param WC_Order $wcOrder
     *
     * @param $paymentType
     * @param $instalmentPeriod
     *
     * @return \Tamara\Response\Checkout\CreateCheckoutResponse
     * @throws Exception
     */
    public function createTamaraCheckoutSession($wcOrder, $paymentType, $instalmentPeriod)
    {
        $client = $this->tamaraClient;
        $checkoutRequest = new CreateCheckoutRequest($this->populateTamaraOrder($wcOrder, $paymentType, $instalmentPeriod));
        try {
            return $client->createCheckout($checkoutRequest);
        } catch (Exception $exception) {
            TamaraCheckout::getInstance()->logMessage(
                sprintf(
                    "Cannot create Tamara Checkout Session.\nError message: ' %s'.\nTrace: %s",
                    $exception->getMessage(),
                    $exception->getTraceAsString()
                )
            );
            throw new Exception('Cannot create Tamara Checkout Session');
        }
    }

    /**
     * Get Tamara Payment Types and its min/max amount
     *
     * @param $countryCode | null
     *
     * @return mixed
     */
    public function getPaymentTypes($countryCode = null)
    {
        if (!($countryCode)) {
            $countryCode = $this->getCurrentCountryCode();
        }
        $this->paymentTypes = $this->settings['country_payment_types'][$countryCode] ?? [];

        return $this->paymentTypes;
    }

    /**
     * Init admin form fields
     */
    public function initFormFields()
    {
        $form_fields = [
            'enabled' => [
                'title' => __('Enable/Disable', $this->textDomain),
                'label' => __('Enable Tamara Gateway', $this->textDomain),
                'type' => 'checkbox',
            ],
            'tamara_settings_help_texts' => [
                'title' => __('Tamara Settings Help Texts', $this->textDomain),
                'type' => 'title',
                'description' =>
                    '<div class="tamara-settings-help-texts-description">
                        <p>'.__('Here you can browse some help texts and find solutions for common issues with our plugin.',
                        $this->textDomain).'</p>
                        <ul>
                            <li><p class="tamara-highlight">'.__('If there is any issue with your API URL, API Token, 
                            Notification Key or Public Key please contact Tamara Team for support at <a href="mailto:merchant.support@tamara.co">merchant.support@tamara.co</a>', $this->textDomain).'</p></li>
                        </ul>
                    </div>'
                    .$this->renderHelpTextsHtml(),
            ],
            'tamara_confidential_config' => [
                'title' => __('Confidential Configuration', $this->textDomain),
                'type' => 'title',
                'description' => '<p>Update Your Confidential Configuration Received From Tamara.</p>',
            ],
            'environment' => [
                'title' => __('Tamara Working Mode', $this->textDomain),
                'label' => __('Choose Tamara Working Mode', $this->textDomain),
                'type' => 'select',
                'default' => static::ENVIRONMENT_LIVE_MODE,
                'options' => [
                    static::ENVIRONMENT_LIVE_MODE => 'Live Mode',
                    static::ENVIRONMENT_SANDBOX_MODE => 'Sandbox Mode',
                ],
                'description' => __('This setting specifies whether you will process live transactions, or whether you will process simulated transactions using the Tamara Sandbox.',
                    $this->textDomain),
            ],
            'live_api_url' => [
                'title' => __('Live API URL', $this->textDomain),
                'type' => 'text',
                'description' => __('The Tamara Live API URL <span class="tamara-highlight">(https://api.tamara.co)</span>', $this->textDomain),
                'default' => static::LIVE_API_URL,
                'custom_attributes' => [
                    'value' => static::LIVE_API_URL,
                ],
            ],
            'live_api_token' => [
                'title' => __('Live API Token (Merchant Token)', $this->textDomain),
                'type' => 'textarea',
                'description' => __('Get your API token from Tamara.', $this->textDomain),
            ],
            'live_notification_token' => [
                'title' => __('Live Notification Key', $this->textDomain),
                'type' => 'text',
                'description' => __('Get your Notification key from Tamara.', $this->textDomain),
                'default' => '',
            ],
            'live_public_key' => [
                'title' => __('Live Public Key', $this->textDomain),
                'type' => 'text',
                'description' => __('Get your Public key from Tamara.', $this->textDomain),
            ],
            'sandbox_api_url' => [
                'title' => __('Sandbox API URL', $this->textDomain),
                'type' => 'text',
                'description' => __('The Tamara Sandbox API URL <span class="tamara-highlight">(https://api-sandbox.tamara.co)</span>', $this->textDomain),
                'default' => static::SANDBOX_API_URL,
                'custom_attributes' => [
                    'value' => static::SANDBOX_API_URL,
                ],
            ],
            'sandbox_api_token' => [
                'title' => __('Sandbox API Token (Merchant Token)', $this->textDomain),
                'type' => 'textarea',
                'description' => __('Get your API token for testing from Tamara.', $this->textDomain),
                'required' => 'required',
            ],
            'sandbox_notification_token' => [
                'title' => __('Sandbox Notification Key', $this->textDomain),
                'type' => 'text',
                'description' => __('Get your Notification key for testing from Tamara.',
                    $this->textDomain),
            ],
            'sandbox_public_key' => [
                'title' => __('Sandbox Public Key', $this->textDomain),
                'type' => 'text',
                'description' => __('Get your Public key for testing from Tamara.',
                    $this->textDomain),
            ],
            'tamara_order_statuses_mapping' => [
                'title' => __('Order Statuses Mappings', $this->textDomain),
                'type' => 'title',
                'description' => '<p>Mapping status for order according to Tamara action result.</p>
                <div class="tamara-order-statuses-mappings-manage button-primary">'.__('Manage Order Statuses Mappings', $this->textDomain).'<i class="tamara-toggle-btn fa-solid fa-chevron-down"></i></div>',
            ],
            'tamara_payment_cancel' => [
                'title' => __('Order status for payment cancelled from Tamara', $this->textDomain),
                'type' => 'select',
                'default' => 'wc-tamara-p-canceled',
                'options' => wc_get_order_statuses(),
                'description' => __('Map status for order when the payment is cancelled from Tamara during checkout.', $this->textDomain),
            ],
            'tamara_payment_failure' => [
                'title' => __('Order status for payment failed from Tamara', $this->textDomain),
                'type' => 'select',
                'default' => 'wc-tamara-p-failed',
                'options' => wc_get_order_statuses(),
                'description' => __('Map status for order when the payment is failed from Tamara during checkout.', $this->textDomain),
            ],
            'tamara_authorise_done' => [
                'title' => __('Order status for Authorise success from Tamara', $this->textDomain),
                'type' => 'select',
                'default' => 'wc-processing',
                'options' => wc_get_order_statuses(),
                'description' => __('Map status for order when the payment is authorised successfully from Tamara.', $this->textDomain),
            ],
            'tamara_authorise_failure' => [
                'title' => __('Order status for Authorise failed from Tamara', $this->textDomain),
                'type' => 'select',
                'default' => 'wc-tamara-a-failed',
                'options' => wc_get_order_statuses(),
                'description' => __('Map status for order when the payment is failed in authorising from Tamara.', $this->textDomain),
            ],
            'tamara_capture_failure' => [
                'title' => __('Order status for Capture failed from Tamara', $this->textDomain),
                'type' => 'select',
                'default' => 'wc-tamara-c-failed',
                'options' => wc_get_order_statuses(),
                'description' => __('Map status for order when the Capture process is failed.', $this->textDomain),
            ],
            'tamara_order_cancel' => [
                'title' => __('Order status for cancelling the order from Tamara through Webhook', $this->textDomain),
                'type' => 'select',
                'default' => 'wc-tamara-o-canceled',
                'options' => wc_get_order_statuses(),
                'description' => __('Map status for order when it is cancelled from Tamara (Order Expired, Order Declined...) through Webhook.',
                    $this->textDomain),
            ],
            'tamara_order_statuses_trigger' => [
                'title' => __('Order Statuses to Trigger Tamara Events', $this->textDomain),
                'type' => 'title',
                'description' => '<p>Update order statuses used to trigger events to Tamara.</p>
                <div class="tamara-order-statuses-trigger-manage button-primary">'.__('Manage Order Statuses Trigger', $this->textDomain).'<i class="tamara-toggle-btn fa-solid fa-chevron-down"></i></div>',
            ],
            'tamara_cancel_order' => [
                'title' => __('Order status that trigger Tamara cancel process for an order', $this->textDomain),
                'type' => 'select',
                'options' => wc_get_order_statuses()['wc-cancelled'],
                'description' => __('When you update an order to this status it would connect to Tamara API to trigger the Cancel payment process on Tamara.',
                    $this->textDomain),
            ],
            'tamara_payment_capture' => [
                'title' => __('Order status that trigger Tamara capture process for an order', $this->textDomain),
                'type' => 'select',
                'default' => 'wc-completed',
                'options' => wc_get_order_statuses(),
                'description' => __('When you update an order to this status it would connect to Tamara API to trigger the Capture payment process on Tamara.', $this->textDomain),
            ],
            'tamara_custom_settings' => [
                'title' => __('Tamara Custom Settings', $this->textDomain),
                'type' => 'title',
                'description' => __('Configure Tamara Custom Settings', $this->textDomain)
                                 .'<div class="tamara-custom-settings-manage button-primary">'.__('Show Tamara Custom Settings', $this->textDomain).'<i class="tamara-toggle-btn fa-solid fa-chevron-down"></i></div>'
            ],
            'excluded_products' => [
                'title' => __('Excluded Product Ids', $this->textDomain),
                'type' => 'text',
                'description' => __('Enter the product ids that you want to exclude from using Tamara to checkout 
                (These ids are separated by commas e.g. 101, 205).', $this->textDomain),
                'default' => null,
            ],
            'excluded_product_categories' => [
                'title' => __('Excluded Product Category Ids', $this->textDomain),
                'type' => 'text',
                'description' => __('Enter the product category ids that you want to exclude from using Tamara to checkout 
                (These ids are separated by commas e.g. 26, 104).', $this->textDomain),
                'default' => null,
            ],
            'beautify_merchant_urls' => [
                'title' => __('Enable Beautiful Merchant Urls', $this->textDomain),
                'type' => 'checkbox',
                'description' => __('In you tick on this setting, the urls for handling webhook will be beautified. After enabling this, please go to "Dashboard => Settings => Permalinks" and click on "Save Changes" to take effect.',
                    $this->textDomain),
            ],
            'tamara_general_settings' => [
                'title' => __('Tamara Advanced Settings', $this->textDomain),
                'type' => 'title',
                'description' => __('Configure Tamara Advanced Settings <br>
                                <p class="tamara-highlight">Please read the descriptions of these settings carefully before making a change 
                                or please contact Tamara Team for more details.</p>'
                        , $this->textDomain)
                 .'<div class="tamara-advanced-settings-manage button-primary">'.__('Show Tamara Advanced Settings', $this->textDomain).'<i class="tamara-toggle-btn fa-solid fa-chevron-down"></i></div>'
            ],
            'crobjob_enabled' => [
                'title' => __('Enable Cron Job', $this->textDomain),
                'type' => 'checkbox',
                'description' => __('In you tick on this setting, Tamara will use a cron-job to find all completed orders that has not been verified but not authorised or not captured within 180 days and force them to be authorised or captured. It fires an asynchronous call on Admin request to perform this action.',
                    $this->textDomain),
                'default' => 'yes',
            ],
            'force_billing_phone' => [
                'title' => __('Force Enable Billing Phone', $this->textDomain),
                'label' => __('Enable Billing Phone Field', $this->textDomain),
                'default' => 'yes',
                'type' => 'checkbox',
                'description' => __('In you tick on this setting, the billing phone field will be forced to display on checkout screen, which is required to use for Tamara checkout.',
                    $this->textDomain),
            ],
            'popup_widget_disabled' => [
                'title' => __('Disable Single Product Details Popup (PDP) Widget', $this->textDomain),
                'label' => __('Disable PDP Widget on Single Product Page', $this->textDomain),
                'default' => 'no',
                'type' => 'checkbox',
                'description' => __('In you tick on this setting, the PDP widget will be hidden on the single product page.',
                    $this->textDomain),
            ],
            'popup_widget_position' => [
                'title' => __('PDP Widget Position', $this->textDomain),
                'type' => 'select',
                'options' => static::TAMARA_POPUP_WIDGET_POSITIONS,
                'description' => __('Choose a position where you want to display the Tamara Payment Popup Widget on single product page. Or, you can use shortcode with attributes to show it on custom pages 
                    e.g. [tamara_show_popup price="99" currency="SAR" language="en"]', $this->textDomain),
                'default' => 'woocommerce_single_product_summary',
            ],
            'cart_popup_widget_disabled' => [
                'title' => __('Disable Cart Popup Widget', $this->textDomain),
                'label' => __('Disable Cart Popup Widget', $this->textDomain),
                'default' => 'no',
                'type' => 'checkbox',
                'description' => __('In you tick on this setting, the popup widget will be hidden on the cart page.',
                    $this->textDomain),
            ],
            'cart_popup_widget_position' => [
                'title' => __('Cart Popup Widget Position', $this->textDomain),
                'type' => 'select',
                'options' => static::TAMARA_CART_POPUP_WIDGET_POSITIONS,
                'description' => __('Choose a position where you want to display the Tamara Payment Popup Widget on cart page.', $this->textDomain),
                'default' => 'woocommerce_proceed_to_checkout',
            ],
            'webhook_enabled' => [
                'title' => __('Enable Webhook', $this->textDomain),
                'type' => 'checkbox',
                'description' => __('In you tick on this setting, Tamara will use the webhook to handle the Order Declined and Order Expired.',
                    $this->textDomain)
                .'<p><strong>Webhook ID: </strong>'.$this->getWebhookId().'</p>'
            ],
            'cancel_url' => [
                'title' => __('Tamara Payment Cancel Url', $this->textDomain),
                'type' => 'text',
                'description' => __('Enter the custom CANCEL url for customers to be redirected to after PAYMENT is CANCELLED (leave it blank to use the default one). You can use action `after_tamara_cancel` to handle further actions.',
                    $this->textDomain),
                'default' => null,
            ],
            'failure_url' => [
                'title' => __('Tamara Payment Failure Url', $this->textDomain),
                'type' => 'text',
                'description' => __('Enter the custom FAILURE url for customers to be redirected to after PAYMENT is FAILED (leave it blank to use the default one). You can use action `after_tamara_failure` to handle further actions.',
                    $this->textDomain),
                'default' => null,
            ],
            'debug_info' => [
                'title' => __('Debug Info', $this->textDomain),
                'type' => 'title',
                'description' =>
                    '<div class="debug-info-manage button-primary" >'.__('Show Debug Info', $this->textDomain).'<i class="tamara-toggle-btn fa-solid fa-chevron-down"></i></div>'
            ],
            'debug_info_text' => [
                'type' => 'text',
                'title' => 'Platform & Extensions:',
                'description' =>
                    '<table class="tamara-debug-info-table">'.'<tr><td>'.sprintf('<strong>PHP Version:</strong> %s', PHP_VERSION).'</td></tr>'
                    .'<tr><td>'.sprintf('<strong>PHP loaded extension:</strong> %s', implode(', ', get_loaded_extensions())).'</td></tr>'
                    .'<tr><td><h4>Default Merchant URLs:</h4></td></tr>'
                    .'<tr><td><ul><li>'.__('Tamara Success URL: ', $this->textDomain).('Default <strong>WooCommerce Order Received</strong> url is used.').'</li>'
                    .'<li>'.__('Tamara Cancel URL: ', $this->textDomain).($this->getTamaraCancelUrl() ?: 'N/A').'</li>'
                    .'<li>'.__('Tamara Failure URL: ', $this->textDomain).($this->getTamaraFailureUrl() ?: 'N/A').'</li>'
                    .'<li>'.__('Tamara Notification URL: ', $this->textDomain).($this->getTamaraIpnUrl() ?: 'N/A').'</li>'
                    .'<li>'.__('Tamara Webhook URL: ', $this->textDomain).($this->getTamaraWebhookUrl() ?: 'N/A').'</li></ul></td></tr>'.'</table>'
            ],
            'custom_log_message_enabled' => [
                'title' => __('Enable Tamara Custom Log Message', $this->textDomain),
                'type' => 'checkbox',
                'description' =>
                    __('In you tick on this setting, all the message logs will be written and saved to the Tamara custom log file in your upload directory. 
                     The message log download link will be <strong>available below</strong>,  after you <strong>enable this setting.</strong>', $this->textDomain),
            ],
            'custom_log_message' => [
                'title' => __('Tamara Custom Log Message', $this->textDomain),
                'type' => 'text',
                'description' => $this->prepareDebugLogDownloadLink(),
            ],
            'plugin_version' => [
                'type' => 'title',
                'description' =>
                    '<p style="margin-top: 2.6rem;">'.sprintf('Tamara Checkout Plugin Version: %s', TAMARA_CHECKOUT_VERSION).'</p>'
            ]
        ];

        $this->form_fields = $form_fields;

        if (!TamaraCheckout::getInstance()->isCustomLogMessageEnabled()) {
            unset($this->form_fields['custom_log_message']);
            if (file_exists(TamaraCheckout::getInstance()->logMessageFilePath())) {
                wp_delete_file(TamaraCheckout::getInstance()->logMessageFilePath());
            }
        }
    }

    /**
     * Get Shipping Information
     */
    public function getShippingInfo()
    {
        $shippedAt = new DateTimeImmutable();
        $shippingCompany = 'N/A';
        $trackingNumber = 'N/A';
        $trackingUrl = 'N/A';

        return new ShippingInfo($shippedAt, $shippingCompany, $trackingNumber, $trackingUrl);
    }

    /**
     * Tamara Capture Payment
     *
     * @param int $wcOrderId
     * @param WC_Order $statusFrom
     * @param WC_Order $statusTo
     * @param WC_Order $wcOrder
     *
     */
    public function tamaraCapturePayment($wcOrderId, $statusFrom, $statusTo, $wcOrder)
    {
        if (TamaraCheckout::TAMARA_FULLY_CAPTURED_STATUS === TamaraCheckout::getInstance()->getTamaraOrderStatus($wcOrderId)) {
            $wcOrder->add_order_note(__('Tamara - The payment has been captured successfully.', $this->textDomain));
            return true;
        } else {
            $payment_method = $wcOrder->get_payment_method();
            // Remove wc- prefix
            $tamaraCapturePaymentStatus = substr($this->tamaraStatus['payment_capture'], 3);

            if ($tamaraCapturePaymentStatus === $statusTo && TamaraCheckout::getInstance()->isTamaraGateway($payment_method)) {
                $tamaraOrderId = $this->getTamaraOrderId($wcOrderId);
                $captureId = $this->getTamaraCaptureId($wcOrderId);
                if (empty($captureId) && $tamaraOrderId &&
                    TamaraCheckout::TAMARA_AUTHORISED_STATUS === TamaraCheckout::getInstance()->getTamaraOrderStatus($wcOrderId)) {
                    $this->captureWcOrder($wcOrderId);
                } elseif ($captureId) {
                    $this->updateTamaraCaptureId($wcOrderId, $captureId);
                    $orderNote = 'Tamara - The payment of this order was already captured.';
                    $newOrderStatus = $this->tamaraStatus['payment_capture'] ?? $statusFrom;
                    TamaraCheckout::getInstance()->updateOrderStatusAndAddOrderNote($wcOrder, $orderNote, $newOrderStatus, '');
                }
            }
        }
    }

    /**
     * Tamara Capture Payment Method
     *
     * @param int $wcOrderId
     *
     */
    public function captureWcOrder($wcOrderId)
    {
        $wcOrder = wc_get_order($wcOrderId);
        $wcOrderTotal = new Money(MoneyHelper::formatNumber($wcOrder->get_total()), $wcOrder->get_currency());
        $wcShippingTotal = new Money(MoneyHelper::formatNumber($wcOrder->get_shipping_total()), $wcOrder->get_currency());
        $wcTaxTotal = new Money(MoneyHelper::formatNumber($wcOrder->get_total_tax()), $wcOrder->get_currency());
        $wcDiscountTotal = new Money(MoneyHelper::formatNumber($wcOrder->get_discount_total()), $wcOrder->get_currency());
        $wcOrderShippingInfo = $this->getShippingInfo();
        $wcOrderItems = $this->populateTamaraOrderItems($wcOrder);
        $tamaraOrderId = $this->getTamaraOrderId($wcOrderId);

        try {
            $captureResponse = $this->tamaraClient->capture(
                new CaptureRequest(
                    new Capture(
                        $tamaraOrderId,
                        $wcOrderTotal,
                        $wcShippingTotal,
                        $wcTaxTotal,
                        $wcDiscountTotal,
                        $wcOrderItems,
                        $wcOrderShippingInfo
                    )
                )
            );
            TamaraCheckout::getInstance()->logMessage(sprintf("Tamara Capture Response Data: %s", print_r($captureResponse, true)));
        } catch (Exception $tamaraCaptureException) {
            TamaraCheckout::getInstance()->logMessage(
                sprintf(
                    "Tamara Service timeout or disconnected.\nError message: ' %s'.\nTrace: %s",
                    $tamaraCaptureException->getMessage(),
                    $tamaraCaptureException->getTraceAsString()
                )
            );
        }

        if (!empty($captureResponse) && $captureResponse instanceof CaptureResponse && $captureResponse->isSuccess()) {
            $captureId = $captureResponse->getCaptureId();
            $this->updateTamaraCaptureId($wcOrderId, $captureId);
            $orderNote = sprintf(__('Tamara - The payment has been captured successfully. Capture ID: %s'), $captureId);
            $newOrderStatus = $this->tamaraStatus['payment_capture'];
        } else {
            $errorMessage = null;
            if (isset($tamaraCaptureException) && $tamaraCaptureException instanceof Exception) {
                $errorMessage = $tamaraCaptureException->getMessage();
                TamaraCheckout::getInstance()->logMessage($errorMessage);
            } elseif (isset($captureResponse)) {
                $errorMessage = $captureResponse->getMessage();
                TamaraCheckout::getInstance()->logMessage($errorMessage);
                if (409 === $captureResponse->getStatusCode()) {
                    $orderNote = sprintf(__('Tamara - There was a conflict in capturing the payment, error message: %s'), $errorMessage);
                    $wcOrder->add_order_note($orderNote);

                    return;
                }
            }
            $orderNote = sprintf(__('Tamara - The payment can not be captured, error message: %s'), $errorMessage);
            $newOrderStatus = $this->tamaraStatus['capture_failed'];
        }
        TamaraCheckout::getInstance()->updateOrderStatusAndAddOrderNote($wcOrder, $orderNote, $newOrderStatus, '');
    }

    /**
     * Handle Tamara Cancel Order before it's captured
     *
     * @param int $wcOrderId
     * @param WC_Order $statusFrom
     * @param WC_Order $statusTo
     * @param WC_Order $wcOrder
     *
     */
    public function tamaraCancelOrder($wcOrderId, $statusFrom, $statusTo, $wcOrder)
    {
        $payment_method = $wcOrder->get_payment_method();
        if (TamaraCheckout::TAMARA_CANCELED_STATUS === TamaraCheckout::getInstance()->getTamaraOrderStatus($wcOrderId)) {
            $wcOrder->add_order_note(__('Tamara – The order has been cancelled successfully', $this->textDomain));
            return true;
        } else {
            if ('cancelled' === $statusTo && TamaraCheckout::getInstance()->isTamaraGateway($payment_method)) {
                $wcOrderTotal = new Money(MoneyHelper::formatNumber($wcOrder->get_total()), $wcOrder->get_currency());
                $wcShippingTotal = new Money(MoneyHelper::formatNumber($wcOrder->get_shipping_total()),
                    $wcOrder->get_currency());
                $wcTaxTotal = new Money(MoneyHelper::formatNumber($wcOrder->get_total_tax()), $wcOrder->get_currency());
                $wcDiscountTotal = new Money(MoneyHelper::formatNumber($wcOrder->get_discount_total()),
                    $wcOrder->get_currency());
                $wcOrderItems = $this->populateTamaraOrderItems($wcOrder);
                $tamaraOrderId = $this->getTamaraOrderId($wcOrderId);
                $captureId = $this->getTamaraCaptureId($wcOrderId);
                if (empty($captureId) && $tamaraOrderId) {
                    try {
                        $cancelResponse = $this->tamaraClient->cancelOrder(new CancelOrderRequest($tamaraOrderId,
                            $wcOrderTotal,
                            $wcOrderItems, $wcShippingTotal, $wcTaxTotal, $wcDiscountTotal));
                        TamaraCheckout::getInstance()->logMessage(sprintf("Tamara Cancel Response Data: %s", print_r($cancelResponse, true)));
                    } catch (Exception $tamaraCancelException) {
                        TamaraCheckout::getInstance()->logMessage(sprintf("Tamara Service timeout or disconnected.\nError message: '%s'.\nTrace: %s",
                            $tamaraCancelException->getMessage(), $tamaraCancelException->getTraceAsString()));
                    }

                    if (isset($cancelResponse) && $cancelResponse->isSuccess()) {
                        $cancelId = $cancelResponse->getCancelId();
                        $wcOrder->add_order_note(__('Tamara – The order has been cancelled successfully', $this->textDomain));
                        $this->updateTamaraCancelId($wcOrderId, $cancelId);
                    } else {
                        $errorMessage = null;
                        if (isset($tamaraCancelException) && $tamaraCancelException instanceof Exception) {
                            $errorMessage = $tamaraCancelException->getMessage();
                            TamaraCheckout::getInstance()->logMessage($errorMessage);
                        } elseif (isset($cancelResponse)) {
                            $errorMessage = $cancelResponse->getMessage();
                            TamaraCheckout::getInstance()->logMessage($errorMessage);
                        }
                        $orderNote = ('Tamara – The order can not be cancelled - '.$errorMessage);
                        $newOrderStatus = $statusFrom;
                        TamaraCheckout::getInstance()->updateOrderStatusAndAddOrderNote($wcOrder, $orderNote, $newOrderStatus, '');
                    }

                } elseif ($captureId) {
                    $orderNote = ('Tamara – The order can not be cancelled as it was captured. Please try "Refund" function instead.');
                    $newOrderStatus = $statusFrom;
                    TamaraCheckout::getInstance()->updateOrderStatusAndAddOrderNote($wcOrder, $orderNote, $newOrderStatus, '');
                }
            }
        }
    }

    /**
     * Register Tamara Webhook
     *
     * @return string|null
     */
    public function registerTamaraWebhook()
    {
        try {
            $webhookUrl = $this->getTamaraWebhookUrl();
            $webhookRequest = new RegisterWebhookRequest(
                $webhookUrl, static::REGISTERED_WEBHOOKS
            );

            $response = $this->tamaraClient->registerWebhook($webhookRequest);
            if ($response->isSuccess()) {
                $webhookId = $response->getWebhookId();
                TamaraCheckout::getInstance()->logMessage(sprintf("Webhook Register Data: %s", print_r($response, true)));

                return $webhookId;
            }

            $firstErrorCode = $response->getErrors()[0]['error_code'] ?? '';
            if ('webhook_already_registered' === $firstErrorCode) {
                $registeredWebhookId = $response->getErrors()[0]['data']['webhook_id'] ?? null;
                TamaraCheckout::getInstance()->logMessage(sprintf("Webhook Already Registered"));

                return $registeredWebhookId;
            }
        } catch (Exception $exception) {
            TamaraCheckout::getInstance()->logMessage(
                sprintf(
                    "Webhook Register Failed.\nError message: ' %s'.\nTrace: %s",
                    $exception->getMessage(),
                    $exception->getTraceAsString()
                )
            );
        }

        return null;
    }

    /**
     * Delete Tamara Webhook
     *
     * @param $webhookId string
     *
     * @return bool
     */
    public function deleteTamaraWebhook($webhookId)
    {
        try {
            $request = new RemoveWebhookRequest($webhookId);
            $response = $this->tamaraClient->removeWebhook($request);

            if ($response->isSuccess()) {
                TamaraCheckout::getInstance()->logMessage(sprintf("Webhook Delete Data: %s", print_r($response, true)));

                return true;
            }
        } catch (Exception $exception) {
            TamaraCheckout::getInstance()->logMessage(
                sprintf(
                    "Webhook Delete Failed.\nError message: ' %s'.\nTrace: %s",
                    $exception->getMessage(),
                    $exception->getTraceAsString()
                )
            );
        }

        return false;
    }

    /**
     * Check if all the required field values have been input or not
     * and verify api params
     */
    public function validateRequiredFields()
    {
        $workingMode = $this->get_option('environment');
        $apiUrl = (static::ENVIRONMENT_LIVE_MODE === $workingMode) ? $this->get_option('live_api_url') : $this->get_option('sandbox_api_url');
        $apiToken = (static::ENVIRONMENT_LIVE_MODE === $workingMode) ? $this->get_option('live_api_token') : $this->get_option('sandbox_api_token');
        $notificationToken = (static::ENVIRONMENT_LIVE_MODE === $workingMode) ? $this->get_option('live_notification_token') : $this->get_option('sandbox_notification_token');

        if (!$apiUrl || !$apiToken || !$notificationToken || !$this->verifyApiParams($apiUrl, $apiToken)) {
            return false;
        }

        return true;
    }

    /**
     * Verify to have correct Tamara API url and token or not
     *
     * @param $apiUrl
     * @param $apiToken
     *
     * @return bool
     */
    public function verifyApiParams($apiUrl, $apiToken)
    {
        $tamaraClient = $this->buildTamaraClient($apiUrl, $apiToken);
        $merchant = new Merchant();
        $getDetailsInfoRequest = new GetDetailsInfoRequest($merchant);
        try {
            $getDetailsInfoResponse = $tamaraClient->getMerchantDetailsInfo($getDetailsInfoRequest);
            if ($getDetailsInfoResponse->isSuccess()) {
                return true;
            }
        } catch (Exception $getDetailsInfoException) {
            TamaraCheckout::getInstance()->logMessage(
                sprintf(
                    "Tamara Get Merchant Details Info Failed Response.\nError message: ' %s'.\nTrace: %s",
                    $getDetailsInfoException->getMessage(),
                    $getDetailsInfoException->getTraceAsString()
                )
            );
        }

        return false;
    }

    /**
     * Raise Admin Error notice on failed API params
     */
    public function raiseAdminError()
    {
        WC_Admin_Settings::add_error(
            __('Error! Tamara checkout params cannot be retrieved correctly. Please recheck your Tamara API Token (Merchant Token).', $this->textDomain)
        );
    }

    /**
     * Raise Admin Error notice on non selected payment types
     */
    public function raiseAdminErrorOnNonSelectedPaymentTypes()
    {
        WC_Admin_Settings::add_error(
            __('Error! Tamara disabled. Please choose at least one payment type to use with Tamara.',
                $this->textDomain)
        );
    }

    /**
     * Get Merchant Success Url Slug
     *
     * @param array $params
     * @param WC_Order $wcOrder
     *
     * @return string
     */
    public function getTamaraSuccessUrl($wcOrder, $params = [])
    {
        $orderReceivedUrl = !empty($wcOrder) ? esc_url_raw($wcOrder->get_checkout_order_received_url()) : home_url();
        $successUrlFromTamara = add_query_arg(
            $params, $orderReceivedUrl
        );

        return TamaraCheckout::getInstance()->removeTrailingSlashes($successUrlFromTamara);
    }

    /**
     * Get Merchant Cancel Url Slug
     *
     * @param $params
     *
     * @return string
     */
    public function getTamaraCancelUrl($params = [])
    {
        $cancelUrl = $this->get_option('cancel_url') ? $this->get_option('cancel_url') : null;
        if (!empty($cancelUrl)) {
            $cancelUrl = add_query_arg(
                $params,
                $cancelUrl
            );

            return TamaraCheckout::getInstance()->removeTrailingSlashes($cancelUrl);
        }

        if ($this->isBeautifyMerchantUrlsEnabled()) {
            $cancelUrlFromTamara = add_query_arg(
                $params,
                home_url(static::PAYMENT_CANCEL_SLUG)
            );
        } else {
            $cancelUrlFromTamara = add_query_arg(
                array_merge(
                    $params,
                    [
                        'pagename' => static::PAYMENT_CANCEL_SLUG,
                    ]
                ),
                home_url()
            );
        }

        return TamaraCheckout::getInstance()->removeTrailingSlashes($cancelUrlFromTamara);
    }

    /**
     * Get Merchant Failure Url Slug
     *
     * @param $params
     *
     * @return string
     */
    public function getTamaraFailureUrl($params = [])
    {
        $failureUrl = $this->get_option('failure_url') ? $this->get_option('failure_url') : null;
        if (!empty($failureUrl)) {
            $failureUrl = add_query_arg(
                $params,
                $failureUrl
            );

            return TamaraCheckout::getInstance()->removeTrailingSlashes($failureUrl);
        }

        if ($this->isBeautifyMerchantUrlsEnabled()) {
            $failUrlFromTamara = add_query_arg(
                $params,
                home_url(static::PAYMENT_FAIL_SLUG)
            );
        } else {
            $failUrlFromTamara = add_query_arg(
                array_merge(
                    $params,
                    [
                        'pagename' => static::PAYMENT_FAIL_SLUG,
                    ]
                ),
                home_url()
            );
        }

        return TamaraCheckout::getInstance()->removeTrailingSlashes($failUrlFromTamara);
    }

    /**
     * Get Tamara Ipn Url to handle Notification
     */
    public function getTamaraIpnUrl()
    {
        if ($this->isBeautifyMerchantUrlsEnabled()) {
            return home_url(static::IPN_SLUG);
        } else {
            return add_query_arg(
                [
                    'pagename' => static::IPN_SLUG,
                ],
                home_url()
            );
        }
    }

    /**
     * Get Tamara Webhook Url Slug
     */
    public function getTamaraWebhookUrl()
    {
        if ($this->isBeautifyMerchantUrlsEnabled()) {
            return home_url(static::WEBHOOK_SLUG);
        } else {
            return add_query_arg(
                [
                    'pagename' => static::WEBHOOK_SLUG,
                ],
                home_url()
            );
        }
    }

    /**
     * We need to update settings to db options (table options)
     */
    public function updateThisSettingsToOptions()
    {
        update_option(
            $this->get_option_key(),
            apply_filters(
                'woocommerce_settings_api_sanitized_fields_'.$this->id,
                $this->settings
            ),
            'yes'
        );
    }

    /**
     * Initialize attributes that are fixed
     */
    protected function initBaseAttributes()
    {
        $this->id = TamaraCheckout::TAMARA_GATEWAY_ID;
        $this->has_fields = true;
        $this->order_button_text = __('Proceed to Tamara Payment', $this->textDomain);
        $this->method_title = __('Tamara Gateway', $this->textDomain);
        $this->method_description = __(static::TAMARA_GATEWAY_DEFAULT_TITLE, $this->textDomain);
        $this->errorMap = $this->getErrorMap();
        $this->paymentType = static::PAYMENT_TYPE_PAY_BY_LATER;
        if (is_admin()) {
            $this->title = static::TAMARA_GATEWAY_DEFAULT_TITLE;
        } else {
            $this->title = __($this->getPaymentTypeTitleMapping()[$this->id], $this->textDomain) ?? static::TAMARA_GATEWAY_DEFAULT_TITLE;
        }
    }

    /**
     * Initialize attributes that can be changed through admin settings
     */
    protected function initSettingAttributes()
    {
        // Ensure the environment is set before calling isLiveMode()
        $this->environment = $this->get_option('environment', static::ENVIRONMENT_LIVE_MODE);
        $this->initTamaraClient();

        $this->enabled = $this->get_option('enabled', 'no');
        $this->payByLaterEnabled = $this->get_option('pay_by_later_enabled', 'no');
        $this->payByInstalmentsEnabled = $this->get_option('pay_by_instalments_enabled', 'no');
        $this->customLogMessageEnabled = $this->get_option('custom_log_message_enabled', 'no');
        $this->popupWidgetPosition = $this->get_option('popup_widget_position', 'woocommerce_single_product_summary');
        $this->webhookEnabled = $this->get_option('webhook_enabled', 'no');
        $this->beautifyMerchantUrlsEnabled = $this->get_option('beautify_merchant_urls', 'no');
        $this->webhookId = $this->get_option('webhook_id');

        $this->initTamaraStatus();
    }

    /**
     * Initialize SDK client instance for Tamara actions
     */
    protected function initTamaraClient()
    {
        if ($this->isLiveMode()) {
            $this->apiUrl = $this->get_option('live_api_url', null);
            $this->apiToken = $this->get_option('live_api_token', null);
            $this->notificationToken = $this->get_option('live_notification_token', null);
            $this->publicKey = $this->get_option('live_public_key', null);
        } else {
            $this->apiUrl = $this->get_option('sandbox_api_url', null);
            $this->apiToken = $this->get_option('sandbox_api_token', null);
            $this->notificationToken = $this->get_option('sandbox_notification_token', null);
            $this->publicKey = $this->get_option('sandbox_public_key', null);
        }
        $this->tamaraClient = $this->buildTamaraClient($this->apiUrl, $this->apiToken);
        $this->refinePublicKey();
    }

    /**
     * Refine Public key and save to option when it's empty
     */
    protected function refinePublicKey()
    {
        $publicKey = $this->isLiveMode() ? $this->get_option('live_public_key', null) : $this->get_option('sandbox_public_key', null);
        if (empty($publicKey)) {
            $publicKey = $this->getPublicKeyFromRemote();
            if (!empty($publicKey)) {
                $this->publicKey = $publicKey;
                if ($this->isLiveMode()) {
                    $this->update_option('live_public_key', $publicKey);
                } else {
                    $this->update_option('sandbox_public_key', $publicKey);
                }
            }
        }
    }

    /**
     * Initilize Tamara Custom Order Statuses for order managements
     */
    protected function initTamaraStatus()
    {
        $this->tamaraStatus['payment_cancelled'] = $this->get_option('tamara_payment_cancel', 'wc-tamara-p-canceled');
        $this->tamaraStatus['payment_failed'] = $this->get_option('tamara_payment_failure', 'wc-tamara-p-failed');
        $this->tamaraStatus['payment_capture'] = $this->get_option('tamara_payment_capture', 'wc-completed');
        $this->tamaraStatus['capture_failed'] = $this->get_option('tamara_capture_failure', 'wc-tamara-c-failed');
        $this->tamaraStatus['authorise_done'] = $this->get_option('tamara_authorise_done', 'wc-processing');
        $this->tamaraStatus['authorise_failed'] = $this->get_option('tamara_authorise_failure', 'wc-tamara-a-failed');
        $this->tamaraStatus['order_cancelled'] = $this->get_option('tamara_order_cancel', 'wc-tamara-o-canceled');
    }

    /**
     * Common error codes when calling create checkout session API
     */
    protected function getErrorMap()
    {
        return [
            'total_amount_invalid_limit_24hrs_gmv' => __('We are not able to process your order via Tamara currently, please try again later or proceed with a different payment method.', $this->textDomain),
            'tamara_disabled' => __('Tamara is currently unavailable, please try again later.', $this->textDomain),
            'consumer_invalid_phone_number' => __('Invalid Consumer Phone Number', $this->textDomain),
            'invalid_phone_number' => __('Invalid Phone Number', $this->textDomain),
            'total_amount_invalid_currency' => __('We do not support cross currencies. Please select the correct currency for your country.', $this->textDomain),
            'billing_address_invalid_phone_number' => __('Invalid Billing Address Phone Number', $this->textDomain),
            'shipping_address_invalid_phone_number' => __('Invalid Shipping Address Phone Number', $this->textDomain),
            'total_amount_invalid_limit' => __('The grand total of order is over/under limit of Tamara', $this->textDomain),
            'currency_unsupported' => __('We do not support cross currencies. Please select the correct currency for your country', $this->textDomain),
            'Your order information is invalid' => __('Your order information is invalid', $this->textDomain),
            'Invalid country code' => __('Invalid country code', $this->textDomain),
            'We do not support your delivery country' => __('We do not support your delivery country', $this->textDomain),
            'Your phone number is invalid. Please check again' => __('Your phone number is invalid. Please check again', $this->textDomain),
            'We do not support cross currencies. Please select the correct currency for your country' => __('We do not support cross currencies. Please select the correct currency for your country', $this->textDomain),
        ];
    }

    /**
     * Get webhook id and shown in settings page
     */
    protected function getWebhookId()
    {
        if ($this->get_option('webhook_id') && !empty($this->get_option('webhook_id'))) {
            return $this->get_option('webhook_id');
        } else {
            return 'N/A';
        }
    }

    /**
     * Handle rewrite rules on enable/disable beautiful merchant urls
     */
    protected function processRewriteRules()
    {
        $this->beautifyMerchantUrlsEnabled = $this->get_option('beautify_merchant_urls');
        if ($this->isBeautifyMerchantUrlsEnabled()) {
            // Add rewrite rules
            TamaraCheckout::getInstance()->addCustomRewriteRules();
            // Flush rewrite rules
            flush_rewrite_rules(false);
        }
    }

    /**
     * Populate Pay By Later type min limit
     */
    public function populateMinLimit()
    {
        $minLimit = !empty($this->getPaymentTypes()[static::PAYMENT_TYPE_PAY_BY_LATER]['min_limit']) ? $this->getPaymentTypes()[static::PAYMENT_TYPE_PAY_BY_LATER]['min_limit'] : null;
        $this->settings['min_limit'] = $minLimit;

        return $minLimit;
    }

    /**
     * Populate Pay By Later type max limit
     */
    public function populateMaxLimit()
    {
        $maxLimit = !empty($this->getPaymentTypes()[static::PAYMENT_TYPE_PAY_BY_LATER]['max_limit']) ? $this->getPaymentTypes()[static::PAYMENT_TYPE_PAY_BY_LATER]['max_limit'] : null;
        $this->settings['max_limit'] = $maxLimit;

        return $maxLimit;
    }

    /**
     * Get country code based on its currency
     *
     * @return array
     */
    public function getCurrencyToCountryMapping()
    {
        return [
            'SAR' => 'SA',
            'AED' => 'AE',
            'KWD' => 'KW',
            'BHD' => 'BH',
        ];
    }

    /**
     * Get country name based on country code
     *
     * @return array
     */
    public function getCountryCodeToCountryMapping()
    {
        return [
            'SA' => 'Saudi Arabia',
            'AE' => 'United Arab Emirates',
            'KW' => 'Kuwait',
            'BH' => 'Bahrain',
        ];
    }

    /**
     * Build Tamara Country Payment Types Cache Key with unique Api params
     *
     * @return string
     */
    public function buildCountryPaymentTypesCacheKey()
    {
        return 'tamara_country_payment_types_'.md5(json_encode([
                $this->apiUrl,
                $this->apiToken,
            ]));
    }

    /**
     * Build Tamara Country Payment Types Cache Key V2 with unique Api params
     *
     * @return string
     */
    public function buildCountryPaymentTypesCacheKeyV2($cartTotal, $customerPhone, $countryCode, $isVip)
    {
        $saltArr = [
            $this->apiToken,
            $this->apiUrl,
            $cartTotal,
            $customerPhone,
            $countryCode,
            $isVip
        ];
        return 'tamara_country_payment_types_v2_'.md5(json_encode($saltArr));
    }

    /**
     * Get All Country Payment Types Limit Amounts
     *
     * @return array
     */
    public function getCountryPaymentTypes($getFromCache = true, $currency = '', $phone = '', $total = null)
    {
        $countryPaymentTypesCacheKey = $this->buildCountryPaymentTypesCacheKey();
        $countryPaymentTypes = [];
        if ($getFromCache) {
            $countryPaymentTypes = get_transient($countryPaymentTypesCacheKey);
        }
        if (empty($countryPaymentTypes)) {
            $countryCodes = $this->get_option('allowed_shipping_country_codes') ?? [];
            if (is_string($countryCodes)) {
                $countryCodes = $this->convertAllowedCountryStringValue($countryCodes);
            }
            $countryPaymentTypes = [];
            foreach ($countryCodes as $key => $countryCode) {
                try {
                    $response = $this->tamaraClient->getPaymentTypes($countryCode, $currency, $phone, $total);
                    if ($response->isSuccess() && $response->getPaymentTypes()->count() > 0) {
                        $paymentTypes = [];
                        /** @var PaymentType $paymentType */
                        foreach ($response->getPaymentTypes() as $paymentType) {
                            $paymentTypes[$paymentType->getName()]['min_limit'] = $paymentType->getMinLimit()->getAmount();
                            $paymentTypes[$paymentType->getName()]['max_limit'] = $paymentType->getMaxLimit()->getAmount();
                            $paymentTypes[$paymentType->getName()]['payment_type_array'] = $paymentType->toArray();
                        }
                        $countryPaymentTypes[$countryCode] = $paymentTypes;
                    }
                } catch (Exception $exception) {
                    TamaraCheckout::getInstance()->logMessage(
                        sprintf(
                            "Tamara Service timeout or disconnected.\nError message: ' %s'.\nTrace: %s",
                            $exception->getMessage(),
                            $exception->getTraceAsString()
                        )
                    );
                }
            }
            if ($getFromCache && !empty($countryPaymentTypes)) {
                set_transient($countryPaymentTypesCacheKey, $countryPaymentTypes, 600);
            }
        }

        return $countryPaymentTypes;
    }

    /**
     * Return the name of Tamara settings option in the WP DB.
     *
     * @return string
     */
    public function get_option_key()
    {
        return TamaraCheckout::getInstance()->getWCTamaraGatewayOptionKey();
    }

    /**
     * Populate Tamara default description on checkout
     */
    public function populateTamaraDefaultDescription()
    {
        $allowedCountryCodeValues = 'KSA, UAE, KW, BH';
        if (!is_array($allowedCountryCodeValues)) {
            $removeComma = explode(',', $allowedCountryCodeValues);
            $result = implode(', ', $removeComma);
        } else {
            $result = implode(', ', $allowedCountryCodeValues);
        }
        $description = __(sprintf('*Exclusive for shoppers in %s', strtoupper($result)), $this->textDomain);
        if ($this->isSandboxMode()) {
            $description .= '<br/>'.sprintf(__('SANDBOX ENABLED. See the %s for more details.',
                    $this->textDomain),
                    '<a target="_blank" href="https://app-sandbox.tamara.co">Tamara Sandbox Testing Guide</a>');
        }

        return trim($description);
    }

    /**
     * Get Payment type based on its ID
     *
     * @return array
     */
    public function getPaymentTypeMapping()
    {
        return [
            TamaraCheckout::TAMARA_GATEWAY_ID => static::PAYMENT_TYPE_PAY_BY_LATER,
            TamaraCheckout::TAMARA_GATEWAY_PAY_NOW => static::PAYMENT_TYPE_PAY_NOW,
            TamaraCheckout::TAMARA_GATEWAY_CHECKOUT_ID => static::PAYMENT_TYPE_PAY_BY_LATER,
            TamaraCheckout::TAMARA_GATEWAY_PAY_BY_INSTALMENTS_ID => static::PAYMENT_TYPE_PAY_BY_INSTALMENTS,
            TamaraCheckout::TAMARA_GATEWAY_PAY_IN_2 => static::PAYMENT_TYPE_PAY_BY_INSTALMENTS,
            TamaraCheckout::TAMARA_GATEWAY_PAY_IN_3 => static::PAYMENT_TYPE_PAY_BY_INSTALMENTS,
            TamaraCheckout::TAMARA_GATEWAY_PAY_IN_4 => static::PAYMENT_TYPE_PAY_BY_INSTALMENTS,
            TamaraCheckout::TAMARA_GATEWAY_PAY_IN_5 => static::PAYMENT_TYPE_PAY_BY_INSTALMENTS,
            TamaraCheckout::TAMARA_GATEWAY_PAY_IN_6 => static::PAYMENT_TYPE_PAY_BY_INSTALMENTS,
            TamaraCheckout::TAMARA_GATEWAY_PAY_IN_7 => static::PAYMENT_TYPE_PAY_BY_INSTALMENTS,
            TamaraCheckout::TAMARA_GATEWAY_PAY_IN_8 => static::PAYMENT_TYPE_PAY_BY_INSTALMENTS,
            TamaraCheckout::TAMARA_GATEWAY_PAY_IN_9 => static::PAYMENT_TYPE_PAY_BY_INSTALMENTS,
            TamaraCheckout::TAMARA_GATEWAY_PAY_IN_10 => static::PAYMENT_TYPE_PAY_BY_INSTALMENTS,
            TamaraCheckout::TAMARA_GATEWAY_PAY_IN_11 => static::PAYMENT_TYPE_PAY_BY_INSTALMENTS,
            TamaraCheckout::TAMARA_GATEWAY_PAY_IN_12 => static::PAYMENT_TYPE_PAY_BY_INSTALMENTS,
        ];
    }

    /**
     * Get Payment type title based on its ID and site locale
     *
     * @param null $instalment
     *
     * @return array
     */
    public function getPaymentTypeTitleMapping($instalment = null)
    {
        $siteLocale = substr(get_locale(), 0, 2) ?? 'en';
        if ('ar' === $siteLocale) {
            return [
                TamaraCheckout::TAMARA_GATEWAY_ID => $this->getPayByLaterTitleAr(),
                'tamara-gateway-pay-in-'.$instalment => $this->getPayInXTitleAr($instalment),
            ];
        } else {
            return [
                TamaraCheckout::TAMARA_GATEWAY_ID => $this->getPayByLaterTitle(),
                'tamara-gateway-pay-in-'.$instalment => $this->getPayInXTitle($instalment),
            ];
        }
    }

    /**
     * Handle Tamara Webhook to register/delete Webhook Id
     */
    protected function populateTamaraWebhook()
    {
        $webhookId = $this->webhookId ?? '';
        $this->webhookEnabled = $this->get_option('webhook_enabled');
        if ($this->isWebhookEnabled()) {
            if (empty($webhookId) || !is_string($webhookId)) {
                return $this->registerTamaraWebhook();
            } elseif ($webhookId) {
                return $webhookId;
            }
        } else {
            $this->deleteTamaraWebhook($webhookId);

            return null;
        }

        return null;
    }

    /**
     * @param $cartTotal
     *
     * @return bool
     */
    protected function isCartTotalValid($cartTotal)
    {
        // Force pull country payment types from remote api
        $countryPaymentTypes = $this->getCountryPaymentTypes();
        $paymentTypes = $this->getPaymentTypes();

        return (TamaraCheckout::getInstance()->populatePayLaterMinLimit() <= $cartTotal && TamaraCheckout::getInstance()->populatePayLaterMaxLimit() >= $cartTotal);
    }

    /**
     * @param string $apiUrl
     * @param string $apiToken
     *
     * @return Client
     */
    protected function buildTamaraClient($apiUrl, $apiToken)
    {
        $apiUrl = TamaraCheckout::getInstance()->removeTrailingSlashes($apiUrl);
        $requestTimeout = 0;
        $logger = null;
        $transport = new NyholmHttpAdapter($requestTimeout, $logger);

        return Client::create(Configuration::create($apiUrl, $apiToken, $requestTimeout, $logger, $transport));
    }

    /**
     * Render HTML to show in Country Payments Description
     *
     * @return string
     */
    protected function renderTamaraCountryPaymentTypesHtml()
    {
        $htmlString = '';
        $currencyByCountryCode = array_flip($this->getCurrencyToCountryMapping());
        $countryPaymentTypes = !empty($this->settings['country_payment_types']) ? $this->settings['country_payment_types'] : [];
        if (!empty($countryPaymentTypes)) {
            foreach ($countryPaymentTypes as $countryCode => $countryPaymentType) {
                if (!empty($countryPaymentType[static::PAYMENT_TYPE_PAY_BY_LATER])) {
                    $htmlString .= '
                <div class="tamara-paylater-limits">
                    <h4>'.__(WC()->countries->countries[$countryCode], $this->textDomain).'</h4>
                    <div class="tamara-paylater-limits__items">
                            <div class="tamara-paylater-limits__items__amount">
                            <p>'.__('- Min Limit: ', $this->textDomain)
                                   .__($currencyByCountryCode[$countryCode], $this->textDomain).' '
                                   .(MoneyHelper::formatNumberGeneral($countryPaymentType[static::PAYMENT_TYPE_PAY_BY_LATER]['min_limit'])).'</p>
                            <p>'.__('- Max Limit: ', $this->textDomain)
                                   .__($currencyByCountryCode[$countryCode], $this->textDomain).' '
                                   .(MoneyHelper::formatNumberGeneral($countryPaymentType[static::PAYMENT_TYPE_PAY_BY_LATER]['max_limit'])).'</p>
                            </div>
                    </div>
                </div>';
                }
            }

            return $htmlString;
        } else {
            return 'N/A';
        }
    }

    /**
     * Render HTML to show in Pay Now Description
     *
     * @return string
     */
    protected function renderTamaraPayNowOptionHtml()
    {
        $htmlString = '';
        $currencyByCountryCode = array_flip($this->getCurrencyToCountryMapping());
        $countryPaymentTypes = !empty($this->settings['country_payment_types']) ? $this->settings['country_payment_types'] : [];
        if (!empty($countryPaymentTypes)) {
            foreach ($countryPaymentTypes as $countryCode => $countryPaymentType) {
                if (!empty($countryPaymentType[static::PAYMENT_TYPE_PAY_NOW])) {
                    $htmlString .= '
                <div class="tamara-paynow-limits">
                    <h4>'.__(WC()->countries->countries[$countryCode], $this->textDomain).'</h4>
                    <div class="tamara-paynow-limits__items">
                            <div class="tamara-paynow-limits__items__amount">
                            <p>'.__('- Min Limit: ', $this->textDomain)
                                   .__($currencyByCountryCode[$countryCode], $this->textDomain).' '
                                   .(MoneyHelper::formatNumberGeneral($countryPaymentType[static::PAYMENT_TYPE_PAY_NOW]['min_limit'])).'</p>
                            <p>'.__('- Max Limit: ', $this->textDomain)
                                   .__($currencyByCountryCode[$countryCode], $this->textDomain).' '
                                   .(MoneyHelper::formatNumberGeneral($countryPaymentType[static::PAYMENT_TYPE_PAY_NOW]['max_limit'])).'</p>
                            </div>
                    </div>
                </div>';
                }
            }

            return $htmlString;
        } else {
            return 'N/A';
        }
    }

    /**
     * Render settings help texts template
     *
     * @return string
     */
    protected function renderHelpTextsHtml()
    {
        return '<div class="tamara-settings-help-texts">
                    <div class="tamara-settings-help-texts__manage button-primary">'.__('Show More Help Texts', $this->textDomain).'<i class="tamara-toggle-btn fa-solid fa-chevron-down"></i></div>
                    <div class="tamara-settings-help-texts__content">
                        <ul>    
                            <li>'.__('Please make sure the Tamara payment status of the order is <strong>captured</strong> before making a refund.', $this->textDomain).'</li>
                            <li>'.__('You can use the shortcode with attributes to show Tamara product widget on custom pages e.g. <strong>[tamara_show_popup price="99" currency="SAR" language="en"].</strong>', $this->textDomain).'</li>
                            <li>'.__('For Tamara payment success URL, you can use action <strong>after_tamara_success</strong> to handle further actions.', $this->textDomain).'</li>                                    
                            <li>'.__('For Tamara payment cancel URL, you can use action <strong>after_tamara_cancel</strong> to handle further actions.', $this->textDomain).'</li>                                    
                            <li>'.__('For Tamara payment failed URL, you can use action <strong>after_tamara_failure</strong> to handle further actions.', $this->textDomain).'</li>
                            <li>'.__('All the debug log messages sent from Tamara will be written and saved to the Tamara custom log file in your upload directory.', $this->textDomain).'</li>
                        </ul>
                    </div>
                </div>';
    }

    /**
     * Get available supported instalments based on country code
     *
     * @param $countryCode
     *
     * @return array[]
     */
    public function getAvailableSupportedInstalments($countryCode)
    {
        $supportedInstalments = $this->getPaymentTypes($countryCode)[static::PAYMENT_TYPE_PAY_BY_INSTALMENTS]['payment_type_array']['supported_instalments'] ?? [];
        $currencyByCountryCode = __(array_flip($this->getCurrencyToCountryMapping())[$countryCode], $this->textDomain);
        $availableInstalments = [];
        $result = [];
        if (!empty($supportedInstalments)) {
            foreach ($supportedInstalments as $instalment) {
                $instalmentMinLimit = $instalment['min_limit']['amount'] ?? null;
                $instalmentMaxLimit = $instalment['max_limit']['amount'] ?? null;
                $result['pay_in_'.$instalment['instalments'].'_'.$countryCode] = [
                    'title' => __('Pay In '.$instalment['instalments'], $this->textDomain),
                    'label' => __('Enable Pay In '.$instalment['instalments'], $this->textDomain),
                    'default' => 'yes',
                    'type' => 'checkbox',
                    'class' => 'tamara-payinx tamara-payinx-'.$countryCode,
                    'description' =>
                        '<div class="tamara-payinx-limits">
                            <div class="tamara-payinx-limits__amount">
                                <p>'.__('- Min Amount: ', $this->textDomain).(MoneyHelper::formatNumberGeneral($instalmentMinLimit).' '.$currencyByCountryCode ?? 'N/A').'</p>
                                <p>'.__('- Max Amount: ', $this->textDomain).(MoneyHelper::formatNumberGeneral($instalmentMaxLimit).' '.$currencyByCountryCode ?? 'N/A').'</p>
                            </div>
                        </div>',
                ];
                $availableInstalments[$countryCode.$instalment['instalments']] = $instalment['instalments'];
            }
        }

        // Turn all unavailable instalments options to 'no'
        for ($i = 2; $i <= 12; $i++) {
            if (!in_array($i, array_values($availableInstalments))) {
                $this->settings['pay_in_'.$i.'_'.$countryCode] = 'no';
                $this->updateThisSettingsToOptions();
            }
        }

        ksort($result, SORT_NATURAL);

        return $result;
    }

    /**
     * Get available Pay Later options from settings
     *
     * @return mixed
     */
    public function getAvailablePayLaterOptions()
    {
        $result = [];
        $countryPaymentTypes = !empty($this->settings['country_payment_types']) ? $this->settings['country_payment_types'] : [];
        if (!empty($countryPaymentTypes)) {
            foreach ($countryPaymentTypes as $countryCode => $countryPaymentType) {
                if (!empty($countryPaymentType[static::PAYMENT_TYPE_PAY_BY_LATER])) {
                    $result['pay_by_later_enabled'] = [
                        'title' => __('Pay By Later', $this->textDomain),
                        'label' => __('Enable Pay By Later', $this->textDomain),
                        'default' => 'yes',
                        'type' => 'checkbox',
                        'description' =>
                            '<p>'.__('Limit changes cache will be cleared and updated whenever you refresh this page or save the settings.', $this->textDomain).'</p>'.
                            $this->renderTamaraCountryPaymentTypesHtml(),
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Get available Pay Now options from settings
     *
     * @return array
     */
    public function getAvailablePayNowOptions()
    {
        $result = [];
        $countryPaymentTypes = !empty($this->settings['country_payment_types']) ? $this->settings['country_payment_types'] : [];
        if (!empty($countryPaymentTypes)) {
            foreach ($countryPaymentTypes as $countryCode => $countryPaymentType) {
                if (!empty($countryPaymentType[static::PAYMENT_TYPE_PAY_NOW])) {
                    $result['pay_now_enabled'] = [
                        'title' => __('Pay Now', $this->textDomain),
                        'label' => __('Enable Pay Now', $this->textDomain),
                        'default' => 'yes',
                        'type' => 'checkbox',
                        'description' =>
                            '<p>'.__('Limit changes cache will be cleared and updated whenever you refresh this page or save the settings.', $this->textDomain).'</p>'.
                            $this->renderTamaraPayNowOptionHtml(),
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Refresh local cache for getting Payment Types data
     */
    protected function refreshPaymentTypeCache()
    {
        $countryPaymentTypesCacheKey = $this->buildCountryPaymentTypesCacheKey();
        delete_transient($countryPaymentTypesCacheKey);
    }

    /**
     * Refresh local cache for getting Payment Types V2 data
     */
    protected function refreshPaymentTypeCacheV2()
    {
        $countryPaymentTypesCacheKey = $this->buildCountryPaymentTypesCacheKeyV2();
        delete_transient($countryPaymentTypesCacheKey);
    }

    /**
     * Prepare debug log message download link in Admin settings
     */
    protected function prepareDebugLogDownloadLink()
    {
        $logFilePathExists = file_exists(TamaraCheckout::getInstance()->logMessageFilePath());
        if (!$logFilePathExists) {
            return
                '<p>'.__('No message log found!', $this->textDomain).'</p>';
        } else {
            return
                '<a target="_blank" href="'.TamaraCheckout::getInstance()->logMessageFileUrl().'" class="button-primary" download>'.__('Download Log', $this->textDomain).'</a>';
        }
    }

    /**
     * Generate Pay Later Options content in Admin Settings
     *
     * @return string
     */
    protected function generatePayLaterOptionContent()
    {
        $countryPaymentTypes = !empty($this->settings['country_payment_types']) ? $this->settings['country_payment_types'] : [];
        if (!empty($countryPaymentTypes)) {
            foreach ($countryPaymentTypes as $countryCode => $countryPaymentType) {
                if (!empty($countryPaymentType[static::PAYMENT_TYPE_PAY_BY_LATER])) {
                    return '<div class="tamara-paylater-manage button-primary">'.__('Manage Pay Later Options', $this->textDomain).'<i class="tamara-toggle-btn fa-solid fa-chevron-down"></i></div>';
                }
            }
        }

        return null;
    }

    /**
     * Generate Pay Now Options content in Admin Settings
     *
     * @return string
     */
    protected function generatePayNowOptionContent()
    {
        $countryPaymentTypes = !empty($this->settings['country_payment_types']) ? $this->settings['country_payment_types'] : [];
        if (!empty($countryPaymentTypes)) {
            foreach ($countryPaymentTypes as $countryCode => $countryPaymentType) {
                if (!empty($countryPaymentType[static::PAYMENT_TYPE_PAY_NOW])) {
                    return '<div class="tamara-paynow-manage button-primary">'.__('Manage Pay Now Options', $this->textDomain).'<i class="tamara-toggle-btn fa-solid fa-chevron-down"></i></div>';
                }
            }
        }

        return null;
    }

    /**
     * Generate Pay In X Options content in Admin Settings
     *
     * @param $countryCode
     *
     * @return string
     */
    protected function generatePayInXOptionsContent($countryCode)
    {
        $supportedInstalments = $this->getPaymentTypes($countryCode)[static::PAYMENT_TYPE_PAY_BY_INSTALMENTS] ?? [];
        $currencyByCountryCode = __(array_flip($this->getCurrencyToCountryMapping())[$countryCode], $this->textDomain);
        if (!empty($supportedInstalments)) {
            return
                '<p class="pay-in-x-'.strtolower($currencyByCountryCode).'-note">'.__('<strong>Note:</strong> Payment type Pay In X options will be listed below, after your confidential configuration are confirmed on settings saved.
                 <br>After your confidential settings are saved and fetch new Pay In X options, please re-configure your Pay In X available options and click on "Save changes" again to take effects.', $this->textDomain).'</p>
                 <p class="pay-in-x-'.strtolower($currencyByCountryCode).'-note">'.__('Limit changes cache will be cleared and updated whenever you refresh this page or save the settings.', $this->textDomain).'</p>
                 <div class="tamara-payinx-'.strtolower($currencyByCountryCode).'-manage button-primary">'.sprintf(__('Manage Pay In X in %s', $this->textDomain), $currencyByCountryCode).'<i class="tamara-toggle-btn fa-solid fa-chevron-down"></i></div>';
        }

        return '<p class="pay-in-x-'.strtolower($currencyByCountryCode).'-note"><strong>'.sprintf(__('Pay In X Options in %s currency unavailable.',
                $this->textDomain), $currencyByCountryCode).'</strong></p>';
    }

    /**
     * Get store current country code
     *
     * @return mixed|string
     */
    public function getCurrentCountryCode()
    {
        $storeBaseCountry = WC()->countries->get_base_country() ?? static::DEFAULT_COUNTRY_CODE;
        if (is_admin() && !wp_doing_ajax()) {
            return WC()->countries->get_base_country();
        } else {
            return !empty($this->getCurrencyToCountryMapping()[get_woocommerce_currency()]) ?
                $this->getCurrencyToCountryMapping()[get_woocommerce_currency()] : $storeBaseCountry;
        }
    }

    /**
     * Populate default titles for Pay In X
     *
     * @param $instalment
     *
     * @return array
     */
    public function populateDefaultPayInXTitles($instalment)
    {
        return [
            'instalmentDefaultEnTitle' => sprintf('Tamara: Split in %d, interest-free', $instalment),
            'instalmentDefaultArTitle' => sprintf('تمارا: قسم فاتورتك على ( %d ) دفعات بدون فوائد', $instalment),
        ];
    }

    /**
     * Handle Tamara success action on order received page
     */
    public function handleTamaraSuccessOrderReceivedPage()
    {
        $wcOrderId = filter_input(INPUT_GET, 'wcOrderId', FILTER_SANITIZE_NUMBER_INT) ?? null;
        $wcOrder = wc_get_order($wcOrderId);

        if (!empty($wcOrder) && TamaraCheckout::getInstance()->isTamaraGateway($wcOrder->get_payment_method())) {
            wp_enqueue_script('tamara-checkout-success', TamaraCheckout::getInstance()->baseUrl.'/assets/dist/js/tamaraSuccess.js',
                ['jquery'], TamaraCheckout::getInstance()->version, true);
            do_action('after_tamara_success');
        } else {
            return;
        }
    }

    /**
     * Get Tamara capture id by WC reference id
     *
     * @param int $wcOrderId
     *
     * @return mixed|null
     *
     */
    public function getTamaraCaptureId($wcOrderId)
    {
        $savedCaptureIdFromPostMeta = get_post_meta($wcOrderId, '_tamara_capture_id', true) ?? null;
        $savedCaptureIdFromSecondPostMeta = get_post_meta($wcOrderId, 'capture_id', true) ?? null;
        $savedCaptureId = !empty($savedCaptureIdFromPostMeta) ? $savedCaptureIdFromPostMeta
            : $savedCaptureIdFromSecondPostMeta;

        if (empty($savedCaptureId)) {
            try {
                $getOrderByReferenceIdRequest = new GetOrderByReferenceIdRequest($wcOrderId);
                $getOrderByReferenceIdResponse = $this->tamaraClient->getOrderByReferenceId(
                    $getOrderByReferenceIdRequest);
            } catch (Exception $tamaraGetCaptureIdException) {
                TamaraCheckout::getInstance()->logMessage(
                    sprintf(
                        "Tamara Get Capture ID Error.\nError message: ' %s'.\nTrace: %s",
                        $tamaraGetCaptureIdException->getMessage(),
                        $tamaraGetCaptureIdException->getTraceAsString()
                    )
                );
            }
            if (!empty($getOrderByReferenceIdResponse && $getOrderByReferenceIdResponse->isSuccess())) {
                $savedCaptureId = $getOrderByReferenceIdResponse->getTransactions()->getCaptures()->toArray()[0]['capture_id'] ?? '';
                $this->updateTamaraCaptureId($wcOrderId, $savedCaptureId);
            }
        }

        return $savedCaptureId;
    }

    /**
     * Get Tamara cancel id by WC reference id
     *
     * @param int $wcOrderId
     *
     * @return mixed|null
     *
     */
    public function getTamaraCancelId($wcOrderId)
    {
        $savedCancelIdFromPostMeta = get_post_meta($wcOrderId, '_tamara_cancel_id', true) ?? null;
        $savedCancelIdFromSecondPostMeta = get_post_meta($wcOrderId, 'tamara_cancel_id', true) ?? null;
        $savedCancelId = !empty($savedCancelIdFromPostMeta) ? $savedCancelIdFromPostMeta
            : $savedCancelIdFromSecondPostMeta;

        if (empty($savedCancelId)) {
            try {
                $getOrderByReferenceIdRequest = new GetOrderByReferenceIdRequest($wcOrderId);
                $getOrderByReferenceIdResponse = $this->tamaraClient->getOrderByReferenceId(
                    $getOrderByReferenceIdRequest);
            } catch (Exception $tamaraGetCancelIdException) {
                TamaraCheckout::getInstance()->logMessage(
                    sprintf(
                        "Tamara Get Cancel ID Error.\nError message: ' %s'.\nTrace: %s",
                        $tamaraGetCancelIdException->getMessage(),
                        $tamaraGetCancelIdException->getTraceAsString()
                    )
                );
            }
            if (!empty($getOrderByReferenceIdResponse) && $getOrderByReferenceIdResponse->isSuccess()) {
                $savedCancelId = $getOrderByReferenceIdResponse->getTransactions()->getCancels()->toArray()[0]['cancel_id'] ?? '';
                $this->updateTamaraCancelId($wcOrderId, $savedCancelId);
            }
        }

        return $savedCancelId;
    }

    /**
     * Get Tamara order id by WC reference id
     *
     * @param int $wcOrderId
     *
     * @return mixed|null
     *
     */
    public function getTamaraOrderId($wcOrderId)
    {
        $savedTamaraOrderIdFromPostMeta = get_post_meta($wcOrderId, '_tamara_order_id', true) ?? null;
        $savedTamaraOrderIdFromSecondPostMeta = get_post_meta($wcOrderId, 'tamara_order_id', true) ?? null;
        $savedTamaraOrderId = !empty($savedTamaraOrderIdFromPostMeta) ? $savedTamaraOrderIdFromPostMeta
            : $savedTamaraOrderIdFromSecondPostMeta;

        if (empty($savedTamaraOrderId)) {
            try {
                $getOrderByReferenceIdRequest = new GetOrderByReferenceIdRequest($wcOrderId);
                $getOrderByReferenceIdResponse = $this->tamaraClient->getOrderByReferenceId(
                    $getOrderByReferenceIdRequest);
            } catch (Exception $tamaraGetOrderIdException) {
                TamaraCheckout::getInstance()->logMessage(
                    sprintf(
                        "Tamara Get Capture ID Error.\nError message: ' %s'.\nTrace: %s",
                        $tamaraGetOrderIdException->getMessage(),
                        $tamaraGetOrderIdException->getTraceAsString()
                    )
                );
            }
            if (!empty($getOrderByReferenceIdResponse && $getOrderByReferenceIdResponse->isSuccess())) {
                $savedTamaraOrderId = $getOrderByReferenceIdResponse->getOrderId() ?? '';
                $this->updateTamaraOrderId($wcOrderId, $savedTamaraOrderId);
            }
        }

        return $savedTamaraOrderId;
    }

    /**
     * @param $wcOrderId
     * @param $tamaraCaptureId
     */
    public function updateTamaraCaptureId($wcOrderId, $tamaraCaptureId): void
    {
        update_post_meta($wcOrderId, 'capture_id', $tamaraCaptureId);
        update_post_meta($wcOrderId, '_tamara_capture_id', $tamaraCaptureId);
    }

    /**
     * @param $wcOrderId
     * @param $tamaraOrderId
     */
    public function updateTamaraOrderId($wcOrderId, $tamaraOrderId): void
    {
        update_post_meta($wcOrderId, 'tamara_order_id', $tamaraOrderId);
        update_post_meta($wcOrderId, '_tamara_order_id', $tamaraOrderId);
    }

    /**
     * @param $wcOrderId
     * @param $tamaraOrderId
     */
    public function updateTamaraCancelId($wcOrderId, $tamaraCancelId): void
    {
        update_post_meta($wcOrderId, 'tamara_cancel_id', $tamaraCancelId);
        update_post_meta($wcOrderId, '_tamara_cancel_id', $tamaraCancelId);
    }

    /**
     * Convert old Allowed Shipping Country string with separator is ',' to array
     *
     * @param $dataInput
     *
     * @return array
     */
    public function convertAllowedCountryStringValue($dataInput)
    {
        return array_map('trim', explode(',',
            strtoupper($dataInput)));
    }

    /**
     * Check if the Merchant has single checkout enabled
     *
     * @return bool
     */
    public function isSingleCheckoutEnabled()
    {
        $merchant = new Merchant();
        $getDetailsInfoRequest = new GetDetailsInfoRequest($merchant);
        try {
            $getDetailsInfoResponse = $this->tamaraClient->getMerchantDetailsInfo($getDetailsInfoRequest);
            if ($getDetailsInfoResponse && $getDetailsInfoResponse->isSuccess()) {
                return $getDetailsInfoResponse->getDetailsInfo()->getSingleCheckoutEnabled();
            }
        } catch (Exception $getDetailsInfoException) {
            TamaraCheckout::getInstance()->logMessage(
                sprintf(
                    "Tamara Get Merchant Details Info Failed Response.\nError message: ' %s'.\nTrace: %s",
                    $getDetailsInfoException->getMessage(),
                    $getDetailsInfoException->getTraceAsString()
                )
            );
        }

        return false;
    }

    /**
     * Get Public key from remote
     *
     * @return mixed
     */
    public function getPublicKeyFromRemote()
    {
        $merchant = new Merchant();
        $getDetailsInfoRequest = new GetDetailsInfoRequest($merchant);
        try {
            $getDetailsInfoResponse = $this->tamaraClient->getMerchantDetailsInfo($getDetailsInfoRequest);
            if ($getDetailsInfoResponse && $getDetailsInfoResponse->isSuccess()) {
                return $getDetailsInfoResponse->getDetailsInfo()->getPublicKey();
            }
        } catch (Exception $getDetailsInfoException) {
            TamaraCheckout::getInstance()->logMessage(
                sprintf(
                    "Tamara Get Merchant Details Info Failed Response.\nError message: ' %s'.\nTrace: %s",
                    $getDetailsInfoException->getMessage(),
                    $getDetailsInfoException->getTraceAsString()
                )
            );
        }

        return null;
    }

    /**
     * Return store public key value
     *
     * @return mixed
     */
    public function getPublicKey()
    {
        return $this->publicKey;
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
        $isMethodAvailable = false;
        $descriptionEn = '';
        $descriptionAr = '';
        if (!empty($paymentOptions)) {
            foreach ($paymentOptions as $paymentOption) {
                if (($this->paymentType === $paymentOption['payment_type'] && static::PAYMENT_TYPE_PAY_BY_INSTALMENTS !== $paymentOption['payment_type'])
                     || (static::PAYMENT_TYPE_PAY_BY_INSTALMENTS === $paymentOption['payment_type'] && $this->instalmentPeriod === $paymentOption['instalment'])) {
                    $isMethodAvailable = true;
                    $descriptionEn = $paymentOption['description_en'];
                    $descriptionAr = $paymentOption['description_ar'];
                    break;
                }
            }
        }
        return [
            'isMethodAvailable' => $isMethodAvailable,
            'descriptionEn' => $descriptionEn,
            'descriptionAr' => $descriptionAr
        ];
    }

    /**
     *
     * @return int
     * @throws \Tamara\Wp\Plugin\Dependencies\Tamara\Exception\RequestDispatcherException
     */
    public function countInstalmentPlans()
    {
        $cartTotal = TamaraCheckout::getInstance()->getTotalToCalculate(WC()->cart->total);
        $currentCountryCode = $this->getCurrencyToCountryMapping()[get_woocommerce_currency()] ?? $this->getDefaultBillingCountryCode();
        $customerPhone = TamaraCheckout::getInstance()->getCustomerPhoneNumber() ?? WC()->customer->get_billing_phone();
        $paymentOptions = TamaraCheckout::getInstance()->getPaymentOptions($cartTotal, $customerPhone, $currentCountryCode) ?? [];
        $paymentOptionsCount = 0;
        if (!empty($paymentOptions)) {
            foreach ($paymentOptions as $paymentOption) {
                if ($paymentOption['payment_type'] === static::PAYMENT_TYPE_PAY_BY_INSTALMENTS) {
                    $paymentOptionsCount += 1;
                }
            }
        }

        return $paymentOptionsCount;
    }
}
