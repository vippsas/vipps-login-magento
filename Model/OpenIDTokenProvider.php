<?php

namespace Vipps\Login\Model;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Interface TokenProviderInterface
 * @package Vipps\Login\Model
 * @api
 */
class OpenIDTokenProvider implements TokenProviderInterface
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
        return $this->customerSession->getData('id_token');
    }
}
