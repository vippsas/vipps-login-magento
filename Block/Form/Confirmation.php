<?php
/**
 * Copyright 2021 Vipps
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

namespace Vipps\Login\Block\Form;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Form;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Vipps\Login\Gateway\Command\UserInfoCommand;
use Vipps\Login\Model\Customer\AccountsProvider;
use Vipps\Login\Model\TokenProviderInterface;

/**
 * Class Confirmation
 * @package Vipps\Login\Block\Form
 */
class Confirmation extends Template
{
    /**
     * @var TokenProviderInterface
     */
    private $accessTokenProvider;

    /**
     * @var AccountsProvider
     */
    private $accountsProvider;

    /**
     * @var UserInfoCommand
     */
    private $userInfoCommand;

    /**
     * Confirmation constructor.
     *
     * @param Context $context
     * @param TokenProviderInterface $accessTokenProvider
     * @param AccountsProvider $accountsProvider
     * @param UserInfoCommand $userInfoCommand
     * @param array $data
     */
    public function __construct(
        Context $context,
        TokenProviderInterface $accessTokenProvider,
        AccountsProvider $accountsProvider,
        UserInfoCommand $userInfoCommand,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->accessTokenProvider = $accessTokenProvider;
        $this->accountsProvider = $accountsProvider;
        $this->userInfoCommand = $userInfoCommand;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Confirm your email'));
        return parent::_prepareLayout();
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getAjaxLoginUrl()
    {
        return $this->_urlBuilder->getRouteUrl('vipps/login/passwordConfirm');
    }

    /**
     * @return string
     */
    public function getAjaxEmailConfirmationUrl()
    {
        return $this->_urlBuilder->getRouteUrl('vipps/login/emailConfirmation');
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getEmails()
    {
        $accessToken = $this->accessTokenProvider->get();
        $userInfo = $this->userInfoCommand->execute($accessToken);

        $email = null;
        if ($userInfo->getIsEmailVerified()) {
            $email = $userInfo->getEmail();
        }

        $customers = $this->accountsProvider->get(
            $userInfo->getPhoneNumber(),
            $email
        );

        $emails = [];
        /** @var CustomerInterface $customer */
        foreach ($customers as $customer) {
            $emails[] = $customer->getEmail();
        }

        return $emails;
    }

    /**
     * Check if autocomplete is disabled on storefront
     *
     * @return bool
     */
    public function isAutocompleteDisabled()
    {
        return (bool)!$this->_scopeConfig->getValue(
            Form::XML_PATH_ENABLE_AUTOCOMPLETE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
