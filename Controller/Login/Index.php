<?php
/**
 * Copyright 2021 Vipps
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
 * TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

declare(strict_types=1);

namespace Vipps\Login\Controller\Login;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;
use Vipps\Login\Api\ApiEndpointsInterface;
use Vipps\Login\Model\ConfigInterface;
use Vipps\Login\Model\StateKey;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param SessionManagerInterface $customerSession
     * @param ApiEndpointsInterface $apiEndpoints
     * @param ConfigInterface $config
     * @param StateKey $stateKey
     * @param UrlInterface $url
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $customerSession,
        ApiEndpointsInterface $apiEndpoints,
        ConfigInterface $config,
        StateKey $stateKey,
        UrlInterface $url,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->apiEndpoints = $apiEndpoints;
        $this->config = $config;
        $this->stateKey = $stateKey;
        $this->url = $url;
        $this->logger = $logger;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $refererUrl = $this->_redirect->getRefererUrl();

        try {
            $clientId = $this->config->getLoginClientId();
            if (empty($clientId)) {
                throw new LocalizedException(__('Invalid module configuration. Please, contact store administrator.'));
            }

            $params = [
                'client_id='. $clientId,
                'response_type=code',
                'scope=' . 'openid address name email phoneNumber api_version_2',
                'state=' . $this->getStateKey(),
                'redirect_uri=' .  trim($this->url->getUrl('vipps/login/redirect'), '/')
            ];

        $vippsRedirectUrl = $this->apiEndpoints->getAuthorizationEndpoint()
            . '?' . implode('&', $params);

        $this->customerSession->setVippsRedirectUrl($refererUrl);
            $resultRedirect->setUrl($vippsRedirectUrl);
        } catch (LocalizedException $e) {
            $resultRedirect->setUrl($refererUrl);
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e->getMessage());
        } catch (\Exception $e) {
            $resultRedirect->setUrl($refererUrl);
            $this->messageManager->addErrorMessage(__('An error occurred. Please, try again later.'));
            $this->logger->critical($e->getMessage());
        }
        return $resultRedirect;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    private function getStateKey()
    {
        $state = $this->stateKey->generate();
        $this->customerSession->setData(StateKey::DATA_KEY_STATE, $state);

        return $state;
    }
}
