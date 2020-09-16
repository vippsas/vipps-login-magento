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

namespace Vipps\Login\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Vipps\Login\Api\Data\VippsCustomerInterface;

/**
 * Class VippsCustomer
 * @package Vipps\Login\Model\Data
 */
class VippsCustomer extends AbstractExtensibleObject implements VippsCustomerInterface
{
    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->_get('entity_id');
    }

    /**
     * @param int $value
     *
     * @return VippsCustomerInterface
     */
    public function setEntityId($value)
    {
        return $this->setData('entity_id', $value);
    }

    /**
     * @return int
     */
    public function getCustomerEntityId()
    {
        return $this->_get('customer_entity_id');
    }

    /**
     * @param int $value
     *
     * @return VippsCustomerInterface
     */
    public function setCustomerEntityId($value)
    {
        return $this->setData('customer_entity_id', $value);
    }

    /**
     * @param int $value
     *
     * @return VippsCustomerInterface
     */
    public function setWebsiteId($value)
    {
        return $this->setData('website_id', $value);
    }

    /**
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->_get('website_id');
    }

    /**
     * @return mixed|string|null
     */
    public function getEmail()
    {
        return $this->_get('email');
    }

    /**
     * @param string $value
     *
     * @return VippsCustomerInterface
     */
    public function setEmail($value)
    {
        return $this->setData('email', $value);
    }

    /**
     * @return string
     */
    public function getTelephone()
    {
        return $this->_get('telephone');
    }

    /**
     * @param string $value
     *
     * @return VippsCustomerInterface
     */
    public function setTelephone($value)
    {
        return $this->setData('telephone', $value);
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getLinked()
    {
        return $this->_get('linked');
    }

    /**
     * @param bool $value
     *
     * @return VippsCustomerInterface
     */
    public function setLinked($value)
    {
        return $this->setData('linked', $value);
    }

    /**
     * @return string
     */
    public function getConfirmationKey()
    {
        return $this->_get('confirmation_key');
    }

    /**
     * @param string $value
     *
     * @return VippsCustomerInterface
     */
    public function setConfirmationKey($value)
    {
        return $this->setData('confirmation_key', $value);
    }

    /**
     * @return int
     */
    public function getConfirmationExp()
    {
        return $this->_get('confirmation_exp');
    }

    /**
     * @param int $value
     *
     * @return VippsCustomerInterface
     */
    public function setConfirmationExp($value)
    {
        return $this->setData('confirmation_exp', $value);
    }

    /**
     * @return int
     */
    public function getSyncAddressMode()
    {
        return (int)$this->_get('sync_address_mode');
    }

    /**
     * @param int $value
     *
     * @return VippsCustomerInterface
     */
    public function setSyncAddressMode($value)
    {
        return $this->setData('sync_address_mode', (int)$value);
    }
}
