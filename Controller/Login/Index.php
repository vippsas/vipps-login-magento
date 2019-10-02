<?php

namespace Vipps\Login\Controller\Login;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Action;
use Vipps\Login\Model\ConfigInterface;
use Vipps\Login\Model\UrlResolver;

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
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param UrlResolver $urlResolver
     * @param ConfigInterface $config
     */
    public function __construct(
        Context $context,
        UrlResolver $urlResolver,
        ConfigInterface $config
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->urlResolver = $urlResolver;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $params = [
            'client_id='. $this->config->getLoginClientId(),
            'response_type=code',
            'scope=' . 'openid email phoneNumber',
            'state=060d51ca-1712-429c-8552-534ee0bb8ebb',
            'redirect_uri=' .  'https://test-norway-vipps.vaimo.com/vipps/login/redirect'

        ];

        $vippsRedirectUrl = $this->urlResolver->getUrl('oauth2/auth')
            . '?' . implode('&', $params);

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($vippsRedirectUrl);
        return $resultRedirect;
    }
}
