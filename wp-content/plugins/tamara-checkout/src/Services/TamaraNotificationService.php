<?php


namespace Tamara\Wp\Plugin\Services;

use Exception;
use Tamara\Wp\Plugin\Dependencies\Tamara\Client;
use Tamara\Wp\Plugin\Dependencies\Tamara\Exception\RequestDispatcherException;
use Tamara\Wp\Plugin\Dependencies\Tamara\Notification\NotificationService;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Order\AuthoriseOrderRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Order\AuthoriseOrderResponse;
use Tamara\Wp\Plugin\TamaraCheckout;
use Tamara\Wp\Plugin\Traits\ConfigTrait;
use Tamara\Wp\Plugin\Traits\ServiceTrait;
use Tamara\Wp\Plugin\Traits\WPAttributeTrait;

/**
 * Class TamaraNotificationService
 * @package Tamara\Wp\Plugin\Services
 * @method \Tamara\Wp\Plugin\TamaraCheckout getContainer()
 */
class TamaraNotificationService
{
    use ConfigTrait;
    use WPAttributeTrait;
    use ServiceTrait;

    private const
        REGISTER_WEBHOOKS = [
        'order_expired',
        'order_declined',
    ];

    /**
     * @var WCTamaraGateway
     */
    public $wcTamaraGateway;

    /**
     * @var string Token Key for Tamara Notification service
     */
    public $tokenKey;

    /**
     * @var Client
     */
    public $tamaraClient;

    /**
     * @var array Tamara custom order statuses
     */
    public $tamaraStatus = [];

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * Prepare Tamara Client instance
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function init()
    {
        $this->wcTamaraGateway = $this->getContainer()->getService(WCTamaraGateway::class);
        $this->tamaraClient = $this->wcTamaraGateway->tamaraClient;

        $this->tokenKey = $this->wcTamaraGateway->notificationToken;

        $this->tamaraStatus = $this->wcTamaraGateway->tamaraStatus;
    }

    /**
     * Handle Instant Payment Notification from Tamara to update order
     */
    public function handleIpnRequest()
    {
        try {
            TamaraCheckout::getInstance()->logMessage('Tamara start processing IPN');

            $notification = NotificationService::create($this->tokenKey);
            $message = $notification->processAuthoriseNotification();

            TamaraCheckout::getInstance()->logMessage(sprintf("Tamara Notification Process Response: %s", print_r($message, true)));

            if (!empty($message)) {
                $wcOrderId = $message->getOrderReferenceId();
                $tamaraOrderId = $message->getOrderId();
                $wcOrder = wc_get_order($wcOrderId);

                update_post_meta($wcOrderId, 'payment_method', $wcOrder->get_payment_method());
                $this->wcTamaraGateway->updateTamaraOrderId($wcOrderId, $tamaraOrderId);

                if (!TamaraCheckout::getInstance()->isOrderAuthorised($wcOrderId) && $wcOrder && ('approved' === $message->getOrderStatus())) {
                    $this->authoriseOrder($wcOrderId, $tamaraOrderId);
                }

                http_response_code(200);
            } else {
                TamaraCheckout::getInstance()->logMessage("Tamara IPN Failed: empty message");
                http_response_code(406);
            }
        } catch (Exception $exception) {
            TamaraCheckout::getInstance()->logMessage(sprintf("Tamara IPN Failed: %s", print_r($exception, true)));
            if (403 === $exception->getCode()) {
                http_response_code($exception->getCode());
            } else {
                http_response_code(406);
            }
        }
    }

