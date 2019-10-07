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
 * Class Verification
 * @package Vipps\Login\Block\Form
 */
class Verification extends Template
{
    /**
     * @var TokenProviderInterface
     */
    private $openIDtokenProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var AccountsProvider
     */
    private $accountsProvider;

    /**
     * Verification constructor.
     *
     * @param Context $context
     * @param TokenProviderInterface $openIDtokenProvider
     * @param UrlInterface $urlBuilder
     * @param AccountsProvider $accountsProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        TokenProviderInterface $openIDtokenProvider,
        UrlInterface $urlBuilder,
        AccountsProvider $accountsProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->openIDtokenProvider = $openIDtokenProvider;
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
        return $this->urlBuilder->getUrl('customer/ajax/login');
    }

    /**
     * Get current user email.
     *
     * @return mixed
     */
    public function getEmails()
    {
        $idToken = $this->openIDtokenProvider->get();

        $customers = $this->accountsProvider->retrieveByPhoneOrEmail(
            $idToken->phone_number,
            $idToken->email
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
