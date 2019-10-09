<?php
/**
 * Copyright 2019 Vipps
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

namespace Vipps\Login\Gateway\Command;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Vipps\Login\Api\Data\UserInfoInterface;
use Vipps\Login\Api\Data\UserInfoInterfaceFactory;
use Vipps\Login\Api\ApiEndpointsInterface;

/**
 * Class UserInfoCommand
 * @package Vipps\Login\Gateway\Command
 */
class UserInfoCommand
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ClientFactory
     */
    private $httpClientFactory;

    /**
     * @var UserInfoInterfaceFactory
     */
    private $userInfoFactory;

    /**
     * @var ApiEndpointsInterface
     */
    private $apiEndpoints;

    /**
     * UserInfoCommand constructor.
     *
     * @param SerializerInterface $serializer
     * @param ClientFactory $httpClientFactory
     * @param UserInfoInterfaceFactory $userInfoFactory
     * @param ApiEndpointsInterface $apiEndpoints
     */
    public function __construct(
        SerializerInterface $serializer,
        ClientFactory $httpClientFactory,
        UserInfoInterfaceFactory $userInfoFactory,
        ApiEndpointsInterface $apiEndpoints
    ) {
        $this->serializer = $serializer;
        $this->httpClientFactory = $httpClientFactory;
        $this->userInfoFactory = $userInfoFactory;
        $this->apiEndpoints = $apiEndpoints;
    }

    /**
     * @param $accessToken
     *
     * @return UserInfoInterface
     * @throws \Exception
     */
    public function execute($accessToken)
    {
        $httpClient = $this->httpClientFactory->create();
        $httpClient->addHeader('Authorization', 'Bearer 1' . $accessToken);
        $httpClient->get($this->apiEndpoints->getUserInfoEndpoint());

        $status = $httpClient->getStatus();
        $body = $this->serializer->unserialize($httpClient->getBody());

        if (200 <= $status && 300 > $status) {
            return $this->userInfoFactory->create(['data' => $body]);
        }

        if (400 <= $status && 500 > $status) {
            switch ($status) {
                case 401:
                    $message = $body['error_description']
                        ? __($body['error_description'])
                        : __('%1 Unauthorized', $status);
                    throw new AuthorizationException($message, null, $status);
                    break;
                default:
                    $message = $body['error_description']
                        ? __($body['error_description'])
                        : __('%1 Bad Request', $status);
                    throw new LocalizedException($message, null, $status);
            }
        }

        $message = $body['error_description'] ?? 'An error occurred trying to fetch user info';
        // @todo add log error
        throw new \Exception($message);
    }
}
