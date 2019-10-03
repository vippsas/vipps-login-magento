<?php

namespace Vipps\Login\Gateway\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Vipps\Login\Model\ConfigInterface;
use Vipps\Login\Model\UrlResolver;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Customer\Model\Session as CustomerSession;


use Firebase\JWT\JWT;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;

/**
 * Class TokenCommand
 * @package Vipps\Login\Gateway\Command
 */
class TokenCommand
{
    /**
     * @var string
     */
    const WELL_KNOWN_JWKS_JSON_URL = '.well-known/jwks.json';

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
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var object
     */
    private $id_token;

    /**
     * TokenCommand constructor.
     *
     * @param ConfigInterface $config
     * @param SerializerInterface $serializer
     * @param UrlResolver $urlResolver
     * @param ClientFactory $httpClientFactory
     * @param SessionManagerInterface $customerSession
     */
    public function __construct(
        ConfigInterface $config,
        SerializerInterface $serializer,
        UrlResolver $urlResolver,
        ClientFactory $httpClientFactory,
        SessionManagerInterface $customerSession
    ) {
        $this->config = $config;
        $this->httpClientFactory = $httpClientFactory;
        $this->urlResolver = $urlResolver;
        $this->serializer = $serializer;
        $this->customerSession = $customerSession;
    }

    /**
     * Method to get access and ID tokens from vipps login API.
     *
     * @param $code
     * @throws LocalizedException
     */
    public function execute($code)
    {
        $clientId = $this->config->getLoginClientId();
        $clientSecret = $this->config->getLoginClientSecret();

        $httpClient = $this->httpClientFactory->create();
        $httpClient->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $httpClient->setCredentials($clientId, $clientSecret);

        $httpClient->post($this->urlResolver->getUrl(self::OAUTH_2_0_TOKEN_URL), [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => 'https://test-norway-vipps.vaimo.com/vipps/login/redirect'
        ]);

        try {
            $tokenData = $this->serializer->unserialize($httpClient->getBody());
        } catch (\InvalidArgumentException $e) {
            throw new LocalizedException(__('Some error message'));
        }

        if (!$this->isValid($tokenData)) {
            throw new LocalizedException(__('Some error message'));
        }

        $this->customerSession->setData('id_token', $this->id_token);
        $this->customerSession->setData('access_token', $tokenData['access_token']);
    }

    /**
     * @param $tokenData
     *
     * @return bool
     */
    public function isValid($tokenData): bool
    {
        if (!array_key_exists('id_token', $tokenData)) {
            return false;
        }

        try {
            $this->id_token = JWT::decode($tokenData['id_token'], $this->getPublicKey(), ['RS256']);

        } catch (\Throwable $t) {
            false;
        }

        return true;
    }

    /**
     * @return string
     */
    private function getPublicKey()
    {
        $httpClient = $this->httpClientFactory->create();
        $httpClient->get($this->urlResolver->getUrl(self::WELL_KNOWN_JWKS_JSON_URL));

        try {
            $jwks = $this->serializer->unserialize($httpClient->getBody());
        } catch (\InvalidArgumentException $e) {

        }

        $jwk = $jwks['keys'][0];

        $rsa = new RSA();
        $rsa->loadKey(
            [
                'e' => new BigInteger(base64_decode($jwk['e']), 256),
                'n' => new BigInteger(base64_decode(strtr($jwk['n'], '-_', '+/'), true), 256)
            ]
        );

        return $rsa->getPublicKey();
    }
}
