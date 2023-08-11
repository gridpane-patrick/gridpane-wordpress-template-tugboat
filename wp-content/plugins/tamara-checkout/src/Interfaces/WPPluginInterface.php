<?php


namespace Tamara\Wp\Plugin\Interfaces;


interface WPPluginInterface
{
    /**
     * A method to apply all actions to plugins after configured with params: apply hooks, set up something
     */
    public function initPlugin(): void;
}

