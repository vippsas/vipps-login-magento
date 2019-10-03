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

namespace Vipps\Login\Gateway\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Vipps\Login\Api\Data\UserInfoInterface;
use Vipps\Login\Api\Data\UserInfoInterfaceFactory;
use Vipps\Login\Model\TokenProviderInterface;
use Vipps\Login\Model\UrlResolver;

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
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var TokenProviderInterface
     */
    private $accessTokenProvider;

    /**
     * UserInfoCommand constructor.
     *
     * @param SerializerInterface $serializer
     * @param ClientFactory $httpClientFactory
     * @param UserInfoInterfaceFactory $userInfoFactory
     * @param UrlResolver $urlResolver
     * @param TokenProviderInterface $accessTokenProvider
     */
    public function __construct(
        SerializerInterface $serializer,
        ClientFactory $httpClientFactory,
        UserInfoInterfaceFactory $userInfoFactory,
        UrlResolver $urlResolver,
        TokenProviderInterface $accessTokenProvider
    ) {
        $this->serializer = $serializer;
        $this->httpClientFactory = $httpClientFactory;
        $this->userInfoFactory = $userInfoFactory;
        $this->urlResolver = $urlResolver;
        $this->accessTokenProvider = $accessTokenProvider;
    }

    /**
     * @return UserInfoInterface
     * @throws \Exception
     */
    public function execute()
    {
        $accessToken = $this->accessTokenProvider->get();

        $httpClient = $this->httpClientFactory->create();
        $httpClient->addHeader('Authorization', 'Bearer ' . $accessToken);
        $httpClient->get($this->urlResolver->getUrl('userinfo'));

        if ($httpClient->getStatus() !== 200) {
            throw new \Exception("Error");
        }

        $userInfoData = $this->serializer->unserialize($httpClient->getBody());

        return $this->userInfoFactory->create(['data' => $userInfoData]);
    }
}
