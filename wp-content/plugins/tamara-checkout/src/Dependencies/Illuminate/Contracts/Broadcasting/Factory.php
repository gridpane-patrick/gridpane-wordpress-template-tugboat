<?php

namespace Tamara\Wp\Plugin\Dependencies\Illuminate\Contracts\Broadcasting;

interface Factory
{
    /**
     * Get a broadcaster implementation by name.
     *
     * @param  string|null  $name
     * @return \Tamara\Wp\Plugin\Dependencies\Illuminate\Contracts\Broadcasting\Broadcaster
     */
    public function connection($name = null);
}
