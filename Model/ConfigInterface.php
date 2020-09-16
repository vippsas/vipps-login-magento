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

namespace Vipps\Login\Model;

/**
 * Class Config
 * @package Vipps\Payment\Gateway\Config
 */
interface ConfigInterface
{
    const VIPPS_LOGIN_ENABLED = 'vipps/login/enabled';

    const VIPPS_LOGIN_CLIENT_ID = 'vipps/login/client_id';

    const VIPPS_LOGIN_CLIENT_SECRET = 'vipps/login/client_secret';

    const VIPPS_LOGIN_ENVIRONMENT = 'vipps/login/environment';

    const VIPPS_LOGIN_DEBUG = 'vipps/login/debug';

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getLoginClientId($storeId = null);

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getLoginClientSecret($storeId = null);

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getLoginEnvironment($storeId = null);

    /**
     * Check if debug mode is enabled.
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isDebug($storeId = null);

    /**
     * Check if module is enabled.
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = null);
}
