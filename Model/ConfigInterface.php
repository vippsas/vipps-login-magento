<?php

namespace Vipps\Login\Model;

/**
 * Class Config
 * @package Vipps\Payment\Gateway\Config
 */
interface ConfigInterface
{
    const VIPPS_LOGIN_CLIENT_ID = 'vipps/login/client_id';

    const VIPPS_LOGIN_CLIENT_SECRET = 'vipps/login/client_secret';

    const VIPPS_LOGIN_ENVIRONMENT = 'vipps/login/environment';

    public function getLoginClientId($storeId = null);

    public function getLoginClientSecret($storeId = null);

    public function getLoginEnvironment($storeId = null);
}
