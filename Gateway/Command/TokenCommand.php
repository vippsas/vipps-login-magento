<?php

namespace Vipps\Login\Gateway\Command;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientFactory;
use Vipps\Login\Gateway\Validator\TokenValidator;
use Vipps\Login\Model\ConfigInterface;
use Vipps\Login\Model\UrlResolver;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class TokenCommand
 * @package Vipps\Login\Gateway\Command
 */
class TokenCommand
{
    /**
     * @var string
     */
    const OAUTH_2_0_TOKEN_URL = 'oauth2/token';

    /**
     * @var ClientFactory
     */
    private $httpClientFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var TokenValidator
     */
    private $tokenValidator;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * TokenCommand constructor.
     *
     * @param RequestInterface $request
     * @param ConfigInterface $config
     * @param SerializerInterface $serializer
     * @param UrlResolver $urlResolver
     * @param ClientFactory $httpClientFactory
     * @param TokenValidator $tokenValidator
     */
    public function __construct(
        RequestInterface $request,
        ConfigInterface $config,
        SerializerInterface $serializer,
        UrlResolver $urlResolver,
        ClientFactory $httpClientFactory,
        TokenValidator $tokenValidator
    ) {
        $this->request = $request;
        $this->config = $config;
        $this->httpClientFactory = $httpClientFactory;
        $this->urlResolver = $urlResolver;
        $this->serializer = $serializer;
        $this->tokenValidator = $tokenValidator;
    }

    /**
     * Method to get access and ID tokens from vipps login API.
     *
     * @return object
     * @throws LocalizedException
     */
    public function execute()
    {
        $clientId = $this->config->getLoginClientId();
        $clientSecret = $this->config->getLoginClientSecret();

        $httpClient = $this->httpClientFactory->create();
        $httpClient->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $httpClient->setCredentials($clientId, $clientSecret);

        $httpClient->post($this->urlResolver->getUrl(self::OAUTH_2_0_TOKEN_URL), [
            'grant_type' => 'authorization_code',
            'code' => $this->request->getParam('code'),
            'redirect_uri' => 'https://test-norway-vipps.vaimo.com/vipps/login/redirect'
        ]);

        try {
            $tokenData = $this->serializer->unserialize($httpClient->getBody());
        } catch (\InvalidArgumentException $e) {
            throw new LocalizedException(__('Some error message'));
        }

        return $this->tokenValidator->validate($tokenData); //todo replace returning result with TokenProvider
    }
}
