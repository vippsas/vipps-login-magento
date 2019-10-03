<?php
namespace Vipps\Login\Block\Form;

use Magento\Framework\View\Element\Template;
use Vipps\Login\Model\TokenProviderInterface;
use Magento\Framework\UrlInterface;

/**
 * Class Verification
 * @package Vipps\Login\Block\Form
 */
class Verification extends Template
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * @var TokenProviderInterface
     */
    private $openIDtokenProvider;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Url $customerUrl,
        TokenProviderInterface $openIDtokenProvider,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = false;
        $this->_customerUrl = $customerUrl;
        $this->openIDtokenProvider = $openIDtokenProvider;
        $this->urlBuilder = $urlBuilder;
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
     * Retrieve password forgotten url
     *
     * @return string
     */
    public function getForgotPasswordUrl()
    {
        return $this->_customerUrl->getForgotPasswordUrl();
    }

    public function getEmail()
    {
        $idToken = $this->openIDtokenProvider->get();
        return $idToken->email;
    }

    /**
     * Check if autocomplete is disabled on storefront
     *
     * @return bool
     */
    public function isAutocompleteDisabled()
    {
        return (bool)!$this->_scopeConfig->getValue(
            \Magento\Customer\Model\Form::XML_PATH_ENABLE_AUTOCOMPLETE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
