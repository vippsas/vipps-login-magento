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

namespace Vipps\Login\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Vipps\Login\Api\ApiEndpointsInterface;
use Vipps\Login\Model\Adminhtml\Source\Environment;

/**
 * Class ApiEndpoints
 * @package Vipps\Login\Model
 */
class ApiEndpoints implements ApiEndpointsInterface
{
    /**
     * @var string
     */
    const CACHE_KEY_DEV_ENDPOINT = 'vipps_login_dev_endpoint';

    /**
     * @var string
     */
    const CACHE_KEY_PROD_ENDPOINT = 'vipps_login_prod_endpoint';

    /**
     * @var string
     */
    private static $productionBaseUrl = 'https://api.vipps.no/access-management-1.0/access/';

    /**
     * @var string
     */
    private static $developBaseUrl = 'https://apitest.vipps.no/access-management-1.0/access/';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var ClientFactory
     */
    private $httpClientFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var array|null
     */
    private $preLoadedCache = null;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * APIEndpoints constructor.
     *
     * @param ConfigInterface $config
     * @param ClientFactory $httpClientFactory
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        ClientFactory $httpClientFactory,
        CacheInterface $cache,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->cache = $cache;
        $this->httpClientFactory = $httpClientFactory;
        $this->serializer = $serializer;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getAuthorizationEndpoint()
    {
        $this->retrieveConfig();
        return $this->preLoadedCache[ApiEndpointsInterface::ENDPOINT_AUTHORIZATION_KEY];
    }

    /**
     * @return string
     */
    public function getTokenEndpoint()
    {
        $this->retrieveConfig();
        return $this->preLoadedCache[ApiEndpointsInterface::ENDPOINT_TOKEN_KEY];
    }

    /**
     * @return string
     */
    public function getUserInfoEndpoint()
    {
        $this->retrieveConfig();
        return $this->preLoadedCache[ApiEndpointsInterface::ENDPOINT_USERINFO_KEY];
    }

    /**
     * @return string
     */
    public function getIssuer()
    {
        $this->retrieveConfig();
        return $this->preLoadedCache[ApiEndpointsInterface::ENDPOINT_ISSUER_KEY];
    }

    /**
     * @return string
     */
    public function getEndSessionEndpoint()
    {
        $this->retrieveConfig();
        return $this->preLoadedCache[ApiEndpointsInterface::ENDPOINT_END_SESSION_KEY];
    }

    /**
     * @return string
     */
    public function getRevocationEndpoint()
    {
        $this->retrieveConfig();
        return $this->preLoadedCache[ApiEndpointsInterface::ENDPOINT_REVOCATION_KEY];
    }

    /**
     * @return string
     */
    public function getJwksUri()
    {
        $this->retrieveConfig();
        return $this->preLoadedCache[ApiEndpointsInterface::ENDPOINT_JWKS_KEY];
    }

    /**
     * Method to retrieve api endpoints schema config from cache
     * or update it with request.
     */
    private function retrieveConfig()
    {
        if ($this->preLoadedCache) {
            return $this->preLoadedCache;
        }

        $this->loadFromCache();

        if (!$this->preLoadedCache) {
            $this->updateEndpointsSchema();
        }
    }

    /**
     * Method to update api endpoints schema according to environment settings
     * if schema was not present in cache
     * or apply default settings when error occurred on vipps side.
     */
    private function updateEndpointsSchema()
    {
        $httpClient = $this->httpClientFactory->create();

        $endPointConfigUrl = self::$developBaseUrl . '.well-known/openid-configuration';
        if ($this->isProdEnv()) {
            $endPointConfigUrl = self::$productionBaseUrl . '.well-known/openid-configuration';
        }

        try {
            $httpClient->get($endPointConfigUrl);

            if ($httpClient->getStatus() != 200) {
                return $this->applyDefaultSchema();
            }

            $apiSchema = $this->serializer->unserialize($httpClient->getBody());
            if (!$this->isSchemaValid($apiSchema)) {
                return $this->applyDefaultSchema();
            }
            $this->saveToCache($apiSchema);
            $this->preLoadedCache = $apiSchema;
        } catch (\Throwable $t) {
            $this->logger->critical($t);
            $this->applyDefaultSchema();
        }
    }

    /**
     * Apply default API schema in case, when returned error from VIPPS.
     */
    private function applyDefaultSchema()
    {
        $url = $this->isProdEnv() ? self::$productionBaseUrl : self::$developBaseUrl;
        $defaultAPISchema = [
            ApiEndpointsInterface::ENDPOINT_ISSUER_KEY => $url,
            ApiEndpointsInterface::ENDPOINT_AUTHORIZATION_KEY => $url . 'oauth2/auth',
            ApiEndpointsInterface::ENDPOINT_TOKEN_KEY => $url . 'oauth2/token',
            ApiEndpointsInterface::ENDPOINT_JWKS_KEY => $url . '.well-known/jwks.json',
            ApiEndpointsInterface::ENDPOINT_USERINFO_KEY => $url . 'userinfo',
            ApiEndpointsInterface::ENDPOINT_REVOCATION_KEY => $url . 'oauth2/revoke',
            ApiEndpointsInterface::ENDPOINT_END_SESSION_KEY => $url . 'oauth2/sessions/logout'
        ];

        $this->preLoadedCache = $defaultAPISchema;
        $this->saveToCache($defaultAPISchema);
    }

    /**
     * Check if Vipps_Login module uses production environment.
     *
     * @return bool
     */
    private function isProdEnv()
    {
        return Environment::ENVIRONMENT_PRODUCTION === $this->config->getLoginEnvironment();
    }

    /**
     * Save cache data to cache under corresponding cache key.
     *
     * @param array|string $data
     */
    private function saveToCache($data)
    {
        if ($this->isProdEnv()) {
            $this->cache->save(
                $this->serializer->serialize($data),
                self::CACHE_KEY_PROD_ENDPOINT
            );
        } else {
            $this->cache->save(
                $this->serializer->serialize($data),
                self::CACHE_KEY_DEV_ENDPOINT
            );
        }
    }

    /**
     * Load api endpoints schema from cache.
     */
    private function loadFromCache()
    {
        if ($this->isProdEnv()) {
            $schema = $this->cache->load(self::CACHE_KEY_PROD_ENDPOINT);
        } else {
            $schema = $this->cache->load(self::CACHE_KEY_DEV_ENDPOINT);
        }

        if ($schema) {
            $this->preLoadedCache = $this->serializer->unserialize($schema);
        }
    }

    /**
     *
     * Check if returned api endpoints schema is valid.
     * @param array $schema
     *
     * @return bool
     */
    private function isSchemaValid(array $schema)
    {
        $requiredKeys = [
            ApiEndpointsInterface::ENDPOINT_ISSUER_KEY,
            ApiEndpointsInterface::ENDPOINT_AUTHORIZATION_KEY,
            ApiEndpointsInterface::ENDPOINT_TOKEN_KEY,
            ApiEndpointsInterface::ENDPOINT_JWKS_KEY,
            ApiEndpointsInterface::ENDPOINT_USERINFO_KEY,
            ApiEndpointsInterface::ENDPOINT_REVOCATION_KEY,
            ApiEndpointsInterface::ENDPOINT_END_SESSION_KEY
        ];

        foreach ($requiredKeys as $key) {
            if (!isset($schema[$key])) {
                return false;
            }
        }

        return true;
    }
}
