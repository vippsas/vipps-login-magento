<?php
/**
 *  Copyright © Vaimo Norge AS. All rights reserved.
 *  See LICENSE.txt for license details.
 */

namespace Vipps\Login\Model;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class AccessTokenProvider
 * @package Vipps\Login\Model
 * @apu
 */
class AccessTokenProvider implements TokenProviderInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * TokenProvider constructor.
     *
     * @param SessionManagerInterface $customerSession
     */
    public function __construct(SessionManagerInterface $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * Method to get valid token string.
     *
     * @return object|string
     */
    public function get()
    {
        return $this->customerSession->getData('vipps_login_access_token');
    }
}
