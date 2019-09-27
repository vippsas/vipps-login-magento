<?php
/**
 *  Copyright © Vaimo Norge AS. All rights reserved.
 *  See LICENSE.txt for license details.
 */
namespace Vipps\Login\Model;

use Magento\Payment\Gateway\ConfigInterface;
use Vipps\Login\Model\Adminhtml\Source\Environment;

/**
 * Class UrlResolver
 * @package Vipps\Payment\Model
 */
class UrlResolver
{
    /**
     * @var string
     */
    private static $productionBaseUrl = 'https://api.vipps.no';

    /**
     * @var string
     */
    private static $developBaseUrl = 'https://apitest.vipps.no';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * VippsUrlProvider constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getBaseUrl()
    {
        $env = $this->config->getValue('environment');
        return $env === Environment::ENVIRONMENT_DEVELOP ? self::$developBaseUrl : self::$productionBaseUrl;
    }

    public function getUrl($url)
    {
        return $this->getBaseUrl() . $url;
    }
}
