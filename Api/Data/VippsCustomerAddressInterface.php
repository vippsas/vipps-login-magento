<?php
/**
 * Copyright 2018 Vipps
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
 * IN THE SOFTWARE
 */
namespace Vipps\Login\Api\Data;

/**
 * Interface VippsCustomerAddressInterface
 * @package Vipps\Login\Api\Data
 * @api
 */
interface VippsCustomerAddressInterface
{
    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setEntityId($value);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setCustomerId($value);

    /**
     * @return int
     */
    public function getCustomerAddressId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setCustomerAddressId($value);

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCountry($value);

    /**
     * @return string
     */
    public function getStreetAddress();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setStreetAddress($value);

    /**
     * @return string
     */
    public function getAddressType();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setAddressType($value);

    /**
     * @return string
     */
    public function getFormatted();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setFormatted($value);

    /**
     * @return string
     */
    public function getPostalCode();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setPostalCode($value);

    /**
     * @return string
     */
    public function getRegion();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setRegion($value);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsDefault();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsDefault($value);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsConverted();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsConverted($value);
}