<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vipps\Login\Controller\Login;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Session\SessionManagerInterface;
use Vipps\Login\Gateway\Command\TokenCommand;

/**
 * Class Redirect
 * @package Vipps\Login\Controller\Login
 */
class Redirect extends Action
{
    /**
     * @var
     */
    private $customerRegistry;

    /**
     * @var SessionManagerInterface|Session
     */
    private $sessionManager;

    /**
     * @var TokenCommand
     */
    private $tokenCommand;

    /**
     * Redirect constructor.
     *
     * @param Context $context
     * @param CustomerRegistry $customerRegistry
     * @param SessionManagerInterface $sessionManager
     * @param TokenCommand $tokenCommand
     */
    public function __construct(
        Context $context,
        CustomerRegistry $customerRegistry,
        SessionManagerInterface $sessionManager,
        TokenCommand $tokenCommand
    ) {
        parent::__construct($context);
        $this->customerRegistry = $customerRegistry;
        $this->sessionManager = $sessionManager;
        $this->tokenCommand = $tokenCommand;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $result = $this->tokenCommand->execute();

        try {

            /** @var Customer $customer */
            $customer = $this->customerRegistry->retrieveByEmail($result->email);
            $this->sessionManager->setCustomerAsLoggedIn($customer);

            return $this->_redirect('/');
        } catch (\Throwable $t) {
            return 'An error occurred!' . $t->getMessage();
        }
    }
}
