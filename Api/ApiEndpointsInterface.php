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

namespace Vipps\Login\Api;

/**
 * Interface ApiEndpointsInterface
 * @package Vipps\Login\Api
 * @api
 */
interface ApiEndpointsInterface
{
    /**
     * @var string
     */
    const ENDPOINT_ISSUER_KEY = 'issuer';

    /**
     * @var string
     */
    const ENDPOINT_AUTHORIZATION_KEY = 'authorization_endpoint';

    /**
     * @var string
     */
    const ENDPOINT_USERINFO_KEY = 'userinfo_endpoint';

    /**
     * @var string
     */
    const ENDPOINT_TOKEN_KEY = 'token_endpoint';

    /**
     * @var string
     */
    const ENDPOINT_REVOCATION_KEY = 'revocation_endpoint';

    /**
     * @var string
     */
    const ENDPOINT_END_SESSION_KEY = 'end_session_endpoint';

    /**
     * @var string
     */
    const ENDPOINT_JWKS_KEY = 'jwks_uri';

    /**
     * @return string
     */
    public function getAuthorizationEndpoint();

    /**
     * @return string
     */
    public function getUserInfoEndpoint();

    /**
     * @return string
     */
    public function getTokenEndpoint();

    /**
     * @return string
     */
    public function getRevocationEndpoint();

    /**
     * @return string
     */
    public function getEndSessionEndpoint();

    /**
     * @return string
     */
    public function getJwksUri();

    /**
     * @return string
     */
    public function getIssuer();
}
