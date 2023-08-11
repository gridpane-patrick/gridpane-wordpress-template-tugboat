<?php

namespace ITeam\Kashier\Api\Data;

use ITeam\Kashier\Common\KashierModel;

/**
 * Class RefundRequest
 *
 * A resource representing a Refund request.
 * @package ITeam\Kashier\Api
 *
 * @property string merchantId
 * @property string amount
 * @property string transactionId
 * @property string orderId
 *
 */
class RefundRequest extends KashierModel
{
    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param $merchantId
     *
     * @return RefundRequest
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

      /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param $orderId
     *
     * @return RefundRequest
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param $transactionId
     *
     * @return RefundRequest
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

   
     /**
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param $transactionId
     *
     * @return RefundRequest
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }
}
