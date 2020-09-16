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

namespace Vipps\Login\Gateway\Command;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Vipps\Login\Api\Data\UserInfoInterface;
use Vipps\Login\Api\Data\UserInfoInterfaceFactory;
use Vipps\Login\Api\ApiEndpointsInterface;
use Vipps\Login\Model\TokenProviderInterface;

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
     * @var TokenProviderInterface
     */
    private $tokenPayloadProvider;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * UserInfoCommand constructor.
     *
     * @param SerializerInterface $serializer
     * @param ClientFactory $httpClientFactory
     * @param UserInfoInterfaceFactory $userInfoFactory
     * @param ApiEndpointsInterface $apiEndpoints
     * @param TokenProviderInterface $tokenPayloadProvider
     */
    public function __construct(
        SerializerInterface $serializer,
        ClientFactory $httpClientFactory,
        UserInfoInterfaceFactory $userInfoFactory,
        ApiEndpointsInterface $apiEndpoints,
        TokenProviderInterface $tokenPayloadProvider
    ) {
        $this->serializer = $serializer;
        $this->httpClientFactory = $httpClientFactory;
        $this->userInfoFactory = $userInfoFactory;
        $this->apiEndpoints = $apiEndpoints;
        $this->tokenPayloadProvider = $tokenPayloadProvider;
    }

    /**
     * @param string $accessToken
     *
     * @return UserInfoInterface
     * @throws AuthorizationException
     * @throws LocalizedException
     * @throws \Exception
     */
    public function execute($accessToken)
    {
        if (isset($this->cache[$accessToken])) {
            return $this->cache[$accessToken];
        }

        $httpClient = $this->httpClientFactory->create();
        $httpClient->addHeader('Authorization', 'Bearer ' . $accessToken);
        $httpClient->get($this->apiEndpoints->getUserInfoEndpoint());

        $status = $httpClient->getStatus();
        $body = $this->serializer->unserialize($httpClient->getBody());

        if (200 <= $status && 300 > $status) {
            $tokenPayload = $this->tokenPayloadProvider->get();
            if (empty($body['sub']) || empty($tokenPayload['sub']) || $body['sub'] !== $tokenPayload['sub']) {
                throw new LocalizedException(__('An error occurred trying to fetch user info'));
            }
            $this->cache[$accessToken] = $this->userInfoFactory->create(['data' => $body]);
            return $this->cache[$accessToken];
        }

        if (400 <= $status && 500 > $status) {
            switch ($status) {
                case 401:
                    $message = $body['error_description']
                        ? __($body['error_description'])
                        : __('%1 Unauthorized', $status);
                    throw new AuthorizationException($message, null, $status);
                default:
                    $message = $body['error_description']
                        ? __($body['error_description'])
                        : __('%1 Bad Request', $status);
                    throw new LocalizedException($message, null, $status);
            }
        }

        $message = $body['error_description'] ?? 'An error occurred trying to fetch user info';
        throw new LocalizedException(__($message));
    }
}
