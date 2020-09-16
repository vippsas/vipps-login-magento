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
use Vipps\Login\Api\Data\VippsCustomerAddressInterface;

/**
 * Class VippsCustomer
 * @package Vipps\Login\Model\Data
 */
class VippsCustomerAddress extends AbstractExtensibleObject implements VippsCustomerAddressInterface
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
     * @return VippsCustomerAddressInterface
     */
    public function setEntityId($value)
    {
        return $this->setData('entity_id', $value);
    }

    /**
     * @return int
     */
    public function getVippsCustomerId()
    {
        return $this->_get('vipps_customer_id');
    }

    /**
     * @param int $value
     *
     * @return VippsCustomerAddressInterface
     */
    public function setVippsCustomerId($value)
    {
        return $this->setData('vipps_customer_id', $value);
    }

    /**
     * @return int|null
     */
    public function getCustomerAddressId()
    {
        return $this->_get('customer_address_id');
    }

    /**
     * @param int $value
     *
     * @return VippsCustomerAddressInterface
     */
    public function setCustomerAddressId($value)
    {
        return $this->setData('customer_address_id', $value);
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->_get('country');
    }

    /**
     * @param string $value
     *
     * @return VippsCustomerAddressInterface
     */
    public function setCountry($value)
    {
        return $this->setData('country', $value);
    }

    /**
     * @return string
     */
    public function getStreetAddress()
    {
        return $this->_get('street_address');
    }

    /**
     * @param string $value
     *
     * @return VippsCustomerAddressInterface
     */
    public function setStreetAddress($value)
    {
        return $this->setData('street_address', $value);
    }

    /**
     * @return string
     */
    public function getAddressType()
    {
        return $this->_get('address_type');
    }

    /**
     * @param string $value
     *
     * @return VippsCustomerAddressInterface
     */
    public function setAddressType($value)
    {
        return $this->setData('address_type', $value);
    }

    /**
     * @return string
     */
    public function getFormatted()
    {
        return $this->_get('formatted');
    }

    /**
     * @param string $value
     *
     * @return VippsCustomerAddressInterface
     */
    public function setFormatted($value)
    {
        return $this->setData('formatted', $value);
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->_get('postal_code');
    }

    /**
     * @param string $value
     *
     * @return VippsCustomerAddressInterface
     */
    public function setPostalCode($value)
    {
        return $this->setData('postal_code', $value);
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->_get('region');
    }

    /**
     * @param string $value
     *
     * @return VippsCustomerAddressInterface
     */
    public function setRegion($value)
    {
        return $this->setData('region', $value);
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsDefault()
    {
        return $this->_get('is_default');
    }

    /**
     * @param bool $value
     *
     * @return VippsCustomerAddressInterface
     */
    public function setIsDefault($value)
    {
        return $this->setData('is_default', $value);
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getWasChanged()
    {
        return $this->_get('was_changed');
    }

    /**
     * @param bool $value
     *
     * @return VippsCustomerAddressInterface
     */
    public function setWasChanged($value)
    {
        return $this->setData('was_changed', $value);
    }
}
