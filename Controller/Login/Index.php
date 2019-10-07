<?php

namespace Vipps\Login\Controller\Login;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\UrlInterface;
use Vipps\Login\Api\ApiEndpointsInterface;
use Vipps\Login\Model\ConfigInterface;
use Vipps\Login\Model\StateKey;

/**
 * Class Index
 * @package Vipps\Login\Controller\Login
 */
class Index extends Action
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ApiEndpointsInterface
     */
    private $apiEndpoints;

    /**
     * @var StateKey
     */
    private $stateKey;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param ApiEndpointsInterface $apiEndpoints
     * @param ConfigInterface $config
     * @param StateKey $stateKey
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        ApiEndpointsInterface $apiEndpoints,
        ConfigInterface $config,
        StateKey $stateKey,
        UrlInterface $url
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->apiEndpoints = $apiEndpoints;
        $this->stateKey = $stateKey;
        $this->url = $url;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $params = [
            'client_id='. $this->config->getLoginClientId(),
            'response_type=code',
            'scope=' . 'openid address name email phoneNumber birthDate',
            'state=' . $this->stateKey->generate(),
            'redirect_uri=' .  trim($this->url->getUrl('vipps/login/redirect'))

        ];

        $vippsRedirectUrl = $this->apiEndpoints->getAuthorizationEndpoint()
            . '?' . implode('&', $params);

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($vippsRedirectUrl);
        return $resultRedirect;
    }
}
