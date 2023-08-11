<?php


namespace Tamara\Wp\Plugin;

use Exception;
use Tamara\Wp\Plugin\Dependencies\Illuminate\Container\Container;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Money;
use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Payment\Refund;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Order\GetOrderByReferenceIdRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Payment\RefundRequest;
use Tamara\Wp\Plugin\Helpers\MoneyHelper;
use Tamara\Wp\Plugin\Interfaces\WPPluginInterface;
use Tamara\Wp\Plugin\Services\TamaraNotificationService;
use Tamara\Wp\Plugin\Services\ViewService;
use Tamara\Wp\Plugin\Services\WCTamaraGateway;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayNextMonth;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayNow;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayCheckout;
use Tamara\Wp\Plugin\Services\WCTamaraGatewayPayByInstalments;
use Tamara\Wp\Plugin\Traits\ConfigTrait;
use Tamara\Wp\Plugin\Traits\ServiceTrait;
use Tamara\Wp\Plugin\Traits\TamaraPaymentTypesTrait;
use Tamara\Wp\Plugin\Traits\WPAttributeTrait;
use WC_Order;
use WP;

class TamaraCheckout extends Container implements WPPluginInterface
{
    use ConfigTrait;
    use WPAttributeTrait;
    use TamaraPaymentTypesTrait;

    /**
     * @var string Tamara CheckoutFrame JS Url
     */
    public const
        TAMARA_PRODUCT_WIDGET_URL = 'https://cdn.tamara.co/widget/product-widget.min.js',
        TAMARA_INFORMATION_WIDGET_URL = 'https://cdn.tamara.co/widget/tamara-widget.min.js',
        TAMARA_SUMMARY_WIDGET_URL = 'https://cdn.tamara.co/widget-v2/tamara-widget.js',
        TAMARA_INSTALLMENT_PLAN_WIDGET_URL = 'https://cdn.tamara.co/widget/installment-plan.min.js',

        TAMARA_PRODUCT_WIDGET_SANDBOX_URL = 'https://cdn-sandbox.tamara.co/widget/product-widget.min.js',
        TAMARA_INFORMATION_WIDGET_SANDBOX_URL = 'https://cdn-sandbox.tamara.co/widget/tamara-widget.min.js',
        TAMARA_SUMMARY_WIDGET_SANDBOX_URL = 'https://cdn-sandbox.tamara.co/widget-v2/tamara-widget.js',
        TAMARA_INSTALLMENT_PLAN_WIDGET_SANDBOX_URL = 'https://cdn-sandbox.tamara.co/widget/installment-plan.min.js',

        TAMARA_LOGO_BADGE_EN_URL = 'https://cdn.tamara.co/assets/png/tamara-logo-badge-en.png',
        TAMARA_LOGO_BADGE_AR_URL = 'https://cdn.tamara.co/assets/png/tamara-logo-badge-ar.png',
        MESSAGE_LOG_FILE_NAME = 'tamara-custom.log',
        TAMARA_GATEWAY_ID = 'tamara-gateway',
        TAMARA_GATEWAY_PAY_NOW = 'tamara-gateway-pay-now',
        TAMARA_GATEWAY_PAY_NEXT_MONTH = 'tamara-gateway-pay-next-month',
        TAMARA_GATEWAY_PAY_BY_INSTALMENTS_ID = 'tamara-gateway-pay-by-instalments',
        TAMARA_GATEWAY_PAY_IN_X = 'tamara-gateway-pay-in-',
        TAMARA_GATEWAY_PAY_IN_2 = 'tamara-gateway-pay-in-2',
        TAMARA_GATEWAY_PAY_IN_3 = 'tamara-gateway-pay-in-3',
        TAMARA_GATEWAY_PAY_IN_4 = 'tamara-gateway-pay-in-4',
        TAMARA_GATEWAY_PAY_IN_5 = 'tamara-gateway-pay-in-5',
        TAMARA_GATEWAY_PAY_IN_6 = 'tamara-gateway-pay-in-6',
        TAMARA_GATEWAY_PAY_IN_7 = 'tamara-gateway-pay-in-7',
        TAMARA_GATEWAY_PAY_IN_8 = 'tamara-gateway-pay-in-8',
        TAMARA_GATEWAY_PAY_IN_9 = 'tamara-gateway-pay-in-9',
        TAMARA_GATEWAY_PAY_IN_10 = 'tamara-gateway-pay-in-10',
        TAMARA_GATEWAY_PAY_IN_11 = 'tamara-gateway-pay-in-11',
        TAMARA_GATEWAY_PAY_IN_12 = 'tamara-gateway-pay-in-12',
        TAMARA_GATEWAY_CHECKOUT_ID = 'tamara-gateway-checkout',
        TAMARA_AUTHORISED_STATUS = 'authorised',
        TAMARA_CANCELED_STATUS = 'canceled',
        TAMARA_PARTIALLY_CAPTURED_STATUS = 'partially_captured',
        TAMARA_FULLY_CAPTURED_STATUS = 'fully_captured',
        TAMARA_PARTIALLY_REFUNDED_STATUS = 'partially_refunded',
        TAMARA_FULLY_REFUNDED_STATUS = 'fully_refunded',
        TAMARA_INLINE_TYPE_KNOWMORE_WIDGET_INT = 1,
        TAMARA_INLINE_TYPE_PRODUCT_WIDGET_INT = 2,
        TAMARA_INLINE_TYPE_CART_WIDGET_INT = 3,
        DOWN_PAYMENT = 'down_payment',
        INSTALMENT = 'instalment',
        PAY_LATER_PDP_MAX_AMOUNT = 200;

    /**
     * @var string Version of this plugin
     */
    public $version;

    /** @noinspection PhpUnusedElementInspection */
    /**
     * @var string Base path to this plugin
     */
    public $basePath;

    /** @noinspection PhpUnusedElementInspection */
    /**
     * @var string Base url of the folder of this plugin
     */
    public $baseUrl;

    /**
     * @var string The filename of the plugin (it should have full path + file name)
     */
    public $pluginFilename;

    /**
     * @var \WP_REST_Request $restApiRequest
     */
    protected $restApiRequest;

    /**
     * @var string The customer phone number on checkout
     */
    protected $customerPhoneNumber;

    /**
     * Tamara_Checkout constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->bindConfig($config);

        // phpcs:ignore PSR2.ControlStructures.ControlStructureSpacing.SpacingAfterOpenBrace
        if (!empty($services = $config['services'] ?? null)) {
            $this->registerServices($services);
        }
    }

    /**
     * @param float $totalAmount
     * @param int $numberOfInstalments
     *
     * @return array
     */
    public static function calculateInstalmentPlan(float $totalAmount, int $numberOfInstalments = 3): array
    {
        $totalAmount = $totalAmount * 100;
        $modAmount = $totalAmount % $numberOfInstalments;
        $downPayment = round(floatval((($totalAmount - $modAmount) / $numberOfInstalments / 100) + ($modAmount / 100)), 2);
        $instalment = ($totalAmount - $modAmount) / $numberOfInstalments / 100;

        return [
            static::DOWN_PAYMENT => $downPayment,
            static::INSTALMENT => $instalment,
        ];
    }

