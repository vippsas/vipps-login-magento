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
use Vipps\Login\Api\Data\UserInfoInterface;

/**
 * Class UserInfo
 * @package Vipps\Login\Model
 */
class UserInfo extends AbstractExtensibleObject implements UserInfoInterface
{
    /**
     * {@inheritdoc}
     *
     * @return mixed|null
     */
    public function getBirthdate()
    {
        return $this->_get(UserInfoInterface::BIRTHDATE);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $value
     *
     * @return mixed|UserInfo
     */
    public function setBirthdate($value)
    {
        return $this->setData(UserInfoInterface::BIRTHDATE, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed|null
     */
    public function getEmail()
    {
        return $this->_get(UserInfoInterface::EMAIL);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $value
     *
     * @return mixed|UserInfo
     */
    public function setEmail($value)
    {
        return $this->setData(UserInfoInterface::EMAIL, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed|null
     */
    public function getFamilyName()
    {
        return $this->_get(UserInfoInterface::FAMILY_NAME);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $value
     *
     * @return mixed|UserInfo
     */
    public function setFamilyName($value)
    {
        return $this->setData(UserInfoInterface::FAMILY_NAME, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed|null
     */
    public function getGivenName()
    {
        return $this->_get(UserInfoInterface::GIVEN_NAME);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $value
     *
     * @return mixed|UserInfo
     */
    public function setGivenName($value)
    {
        return $this->setData(UserInfoInterface::GIVEN_NAME, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed|null
     */
    public function getName()
    {
        return $this->_get(UserInfoInterface::NAME);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $value
     *
     * @return mixed|UserInfo
     */
    public function setName($value)
    {
        return $this->setData(UserInfoInterface::NAME, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed|null
     */
    public function getPhoneNumber()
    {
        return $this->_get(UserInfoInterface::PHONE_NUMBER);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $value
     *
     * @return mixed|UserInfo
     */
    public function setPhoneNumber($value)
    {
        return $this->setData(UserInfoInterface::PHONE_NUMBER, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed|[]
     */
    public function getAddress()
    {
        return (array)$this->_get(UserInfoInterface::ADDRESS);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $value
     *
     * @return mixed|UserInfo
     */
    public function setAddress($value)
    {
        return $this->setData(UserInfoInterface::ADDRESS, $value);
    }
}
