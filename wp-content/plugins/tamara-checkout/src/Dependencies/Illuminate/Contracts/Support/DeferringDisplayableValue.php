<?php

namespace Tamara\Wp\Plugin\Dependencies\Illuminate\Contracts\Support;

interface DeferringDisplayableValue
{
    /**
     * Resolve the displayable value that the class is deferring.
     *
     * @return \Tamara\Wp\Plugin\Dependencies\Illuminate\Contracts\Support\Htmlable|string
     */
    public function resolveDisplayableValue();
}
