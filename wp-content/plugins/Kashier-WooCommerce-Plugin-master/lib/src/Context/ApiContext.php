<?php

namespace ITeam\Kashier\Context;

use ITeam\Kashier\Core\KashierConfigManager;

/**
 * Class Context
 *
 * Call level parameters such as credentials ... etc.
 *
 * @package ITeam\Kashier\Context
 */
class ApiContext
{
    private $merchantId;

    /**
     * This is a placeholder for holding credential for the request
     * If the value is not set, it would get the value from @\ITeam\Kashier\Core\KashierCredentialManager
     *
     * @var \ITeam\Kashier\Auth\KashierKey
     */
    private $credential;

 
    private $secretKey;

    /**
     * Construct
     *
     * @param string $merchantId
     * @param \ITeam\Kashier\Auth\KashierKey $credential
     */
    public function __construct($merchantId, $credential = null, $secretKey = null)
    {
        $this->merchantId = $merchantId;
        $this->credential = $credential;
        $this->secretKey = $secretKey;
    }

    public function getMerchantId() {
        return $this->merchantId;
    }

    /**
     * Get Credential
     *
     * @return \ITeam\Kashier\Auth\KashierKey
     */
    public function getCredential()
    {
        return $this->credential;
    }

    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Sets Config
     *
     * @param array $config SDK configuration parameters
     */
    public function setConfig(array $config)
    {
        KashierConfigManager::getInstance()->addConfigs($config);
    }

    /**
     * Gets Configurations
     *
     * @return array
     */
    public function getConfig()
    {
        return KashierConfigManager::getInstance()->getConfigHashmap();
    }

    /**
     * Gets a specific configuration from key
     *
     * @param $searchKey
     * @return mixed
     */
    public function get($searchKey)
    {
        return KashierConfigManager::getInstance()->get($searchKey);
    }
}
