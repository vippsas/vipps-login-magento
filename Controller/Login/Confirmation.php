<?php
/**
 * Copyright 2020 Vipps
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

use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Vipps\Login\Model\ConfigInterface;
use Vipps\Login\Model\TokenProviderInterface;

/**
 * Class Confirmation
 * @package Vipps\Login\Controller\Login
 */
class Confirmation implements ActionInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TokenProviderInterface
     */
    private $tokenPayloadProvider;

    /**
     * @var SessionManagerInterface|Session
     */
    private $customerSession;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * Confirmation constructor.
     *
     * @param ResultFactory $resultFactory
     * @param RedirectInterface $redirect
     * @param ResponseInterface $response
     * @param ConfigInterface $config
     * @param SessionManagerInterface $customerSession
     * @param ManagerInterface $messageManager
     * @param TokenProviderInterface $tokenPayloadProvider
     */
    public function __construct(
        ResultFactory $resultFactory,
        RedirectInterface $redirect,
        ResponseInterface $response,
        ConfigInterface $config,
        SessionManagerInterface $customerSession,
        ManagerInterface $messageManager,
        TokenProviderInterface $tokenPayloadProvider
    ) {
        $this->resultFactory = $resultFactory;
        $this->redirect = $redirect;
        $this->response = $response;
        $this->config = $config;
        $this->customerSession = $customerSession;
        $this->tokenPayloadProvider = $tokenPayloadProvider;
        $this->messageManager = $messageManager;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->_redirect('customer/account');
        }

        if (!$this->tokenPayloadProvider->get()) {
            $this->messageManager->addErrorMessage(__('An error occurred. Please, try again later.'));
            return $this->_redirect('customer/account/login');
        }

        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }

    /**
     * Set redirect into response
     *
     * @param string $path
     * @param array $arguments
     * @return ResponseInterface
     */
    private function _redirect($path, $arguments = [])
    {
        $this->redirect->redirect($this->response, $path, $arguments);
        return $this->response;
    }
}