    /**
     * Register service providers set in config
     *
     * @param $services
     */
    protected function registerServices($services)
    {
        foreach ($services as $serviceClassname => $serviceConfig) {
            $this->singleton(
                $serviceClassname,
                function ($container) use ($serviceClassname, $serviceConfig) {
                    $serviceInstance = new $serviceClassname();
                    if (method_exists($serviceInstance, 'bindConfig')) {
                        $serviceInstance->bindConfig($serviceConfig);
                    }

                    if (in_array(ServiceTrait::class, class_uses($serviceInstance))) {
                        $serviceInstance->setContainer($container);
                        $serviceInstance->init();
                    }

                    return $serviceInstance;
                }
            );
        }
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @param string $alias
     *
     * @return mixed
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getService($alias)
    {
        return $this->make($alias);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get the `view` service
     *
     * @return ViewService
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function getServiceView()
    {
        return static::getInstance()->getService(ViewService::class);
    }

    /**
     * @param $config
     *
     * @throws Exception
     */
    public static function initInstanceWithConfig($config)
    {
        if (is_null(static::$instance)) {
            static::setInstance(new static($config));
        }

        // phpcs:ignore PSR2.ControlStructures.ControlStructureSpacing.SpacingAfterOpenBrace
        if (!static::getInstance() instanceof static) {
            throw new Exception('No plugin initialized.');
        }
    }

    /**
     * Initialize all needed things for this plugin: hooks, assignments...
     */
    public function initPlugin(): void
    {
        add_action('init', [$this, 'checkWooCommerceExistence']);
        if (!class_exists('WooCommerce')) {
            return;
        }

        // Load text domain
        add_action('init', [$this, 'tamaraLoadTextDomain']);

        // Register new Tamara custom statuses
        add_action('init', [$this, 'registerTamaraCustomOrderStatuses']);

        // For Admin
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminSettingScripts']);

        // Handle refund when a refund is created
        add_action('woocommerce_create_refund', [$this, 'tamaraRefundPayment'], 10, 2);

        // Add Tamara custom statuses to wc order status list
        add_filter('wc_order_statuses', [$this, 'addTamaraCustomOrderStatuses']);

        // Add note on Refund
        add_action('woocommerce_order_item_add_action_buttons', [$this, 'addRefundNote']);

        add_filter('woocommerce_rest_prepare_shop_order_object', [$this, 'updateTamaraCheckoutDataToOrder'], 10, 3);

        add_action('init', [$this, 'addCustomRewriteRules']);
        add_action('init', [$this, 'addTamaraAuthoriseFailedMessage'], 1000);
        add_action('parse_request', [$this, 'handleTamaraApi'], 1000);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
//        add_filter('woocommerce_checkout_fields', [$this, 'adjustBillingPhoneDescription']);
        add_filter('woocommerce_payment_gateways', [$this, 'registerTamaraPaymentGateway']);
        add_filter('woocommerce_available_payment_gateways', [$this, 'adjustTamaraPaymentTypesOnCheckout'], 9998, 1);
        add_action('woocommerce_update_options_checkout_'.static::TAMARA_GATEWAY_ID, [$this, 'onSaveSettings'], 10, 1);
        add_action($this->getTamaraPopupWidgetPosition(), [$this, 'showTamaraProductPopupWidget']);
        add_action($this->getTamaraCartPopupWidgetPosition(), [$this, 'showTamaraCartProductPopupWidget']);
        add_action('wp_ajax_tamara_perform_cron', [$this, 'performCron']);
        add_action('wp_ajax_tamara-authorise', [$this, 'tamaraAuthoriseHandler']);
        add_action('wp_ajax_nopriv_tamara-authorise', [$this, 'tamaraAuthoriseHandler']);
        add_action('wp_head', [$this, 'tamaraCheckoutParams']);
        add_action('woocommerce_checkout_update_order_review', [$this, 'getUpdatedPhoneNumberOnCheckout']);

        if ($this->isCronjobEnabled()) {
            add_action('admin_footer', [$this, 'addCronJobTriggerScript']);
        }

        add_shortcode('tamara_show_popup', [$this, 'tamaraProductPopupWidget']);
        add_shortcode('tamara_show_cart_popup', [$this, 'tamaraCartPopupWidget']);
        add_shortcode('tamara_authorise_order', [$this, 'doAuthoriseOrderAction']);

        // For Rest Api
        add_filter('rest_pre_dispatch', [$this, 'populateRestApiRequest'], 1, 3);

        // Update Settings Url in admin for Pay By Instalments
        add_action('admin_head', [$this, 'updatePayByInstalmentSettingUrl']);
        add_filter('woocommerce_billing_fields', [$this, 'forceRequireBillingPhone'], 1001, 2);

        add_action('wp_ajax_tamara-get-instalment-plan', [$this, 'getInstalmentPlanAccordingToProductVariation']);
        add_action('wp_ajax_nopriv_tamara-get-instalment-plan', [$this, 'getInstalmentPlanAccordingToProductVariation']);

        add_action('wp_ajax_update-tamara-checkout-params', [$this, 'updateTamaraCheckoutParams']);
        add_action('wp_ajax_nopriv_update-tamara-checkout-params', [$this, 'updateTamaraCheckoutParams']);

        add_action('get_header', [$this, 'overrideWcClearCart'], 8);
        add_action('wp_loaded', [$this, 'cancelOrder'], 21);

        // Add Tamara Note on Order Received page
        add_filter('woocommerce_thankyou_order_received_text', [$this, 'tamaraOrderReceivedText'], 10, 2);

    }

    /**
     * Populate Rest Api Request
     *
     * @param mixed $result
     * @param \WP_REST_Server $restApiServer
     * @param \WP_REST_Request $restApiRequest
     *
     * @return mixed
     */
    public function populateRestApiRequest($result, $restApiServer, $restApiRequest)
    {
        $this->setRestApiRequest($restApiRequest);

        return $result;
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Add Tamara Note after successful payment
     *
     * @param string $str
     * @param \Automattic\WooCommerce\Admin\Overrides\Order $order
     *
     * @return string
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function tamaraOrderReceivedText($str, $order)
    {
        if (empty($order)) {
            return $str;
        }

        $payment_method = $order->get_payment_method();

        if (!empty($payment_method) && $this->isTamaraGateway($payment_method)) {
            return $str.$this->getServiceView()->render('views/woocommerce/checkout/tamara-order-received-button',
                    [
                        'textDomain' => $this->textDomain,
                    ]);
        }

        return $str;
    }

    /**
     * Handle Tamara log message
     *
     * @param string $message
     *
     */
    public function logMessage($message)
    {
        if ($this->isCustomLogMessageEnabled()) {
            if (is_array($message)) {
                $message = json_encode($message);
            }
            $fileHandle = fopen($this->logMessageFilePath(), "a");
            fwrite($fileHandle, "[".gmdate('Y-m-d h:i:s')."] ".$message."\n");
            fclose($fileHandle);
        }
    }

    /**
     * Update order status and add order note wrapper
     *
     * @param WC_Order $wcOrder
     * @param string $orderNote
     * @param string $newOrderStatus
     * @param string $updateOrderStatusNote
     *
     */
    public function updateOrderStatusAndAddOrderNote($wcOrder, $orderNote, $newOrderStatus, $updateOrderStatusNote)
    {
        if ($wcOrder) {
            $this->logMessage(sprintf("Tamara - Prepare to Update Order Status - Order ID: %s, Order Note: %s, new order status: %s, order status note: %s", $wcOrder->get_id(), $orderNote, $newOrderStatus, $updateOrderStatusNote));
            try {
                $wcOrder->add_order_note($orderNote);
                $wcOrder->update_status($newOrderStatus, $updateOrderStatusNote, true);
            } catch (Exception $exception) {
                $this->logMessage(sprintf("Tamara - Failed to Update Order Status - Order ID: %s, Order Note: %s, new order status: %s, order status note: %s. Error Message: %s", $wcOrder->get_id(), $orderNote, $newOrderStatus, $updateOrderStatusNote, $exception->getMessage()));
            }
        }
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get WC Tamara Gateway Pay By Later class
     *
     * @return WCTamaraGateway
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getWCTamaraGatewayService()
    {
        return $this->getService(WCTamaraGateway::class);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get WC Tamara Gateway Pay Now class
     *
     * @return WCTamaraGatewayPayNow
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getWCTamaraGatewayPayNowService()
    {
        return $this->getService(WCTamaraGatewayPayNow::class);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get WC Tamara Gateway Pay By Instalments class
     *
     * @return WCTamaraGateway
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getWCTamaraGatewayPayByInstalmentsService()
    {
        return $this->getService(WCTamaraGatewayPayByInstalments::class);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get WC Tamara Gateway Pay In X class
     *
     * @param $instalment
     *
     * @return WCTamaraGateway
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getWCTamaraGatewayPayInXService($instalment)
    {
        $instalmentService = 'Tamara\Wp\Plugin\Services\WCTamaraGatewayPayIn'.$instalment;

        return $this->getService($instalmentService);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get WC Tamara Gateway Single Checkout class
     *
     * @return WCTamaraGateway
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getWCTamaraGatewayCheckoutService()
    {
        return $this->getService(WCTamaraGatewayCheckout::class);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get WC Tamara Gateway Pay Next Month class
     *
     * @return WCTamaraGateway
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getWCTamaraGatewayPayNextMonthService()
    {
        return $this->getService(WCTamaraGatewayPayNextMonth::class);
    }

    /**
     * Get Tamara Popup Widget postion
     */
    public function getTamaraPopupWidgetPosition()
    {
        return $this->getWCTamaraGatewayOptions()['popup_widget_position'] ?? 'woocommerce_single_product_summary';
    }

    /**
     * Get Tamara Cart Popup Widget postion
     */
    public function getTamaraCartPopupWidgetPosition()
    {
        return $this->getWCTamaraGatewayOptions()['cart_popup_widget_position'] ?? 'woocommerce_proceed_to_checkout';
    }

    /**
     * Check if Payment type Pay By Later is enabled in admin settings
     */
    public function isPayByLaterEnabled()
    {
        return 'yes' === ($this->getWCTamaraGatewayOptions()['pay_by_later_enabled'] ?? 'no');
    }

    /**
     * Check if Payment type Pay Now is enabled in admin settings
     */
    public function isPayNowEnabled()
    {
        return 'yes' === ($this->getWCTamaraGatewayOptions()['pay_now_enabled'] ?? 'no');
    }

    /**
     * Check if Payment type Pay By Instalments is enabled in admin settings
     */
    public function isPayByInstalmentsEnabled()
    {
        return 'yes' === ($this->getWCTamaraGatewayOptions()['pay_by_instalments_enabled'] ?? 'no');
    }

    /**
     * Check if a specific Pay In X payment type is enabled in admin settings
     *
     * @param $instalment
     * @param $countryCode
     *
     * @return bool
     */
    public function isPayInXEnabled($instalment, $countryCode)
    {
        return 'yes' === ($this->getWCTamaraGatewayOptions()['pay_in_'.$instalment.'_'.$countryCode] ?? 'no');
    }

    /**
     * Check if Tamara Gateway is enabled in admin settings
     */
    public function isTamaraGatewayEnabled()
    {
        return 'yes' === ($this->getWCTamaraGatewayOptions()['enabled'] ?? 'no');
    }

    /**
     * Check if Tamara custom log message is enabled in admin settings
     */
    public function isCustomLogMessageEnabled()
    {
        return 'yes' === ($this->getWCTamaraGatewayOptions()['custom_log_message_enabled'] ?? 'no');
    }

    /**
     * Check if Tamara force billing phone option is enabled in admin settings
     */
    public function isForceBillingPhoneEnabled()
    {
        return 'yes' === ($this->getWCTamaraGatewayOptions()['force_billing_phone'] ?? 'no');
    }

    /**
     * Check if Cronjob is enabled in admin settings
     */
    public function isCronjobEnabled()
    {
        return 'yes' === ($this->getWCTamaraGatewayOptions()['crobjob_enabled'] ?? 'no');
    }

    /**
     * Check if Tamara Pay Later popup widget is enabled in admin settings
     */
    public function isPayLaterPDPEnabled()
    {
        return 'yes' === ($this->getWCTamaraGatewayOptions()['pay_later_popup_widget_enabled'] ?? 'no');
    }

    /**
     * Check if Always Show Popup Widget is enabled in admin settings
     */
    public function isAlwaysShowWidgetPopupEnabled()
    {
        return 'yes' === ($this->getWCTamaraGatewayOptions()['always_show_popup_widget_enabled'] ?? 'no');
    }

    /**
     * Check if Showing Popup Widget is disabled in admin settings
     */
    public function isWidgetPopupDisabled()
    {
        return 'yes' === ($this->getWCTamaraGatewayOptions()['popup_widget_disabled'] ?? 'no');
    }

    /**
     * Check if Showing Popup Widget in Cart page is disabled in admin settings
     */
    public function isCartWidgetPopupDisabled()
    {
        return 'yes' === ($this->getWCTamaraGatewayOptions()['cart_popup_widget_disabled'] ?? 'no');
    }

    /**
     * Check if Credit Precheck is enabled in admin settings
     */
    public function isCreditPrecheckEnabled()
    {
        return 'yes' === ($this->getWCTamaraGatewayOptions()['credit_precheck_enabled'] ?? 'no');
    }

    /**
     * Get WC Tamara Gateway options
     */
    public function getWCTamaraGatewayOptions()
    {
        return get_option($this->getWCTamaraGatewayOptionKey(), null);
    }

    /**
     * Get WC Tamara Gateway options
     */
    public function getWCTamaraGatewayOptionKey()
    {
        return 'woocommerce_'.static::TAMARA_GATEWAY_ID.'_settings';
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get on save settings method from WC Tamara Gateway
     *
     * @param $settings
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function onSaveSettings($settings)
    {
        return $this->getWCTamaraGatewayService()->onSaveSettings($settings);
    }

    /**
     * Tamara Log File Path
     */
    public function logMessageFilePath()
    {
        return (defined('UPLOADS') ? UPLOADS : (WP_CONTENT_DIR.'/uploads/').static::MESSAGE_LOG_FILE_NAME);
    }

    /**
     * Tamara Log File Url
     */
    public function logMessageFileUrl()
    {
        return wp_upload_dir()['baseurl'].'/'.static::MESSAGE_LOG_FILE_NAME;
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Force pending capture payments within 180 days to be captured
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function forceCaptureTamaraOrder()
    {
        $tamaraCapturePaymentStatus = $this->getWCTamaraGatewayService()->tamaraStatus['payment_capture'] ?? 'wc-completed';
        $customerOrders = [
            'fields' => 'ids',
            'post_type' => 'shop_order',
            'post_status' => $tamaraCapturePaymentStatus,
            'date_query' => [
                'after' => date('Y-m-d', strtotime('-180 days')),
                'inclusive' => true,
            ],
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'tamara_order_id',
                    'compare' => 'EXISTS',
                ],
                [
                    'key' => 'capture_id',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ];

        $customerOrdersQuery = new \WP_Query($customerOrders);

        $wcOrderIds = $customerOrdersQuery->posts;

        foreach ($wcOrderIds as $wcOrderId) {
            if (static::TAMARA_FULLY_CAPTURED_STATUS === TamaraCheckout::getInstance()->getTamaraOrderStatus($wcOrderId)) {
                $wcOrder = wc_get_order($wcOrderId);
                $wcOrder->add_order_note(__('Tamara - The payment has been captured successfully.', $this->textDomain));
                return true;
            } else {
                $this->getWCTamaraGatewayService()->captureWcOrder($wcOrderId);
            }
        }
    }

    /**
     * Force pending authorise payments within 180 days to be authorised
     *
     */
    public function forceAuthoriseTamaraOrder()
    {
        $toAuthoriseStatus = 'wc-pending';
        $customerOrders = [
            'fields' => 'ids',
            'post_type' => 'shop_order',
            'post_status' => $toAuthoriseStatus,
            'date_query' => [
                'after' => date('Y-m-d', strtotime('-180 days')),
                'inclusive' => true,
            ],
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'tamara_checkout_session_id',
                    'compare' => 'EXISTS',
                ],
                [
                    'key' => 'tamara_order_id',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ];

        $customerOrdersQuery = new \WP_Query($customerOrders);

        $wcOrderIds = $customerOrdersQuery->posts;

        foreach ($wcOrderIds as $wcOrderId) {
            if (!$this->isOrderAuthorised($wcOrderId)) {
                $this->authoriseOrder($wcOrderId);
            }
        }
    }

    /**
     * Add Tamara Refund Note
     *
     * @param WC_Order $order
     */
    public function addRefundNote($order)
    {
        if ($this->isTamaraGateway($order->get_payment_method())) {
            echo '<br>'.__('This order is paid via Tamara Pay Later.', $this->textDomain);
            echo '<br>'.'<strong>'.__('You need to refund the full shipping amount.',
                    $this->textDomain).'</strong>';
        }
    }

    /**
     * Register Tamara new statuses
     */
    public function registerTamaraCustomOrderStatuses()
    {
        register_post_status('wc-tamara-p-canceled', [
            'label' => _x('Tamara Payment Cancelled', 'Order status', $this->textDomain),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Tamara Payment Cancelled <span class="count">(%s)</span>',
                'Tamara Payment Cancelled <span class="count">(%s)</span>', $this->textDomain),
        ]);

        register_post_status('wc-tamara-p-failed', [
            'label' => _x('Tamara Payment Failed', 'Order status', $this->textDomain),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Tamara Payment Failed <span class="count">(%s)</span>',
                'Tamara Payment Failed <span class="count">(%s)</span>', $this->textDomain),
        ]);

        register_post_status('wc-tamara-c-failed', [
            'label' => _x('Tamara Capture Failed', 'Order status', $this->textDomain),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Tamara Capture Failed <span class="count">(%s)</span>',
                'Tamara Capture Failed <span class="count">(%s)</span>', $this->textDomain),
        ]);

        register_post_status('wc-tamara-a-done', [
            'label' => _x('Tamara Authorise Success', 'Order status', $this->textDomain),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Tamara Authorise Success <span class="count">(%s)</span>',
                'Tamara Authorise Success <span class="count">(%s)</span>', $this->textDomain),
        ]);

        register_post_status('wc-tamara-a-failed', [
            'label' => _x('Tamara Authorise Failed', 'Order status', $this->textDomain),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Tamara Authorise Failed <span class="count">(%s)</span>',
                'Tamara Authorise Failed <span class="count">(%s)</span>', $this->textDomain),
        ]);

        register_post_status('wc-tamara-o-canceled', [
            'label' => _x('Tamara Order Cancelled', 'Order status', $this->textDomain),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Tamara Order Cancelled <span class="count">(%s)</span>',
                'Tamara Order Cancelled <span class="count">(%s)</span>', $this->textDomain),
        ]);

        register_post_status('wc-tamara-p-capture', [
            'label' => _x('Tamara Payment Capture', 'Order status', $this->textDomain),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Tamara Payment Capture <span class="count">(%s)</span>',
                'Tamara Payment Capture <span class="count">(%s)</span>', $this->textDomain),
        ]);
    }

    /**
     * Add Tamara Statuses to the list of WC Order statuses
     *
     * @param array $order_statuses
     *
     * @return array $order_statuses
     */
    public function addTamaraCustomOrderStatuses($order_statuses)
    {
        $order_statuses['wc-tamara-p-canceled'] = _x('Tamara Payment Cancelled', 'Order status',
            $this->textDomain);
        $order_statuses['wc-tamara-p-failed'] = _x('Tamara Payment Failed', 'Order status',
            $this->textDomain);
        $order_statuses['wc-tamara-c-failed'] = _x('Tamara Capture Failed', 'Order status',
            $this->textDomain);
        $order_statuses['wc-tamara-a-done'] = _x('Tamara Authorise Done', 'Order status',
            $this->textDomain);
        $order_statuses['wc-tamara-a-failed'] = _x('Tamara Authorise Failed', 'Order status',
            $this->textDomain);
        $order_statuses['wc-tamara-o-canceled'] = _x('Tamara Order Cancelled', 'Order status',
            $this->textDomain);
        $order_statuses['wc-tamara-p-capture'] = _x('Tamara Payment Capture', 'Order status',
            $this->textDomain);

        return $order_statuses;
    }

    /**
     * Localize the plugin
     */
    public function tamaraLoadTextDomain()
    {
        $locale = determine_locale();
        $mofile = $locale.'.mo';
        load_textdomain($this->textDomain, $this->basePath.'/languages/'.$this->textDomain.'-'.$mofile);

    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Handle process for Tamara endpoint slug returned
     *
     * @param WP $wp
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handleTamaraApi($wp)
    {
        $pagename = $wp->query_vars['pagename'] ?? null;
        $tamaraPageSlugs = [
            WCTamaraGateway::IPN_SLUG,
            WCTamaraGateway::WEBHOOK_SLUG,
            WCTamaraGateway::PAYMENT_SUCCESS_SLUG,
            WCTamaraGateway::PAYMENT_CANCEL_SLUG,
            WCTamaraGateway::PAYMENT_FAIL_SLUG,
        ];
        if (in_array($pagename, $tamaraPageSlugs)) {
            $this->logMessage(sprintf('Pagename: %s', $pagename));
        }

        if (WCTamaraGateway::IPN_SLUG === $pagename) {
            /** @var TamaraNotificationService $tamara_notification_service */
            $tamara_notification_service = $this->getService(TamaraNotificationService::class);
            $tamara_notification_service->handleIpnRequest();
            exit;
        } // Handle webhook
        elseif (WCTamaraGateway::WEBHOOK_SLUG === $pagename) {
            /** @var TamaraNotificationService $tamara_notification_service */
            $tamara_notification_service = $this->getService(TamaraNotificationService::class);
            $tamara_notification_service->handleWebhook();
            exit;
        } elseif (WCTamaraGateway::PAYMENT_CANCEL_SLUG === $pagename) {
            $this->handleTamaraCancelUrl();
            do_action('after_tamara_cancel');
            exit;
        } elseif (WCTamaraGateway::PAYMENT_FAIL_SLUG === $pagename) {
            $this->handleTamaraFailureUrl();
            do_action('after_tamara_failure');
            exit;
        }
    }

    /**
     * Detect if an order is authorised or not
     *
     * @param $wcOrderId
     *
     * @return bool
     */
    public function isOrderAuthorised($wcOrderId)
    {
        return !!get_post_meta($wcOrderId, 'tamara_authorized', true);
    }

    /**
     * Prevent an order is cancelled from FE if its payment has been authorised from Tamara
     *
     * @param WC_Order $wcOrder
     * @param int $wcOrderId
     *
     */
    protected function preventOrderCancelAction($wcOrder, $wcOrderId)
    {
        $orderNote = 'This order can not be cancelled because the payment was authorised from Tamara. Order ID: '.$wcOrderId;
        $wcOrder->add_order_note($orderNote);
        $this->logMessage($orderNote);
        wp_redirect(wc_get_cart_url());
    }

    /**
     * Add needed params for Tamara checkout success url
     */
    public function tamaraCheckoutParams()
    {
        $storeCurrency = get_woocommerce_currency();
        $publicKey = $this->getWCTamaraGatewayService()->getPublicKey() ?? '';
        $siteLocale = substr(get_locale(), 0, 2) ?? "en";
        $countryCode = $this->getWCTamaraGatewayService()->getCurrentCountryCode();
        ?>
        <meta name="generator" content="TamaraCheckout <?php echo $this->version ?>" />
        <script type="text/javascript">
            let tamaraCheckoutParams = {
                "ajaxUrl": "<?php echo esc_attr(admin_url('admin-ajax.php')) ?>",
                "publicKey": "<?php echo $publicKey ?>",
                "currency": "<?php echo $storeCurrency ?>",
                "country": "<?php echo $countryCode ?>",
            };
            window.tamaraWidgetConfig = {
                lang: "<?php echo $siteLocale ?>",
                country: "<?php echo $countryCode ?>",
                publicKey: "<?php echo $publicKey ?>",
            };
        </script>
        <?php if ($this->getWCTamaraGatewayService()->isLiveMode()) { ?>
        <script type="text/javascript" defer src="<?php echo static::TAMARA_SUMMARY_WIDGET_URL ?>"></script>
    <?php } else { ?>
        <script type="text/javascript" defer src="<?php echo static::TAMARA_SUMMARY_WIDGET_SANDBOX_URL ?>"></script>
    <?php }
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Detect if payment for WC order has been approved from Tamara
     *
     * @param int $wcOrderId
     *
     * @return bool
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function isOrderTamaraApproved($wcOrderId)
    {
        $tamaraOrder = $this->getTamaraOrderByWcOrderId($wcOrderId);
        if ($tamaraOrder && 'approved' === $tamaraOrder->getStatus()) {
            return true;
        }

        return false;
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get Tamara order id by WC order Id
     *
     * @param int $wcOrderId
     *
     * @return string
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function getTamaraOrderId($wcOrderId)
    {
        $tamaraOrder = $this->getTamaraOrderByWcOrderId($wcOrderId);
        if ($tamaraOrder) {
            return $tamaraOrder->getOrderId();
        }

        return null;
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get Tamara order by WC order Id
     *
     * @param int $wcOrderId
     *
     * @return null|Dependencies\Tamara\Response\Order\GetOrderByReferenceIdResponse
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getTamaraOrderByWcOrderId($wcOrderId)
    {
        $tamaraClient = $this->getWCTamaraGatewayService()->tamaraClient;
        try {
            $tamaraOrderResponse = $tamaraClient->getOrderByReferenceId(new GetOrderByReferenceIdRequest($wcOrderId));
            $this->logMessage(sprintf("Tamara Get Order by Reference ID Response: %s", print_r($tamaraOrderResponse, true)));
            if ($tamaraOrderResponse->isSuccess()) {
                return $tamaraOrderResponse;
            }
        } catch (Exception $tamaraOrderResponseException) {
            $this->logMessage(
                sprintf(
                    "Tamara Get Order by Reference ID Failed Response.\nError message: ' %s'.\nTrace: %s",
                    $tamaraOrderResponseException->getMessage(),
                    $tamaraOrderResponseException->getTraceAsString()
                )
            );
        }

        return null;
    }

    /**
     * If the order is not authorised from Tamara, do it on the Tamara Success Url returned
     */
    public function tamaraAuthoriseHandler()
    {
        $wcOrderId = filter_input(INPUT_POST, 'wcOrderId', FILTER_SANITIZE_NUMBER_INT);
        $authoriseSuccessResponse = [
            'message' => 'authorise_success',
        ];

        if ($this->isOrderAuthorised($wcOrderId) || $this->authoriseOrder($wcOrderId)) {
            wp_send_json($authoriseSuccessResponse);
        }

        wp_send_json(
            [
                'message' => 'authorise_failed',
            ]
        );
    }

    /**
     * Do authorise order with order id from payload returning from Tamara
     */
    public function doAuthoriseOrderAction()
    {
        $wcOrderId = filter_input(INPUT_GET, 'wcOrderId', FILTER_SANITIZE_NUMBER_INT);
        $wcOrderId || $wcOrderId = filter_input(INPUT_POST, 'wcOrderId', FILTER_SANITIZE_NUMBER_INT);

        $this->authoriseOrder($wcOrderId);
    }

    /**
     * @param $wcOrderId
     *
     * @return bool true if an authorise action is made successfully, false if failed
     * or already authorised
     */
    public function authoriseOrder($wcOrderId)
    {
        $wcOrder = wc_get_order($wcOrderId);

        try {
            if (!$this->isOrderAuthorised($wcOrderId) && $wcOrder && ($this->isOrderTamaraApproved($wcOrderId))) {
                $tamaraOrderId = $this->getTamaraOrderId($wcOrderId);
                /** @var TamaraNotificationService $tamaraNotificationService */
                $tamaraNotificationService = $this->getService(TamaraNotificationService::class);
                $tamaraNotificationService->authoriseOrder($wcOrderId, $tamaraOrderId);

                if ($this->isOrderAuthorised($wcOrderId)) {
                    return true;
                }
            }
        } catch (Exception $exception) {
        }

        return false;
    }

    /**
     * Add Tamara Authorise Failed Message on cart page
     */
    public function addTamaraAuthoriseFailedMessage()
    {
        $tamaraAuthoriseParam = filter_input(INPUT_GET, 'tamara_authorise');
        if ('failed' === $tamaraAuthoriseParam && !static::isRestRequest()) {
            if (function_exists('wc_add_notice')) {
                wc_add_notice(__('We are unable to authorise your payment from Tamara. Please contact us if you need assistance.', $this->textDomain), 'error');
            }
        }
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Do needed things on Tamara Cancel Url returned
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handleTamaraCancelUrl()
    {
        $orderId = filter_input(INPUT_GET, 'wcOrderId', FILTER_SANITIZE_NUMBER_INT);
        $wcOrder = wc_get_order($orderId);
        if ($this->isOrderAuthorised($orderId)) {
            $this->preventOrderCancelAction($wcOrder, $orderId);
        } elseif (!empty($orderId)) {
            $newOrderStatus = $this->getWCTamaraGatewayService()->tamaraStatus['payment_cancelled'];
            $orderNote = 'The payment for this order has been cancelled from Tamara.';
            $this->updateOrderStatusAndAddOrderNote($wcOrder, $orderNote, $newOrderStatus, '');
            $cancelUrlFromTamara = add_query_arg(
                [
                    'tamara_custom_status' => 'tamara-p-canceled',
                    'redirect_from' => 'tamara',
                    'cancel_order' => 'true',
                    'order' => $wcOrder->get_order_key(),
                    'order_id' => $orderId,
                    '_wpnonce' => wp_create_nonce('woocommerce-cancel_order'),
                ],
                $wcOrder->get_cancel_order_url_raw()
            );
            wp_redirect($cancelUrlFromTamara);
        }
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Do needed things on Tamara Failure Url returned
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handleTamaraFailureUrl()
    {
        $orderId = filter_input(INPUT_GET, 'wcOrderId', FILTER_SANITIZE_NUMBER_INT);
        $wcOrder = wc_get_order($orderId);
        if ($this->isOrderAuthorised($orderId)) {
            $this->preventOrderCancelAction($wcOrder, $orderId);
        } elseif (!empty($orderId)) {
            $newOrderStatus = $this->getWCTamaraGatewayService()->tamaraStatus['payment_failed'];
            $orderNote = 'The payment for this order has been declined from Tamara.';
            $this->updateOrderStatusAndAddOrderNote($wcOrder, $orderNote, $newOrderStatus, '');
            $failureUrlFromTamara = add_query_arg(
                [
                    'tamara_custom_status' => 'tamara-p-failed',
                    'redirect_from' => 'tamara',
                    'cancel_order' => 'true',
                    'order' => $wcOrder->get_order_key(),
                    'order_id' => $orderId,
                    '_wpnonce' => wp_create_nonce('woocommerce-cancel_order'),
                ],
                $wcOrder->get_cancel_order_url_raw()
            );
            wp_redirect($failureUrlFromTamara);
        }
    }

    /**
     * Do some needed things when activate plugin
     */
    public function activatePlugin()
    {
        if (!class_exists('WooCommerce')) {
            die(sprintf(__('Plugin `%s` needs Woocommerce to be activated', $this->textDomain),
                'Tamara Checkout'));
        }
    }

    /**
     * @noinspection PhpUnusedDeclarationInspection
     */
    public function deactivatePlugin()
    {
        // The problem with calling flush_rewrite_rules() is that the rules instantly get regenerated, while your plugin's hooks are still active.
        delete_option('rewrite_rules');
    }

    /**
     * Add rewrite rule for Tamara IPN and Webhook response page
     */
    public function addCustomRewriteRules()
    {
        add_rewrite_rule(WCTamaraGateway::IPN_SLUG.'/?$', 'index.php?pagename='.WCTamaraGateway::IPN_SLUG, 'top');
        add_rewrite_rule(WCTamaraGateway::WEBHOOK_SLUG.'/?$', 'index.php?pagename='.WCTamaraGateway::WEBHOOK_SLUG, 'top');
        add_rewrite_rule(WCTamaraGateway::PAYMENT_SUCCESS_SLUG.'/?$', 'index.php?pagename='.WCTamaraGateway::PAYMENT_SUCCESS_SLUG, 'top');
        add_rewrite_rule(WCTamaraGateway::PAYMENT_CANCEL_SLUG.'/?$', 'index.php?pagename='.WCTamaraGateway::PAYMENT_CANCEL_SLUG, 'top');
        add_rewrite_rule(WCTamaraGateway::PAYMENT_FAIL_SLUG.'/?$', 'index.php?pagename='.WCTamaraGateway::PAYMENT_FAIL_SLUG, 'top');
    }

    /**
     * Run this method under the "init" action
     */
    public function checkWooCommerceExistence()
    {
        if (class_exists('WooCommerce')) {
            // Add "Settings" link when the plugin is active
            add_filter('plugin_action_links_tamara-checkout/tamara-checkout.php', [$this, 'addSettingsLinks']);
        } else {
            require_once(ABSPATH.'wp-admin/includes/plugin.php');
            // Throw a notice if WooCommerce is NOT active
            deactivate_plugins(plugin_basename($this->pluginFilename));
            add_action('admin_notices', [$this, 'noticeNonWooCommerce']);
        }
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Add more links to plugin settings
     *
     * @param $pluginLinks
     *
     * @return array
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function addSettingsLinks($pluginLinks)
    {
        $pluginLinks[] = '<a href="'.$this->getAdminSettingLink().'">'.esc_html__('Settings',
                $this->textDomain).'</a>';

        return $pluginLinks;
    }

    /**
     * Throw a notice if WooCommerce is NOT active
     */
    public function noticeNonWooCommerce()
    {
        $class = 'notice notice-warning';

        $message = sprintf(__('Plugin `%s` deactivated because WooCommerce is not active. Please activate WooCommerce first.',
            $this->textDomain), 'Tamara Checkout');

        printf('<div class="%1$s"><p><strong>%2$s</strong></p></div>', $class, $message);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Add Tamara Payment Gateway
     *
     * @param $gateways
     *
     * @return array
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function registerTamaraPaymentGateway($gateways)
    {
        $gateways[] = $this->getWCTamaraGatewayService();

        return $gateways;
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Adjust Tamara payment types on checkout page based on Tamara settings
     *
     * @param $availableGateways
     *
     * @return array
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function adjustTamaraPaymentTypesOnCheckout($availableGateways)
    {
        if ($this->isTamaraGatewayEnabled() && is_checkout()) {
            if ($this->getWCTamaraGatewayService()->isSingleCheckoutEnabled()) {
                $availableGateways = $this->possiblyAddTamaraSingleCheckout($availableGateways);
            } else {
                for ($i = 12; $i >= 2; $i--) {
                    $tamaraPayLaterKey = static::TAMARA_GATEWAY_ID;
                    $tamaraPayLaterOffset = array_search($tamaraPayLaterKey, array_keys(WC()->payment_gateways->payment_gateways()));
                    $availableGateways = array_merge(
                        array_slice($availableGateways, 0, $tamaraPayLaterOffset),
                        array($this->getWCTamaraGatewayPayInXService($i)->id => $this->getWCTamaraGatewayPayInXService($i)),
                        array_slice($availableGateways, $tamaraPayLaterOffset, null)
                    );
                }
                $payNextMonthService = [static::TAMARA_GATEWAY_PAY_NEXT_MONTH => $this->getWCTamaraGatewayPayNextMonthService()];
                $availableGateways = $this->mergeTamaraPaymentMethodsAfterPayLaterOffset($payNextMonthService, $availableGateways);
                $payNowService = [static::TAMARA_GATEWAY_PAY_NOW => $this->getWCTamaraGatewayPayNowService()];
                $availableGateways = $this->mergeTamaraPaymentMethodsAfterPayLaterOffset($payNowService, $availableGateways);
            }
        }
        return $availableGateways;
    }

    /**
     * Enqueue admin scripts for settings
     */
    public function enqueueAdminSettingScripts()
    {
        // Only enqueue the setting scripts on the Tamara Checkout settings screen.
        if ($this->isTamaraAdminSettingsScreen()) {
            wp_enqueue_script('tamara-checkout-settings-js', $this->baseUrl.'/assets/dist/js/admin.js', ['jquery'],
                $this->version, true);
            wp_enqueue_style('tamara-admin-css', $this->baseUrl.'/assets/dist/css/admin.css', [],
                $this->version);
        } // Load the admin stylesheet on shop order screen
        elseif (isset($_GET['post_type']) && ('shop_order' === $_GET['post_type'])) {
            wp_enqueue_style('tamara-admin-css', $this->baseUrl.'/assets/dist/css/admin.css', [],
                $this->version);
        }
    }

    /**
     * Add some help text for billing phone when using Tamara payment
     *
     * @param $checkoutFields
     *
     * @return mixed
     */
    public function adjustBillingPhoneDescription($checkoutFields)
    {
        if (isset($checkoutFields['billing'], $checkoutFields['billing']['billing_phone'])) {
            $checkoutFields['billing']['billing_phone']['description'] = __('If you use Tamara Payment, this should be your full Tamara registered phone number (e.g. +966504449999 for KSA, +97150888444 for UAE)',
                $this->textDomain);
        }

        return $checkoutFields;
    }

    /**
     * Enqueue FE stylesheet and scripts
     */
    public function enqueueScripts()
    {
        if ($this->getWCTamaraGatewayService()->isLiveMode()) {
            if (is_checkout()) {
                wp_enqueue_script('tamara-information-widget', static::TAMARA_INFORMATION_WIDGET_URL, [], $this->version, true);
                wp_enqueue_script('tamara-installment-plan-widget', static::TAMARA_INSTALLMENT_PLAN_WIDGET_URL, [], $this->version, true);
            }
            wp_enqueue_script('tamara-product-widget', static::TAMARA_PRODUCT_WIDGET_URL, [], $this->version, true);
        } else {
            if (is_checkout()) {
                wp_enqueue_script('tamara-information-sandbox-widget', static::TAMARA_INFORMATION_WIDGET_SANDBOX_URL, [], $this->version, true);
                wp_enqueue_script('tamara-installment-plan-sandbox-widget', static::TAMARA_INSTALLMENT_PLAN_WIDGET_SANDBOX_URL, [], $this->version, true);
            }
            wp_enqueue_script('tamara-product-sandbox-widget', static::TAMARA_PRODUCT_WIDGET_SANDBOX_URL, [], $this->version, true);
        }


        wp_enqueue_style('tamara-checkout', $this->baseUrl.'/assets/dist/css/main.css', [], $this->version . '&' . time());
        wp_enqueue_script('tamara-checkout', $this->baseUrl.'/assets/dist/js/main.js', ['jquery'], $this->version . '&' . time(), true);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Add relevant links to plugins page
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getAdminSettingLink()
    {
        if (version_compare(WC()->version, '2.6', '>=')) {
            $sectionSlug = $this->getWCTamaraGatewayService()->id;
        } else {
            $sectionSlug = strtolower(WCTamaraGateway::class);
        }

        return admin_url('admin.php?page=wc-settings&tab=checkout&section='.$sectionSlug);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @param \WC_Order_Refund $wcOrderRefund
     * @param $args
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws Exception
     */
    public function tamaraRefundPayment($wcOrderRefund, $args)
    {
        $wcOrder = wc_get_order($args['order_id']);
        $wcOrderId = $args['order_id'];
        $payment_method = $wcOrder->get_payment_method();

        if ($this->isTamaraGateway($payment_method)) {
            $tamaraOrderId = $this->getWCTamaraGatewayService()->getTamaraOrderId($wcOrderId);
            $captureId = $this->getWCTamaraGatewayService()->getTamaraCaptureId($wcOrderId);
            $refundCollection = [];
            $wcOrderTotal = new Money(MoneyHelper::formatNumber(abs($wcOrderRefund->get_amount())),
                $wcOrder->get_currency());
            $wcShippingTotal = new Money(MoneyHelper::formatNumber(abs($wcOrderRefund->get_shipping_total())),
                $wcOrder->get_currency());
            $wcTaxTotal = new Money(MoneyHelper::formatNumber(abs($wcOrderRefund->get_total_tax())),
                $wcOrder->get_currency());
            $wcDiscountTotal = new Money(MoneyHelper::formatNumber($wcOrderRefund->get_discount_total()),
                $wcOrder->get_currency());
            $wcOrderItemsRefund = $this->getWCTamaraGatewayService()->populateTamaraRefundOrderItems($wcOrderRefund);

            try {
                $refundItem = new Refund($captureId, $wcOrderTotal, $wcShippingTotal, $wcTaxTotal,
                    $wcDiscountTotal,
                    $wcOrderItemsRefund);
                array_push($refundCollection, $refundItem);
                $refundResponse = $this->getWCTamaraGatewayService()->tamaraClient->refund(new RefundRequest($tamaraOrderId,
                    $refundCollection));
                $this->logMessage(sprintf("Tamara Refund Response Data: %s", print_r($refundResponse, true)));
            } catch (Exception $tamaraRefundException) {
                $this->logMessage(sprintf("Tamara Service timeout or disconnected.\nError message: '%s'.\nTrace: %s",
                    $tamaraRefundException->getMessage(), $tamaraRefundException->getTraceAsString()));
            }

            if (isset($refundResponse) && $refundResponse->isSuccess()) {
                $wcOrder->add_order_note(
                /* translators: Refund ID */
                    sprintf(__('Order has been refunded successfully - Refund ID: #%1$s', $this->textDomain),
                        $wcOrderRefund->get_id()));

            } else {
                $errorMessage = null;

                if (isset($tamaraRefundException) && $tamaraRefundException instanceof Exception) {

                    $errorMessage = $tamaraRefundException->getMessage();
                    $this->logMessage($errorMessage);

                } elseif (isset($refundResponse)) {
                    if ('refund.shipping_amount_invalid' === $refundResponse->getMessage()) {
                        throw new Exception(__('You need to enter the full shipping amount to refund.',
                            $this->textDomain));
                    } elseif ('items_is_empty' === $refundResponse->getErrors()[0]['error_code']) {
                        throw new Exception(__('Refund item is empty. Please choose your item to refund.',
                            $this->textDomain));
                    } elseif ('refund.capture_not_found' === $refundResponse->getMessage()) {
                        $captureNotFoundMessage = __('Tamara Capture ID not found. Please capture the payment before making a refund.',
                            $this->textDomain);
                        $wcOrder->add_order_note($captureNotFoundMessage);
                        throw new Exception($captureNotFoundMessage);
                    }
                }
                throw new Exception(__('Error! Tamara is having a problem. Please contact Tamara and try again later',
                    $this->textDomain));
            }
        }
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Tamara show widget popup shortcode callback method
     *
     * @param $attributes
     *
     * @return bool|string|void|null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function tamaraProductPopupWidget($attributes)
    {
        extract(shortcode_atts(array(
            'price' => '',
            'currency' => '',
            'language' => '',
        ), $attributes));
        $dataPrice = !empty($price) ? $price : $this->getDisplayedProductPrice();
        $dataCurrency = !empty($currency) ? $currency : get_woocommerce_currency();
        $dataLanguage = !empty($language) ? $language : substr(get_locale(), 0, 2);
        if ($this->isWidgetPopupDisabled() ||
            (!empty($this->getDisplayedProductId()) && $this->isExcludedProduct($this->getDisplayedProductId())) ||
            (!empty($this->getDisplayedProductCategoryIds())
             && $this->isExcludedProductCategory($this->getDisplayedProductCategoryIds()))) {
            return false;
        } else {
            $itemPrice = is_array($dataPrice) ? $this->getAppropriateVariationProductPrice($dataPrice) : $dataPrice;
            return $this->getServiceView()->render('views/woocommerce/checkout/tamara-popup-widget',
                [
                    'dataPrice' => $itemPrice ?? 0,
                    'dataCurrency' => $dataCurrency,
                    'dataLanguage' => $dataLanguage ?? 'en',
                    'inlineType' => static::TAMARA_INLINE_TYPE_PRODUCT_WIDGET_INT,
                ]);
        }
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Tamara show cart widget popup shortcode callback method
     *
     * @param $attributes
     *
     * @return bool|string|void|null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function tamaraCartPopupWidget($attributes)
    {
        extract(shortcode_atts(array(
            'price' => '',
            'currency' => '',
            'language' => '',
        ), $attributes));
        $getPrice = is_cart() ? WC()->cart->get_cart_contents_total() : $this->getDisplayedProductPrice();
        $dataPrice = !empty($price) ? $price : $getPrice;
        $dataCurrency = !empty($currency) ? $currency : get_woocommerce_currency();
        $dataLanguage = !empty($language) ? $language : substr(get_locale(), 0, 2);
        if ($this->isCartWidgetPopupDisabled() ||
            (!empty($this->getDisplayedProductId()) && $this->isExcludedProduct($this->getDisplayedProductId())) ||
            (!empty($this->getDisplayedProductCategoryIds())
             && $this->isExcludedProductCategory($this->getDisplayedProductCategoryIds()))) {
            return false;
        } else {
            $itemPrice = is_array($dataPrice) ? $this->getAppropriateVariationProductPrice($dataPrice) : $dataPrice;
            return $this->getServiceView()->render('views/woocommerce/checkout/tamara-popup-widget',
                [
                    'dataPrice' => $itemPrice ?? 0,
                    'dataCurrency' => $dataCurrency,
                    'dataLanguage' => $dataLanguage ?? 'en',
                    'inlineType' => static::TAMARA_INLINE_TYPE_PRODUCT_WIDGET_INT,
                ]);
        }
    }

    /**
     * Show Tamara popup widget
     */
    public function showTamaraProductPopupWidget()
    {
        if ($this->isTamaraGatewayEnabled()) {
            echo do_shortcode('[tamara_show_popup]');
        }
    }

    /**
     * Show Tamara popup widget on Cart page
     */
    public function showTamaraCartProductPopupWidget()
    {
        if ($this->isTamaraGatewayEnabled() && !$this->isCartWidgetPopupDisabled()) {
            echo do_shortcode('[tamara_show_cart_popup]');
        }
    }

    /**
     * Get displayed product price on FE
     */
    public function getDisplayedProductPrice()
    {
        global $product;
        if ($product) {
            if ($product instanceof \WC_Product) {
                if ($product instanceof \WC_Product_Variable) {
                    return $product->get_variation_prices(true)['price'];
                } else {
                    return wc_get_price_to_display($product);
                }
            }
        }

        return null;
    }

    /**
     * Get displayed product id on FE
     */
    public function getDisplayedProductId()
    {
        global $product;
        if ($product) {
            if ($product instanceof \WC_Product) {
                return $product->get_id();
            }
        }

        return null;
    }

    /**
     * Get all category ids of displayed product on FE
     */
    public function getDisplayedProductCategoryIds()
    {
        global $product;
        if ($product) {
            if ($product instanceof \WC_Product) {
                $productId = $product->get_id();

                return wc_get_product_cat_ids($productId);
            }
        }

        return null;
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @param $productPrice
     *
     * @return bool
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function isProductPriceValid($productPrice)
    {
        // Force pull country payment types from remote api
        $countryPaymentTypes = $this->getWCTamaraGatewayService()->getCountryPaymentTypes();
        $paymentTypes = $this->getWCTamaraGatewayService()->getPaymentTypes();

        if (!$this->isAlwaysShowWidgetPopupEnabled() && !$this->getWCTamaraGatewayService()->isSingleCheckoutEnabled()) {
            if (is_array($productPrice)) {
                foreach ($productPrice as $item => $variationPrice) {
                    if (($this->getWCTamaraGatewayService()->populateMinLimit() <= $variationPrice
                         && $this->getWCTamaraGatewayService()->populateMaxLimit() >= $variationPrice)
                        || ($this->populatePayInXsLimitAmountBasedOnProductPrice($variationPrice)['instalmentMinAmount'] <= $variationPrice
                            && $this->populatePayInXsLimitAmountBasedOnProductPrice($variationPrice)['instalmentMaxAmount'] >= $variationPrice)
                        || ($this->populatePayNextMonthMinLimit() <= $variationPrice
                            && $this->populatePayNextMonthMaxLimit() >= $variationPrice)
                    ) {
                        return true;
                        break;
                    }
                }
            } else {
                return ($this->getWCTamaraGatewayService()->populateMinLimit() <= $productPrice
                        && $this->getWCTamaraGatewayService()->populateMaxLimit() >= $productPrice)
                       || ($this->populatePayInXsLimitAmountBasedOnProductPrice($productPrice)['instalmentMinAmount'] <= $productPrice
                           && $this->populatePayInXsLimitAmountBasedOnProductPrice($productPrice)['instalmentMaxAmount'] >= $productPrice)
                       || ($this->populatePayNextMonthMinLimit() <= $productPrice
                           && $this->populatePayNextMonthMaxLimit() >= $productPrice);
            }
        } else {
            return true;
        }
    }

    /**
     * Modify which total amount should be used to display on checkout page
     *
     * @param $amount
     *
     * @return mixed
     */
    public function getTotalToCalculate($amount)
    {
        if (is_checkout_pay_page()) {
            global $wp;
            if (isset($wp->query_vars['order-pay']) && absint($wp->query_vars['order-pay']) > 0) {
                $orderId = absint($wp->query_vars['order-pay']);
                $wcOrder = wc_get_order($orderId);
                if ($wcOrder) {
                    return $wcOrder->get_total();
                }
            }
        }

        return $amount;
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Update Tamara checkout data to order meta data created via rest api
     *
     * @param \WP_REST_Response $response The response object.
     * @param \WP_Post $post Post object.
     * @param \WP_REST_Request $request Request object.
     *
     * @return mixed
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function updateTamaraCheckoutDataToOrder($response, $post, $request)
    {
        $wcOrder = wc_get_order($post);

        if (!empty($wcOrder) && $this->isTamaraGateway($wcOrder->get_payment_method())) {
            $wcOrderId = $wcOrder->get_id();
            $hasTamaraCheckoutUrl = !!get_post_meta($wcOrderId, 'tamara_checkout_url', true);
            $hasTamaraCheckoutSessionId = !!get_post_meta($wcOrderId, 'tamara_checkout_session_id', true);
            if (!$hasTamaraCheckoutUrl && !$hasTamaraCheckoutSessionId) {
                $tamaraCheckoutResponse = $this->getWCTamaraGatewayService()->tamaraCheckoutSession($wcOrderId);
                if ($tamaraCheckoutResponse) {
                    $response_data = $response->get_data();
                    $metaData = [
                        [
                            'key' => 'tamara_checkout_session_id',
                            'value' => $tamaraCheckoutResponse['tamaraCheckoutSessionId'] ?: null,
                        ],
                        [
                            'key' => 'tamara_checkout_url',
                            'value' => $tamaraCheckoutResponse['tamaraCheckoutUrl'] ?: null,
                        ],
                    ];
                    $response_data['meta_data'] = $response_data['meta_data'] + $metaData;
                    $response->set_data($response_data);
                }
            }
        }

        return $response;
    }

    /**
     * Check a request a Rest API request or not
     * @return bool
     */
    public static function isRestRequest()
    {
        $requestUri = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;

        return $requestUri && strpos($requestUri, 'wp-json') !== false;
    }

    /**
     * Set Rest Api Request to $restApiRequest variable
     *
     * @param $restApiRequest
     */
    public function setRestApiRequest($restApiRequest)
    {
        $this->restApiRequest = $restApiRequest;
    }

    /**
     * Return the Rest Api Request
     *
     * @return \WP_REST_Request
     */
    public function getRestApiRequest()
    {
        return $this->restApiRequest;
    }

    /**
     * Redirect Pay By Instalments settings page to Pay By Later settings page
     */
    public function updatePayByInstalmentSettingUrl()
    {
        if (is_admin() && isset($_GET['page'], $_GET['tab'], $_GET['section'])
            && ('wc-settings' === $_GET['page'])
            && ('checkout' === $_GET['tab'])
            && (in_array($_GET['section'], $this->getPayInXIds()))) {
            wp_redirect(admin_url('admin.php?page=wc-settings&tab=checkout&section='.strtolower(static::TAMARA_GATEWAY_ID)));
        }
    }

    /**
     * @param $fields
     * @param $country
     *
     * @return mixed
     */
    public function forceRequireBillingPhone($fields, $country)
    {
        if (is_wc_endpoint_url('edit-address') || !$this->isForceBillingPhoneEnabled() || !empty($fields['billing_phone'])) {
            return $fields;
        } elseif ($this->isForceBillingPhoneEnabled() && empty($fields['billing_phone'])) {
            $fields['billing_phone'] = [
                'label' => __('Phone', 'woocommerce'),
                'required' => true,
            ];

            return $fields;
        }

        return $fields;
    }

    /**
     * Fire an ajax request without waiting for response
     */
    public function addCronJobTriggerScript()
    {
        $ajaxCronjobUrl = esc_attr(add_query_arg([
            'action' => 'tamara_perform_cron',
        ], admin_url('admin-ajax.php')));
        echo <<<SCRIPT
    <script type="text/javascript">
        var data = {
            'action': 'tamara_perform_cron'
        };
        fetch('$ajaxCronjobUrl', {
            credentials: 'same-origin',
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
            // body: JSON.stringify(data),
        });
    </script>
SCRIPT;
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @return false|string
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function performCron()
    {
        if (current_user_can('publish_posts')) {
            $this->forceAuthoriseTamaraOrder();
            $this->forceCaptureTamaraOrder();

            return json_encode(true);
        }

        return json_encode(false);

    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function removeTrailingSlashes($url)
    {
        return rtrim(trim($url), '/');
    }

    /**
     * Get the array of Pay In X Ids
     *
     * @return array
     */
    public function getPayInXIds()
    {
        return [
            static::TAMARA_GATEWAY_CHECKOUT_ID,
            static::TAMARA_GATEWAY_PAY_IN_2,
            static::TAMARA_GATEWAY_PAY_IN_3,
            static::TAMARA_GATEWAY_PAY_IN_4,
            static::TAMARA_GATEWAY_PAY_IN_5,
            static::TAMARA_GATEWAY_PAY_IN_6,
            static::TAMARA_GATEWAY_PAY_IN_7,
            static::TAMARA_GATEWAY_PAY_IN_8,
            static::TAMARA_GATEWAY_PAY_IN_9,
            static::TAMARA_GATEWAY_PAY_IN_10,
            static::TAMARA_GATEWAY_PAY_IN_11,
            static::TAMARA_GATEWAY_PAY_IN_12,
        ];
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Populate the array of enabled Pay In Xs min amount
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function populateMinAmountArrayOfEnabledPayInXs()
    {
        $countryCode = $this->getWCTamaraGatewayService()->getCurrentCountryCode();
        $payInXMinAmount = [];
        for ($i = 12; $i >= 2; $i--) {
            if ($this->getWCTamaraGatewayService()->isSingleCheckoutEnabled()) {
                $payInXMaxAmount[$i] = $this->populateInstalmentPayInXMaxLimit($i, $countryCode);
            } else {
                if ($this->isPayInXEnabled($i, $countryCode)) {
                    $payInXMinAmount[$i] = $this->populateInstalmentPayInXMinLimit($i, $countryCode);
                }
            }
        }

        return $payInXMinAmount;
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Populate the array of enabled Pay In Xs max amount
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function populateMaxAmountArrayOfEnabledPayInXs()
    {
        $countryCode = $this->getWCTamaraGatewayService()->getCurrentCountryCode();
        $payInXMaxAmount = [];
        for ($i = 12; $i >= 2; $i--) {
            if ($this->getWCTamaraGatewayService()->isSingleCheckoutEnabled()) {
                $payInXMaxAmount[$i] = $this->populateInstalmentPayInXMaxLimit($i, $countryCode);
            } else {
                if ($this->isPayInXEnabled($i, $countryCode)) {
                    $payInXMaxAmount[$i] = $this->populateInstalmentPayInXMaxLimit($i, $countryCode);
                }
            }
        }

        return $payInXMaxAmount;
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get min amount priority instalment period
     *
     * @return int | null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getMinAmountOfEnabledPriorityInstalment()
    {
        $minAmountArr = $this->populateMinAmountArrayOfEnabledPayInXs() ?? [];
        if (!empty($minAmountArr)) {
            $filterNullVarArr = array_filter($minAmountArr, function ($v) {
                return !is_null($v);
            });
            if (!empty($filterNullVarArr)) {
                return $minAmountArr[max(array_keys($filterNullVarArr))];
            }
        }

        return null;
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get max amount priority instalment period
     *
     * @return int | null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getMaxAmountOfEnabledPriorityInstalment()
    {
        $maxAmountArr = $this->populateMaxAmountArrayOfEnabledPayInXs() ?? [];
        if (!empty($maxAmountArr)) {
            $filterNullVarArr = array_filter($maxAmountArr, function ($v) {
                return !is_null($v);
            });
            if (!empty($filterNullVarArr)) {
                return $maxAmountArr[max(array_keys($filterNullVarArr))];
            }
        }

        return null;
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Get priority instalment period amongs enabled Pay In Xs
     *
     * @return int | null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getPriorityInstalmentPeriod()
    {
        $maxAmountArr = $this->populateMaxAmountArrayOfEnabledPayInXs() ?? [];
        if (!empty($maxAmountArr)) {
            $filterNullVarArr = array_filter($maxAmountArr, function ($v) {
                return !is_null($v);
            });

            return max(array_keys($filterNullVarArr));
        }

        return null;
    }

    /**
     * Check if current screen is Tamara Admin Settings page
     *
     * @return bool
     */
    public function isTamaraAdminSettingsScreen()
    {
        return (is_admin() && isset($_GET['page'], $_GET['tab'], $_GET['section'])
                && ('wc-settings' === $_GET['page'])
                && ('checkout' === $_GET['tab'])
                && (static::TAMARA_GATEWAY_ID === $_GET['section']));
    }

    /**
     * Return the array of Tamara Gateway Ids
     *
     * @return array
     */
    public function getAllTamaraGatewayIds()
    {
        return [
            static::TAMARA_GATEWAY_ID,
            static::TAMARA_GATEWAY_PAY_NOW,
            static::TAMARA_GATEWAY_PAY_NEXT_MONTH,
            static::TAMARA_GATEWAY_PAY_BY_INSTALMENTS_ID,
            static::TAMARA_GATEWAY_PAY_IN_2,
            static::TAMARA_GATEWAY_PAY_IN_3,
            static::TAMARA_GATEWAY_PAY_IN_4,
            static::TAMARA_GATEWAY_PAY_IN_5,
            static::TAMARA_GATEWAY_PAY_IN_6,
            static::TAMARA_GATEWAY_PAY_IN_7,
            static::TAMARA_GATEWAY_PAY_IN_8,
            static::TAMARA_GATEWAY_PAY_IN_9,
            static::TAMARA_GATEWAY_PAY_IN_10,
            static::TAMARA_GATEWAY_PAY_IN_11,
            static::TAMARA_GATEWAY_PAY_IN_12,
            static::TAMARA_GATEWAY_CHECKOUT_ID,
        ];
    }

    /**
     * Check if a payment method is Tamara
     *
     * @param $paymentMethodId
     *
     * @return bool
     */
    public function isTamaraGateway($paymentMethodId)
    {
        return !!in_array($paymentMethodId, $this->getAllTamaraGatewayIds());
    }

    /**
     * Get all product ids of items in cart, including parent and child ids.
     *
     * @return array
     */
    public function getAllProductIdsInCart()
    {
        $allCartItems = WC()->cart->get_cart();
        $productIds = [];

        foreach ($allCartItems as $item => $values) {
            $itemId = $values['data']->get_id() ?? null;
            $productIds[] = $itemId;
            $product = wc_get_product($itemId);
            // Check if a product is a variation add add its parent id to the list.
            if ($product instanceof \WC_Product_Variation) {
                $productParentId = $product->get_parent_id() ?? null;
                if (!in_array($productParentId, $productIds)) {
                    $productIds[] = $productParentId;
                }
            }
        }

        return $productIds;
    }

    /**
     * Get all category ids of items in cart, including ancestors and subcategories.
     *
     * @return array
     */
    public function getAllProductCategoryIdsInCart()
    {
        $allCartItems = WC()->cart->get_cart();
        $allProductCategoryIds = [];

        foreach ($allCartItems as $item => $values) {
            $productId = $values['data']->get_id() ?? null;
            $allProductCategoryIds = array_merge($allProductCategoryIds, wc_get_product_cat_ids($productId));
        }

        return $allProductCategoryIds;
    }

    /**
     * Get the array of Tamara excluded product ids
     *
     * @return array
     */
    public function getExcludedProductIds()
    {
        $tamaraExcludedProductsOption = $this->getWCTamaraGatewayOptions()['excluded_products'] ?? '';

        return array_map('trim', explode(',',
            $tamaraExcludedProductsOption));
    }

    /**
     * Get the array of Tamara excluded product category ids
     *
     * @return array
     */
    public function getExcludedProductCategoryIds()
    {
        $tamaraExcludedProductCategoriesOption = $this->getWCTamaraGatewayOptions()['excluded_product_categories'] ?? '';

        return array_map('trim', explode(',',
            $tamaraExcludedProductCategoriesOption));
    }

    /**
     * Check if a product is excluded from using Tamara
     *
     * @param int $productId
     *
     * @return bool
     */
    public function isExcludedProduct($productId)
    {
        return !!(in_array($productId, $this->getExcludedProductIds()));
    }

    /**
     * Check if there's any product category id is excluded from using Tamara
     *
     * @param array $productCategoryIds
     *
     * @return bool
     */
    public function isExcludedProductCategory($productCategoryIds)
    {
        return !!(count(array_intersect($productCategoryIds, $this->getExcludedProductCategoryIds())));
    }

    /**
     * Get the base url of plugin
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Get appropriate instalment plan according to current product variation price
     * and return the value through ajax call
     */
    public function getInstalmentPlanAccordingToProductVariation()
    {
        $variationPrice = filter_input(INPUT_POST, 'variationPrice', FILTER_SANITIZE_STRING);
        if (!empty($variationPrice) && !$this->isWidgetPopupDisabled()) {
            $currency = get_woocommerce_currency();
            // Todo: Handle and re-generate if PDP is not initialized when there is no plan for the smallest variation price
            $PDPWidgetData = $this->populatePDPWidgetBasedOnPrice($variationPrice, $currency) ?? [];
            if (!empty($PDPWidgetData['paymentType'])) {
                wp_send_json([
                    'message' => 'success',
                    'data' => $PDPWidgetData,
                ]);
            }
        }

        wp_send_json(
            [
                'message' => 'No payment type found for this price',
            ]
        );
    }

    /**
     * Update Tamara Checkout Params on updated_checkout event
     * and return the value through ajax call
     */
    public function updateTamaraCheckoutParams()
    {
        $storeCurrency = get_woocommerce_currency();
        $countryCode = $this->getWCTamaraGatewayService()->getCurrentCountryCode();

        wp_send_json(
            [
                'message' => 'success',
                'country' => $countryCode,
                'currency' => $storeCurrency,
            ]
        );
    }

    /**
     * Return the first value in variation product price array that meets the smallest instalment plan
     *
     * @param array $variationPriceArr
     *
     * @return mixed
     */
    public function getAppropriateVariationProductPrice($variationPriceArr)
    {
        foreach ($variationPriceArr as $item => $variationPrice) {
            if (($this->getWCTamaraGatewayService()->populateMinLimit() <= $variationPrice
                 && $this->getWCTamaraGatewayService()->populateMaxLimit() >= $variationPrice)
                || ($this->populatePayInXsLimitAmountBasedOnProductPrice($variationPrice)['instalmentMinAmount'] <= $variationPrice
                    && $this->populatePayInXsLimitAmountBasedOnProductPrice($variationPrice)['instalmentMaxAmount'] >= $variationPrice)) {
                return $variationPrice;
                break;
            } elseif ($this->isAlwaysShowWidgetPopupEnabled() && !$this->isWidgetPopupDisabled()) {
                return $variationPrice;
                break;
            }
        }

        return null;
    }

    /**
     * Override Wc Clear Cart function and keep cart for orders with cancelled/failed payments from Tamara
     */
    public function overrideWcClearCart()
    {
        remove_action('get_header', 'wc_clear_cart_after_payment');

        global $wp;
        if (!empty($wp->query_vars['order-received'])) {
            $order_id = absint($wp->query_vars['order-received']);
            $order_key = isset($_GET['key']) ? wc_clean(wp_unslash($_GET['key'])) : ''; // WPCS: input var ok, CSRF ok.
            if ($order_id > 0) {
                $order = wc_get_order($order_id);
                if ($order && hash_equals($order->get_order_key(), $order_key)) {
                    WC()->cart->empty_cart();
                }
            }
        }
        if (WC()->session->order_awaiting_payment > 0) {
            $order = wc_get_order(WC()->session->order_awaiting_payment);
            if ($order && $order->get_id() > 0) {
                // If the order has not failed, or is not pending, the order must have gone through.
                if (!$order->has_status(array('failed', 'pending', 'cancelled'))
                    && !$this->isTamaraGateway($order->get_payment_method())) {
                    WC()->cart->empty_cart();
                }
            }
        }
    }

    /**
     * Cancel a pending order and add Tamara payment cancelled/failed notice.
     */
    public function cancelOrder()
    {
        if (
            isset($_GET['cancel_order']) &&
            isset($_GET['order']) &&
            isset($_GET['order_id']) &&
            (isset($_GET['_wpnonce']) && wp_verify_nonce(wp_unslash($_GET['_wpnonce']), 'woocommerce-cancel_order')) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        ) {
            wc_nocache_headers();
            $order_key = wp_unslash($_GET['order']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $order_id = absint($_GET['order_id']);
            $order = wc_get_order($order_id);
            $paymentMethod = $order->get_payment_method();
            $user_can_cancel = current_user_can('cancel_order', $order_id);
            $order_can_cancel = $order->has_status(apply_filters('woocommerce_valid_order_statuses_for_cancel', array('pending', 'failed'), $order));
            $redirect = isset($_GET['redirect']) ? wp_unslash($_GET['redirect']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

            if ($user_can_cancel && !$order_can_cancel && $this->isTamaraGateway($paymentMethod)) {
                wc_clear_notices();
                wc_add_notice(__('Your payment via Tamara has failed, please try again with a different payment method.', $this->textDomain), 'error');
            }

            if ($redirect) {
                wp_safe_redirect($redirect);
                exit;
            }
        }
    }

    /**
     * Update phone number on every ajax calls on checkout
     *
     * @param $postedData
     *
     * @return void
     */
    public function getUpdatedPhoneNumberOnCheckout($postedData)
    {
        global $woocommerce;

        // Parsing posted data on checkout
        $post = array();
        $vars = explode('&', $postedData);
        foreach ($vars as $k => $value) {
            $v = explode('=', urldecode($value));
            $post[$v[0]] = $v[1];
        }

        // Update phone number get from posted data
        $this->customerPhoneNumber = $post['billing_phone'];
    }

    /**
     * Return customer phone number
     *
     * @return string
     */
    public function getCustomerPhoneNumber()
    {
        return $this->customerPhoneNumber;
    }

    /**
     * Update WC Order Payment method according to Tamara order
     *
     * @param $wcOrderId
     * @param $wcOrder
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function updateWcOrderPaymentMethodAccordingToTamaraOrder($wcOrderId, $wcOrder)
    {
        $tamaraOrder = $this->getTamaraOrderByWcOrderId($wcOrderId);
        $paymentType = $tamaraOrder->getPaymentType();
        update_post_meta($wcOrderId, 'tamara_payment_type', $paymentType);

        if ($paymentType === $this->getWCTamaraGatewayService()->getPaymentTypeMapping()[static::TAMARA_GATEWAY_ID]) {
            update_post_meta($wcOrderId, 'payment_method', static::TAMARA_GATEWAY_ID);
            delete_post_meta($wcOrderId, 'tamara_payment_type_instalment');
            $wcOrder->set_payment_method(static::TAMARA_GATEWAY_ID);
            $wcOrder->set_payment_method_title($this->getWCTamaraGatewayService()::TAMARA_GATEWAY_DEFAULT_TITLE);
            $wcOrder->save();
        } elseif ($paymentType === WCTamaraGateway::PAYMENT_TYPE_PAY_NEXT_MONTH) {
            update_post_meta($wcOrderId, 'payment_method', static::TAMARA_GATEWAY_ID);
            delete_post_meta($wcOrderId, 'tamara_payment_type_instalment');
            $wcOrder->set_payment_method(static::TAMARA_GATEWAY_ID);
            $wcOrder->set_payment_method_title($this->getWCTamaraGatewayService()::TAMARA_GATEWAY_PAY_NEXT_MONTH_DEFAULT_TITLE);
            $wcOrder->save();
        }  elseif ($paymentType === WCTamaraGateway::PAYMENT_TYPE_PAY_NOW) {
            update_post_meta($wcOrderId, 'payment_method', static::TAMARA_GATEWAY_ID);
            delete_post_meta($wcOrderId, 'tamara_payment_type_instalment');
            $wcOrder->set_payment_method(static::TAMARA_GATEWAY_ID);
            $wcOrder->set_payment_method_title($this->getWCTamaraGatewayService()::TAMARA_GATEWAY_PAY_NOW_DEFAULT_TITLE);
            $wcOrder->save();
        } else {
            $instalment = $tamaraOrder->getInstalments();
            update_post_meta($wcOrderId, 'payment_method', static::TAMARA_GATEWAY_PAY_IN_X.$instalment);
            update_post_meta($wcOrderId, 'tamara_payment_type_instalment', $instalment);
            $wcOrder->set_payment_method(static::TAMARA_GATEWAY_PAY_IN_X.$instalment);
            $wcOrder->set_payment_method_title($this->getWCTamaraGatewayService()->getPayInXTitle($instalment));
            $wcOrder->save();
        }
    }

    /**
     * Add Tamara Single Checkout to existing available gateways
     *
     * @param $availableGateways
     *
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function possiblyAddTamaraSingleCheckout($availableGateways)
    {
        $singleCheckoutService = [static::TAMARA_GATEWAY_CHECKOUT_ID => $this->getWCTamaraGatewayCheckoutService()];
        $availableGateways = $this->mergeTamaraPaymentMethodsAfterPayLaterOffset($singleCheckoutService, $availableGateways);
        $payNowService = [static::TAMARA_GATEWAY_PAY_NOW => $this->getWCTamaraGatewayPayNowService()];
        $availableGateways = $this->mergeTamaraPaymentMethodsAfterPayLaterOffset($payNowService, $availableGateways);
        unset($availableGateways[static::TAMARA_GATEWAY_ID]);
        return $availableGateways;
    }

    /**
     * Add other Tamara payment methods right after Pay Later offset on checkout
     *
     * @param $array
     * @param $availableGateways
     *
     * @return array
     */
    protected function mergeTamaraPaymentMethodsAfterPayLaterOffset($array, $availableGateways)
    {
        $tamaraPayLaterKey = static::TAMARA_GATEWAY_ID;
        $tamaraPayLaterOffset = array_search($tamaraPayLaterKey, array_keys(WC()->payment_gateways->payment_gateways()));

        return array_merge(
            array_slice($availableGateways, 0, $tamaraPayLaterOffset),
            $array,
            array_slice($availableGateways, $tamaraPayLaterOffset, null)
        );
    }

    protected function isAfterTamaraAuthorised($orderStatus)
    {
        return !!in_array($orderStatus, $this->getAfterTamaraAuthorisedStatuses());
    }

    protected function getAfterTamaraAuthorisedStatuses()
    {
        return [
            static::TAMARA_CANCELED_STATUS,
            static::TAMARA_PARTIALLY_CAPTURED_STATUS,
            static::TAMARA_FULLY_CAPTURED_STATUS,
            static::TAMARA_PARTIALLY_REFUNDED_STATUS,
            static::TAMARA_FULLY_REFUNDED_STATUS
        ];
    }

    protected function isSupportedCountry($countryCode)
    {
        $supportedCountries = ['SA', 'AE', 'KW', 'BH'];

        return !!in_array($countryCode, $supportedCountries);
    }

    /**
     * Get Tamara order status from remote
     *
     * @param $wcOrderId
     *
     * @return string
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getTamaraOrderStatus($wcOrderId)
    {
        $tamaraOrder = $this->getTamaraOrderByWcOrderId($wcOrderId);
        if ($tamaraOrder) {
            return $tamaraOrder->getStatus() ?? null;
        }

        return null;
    }

}
