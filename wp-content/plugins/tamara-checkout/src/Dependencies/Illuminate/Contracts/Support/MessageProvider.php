<?php

namespace Tamara\Wp\Plugin\Dependencies\Illuminate\Contracts\Support;

interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return \Tamara\Wp\Plugin\Dependencies\Illuminate\Contracts\Support\MessageBag
     */
    public function getMessageBag();
}
