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

use Psr\Log\LoggerInterface;
use Vipps\Login\Controller\Login\Redirect\ActionsPool;
use Vipps\Login\Gateway\Command\TokenCommand;
use Vipps\Login\Model\RedirectUrlResolver;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect as MagentoRedirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Vipps\Login\Model\StateKey;

/**
 * Class Redirect
 * @package Vipps\Login\Controller\Login
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Redirect extends Action
{
    /**
     * @var SessionManagerInterface|Session
     */
    private $sessionManager;

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
     * Redirect constructor.
     *
     * @param Context $context
     * @param SessionManagerInterface $sessionManager
     * @param TokenCommand $tokenCommand
     * @param ActionsPool $actionsPool
     * @param RedirectUrlResolver $redirectUrlResolver
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $sessionManager,
        TokenCommand $tokenCommand,
        ActionsPool $actionsPool,
        RedirectUrlResolver $redirectUrlResolver,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->sessionManager = $sessionManager;
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
        $code = $this->_request->getParam('code');
        $state = $this->_request->getParam('state');
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            if (empty($code) && $this->_request->getParam('error')) {
                $errorDescription = $this->_request->getParam('error_description');
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
            $this->logger->critical($e->getMessage());
        } catch (\Throwable $t) {
            $this->messageManager->addErrorMessage(__('Please, try again later.'));
            $this->logger->critical($t->getMessage());
        }

        $resultRedirect->setPath('vipps/login/error');
        return $resultRedirect;
    }

    /**
     * Save access token and vipps ID token in customer session data.
     *
     * @param $token
     */
    private function storeToken($token)
    {
        $this->sessionManager->setData('vipps_login_id_token', $token['id_token']);
        $this->sessionManager->setData('vipps_login_id_token_payload', $token['id_token_payload']);
        $this->sessionManager->setData('vipps_login_access_token', $token['access_token']);
    }

    /**
     * @param string $state
     *
     * @return bool
     */
    private function isStateKeyValid($state)
    {
        return $state == $this->sessionManager->getData(StateKey::DATA_KEY_STATE);
    }
}
