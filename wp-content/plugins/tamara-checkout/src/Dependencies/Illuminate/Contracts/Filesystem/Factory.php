<?php

namespace Tamara\Wp\Plugin\Dependencies\Illuminate\Contracts\Filesystem;

interface Factory
{
    /**
     * Get a filesystem implementation.
     *
     * @param  string|null  $name
     * @return \Tamara\Wp\Plugin\Dependencies\Illuminate\Contracts\Filesystem\Filesystem
     */
    public function disk($name = null);
}
