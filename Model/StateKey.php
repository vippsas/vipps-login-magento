<?php

namespace Vipps\Login\Model;

use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class StateKey
 * @package Vipps\Login\Model
 */
class StateKey
{
    /**
     * @var string
     */
    const DATA_KEY_STATE = 'vipps_url_state';

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;


    /**
     * StateKey constructor.
     *
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        SessionManagerInterface $sessionManager
    ) {
        $this->sessionManager = $sessionManager;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $state = sha1(uniqid(rand(), true));
        $this->sessionManager->setData('vipps_url_state', $state);
        return $state;
    }

    /**
     * @param $state
     *
     * @return bool
     */
    public function isValid($state): bool
    {
        return $state == $this->sessionManager->getData('vipps_url_state');
    }
}
