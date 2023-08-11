<?php

declare (strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Response\Merchant;

use Tamara\Wp\Plugin\Dependencies\Tamara\Model\Merchant;
use Tamara\Wp\Plugin\Dependencies\Tamara\Response\ClientResponse;

class GetDetailsInfoResponse extends ClientResponse
{
    /**
     * @var Merchant
     */
    private $merchant;

    /**
     * @return Merchant|null
     */
    public function getDetailsInfo(): ?Merchant
    {
        return $this->isSuccess() ? $this->merchant : null;
    }

    protected function parse(array $responseData): void
    {
        $this->merchant = Merchant::fromArray($responseData);
    }
}
