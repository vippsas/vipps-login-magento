<?php
/**
 * Copyright 2019 Vipps
 *
 *    Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 *    documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 *    the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 *    and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 *    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
 *    TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL
 *    THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 *    CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 *    IN THE SOFTWARE
 */

declare(strict_types=1);

namespace Vipps\Login\Controller\Login;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;
use Vipps\Login\Api\ApiEndpointsInterface;
use Vipps\Login\Model\ConfigInterface;
use Vipps\Login\Model\RedirectPathResolver;
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
     * @var SessionManagerInterface
     */
    private $customerSession;
    
    /**
     * @var RedirectPathResolver
     */
    private $redirectPathResolver;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param SessionManagerInterface $customerSession
     * @param RedirectPathResolver $redirectPathResolver
     * @param ApiEndpointsInterface $apiEndpoints
     * @param ConfigInterface $config
     * @param StateKey $stateKey
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $customerSession,
        RedirectPathResolver $redirectPathResolver,
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
        $this->customerSession = $customerSession;
        $this->redirectPathResolver = $redirectPathResolver;
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
            'redirect_uri=' .  trim($this->url->getUrl('vipps/login/redirect'), '/')

        ];

        $vippsRedirectUrl = $this->apiEndpoints->getAuthorizationEndpoint()
            . '?' . implode('&', $params);

        $refererUrl = $this->_redirect->getRefererUrl();
        $this->customerSession->setVippsRedirectUrl($refererUrl);

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($vippsRedirectUrl);
        return $resultRedirect;
    }
}
