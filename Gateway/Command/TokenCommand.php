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
     * @param string $code
     *
     * @return array|bool|float|int|string|null
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
            $token = $this->serializer->unserialize($httpClient->getBody());
            $payload = $this->getPayload($token);

            $token['id_token_payload'] = $payload;
            return $token;
        } catch (\Exception $e) {
            throw new LocalizedException(__('An error occurred trying to get token'), $e);
        }
    }

    /**
     * @param $token
     *
     * @return array|bool|float|int|string|null
     */
    public function getPayload($token)
    {
        if (array_key_exists('id_token', $token)) {
            $payload = JWT::decode($token['id_token'], $this->getPublicKey(), ['RS256']);

            //encode and decode again to convert strClass to array
            return $this->serializer->unserialize($this->serializer->serialize($payload));
        }
        return null;
    }

    /**
     * @return bool|string
     */
    private function getPublicKey()
    {
        $httpClient = $this->httpClientFactory->create();
        $httpClient->get($this->apiEndpoints->getJwksUri());

        $jwks = $this->serializer->unserialize($httpClient->getBody());
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
