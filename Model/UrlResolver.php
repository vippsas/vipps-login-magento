<?php
namespace Vipps\Login\Model;

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
        $env = $this->config->getLoginEnvironment();
        return $env === Environment::ENVIRONMENT_DEVELOP ? self::$developBaseUrl : self::$productionBaseUrl;
    }

    public function getUrl($url)
    {
        return $this->getBaseUrl() . $url;
    }
}
