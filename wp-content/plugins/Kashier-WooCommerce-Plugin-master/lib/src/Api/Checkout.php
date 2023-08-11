<?php

namespace ITeam\Kashier\Api;

use ITeam\Kashier\Common\KashierResourceModel;
use ITeam\Kashier\Rest\ApiContext;
use ITeam\Kashier\Security\ICipher;
use ITeam\Kashier\Transport\KashierRestCall;

/**
 * Class Checkout
 *
 * Lets you create a new checkout post request.
 *
 * @package ITeam\Kashier\Api
 *
 * @property \ITeam\Kashier\Api\Data\CheckoutRequest $checkoutRequest
 * @property string $status
 * @property array $response
 * @property array error
 */
class Checkout extends KashierResourceModel
{
    const PENDING_STATUS_MAP = [
        'PENDING',
        'PENDING_ACTION'
    ];

    /**
     * @param \ITeam\Kashier\Api\Data\CheckoutRequest $checkoutRequest
     *
     * @return Checkout
     */
    public function setCheckoutRequest(\ITeam\Kashier\Api\Data\CheckoutRequest $checkoutRequest)
    {
        $this->checkoutRequest = $checkoutRequest;

        return $this;
    }

    /**
     * Creates and processes a checkout.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param ICipher $cipher
     * @param KashierRestCall $restCall is the Rest Call Service that is used to make rest calls
     *
     * @return Checkout
     * @throws \ITeam\Kashier\Exception\KashierConfigurationException
     * @throws \ITeam\Kashier\Exception\KashierConnectionException
     */
    public function create($apiContext, ICipher $cipher, $restCall = null)
    {
        $this->getCheckoutRequest()
            ->setMerchantId($apiContext->getMerchantId())
            ->setHash($cipher->encrypt());

        $payLoad = $this->getCheckoutRequest()->toJSON();
        $json = self::executeCall(
            '/checkout',
            'POST',
            $payLoad,
            null,
            $apiContext,
            $restCall
        );

        $this->fromJson($json);

        return $this;
    }

    /**
     * @return \ITeam\Kashier\Api\Data\CheckoutRequest
     */
    public function getCheckoutRequest()
    {
        return $this->checkoutRequest;
    }

    public function isSuccess()
    {
        $response = $this->getResponse();

        if (isset($response['card']['result'])) {
            return strtoupper($response['card']['result']) === 'SUCCESS';
        }

        return false;
    }

    public function isPending()
    {
        $responseData = $this->getResponse();
        return in_array($this->getStatus(), self::PENDING_STATUS_MAP)
            || (isset($responseData['card']['result']) && in_array($responseData['card']['result'], self::PENDING_STATUS_MAP));
    }

    public function is3DsRequired()
    {
        $responseData = $this->getResponse();
        return $this->isPending() && isset($responseData['card']['3DSecure']);
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
