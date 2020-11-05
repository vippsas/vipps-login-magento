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
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;
use Vipps\Login\Gateway\Command\UserInfoCommand;
use Vipps\Login\Model\AccessTokenProvider;
use Vipps\Login\Model\VippsAccountManagement;

/**
 * Class EmailConfirmation
 * @package Vipps\Login\Controller\Login
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailConfirmation implements ActionInterface
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
     * @var RequestInterface
     */
    private $request;

    /**
     * EmailConfirmation constructor.
     *
     * @param RequestInterface $request
     * @param CustomerRepositoryInterface $customerRepository
     * @param VippsAccountManagement $vippsAccountManagement
     * @param UserInfoCommand $userInfoCommand
     * @param JsonFactory $jsonFactory
     * @param AccessTokenProvider $accessTokenProvider
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        CustomerRepositoryInterface $customerRepository,
        VippsAccountManagement $vippsAccountManagement,
        UserInfoCommand $userInfoCommand,
        JsonFactory $jsonFactory,
        AccessTokenProvider $accessTokenProvider,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->customerRepository = $customerRepository;
        $this->vippsAccountManagement = $vippsAccountManagement;
        $this->userInfoCommand = $userInfoCommand;
        $this->jsonFactory = $jsonFactory;
        $this->accessTokenProvider = $accessTokenProvider;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @return Json
     */
    public function execute()
    {
        $content = $this->serializer->unserialize($this->request->getContent());
        $email = $content['email'] ?? null;

        /** @var Json $jsonResponse */
        $jsonResponse = $this->jsonFactory->create();
        if (!$email) {
            return $jsonResponse->setData([
                    'error' => true,
                    'message' => $errorMessage ?? __('Email is missing.')
                ]);
        }

        try {
            $customer = $this->customerRepository->get($email);
            $userInfo = $this->userInfoCommand->execute($this->accessTokenProvider->get());

            // send email to customer
            $this->vippsAccountManagement->resendConfirmation($userInfo, $customer);

            return $jsonResponse->setData([
                       'error' => false,
                       'message' =>
                           __('Please check your inbox for a confirmation email.'.
                            ' Click the link in the email to confirm your email address.')
                   ]);
        } catch (InvalidTransitionException $e) {
            $this->logger->critical($e);
            $errorMessage = __('This email does not require confirmation.');
        } catch (AuthorizationException $e) {
            $this->logger->critical($e);
            $errorMessage = $e->getMessage();
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            $errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $errorMessage = __('An error occurred when trying to send email.');
        }

        return $this->jsonFactory->create()
            ->setData([
                'error' => true,
                'message' => $errorMessage ?? __('An error occurred when trying to send email.')
            ]);
    }
}
