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

use Firebase\JWT\Key;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\UrlInterface;
use Vipps\Login\Api\ApiEndpointsInterface;
use Vipps\Login\Model\ConfigInterface;
use Psr\Log\LoggerInterface;
use Firebase\JWT\JWT;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Math\BigInteger;

/**
 * Class TokenCommand
 * @package Vipps\Login\Gateway\Command
 */
class TokenCommand
{
    private const ALGO = 'RS256';

    /**
     * @var string
     */
    private const TOKEN_LEEWAY = 3;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ConfigInterface $config
     * @param SerializerInterface $serializer
     * @param ApiEndpointsInterface $apiEndpoints
     * @param ClientFactory $httpClientFactory
     * @param UrlInterface $url
     * @param LoggerInterface $logger
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ConfigInterface $config,
        SerializerInterface $serializer,
        ApiEndpointsInterface $apiEndpoints,
        ClientFactory $httpClientFactory,
        UrlInterface $url,
        LoggerInterface $logger,
        ResourceConnection $resourceConnection
    ) {
        $this->config = $config;
        $this->httpClientFactory = $httpClientFactory;
        $this->apiEndpoints = $apiEndpoints;
        $this->serializer = $serializer;
        $this->url = $url;
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
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
        $authRecord = $this->fetchAuthorizationRecord($code);
        if (!$authRecord) {
            return null;
        }

        if (isset($authRecord['payload'])) {
            return $this->prepareResponse($authRecord['payload']);
        }

        $clientId = $this->config->getLoginClientId();
        $clientSecret = $this->config->getLoginClientSecret();

        try {
            $httpClient = $this->httpClientFactory->create();
            $httpClient->addHeader('Content-Type', 'application/x-www-form-urlencoded');
            $httpClient->setCredentials($clientId, $clientSecret);

            $httpClient->post($this->apiEndpoints->getTokenEndpoint(), [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => trim($this->url->getUrl('vipps/login/redirect'), '/')
            ]);

            $this->storeAuthorizationRecord($code, $httpClient->getBody());

            return $this->prepareResponse($httpClient->getBody());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(__('An error occurred trying to get token'), $e);
        }
    }

    private function fetchAuthorizationRecord($code): ?array
    {
        $connection = $this->resourceConnection->getConnection('write');
        $connection->delete(
            $connection->getTableName('vipps_login_authorization'),
            \sprintf('created_at < %s', $connection->quote((new \DateTime())->modify('-5 min')->format('Y-m-d H:i-s')))
        );

        $select = $connection->select()
            ->from($connection->getTableName('vipps_login_authorization'))
            ->where('code = ?', $code);

        $row = $connection->fetchRow($select);
        if (!$row) {
            try {
                $connection->insert(
                    $connection->getTableName('vipps_login_authorization'),
                    [
                        'code' => $code,
                        'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                    ]
                );

                $row = $connection->fetchRow($select);
            } catch (\Throwable $t) {
                return null;
            }
        }

        return $row;
    }

    private function storeAuthorizationRecord($code, $payload)
    {
        $connection = $this->resourceConnection->getConnection('write');

        return $connection->update(
            $connection->getTableName('vipps_login_authorization'),
            [
                'payload' => $payload
            ],
            'code = ' . $connection->quote($code)
        );
    }

    private function prepareResponse($body): array
    {
        $token = $this->serializer->unserialize($body);
        $payload = $this->getPayload($token);

        $token['id_token_payload'] = $payload;

        return $token;
    }

    /**
     * @param $token
     *
     * @return array|bool|float|int|string|null
     */
    public function getPayload($token)
    {
        if (array_key_exists('id_token', $token)) {
            JWT::$leeway = self::TOKEN_LEEWAY;

            $headers = (object)['alg' => self::ALGO];
            $payload = JWT::decode($token['id_token'], $this->getPublicKeys(), $headers);

            //encode and decode again to convert strClass to array
            return $this->serializer->unserialize($this->serializer->serialize($payload));
        }
        return null;
    }

    /**
     * @return array
     */
    private function getPublicKeys()
    {
        $httpClient = $this->httpClientFactory->create();
        $httpClient->get($this->apiEndpoints->getJwksUri());

        $jwks = $this->serializer->unserialize($httpClient->getBody());

        $keys = $jwks['keys'] ?? null;

        $publicKeys = [];
        foreach ($keys as $key) {
            if (array_key_exists('e', $key) &&
                array_key_exists('n', $key) &&
                array_key_exists('kid', $key)
            ) {
                $pkey = PublicKeyLoader::load(
                    [
                        'e' => new BigInteger(base64_decode($key['e']), 256),
                        'n' => new BigInteger(base64_decode(strtr($key['n'], '-_', '+/'), true), 256)
                    ]
                );
                $publicKeys[$key['kid']] = new Key($pkey->toString('PKCS8'), self::ALGO);
            }
        }

        return $publicKeys;
    }
}
