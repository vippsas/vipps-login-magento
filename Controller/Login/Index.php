<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vipps\Login\Controller\Login;

/**
 * Class Index
 * @package Vipps\Login\Controller\Login
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $url = 'https://apitest.vipps.no/access-management-1.0/access/oauth2/auth';
        $url .= '?client_id=e9e2d7f5-914d-4132-83c1-e3a3dec026dc';
        $url .= '&response_type=code';
        $url .= '&scope=openid address name email phoneNumber birthDate';
        $url .= '&state=4ea61978-504f-4f58-90c0-866efc79e001';
        $url .= '&redirect_uri=https://test-norway-vipps.vaimo.com/vipps/login/redirect';

        return $this->getResponse()->setRedirect($url)->sendResponse();
    }
}
