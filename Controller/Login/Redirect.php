<?php
/**
 * Copyright 2018 Vipps
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 *  documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 *  the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 *  and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
 *  TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL
 *  THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 *  CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 *  IN THE SOFTWARE.
 */
namespace Vipps\Login\Controller\Login;

use Vipps\Login\Controller\Login\Redirect\ActionsPool;
use Vipps\Login\Gateway\Command\TokenCommand;
use Vipps\Login\Model\StateKey;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect as MagentoRedirect;
use Magento\Framework\App\ResponseInterface;

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
     * @var StateKey
     */
    private $stateKey;

    /**
     * @var ActionsPool
     */
    private $actionsPool;

    /**
     * Redirect constructor.
     *
     * @param Context $context
     * @param SessionManagerInterface $sessionManager
     * @param TokenCommand $tokenCommand
     * @param StateKey $stateKey
     * @param ActionsPool $actionsPool
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $sessionManager,
        TokenCommand $tokenCommand,
        StateKey $stateKey,
        ActionsPool $actionsPool
    ) {
        parent::__construct($context);
        $this->sessionManager = $sessionManager;
        $this->tokenCommand = $tokenCommand;
        $this->stateKey = $stateKey;
        $this->actionsPool = $actionsPool;
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
            if (!$this->stateKey->isValid($state)) {
                $resultRedirect->setPath('vipps/login/error');
                return $resultRedirect;
            }

            // get token
            $token = $this->tokenCommand->execute($code);

            // remember token
            $this->storeToken($token);

            $result = $this->actionsPool->execute($token);
            if ($result instanceof ResultInterface) {
                return $result;
            }
        } catch (\Throwable $t) {
            // @todo put error into log
        }

        $resultRedirect->setPath('vipps/login/error');
        return $resultRedirect;
    }

    /**
     * @param $token
     */
    private function storeToken($token)
    {
        $this->sessionManager->setData('vipps_login_id_token', $token['id_token']);
        $this->sessionManager->setData('vipps_login_id_token_payload', $token['id_token_payload']);
        $this->sessionManager->setData('vipps_login_access_token', $token['access_token']);
    }
}
