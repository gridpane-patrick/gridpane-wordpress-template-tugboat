<?php

namespace Tamara\Wp\Plugin\Dependencies\Illuminate\Contracts\Mail;

interface Factory
{
    /**
     * Get a mailer instance by name.
     *
     * @param  string|null  $name
     * @return \Illuminate\Mail\Mailer
     */
    public function mailer($name = null);
}
