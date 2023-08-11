<?php

namespace ITeam\Kashier\Api;

use ITeam\Kashier\Common\KashierResourceModel;
use ITeam\Kashier\Rest\ApiContext;
use ITeam\Kashier\Transport\KashierRestCall;

/**
 * Class Refund
 *
 * Lets you create a new checkout post request.
 *
 * @package ITeam\Kashier\Api
 *
 * @property \ITeam\Kashier\Api\Data\RefundRequest $checkoutRequest
 * @property string $status
 * @property array $response
 * @property array error
 */
class Refund extends KashierResourceModel
{
    const PENDING_STATUS_MAP = [
        'PENDING',
        'PENDING_ACTION'
    ];

    /**
     * @param \ITeam\Kashier\Api\Data\RefundRequest $checkoutRequest
     *
     * @return Refund
     */
    public function setRefundRequest(\ITeam\Kashier\Api\Data\RefundRequest $checkoutRequest)
    {
        $this->checkoutRequest = $checkoutRequest;

        return $this;
    }

    /**
     * Creates and processes a checkout.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param KashierRestCall $restCall is the Rest Call Service that is used to make rest calls
     *
     * @return Refund
     * @throws \ITeam\Kashier\Exception\KashierConfigurationException
     * @throws \ITeam\Kashier\Exception\KashierConnectionException
     */
    public function create($apiContext, $restCall = null)
    {
        $transactionId = $this->getRefundRequest()->getTransactionId();
        $orderId = $this->getRefundRequest()->getOrderId();

        $payLoad = $this->getRefundRequest()->toJSON();

        $json = self::executeCall(
            '/orders/'.$orderId.'/transactions/'.$transactionId.'?operation=refund',
            'PUT',
            $payLoad,
            array('Authorization' => $apiContext->getSecretKey()),
            $apiContext,
            $restCall
        );

        $this->fromJson($json);
    
        return $this;
    }

    /**
     * @return \ITeam\Kashier\Api\Data\RefundRequest
     */
    public function getRefundRequest()
    {
        return $this->checkoutRequest;
    }

    public function isSuccess()
    {
        $response = $this->getResponse();

        if (isset($response['status'])) {
            return strtoupper($response['status']) === 'SUCCESS';
        }

        return false;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    public function getErrorMessage()
    {
        $responseData = $this->getResponse();
        $errorMessages = $this->getMessages();

        if (isset($errorMessages['en'])) {
            return $errorMessages['en'];
        }

        if (isset($responseData['messages']['en'])) {
            return $responseData['messages']['en'];
        }
    }

}
