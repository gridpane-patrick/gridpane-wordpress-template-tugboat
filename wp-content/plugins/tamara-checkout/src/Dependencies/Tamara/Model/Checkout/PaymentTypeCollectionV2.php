<?php
declare(strict_types=1);

namespace Tamara\Wp\Plugin\Dependencies\Tamara\Model\Checkout;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class PaymentTypeCollectionV2 implements Countable, IteratorAggregate
{
    private $data = [];

    public function __construct(array $paymentTypes)
    {
        foreach ($paymentTypes as $paymentType) {
            $this->data[] = $paymentType;
        }
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->data);
    }
}
