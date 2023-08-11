<?php

declare (strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Model\Checkout;

use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Money;

class PaymentOptionsAvailability
{

    public const COUNTRY = 'country',
        ORDER_VALUE = 'order_value',
        PHONE_NUMBER = 'phone_number',
        IS_VIP = 'is_vip';

    private $country;
    private $orderValue;
    private $phoneNumber;
    private $isVip;

    public function setIsVip($isVip) : PaymentOptionsAvailability
    {
        $this->isVip = $isVip;
        return $this;
    }

    public function toArray() : array
    {
        return [
            self::COUNTRY => $this->getCountry(),
            self::ORDER_VALUE => $this->getOrderValue()->toArray(),
            self::PHONE_NUMBER => $this->getPhoneNumber(),
            self::IS_VIP => $this->isVip()
        ];
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry(string $country) : PaymentOptionsAvailability
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return \Tamara\Wp\Plugin\Dependencies\Tamara\Model\Money
     */
    public function getOrderValue() : Money
    {
        return $this->orderValue;
    }

    public function setOrderValue(Money $orderValue) : PaymentOptionsAvailability
    {
        $this->orderValue = $orderValue;
        return $this;
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber) : PaymentOptionsAvailability
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function isVip()
    {
        return $this->isVip;
    }
}
