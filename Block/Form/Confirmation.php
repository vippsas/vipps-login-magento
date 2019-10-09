<?php
namespace Vipps\Login\Block\Form;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Form;
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
    private $tokenPayloadProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var AccountsProvider
     */
    private $accountsProvider;

    /**
     * Confirmation constructor.
     *
     * @param Context $context
     * @param TokenProviderInterface $tokenPayloadProvider
     * @param UrlInterface $urlBuilder
     * @param AccountsProvider $accountsProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        TokenProviderInterface $tokenPayloadProvider,
        UrlInterface $urlBuilder,
        AccountsProvider $accountsProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->tokenPayloadProvider = $tokenPayloadProvider;
        $this->urlBuilder = $urlBuilder;
        $this->accountsProvider = $accountsProvider;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Welcome back, again'));
        return parent::_prepareLayout();
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getAjaxLoginUrl()
    {
        return $this->urlBuilder->getRouteUrl('vipps/login/passwordConfirm');
    }

    /**
     * @return string
     */
    public function getAjaxEmailConfirmationUrl()
    {
        return $this->urlBuilder->getRouteUrl('vipps/login/emailConfirmation');
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEmails()
    {
        $tokenPayload = $this->tokenPayloadProvider->get();

        $phone = $tokenPayload['phone_number'] ?? null;
        $email = $tokenPayload['email'] ?? null;

        $customers = $this->accountsProvider->get($phone, $email);

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
