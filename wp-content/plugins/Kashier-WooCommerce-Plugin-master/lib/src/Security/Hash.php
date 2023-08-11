<?php

namespace ITeam\Kashier\Security;

use ITeam\Kashier\Iframe\IframeParameter;
use ITeam\Kashier\Rest\ApiContext;

/**
 * Class Hash
 *
 * Helper class to encrypt data with api key
 *
 * @package ITeam\Kashier\Security
 */
class Hash implements ICipher
{
    private $iframeParameter;
    private $apiContext;

    public function __construct(ApiContext $apiContext, IframeParameter $iframeParameter)
    {
        $this->iframeParameter = $iframeParameter;
        $this->apiContext = $apiContext;

    }

    /**
     * Encrypts the input text using the cipher key
     *
     * @return string
     */
    public function encrypt()
    {


        $path = '/?payment='
            . $this->apiContext->getMerchantId()
            . '.'
            . $this->iframeParameter->getOrderId()
            . '.'
            . $this->iframeParameter->getAmount()
            . '.'
            . $this->iframeParameter->getCurrency();

        return hash_hmac('sha256', $path, $this->apiContext->getCredential()->getApiKey(), false);
    }
}
