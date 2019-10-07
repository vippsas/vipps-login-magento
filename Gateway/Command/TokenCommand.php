<?php

namespace Vipps\Login\Gateway\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\UrlInterface;
use Vipps\Login\Api\ApiEndpointsInterface;
use Vipps\Login\Model\ConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
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
     * @var ClientFactory
     */
    private $httpClientFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ApiEndpointsInterface
     */
    private $apiEndpoints;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var object
     */
    private $id_token;
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * TokenCommand constructor.
     *
     * @param ConfigInterface $config
     * @param SerializerInterface $serializer
     * @param ApiEndpointsInterface $apiEndpoints
     * @param ClientFactory $httpClientFactory
     * @param UrlInterface $url
     */
    public function __construct(
        ConfigInterface $config,
        SerializerInterface $serializer,
        ApiEndpointsInterface $apiEndpoints,
        ClientFactory $httpClientFactory,
        UrlInterface $url
    ) {
        $this->config = $config;
        $this->httpClientFactory = $httpClientFactory;
        $this->apiEndpoints = $apiEndpoints;
        $this->serializer = $serializer;
        $this->url = $url;
    }

    /**
     * Method to get access and ID tokens from vipps login API.
     *
     * @param $code
     *
     * @return array|bool|float|int|null|string
     * @throws LocalizedException
     */
    public function execute($code)
    {
        $clientId = $this->config->getLoginClientId();
        $clientSecret = $this->config->getLoginClientSecret();

        $httpClient = $this->httpClientFactory->create();
        $httpClient->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $httpClient->setCredentials($clientId, $clientSecret);

        $httpClient->post($this->apiEndpoints->getTokenEndpoint(), [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => trim($this->url->getUrl('vipps/login/redirect'), '/')
        ]);

        try {
            $tokenData = $this->serializer->unserialize($httpClient->getBody());
        } catch (\InvalidArgumentException $e) {
            throw new LocalizedException(__('Some error message'));
        }

        if (!$this->isValid($tokenData)) {
            throw new LocalizedException(__('Some error message'));
        }

        $tokenData['decoded_id_token'] = $this->id_token;
        return $tokenData;
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
        $httpClient->get($this->apiEndpoints->getJwksUri());

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
