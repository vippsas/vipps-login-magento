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

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect as MagentoRedirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;
use Vipps\Login\Controller\Login\Redirect\ActionsPool;
use Vipps\Login\Gateway\Command\TokenCommand;
use Vipps\Login\Model\RedirectUrlResolver;
use Vipps\Login\Model\StateKey;

/**
 * Class Redirect
 * @package Vipps\Login\Controller\Login
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Redirect implements ActionInterface
{
    /**
     * @var SessionManagerInterface|Session
     */
    private $customerSession;

    /**
     * @var TokenCommand
     */
    private $tokenCommand;

    /**
     * @var ActionsPool
     */
    private $actionsPool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RedirectUrlResolver
     */
    private $redirectUrlResolver;

    /**
     * Customer session
     *
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * Redirect constructor.
     *
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     * @param RedirectFactory $resultRedirectFactory
     * @param SessionManagerInterface $customerSession
     * @param TokenCommand $tokenCommand
     * @param ActionsPool $actionsPool
     * @param RedirectUrlResolver $redirectUrlResolver
     * @param LoggerInterface $logger
     */
    public function __construct(
        ManagerInterface $messageManager,
        RequestInterface $request,
        RedirectFactory $resultRedirectFactory,
        SessionManagerInterface $customerSession,
        TokenCommand $tokenCommand,
        ActionsPool $actionsPool,
        RedirectUrlResolver $redirectUrlResolver,
        LoggerInterface $logger
    ) {
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->customerSession = $customerSession;
        $this->tokenCommand = $tokenCommand;
        $this->actionsPool = $actionsPool;
        $this->redirectUrlResolver = $redirectUrlResolver;
        $this->logger = $logger;
    }

    /**
     * @return ResponseInterface|MagentoRedirect|ResultInterface
     */
    public function execute()
    {
        $code = $this->request->getParam('code');
        $state = $this->request->getParam('state');
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            if (empty($code) && $this->request->getParam('error')) {
                $errorDescription = $this->request->getParam('error_description');
                $this->logger->critical($errorDescription);
                $this->messageManager->addErrorMessage(__($errorDescription));
                $resultRedirect->setUrl($this->redirectUrlResolver->getRedirectUrl());

                return $resultRedirect;
            }

            if (!$this->isStateKeyValid($state)) {
                throw new LocalizedException(__('Invalid state key.'));
            }

            // get token
            $token = $this->tokenCommand->execute($code);

            // remember token
            $this->storeToken($token);

            $result = $this->actionsPool->execute($token);
            if ($result instanceof ResultInterface) {
                return $result;
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e);
            $this->logger->critical($e);
        } catch (\Throwable $t) {
            $this->messageManager->addErrorMessage(__('Please, try again later.'));
            $this->logger->critical($t);
        }

        return $resultRedirect->setPath('vipps/login/error');
    }

    /**
     * Save access token and vipps ID token in customer session data.
     *
     * @param $token
     */
    private function storeToken($token)
    {
        $this->customerSession->setData('vipps_login_id_token', $token['id_token']);
        $this->customerSession->setData('vipps_login_id_token_payload', $token['id_token_payload']);
        $this->customerSession->setData('vipps_login_access_token', $token['access_token']);
    }

    /**
     * @param string $state
     *
     * @return bool
     */
    private function isStateKeyValid($state): bool
    {
        $sessionStateKey = $this->customerSession->getData(StateKey::DATA_KEY_STATE, true);

        if (empty($sessionStateKey)) {
            return false;
        }

        return $state == $sessionStateKey;
    }
}
