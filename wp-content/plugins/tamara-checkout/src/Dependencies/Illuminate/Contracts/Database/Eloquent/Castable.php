<?php

namespace Tamara\Wp\Plugin\Dependencies\Illuminate\Contracts\Database\Eloquent;

interface Castable
{
    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @return string|\Tamara\Wp\Plugin\Dependencies\Illuminate\Contracts\Database\Eloquent\CastsAttributes|\Tamara\Wp\Plugin\Dependencies\Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes
     */
    public static function castUsing();
}
