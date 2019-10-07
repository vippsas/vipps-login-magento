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

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Vipps\Login\Gateway\Command\UserInfoCommand;
use Vipps\Login\Model\VippsAccountManagement;

/**
 * Class EmailConfirmation
 * @package Vipps\Login\Controller\Login
 */
class EmailConfirmation extends Action
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var VippsAccountManagement
     */
    private $vippsAccountManagement;

    /**
     * @var UserInfoCommand
     */
    private $userInfoCommand;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * EmailConfirmation constructor.
     *
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param VippsAccountManagement $vippsAccountManagement
     * @param UserInfoCommand $userInfoCommand
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        VippsAccountManagement $vippsAccountManagement,
        UserInfoCommand $userInfoCommand,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->userInfoCommand = $userInfoCommand;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            try {
                $customer = $this->customerRepository->get($email);
                $userInfo = $this->userInfoCommand->execute();

                // send email to customer
                $this->vippsAccountManagement->resendConfirmation($userInfo, $customer);

                return $this->jsonFactory
                    ->create()
                    ->setData(['success' => true, 'message' => __('Please check your email for confirmation key.')]);
            } catch (InvalidTransitionException $e) {
                $errorMessage = __('This email does not require confirmation.');
            } catch (\Exception $e) {
                $errorMessage = __('Wrong email.');
            }
        }

        return $this->jsonFactory
            ->create()
            ->setData(['error' => true, 'message' => $errorMessage ?? __('An error occurred during sending message')]);
    }
}
