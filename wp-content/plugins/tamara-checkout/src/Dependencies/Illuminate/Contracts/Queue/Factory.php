<?php

namespace Tamara\Wp\Plugin\Dependencies\Illuminate\Contracts\Queue;

interface Factory
{
    /**
     * Resolve a queue connection instance.
     *
     * @param  string|null  $name
     * @return \Tamara\Wp\Plugin\Dependencies\Illuminate\Contracts\Queue\Queue
     */
    public function connection($name = null);
}
