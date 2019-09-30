<?php

namespace Vipps\Login\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Vipps\Login\Model\ConfigInterface;

/**
 * Class Config
 * @package Vipps\Payment\Gateway\Config
 */
class Config implements ConfigInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    public function getLoginClientId($storeId = null)
    {
        return $this->getValue(self::VIPPS_LOGIN_CLIENT_ID, $storeId);
    }
    
    public function getLoginClientSecret($storeId = null)
    {
        return $this->getValue(self::VIPPS_LOGIN_CLIENT_SECRET, $storeId);
    }

    public function getLoginEnvironment($storeId = null)
    {
        return $this->getValue(self::VIPPS_LOGIN_ENVIRONMENT, $storeId);
    }

    /**
     * @param $path
     * @param null $storeId
     *
     * @return mixed
     */
    public function getValue($path, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        return $this->scopeConfig->getValue($path,ScopeInterface::SCOPE_STORE, $storeId);
    }
}
