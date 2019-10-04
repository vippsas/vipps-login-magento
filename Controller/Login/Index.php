<?php

namespace Vipps\Login\Controller\Login;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Action;
use Vipps\Login\Model\ConfigInterface;
use Vipps\Login\Model\StateKey;
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
     * @var StateKey
     */
    private $stateKey;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param UrlResolver $urlResolver
     * @param ConfigInterface $config
     * @param StateKey $stateKey
     */
    public function __construct(
        Context $context,
        UrlResolver $urlResolver,
        ConfigInterface $config,
        StateKey $stateKey
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->urlResolver = $urlResolver;
        $this->stateKey = $stateKey;
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
            'redirect_uri=' .  'https://test-norway-vipps.vaimo.com/vipps/login/redirect'

        ];

        $vippsRedirectUrl = $this->urlResolver->getUrl('oauth2/auth')
            . '?' . implode('&', $params);

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($vippsRedirectUrl);
        return $resultRedirect;
    }
}
