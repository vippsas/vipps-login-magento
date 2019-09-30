<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vipps\Login\Controller\Login;

use Firebase\JWT\JWT;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;



use Vipps\Login\Model\ConfigInterface;

/**
 * Class Redirect
 * @package Vipps\Login\Controller\Login
 */
class Redirect extends \Magento\Framework\App\Action\Action
{
    /**
     * @var
     */
    private $customerRegistry;

    /**
     * @var SessionManagerInterface|Session
     */
    private $sessionManager;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Redirect constructor.
     *
     * @param Context $context
     * @param CustomerRegistry $customerRegistry
     * @param SessionManagerInterface $sessionManager
     * @param ConfigInterface $config
     */
    public function __construct(
        Context $context,
        CustomerRegistry $customerRegistry,
        SessionManagerInterface $sessionManager,
        ConfigInterface $config
    ) {
        parent::__construct($context);
        $this->customerRegistry = $customerRegistry;
        $this->sessionManager = $sessionManager;
        $this->config = $config;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $code = $this->getRequest()->getParam('code');

        $clientId = $this->config->getLoginClientId();
        $clientSecret = $this->config->getLoginClientSecret();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"https://apitest.vipps.no/access-management-1.0/access/oauth2/token");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => 'https://test-norway-vipps.vaimo.com/vipps/login/redirect'
        ]));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret)
        ]);

        $content = curl_exec($ch);
        curl_close ($ch);

        $tokenData = json_decode($content, true);
        $idToken = $tokenData['id_token'];

        try {
            $result = JWT::decode($idToken, $this->getPublicKey(), ['RS256']);

            /** @var Customer $customer */
            $customer = $this->customerRegistry->retrieveByEmail($result->email);
            $this->sessionManager->setCustomerAsLoggedIn($customer);

            return $this->_redirect('/');
        } catch (\Throwable $t) {
            return 'An error occurred!' . $t->getMessage();
        }
    }

    /**
     * @return string
     */
    private function getPublicKey()
    {
        $content = file_get_contents('https://apitest.vipps.no/access-management-1.0/access/.well-known/jwks.json');
        $jwks = json_decode($content, true);
        $jwk = $jwks['keys'][0];

        $rsa = new RSA();
        $rsa->loadKey(
            [
                'e' => new BigInteger(base64_decode($jwk['e']), 256),
                'n' => new BigInteger(base64_decode(strtr($jwk['n'], '-_', '+/'), true), 256)
            ]
        );

        return $rsa->getPublicKey();
    }
}
