<?php

namespace Vipps\Login\Gateway\Validator;

use Firebase\JWT\JWT;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\Serialize\SerializerInterface;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;
use Vipps\Login\Model\ConfigInterface;
use Vipps\Login\Model\UrlResolver;

/**
 * Class TokenCommand
 * @package Vipps\Login\Gateway
 */
class TokenValidator
{
    /**
     * @var string
     */
    const WELL_KNOWN_JWKS_JSON_URL = '.well-known/jwks.json';

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
     * TokenValidator constructor.
     *
     * @param ConfigInterface $config
     * @param UrlResolver $urlResolver
     * @param ClientFactory $httpClientFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ConfigInterface $config,
        UrlResolver $urlResolver,
        ClientFactory $httpClientFactory,
        SerializerInterface $serializer
    ) {
        $this->config = $config;
        $this->httpClientFactory = $httpClientFactory;
        $this->urlResolver = $urlResolver;
        $this->serializer = $serializer;
    }

    public function validate($tokenData)
    {
        if (!array_key_exists('id_token', $tokenData)) {
            throw new LocalizedException(__('Some error message'));
        }

        try {
            $result = JWT::decode($tokenData['id_token'], $this->getPublicKey(), ['RS256']);

        } catch (\Throwable $t) {
            throw new LocalizedException(__('Some error message'));
        }

        return $result;
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
