<?php

class WC_Controller_Moyasar_Payment
{
    public static $instance;

    protected $gateway;
    protected $logger;

    public static function init()
    {
        $controller = new static();

        add_action('rest_api_init', array($controller, 'register_routes'));

        return static::$instance = $controller;
    }

    public function __construct()
    {
        $this->gateway = new WC_Gateway_Moyasar_Payment_Form();
        $this->logger = wc_get_logger();
    }

    public function register_routes()
    {
        register_rest_route(
            'moyasar/v2',
            'payment/initiated',
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'save_initiated_payment'),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            'moyasar/v2',
            'payment/failed',
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'cancel_current_order'),
                'permission_callback' => '__return_true'
            )
        );
    }

    public function save_initiated_payment(WP_REST_Request $request)
    {
        ini_set('display_errors', 0);

        $id = $request->get_param('id');

        if (! $id) {
            $this->logger->warning('Moyasar: missing payment ID from save initiated payment endpoint');

            return new WP_REST_Response(array(
                'success' => false
            ), 400);
        }

        try {
            $order = $this->gateway->get_order_from_url_or_fail();

            $status = $order->get_status('edit');
            if (! in_array($status, array('pending', 'failed'))) {
                $this->logger->info("Moyasar: Order " . $order->get_id() . " is not pending. Status: $status, ignoring transaction id $id");
                new WP_REST_Response(array('success' => true), 201);
            }

            $order->set_transaction_id($id);
            $order->add_order_note("Assigning payment id $id");
            $order->save();

            $this->logger->info("Moyasar: Saved payment ID $id for order " . $order->get_id());

            return new WP_REST_Response(array('success' => true), 201);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $this->logger->error("Moyasar: Could not save payment ID $id");

            return new WP_REST_Response(array(
                'success' => false,
                'message' => $e->getMessage()
            ), 400);
        }
    }

    public function cancel_current_order(WP_REST_Request $request)
    {
        ini_set('display_errors', 0);

        $message = $request->get_param('message');

        try {
            $order = $this->gateway->get_current_order_or_fail();

            if (! $order->is_paid()) {
                $order->set_status('failed');
                $order->add_order_note("Order was canceled for payment failure. Message: $message.");
                $order->save();
            }
        } finally {
            return array('message' => 'Success');
        }
    }
}
