<?php

declare (strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Request\Merchant;

use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Merchant;

class GetDetailsInfoRequest
{
    /**
     * @var Merchant
     */
    private $merchant;

    public function __construct(Merchant $merchant)
    {
        $this->merchant = $merchant;
    }

    /**
     * @return Merchant
     */
    public function getMerchant() : Merchant
    {
        return $this->merchant;
    }
}
