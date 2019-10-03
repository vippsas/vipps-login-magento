<?php
namespace Vipps\Login\Model;

/**
 * Interface TokenProviderInterface
 * @package Vipps\Login\Model
 * @api
 */
interface TokenProviderInterface
{
    /**
     * Method to get valid token string.
     *
     * @return object|string
     */
    public function get();
}
