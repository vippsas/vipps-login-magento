<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vipps\Login\Controller\Login;

use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlInterface;
use Vipps\Login\Model\ConfigInterface;
use Vipps\Login\Model\UrlResolver;

/**
 * Class Index
 * @package Vipps\Login\Controller\Login
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param UrlResolver $urlResolver
     * @param ConfigInterface $config
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        UrlResolver $urlResolver,
        ConfigInterface $config,
        UrlInterface $url
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->urlResolver = $urlResolver;
        $this->url = $url;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $url = $this->urlResolver->getUrl('/access-management-1.0/access/oauth2/auth');
        $url .= '?client_id=' . $this->config->getLoginClientId();
        $url .= '&response_type=code';
        $url .= '&scope=openid address name email phoneNumber birthDate';
        $url .= '&state=' . $this->config->getLoginClientSecret();
        $url .= '&redirect_uri=' . $this->url->getUrl('vipps/login/redirect');

        return $this->getResponse()->setRedirect($url)->sendResponse();
    }
}