    /**
     * Authorise Tamara Order method
     *
     * @param int $wcOrderId
     * @param string $tamaraOrderId
     */
    public function authoriseOrder($wcOrderId, $tamaraOrderId)
    {
        try {
            $response = $this->tamaraClient->authoriseOrder(new AuthoriseOrderRequest($tamaraOrderId));
            TamaraCheckout::getInstance()->logMessage(sprintf("Tamara Payment Authorise Process Response: %s", print_r($response, true)));
        } catch (RequestDispatcherException $e) {
            TamaraCheckout::getInstance()->logMessage(
                sprintf(
                    "Tamara Payment Authorise Failed.\nError message: ' %s'.\nTrace: %s",
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );
        }

        if (!empty($response)) {
            $wcOrder = wc_get_order($wcOrderId);
            if ($response->isSuccess()) {
                $orderNote = 'Tamara - Order authorised successfully with Tamara Notification';
                $newOrderStatus = $this->tamaraStatus['authorise_done'];
                $updateOrderStatusNote = 'Payment received. ';
                TamaraCheckout::getInstance()->updateOrderStatusAndAddOrderNote($wcOrder, $orderNote, $newOrderStatus, $updateOrderStatusNote);

                // Empty cart if payment done
                WC()->cart->empty_cart();
                update_post_meta($wcOrderId, 'tamara_authorized', true);
                update_post_meta($wcOrderId, 'payment_method', $wcOrder->get_payment_method());
                $this->wcTamaraGateway->updateTamaraOrderId($wcOrderId, $tamaraOrderId);
                if (TamaraCheckout::TAMARA_GATEWAY_CHECKOUT_ID === $wcOrder->get_payment_method()) {
                    TamaraCheckout::getInstance()->updateWcOrderPaymentMethodAccordingToTamaraOrder($wcOrderId, $wcOrder);
                }

            } elseif ($this->isAuthorizedResponse($response)) {
                $wcOrder->add_order_note(
                    __(
                        'Tamara - Order authorised re-occurred, ignore it.',
                        $this->textDomain
                    )
                );
            } else {
                $orderNote = 'Tamara - Order authorised failed with Tamara Notification';
                $newOrderStatus = $this->tamaraStatus['authorise_failed'];
                $updateOrderStatusNote = 'Tamara - Order authorised failed.';
                TamaraCheckout::getInstance()->updateOrderStatusAndAddOrderNote($wcOrder, $orderNote, $newOrderStatus, $updateOrderStatusNote);
            }
        }
    }

    /**
     * Handle webhook execution
     */
    public function handleWebhook()
    {
        TamaraCheckout::getInstance()->logMessage('Start Tamara Webhook');
        try {
            $notification = NotificationService::create($this->tokenKey);
            $webhookMessage = $notification->processWebhook();
            $eventType = $webhookMessage->getEventType();

            TamaraCheckout::getInstance()->logMessage(sprintf("Webhook Event Type: %s", print_r($eventType, true)));
            TamaraCheckout::getInstance()->logMessage(sprintf("Webhook Process Message: %s", print_r($webhookMessage, true)));

            if (!in_array($eventType, self::REGISTER_WEBHOOKS)) {
                $response = [
                    'Event type: ' => $eventType,
                    'Webhook tamara order id: ' => $webhookMessage->getOrderId(),
                    'Webhook reference order id: ' => $webhookMessage->getOrderReferenceId(),
                ];
                TamaraCheckout::getInstance()->logMessage(sprintf("Wrong Webhook event. Response Data: %s", print_r($response, true)));

                return false;
            }

            if (!empty($webhookMessage) && !TamaraCheckout::getInstance()->isOrderAuthorised($webhookMessage->getOrderReferenceId())) {
                if ($eventType === 'order_expired' || $eventType === 'order_declined') {
                    $wcOrderId = $webhookMessage->getOrderReferenceId();
                    $wcOrder = wc_get_order($wcOrderId);
                    $orderNote = sprintf(__('Tamara - Event type `%s` received via webhook'), $eventType);
                    $newOrderStatus = $this->tamaraStatus['order_cancelled'];
                    TamaraCheckout::getInstance()->updateOrderStatusAndAddOrderNote($wcOrder, $orderNote, $newOrderStatus, '');
                }

                http_response_code(200);
            }
        } catch (Exception $tamaraWebhookException) {
            TamaraCheckout::getInstance()->logMessage(sprintf("Error message: '%s'.\nTrace: %s",
                $tamaraWebhookException->getMessage(), $tamaraWebhookException->getTraceAsString()));
            if (403 === $tamaraWebhookException->getCode()) {
                http_response_code($tamaraWebhookException->getCode());
            } else {
                http_response_code(406);
            }
        }
        TamaraCheckout::getInstance()->logMessage('End Tamara Webhook');

        return true;
    }

    /**
     * Check if a response telling an order is authorised
     *
     * @param AuthoriseOrderResponse $response
     *
     * @return bool
     */
    protected function isAuthorizedResponse(AuthoriseOrderResponse $response)
    {
        return $response->getStatusCode() === 409;
    }
}