<?php

namespace Tamara\Wp\Plugin\Helpers;

class MoneyHelper
{
    /**
     * Format the amount of money for Tamara SDK
     *
     * @param $amount
     *
     * @return float
     */
    public static function formatNumber($amount)
    {
        return floatval(number_format($amount, 2, ".", ""));
    }

    /**
     * Format the amount of money for general with 2 decimals
     *
     * @param $amount
     *
     * @return mixed
     */
    public static function formatNumberGeneral($amount)
    {
        return number_format($amount, 2, ".", "");
    }
}
