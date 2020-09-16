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
use Vipps\Login\Gateway\Command\UserInfoCommand;
use Vipps\Login\Model\AccessTokenProvider;
use Vipps\Login\Model\VippsAccountManagement;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class EmailConfirmation
 * @package Vipps\Login\Controller\Login
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var AccessTokenProvider
     */
    private $accessTokenProvider;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * EmailConfirmation constructor.
     *
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param VippsAccountManagement $vippsAccountManagement
     * @param UserInfoCommand $userInfoCommand
     * @param JsonFactory $jsonFactory
     * @param AccessTokenProvider $accessTokenProvider
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        VippsAccountManagement $vippsAccountManagement,
        UserInfoCommand $userInfoCommand,
        JsonFactory $jsonFactory,
        AccessTokenProvider $accessTokenProvider,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->userInfoCommand = $userInfoCommand;
        $this->jsonFactory = $jsonFactory;
        $this->accessTokenProvider = $accessTokenProvider;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $content = $this->serializer->unserialize($this->getRequest()->getContent());
        $email = $content['email'] ?? null;
        if ($email) {
            try {
                $customer = $this->customerRepository->get($email);
                $userInfo = $this->userInfoCommand->execute($this->accessTokenProvider->get());

                // send email to customer
                $this->vippsAccountManagement->resendConfirmation($userInfo, $customer);

                return $this->jsonFactory
                    ->create()
                    ->setData(['error' => false, 'message' => __('Please check your inbox for a confirmation email. Click the link in the email to confirm your email address.')]);
            } catch (InvalidTransitionException $e) {
                $errorMessage = __('This email does not require confirmation.');
            } catch (AuthorizationException $e) {
                $errorMessage = $e->getMessage();
            } catch (LocalizedException $e) {
                $errorMessage = $e->getMessage();
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $errorMessage = __('An error occurred when trying to send email.');
            }
        }

        return $this->jsonFactory
            ->create()
            ->setData(['error' => true, 'message' => $errorMessage ?? __('An error occurred when trying to send email.')]);
    }
}
