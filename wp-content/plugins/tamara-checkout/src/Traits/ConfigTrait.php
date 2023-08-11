<?php


namespace Tamara\Wp\Plugin\Traits;


trait ConfigTrait
{

    /**
     * @param array $config
     */
    public function bindConfig($config)
    {
        foreach ((array)$config as $attrName => $attrValue) {
            if (property_exists($this, $attrName)) {
                $this->$attrName = $attrValue;
            }
        }
    }
}
