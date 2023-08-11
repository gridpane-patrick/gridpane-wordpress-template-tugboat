<?php

namespace Tamara\Wp\Plugin\Dependencies\Tamara;

use Tamara\Wp\Plugin\Dependencies\Tamara\HttpClient\HttpClient;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Checkout\CheckPaymentOptionsAvailabilityRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Checkout\CreateCheckoutRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Checkout\GetPaymentTypesRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Checkout\GetPaymentTypesV2Request;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Merchant\GetDetailsInfoRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Order\AuthoriseOrderRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Order\CancelOrderRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Order\GetOrderByReferenceIdRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Order\GetOrderRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Order\UpdateReferenceIdRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Payment\CaptureRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Payment\RefundRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\RequestDispatcher;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Webhook\RegisterWebhookRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Webhook\RemoveWebhookRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Webhook\RetrieveWebhookRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Request\Webhook\UpdateWebhookRequest;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Checkout\CheckPaymentOptionsAvailabilityResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Checkout\CreateCheckoutResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Checkout\GetPaymentTypesResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Merchant\GetDetailsInfoResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Order\AuthoriseOrderResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Order\GetOrderByReferenceIdResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Order\GetOrderResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Order\UpdateReferenceIdResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Payment\CancelResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Payment\CaptureResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Payment\RefundResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Webhook\RegisterWebhookResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Webhook\RemoveWebhookResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Webhook\RetrieveWebhookResponse;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\Webhook\UpdateWebhookResponse;

class Client
{
    /**
     * @var string
     */
    public const VERSION = '1.3.16';

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var RequestDispatcher
     */
    private $requestDispatcher;

    /**
     * @param Configuration $configuration
     *
     * @return Client
     */
    public static function create(Configuration $configuration): Client
    {
        return new static($configuration->createHttpClient());
    }

    /**
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->requestDispatcher = new RequestDispatcher($httpClient);
    }

    /**
     * @param string $countryCode
     * @param string $currency
     *
     * @return GetPaymentTypesResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function getPaymentTypes(string $countryCode, string $currency = ''): GetPaymentTypesResponse
    {
        return $this->requestDispatcher->dispatch(new GetPaymentTypesRequest($countryCode, $currency));
    }

    /**
     * @param GetPaymentTypesV2Request $request
     *
     * @return GetPaymentTypesResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function getPaymentTypesV2(GetPaymentTypesV2Request $request): GetPaymentTypesResponse
    {
        return $this->requestDispatcher->dispatch($request);
    }

    /**
     * @param CreateCheckoutRequest $createCheckoutRequest
     *
     * @return CreateCheckoutResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function createCheckout(CreateCheckoutRequest $createCheckoutRequest): CreateCheckoutResponse
    {
        return $this->requestDispatcher->dispatch($createCheckoutRequest);
    }

    /**
     * @param AuthoriseOrderRequest $authoriseOrderRequest
     *
     * @return AuthoriseOrderResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function authoriseOrder(AuthoriseOrderRequest $authoriseOrderRequest): AuthoriseOrderResponse
    {
        return $this->requestDispatcher->dispatch($authoriseOrderRequest);
    }

    /**
     * @param CancelOrderRequest $cancelOrderRequest
     *
     * @return CancelResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function cancelOrder(CancelOrderRequest $cancelOrderRequest): CancelResponse
    {
        return $this->requestDispatcher->dispatch($cancelOrderRequest);
    }

    /**
     * @param CaptureRequest $captureRequest
     *
     * @return CaptureResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function capture(CaptureRequest $captureRequest): CaptureResponse
    {
        return $this->requestDispatcher->dispatch($captureRequest);
    }

    /**
     * @param RefundRequest $refundRequest
     *
     * @return RefundResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function refund(RefundRequest $refundRequest): RefundResponse
    {
        return $this->requestDispatcher->dispatch($refundRequest);
    }

    /**
     * @param RegisterWebhookRequest $request
     * @return RegisterWebhookResponse
     * @throws Exception\RequestDispatcherException
     */
    public function registerWebhook(RegisterWebhookRequest $request): RegisterWebhookResponse
    {
        return $this->requestDispatcher->dispatch($request);
    }

    /**
     * @param RetrieveWebhookRequest $request
     *
     * @return RetrieveWebhookResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function retrieveWebhook(RetrieveWebhookRequest $request): RetrieveWebhookResponse
    {
        return $this->requestDispatcher->dispatch($request);
    }

    /**
     * @param RemoveWebhookRequest $request
     *
     * @return RemoveWebhookResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function removeWebhook(RemoveWebhookRequest $request): RemoveWebhookResponse
    {
        return $this->requestDispatcher->dispatch($request);
    }

    /**
     * @param UpdateWebhookRequest $request
     *
     * @return UpdateWebhookResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function updateWebhook(UpdateWebhookRequest $request): UpdateWebhookResponse
    {
        return $this->requestDispatcher->dispatch($request);
    }

    /**
     * @param UpdateReferenceIdRequest $request
     *
     * @return UpdateReferenceIdResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function updateOrderReferenceId(UpdateReferenceIdRequest $request): UpdateReferenceIdResponse
    {
        return $this->requestDispatcher->dispatch($request);
    }

    /**
     * @param GetOrderByReferenceIdRequest $request
     *
     * @return GetOrderByReferenceIdResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function getOrderByReferenceId(GetOrderByReferenceIdRequest $request): GetOrderByReferenceIdResponse
    {
        return $this->requestDispatcher->dispatch($request);
    }

    /**
     * Get order details by tamara order id
     *
     * @param GetOrderRequest $request
     *
     * @return GetOrderResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function getOrder(GetOrderRequest $request): GetOrderResponse
    {
        return $this->requestDispatcher->dispatch($request);
    }

    /**
     * Get merchant details information by merchant id
     *
     * @param GetDetailsInfoRequest $request
     *
     * @return GetDetailsInfoResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function getMerchantDetailsInfo(GetDetailsInfoRequest $request): GetDetailsInfoResponse
    {
        return $this->requestDispatcher->dispatch($request);
    }

    /**
     * Check if there are any available payment options for customer with the given order value
     *
     * @param CheckPaymentOptionsAvailabilityRequest $request
     *
     * @return CheckPaymentOptionsAvailabilityResponse
     *
     * @throws Exception\RequestDispatcherException
     */
    public function checkPaymentOptionsAvailability(CheckPaymentOptionsAvailabilityRequest $request): CheckPaymentOptionsAvailabilityResponse
    {
        return $this->requestDispatcher->dispatch($request);
    }
}
