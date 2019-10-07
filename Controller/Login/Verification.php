<?php

namespace Vipps\Login\Controller\Login;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Action;
use Vipps\Login\Model\ConfigInterface;
use Vipps\Login\Model\TokenProviderInterface;

/**
 * Class Verification
 * @package Vipps\Login\Controller\Login
 */
class Verification extends Action
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TokenProviderInterface
     */
    private $openIDtokenProvider;

    /**
     * Verification constructor.
     *
     * @param Context $context
     * @param ConfigInterface $config
     * @param TokenProviderInterface $openIDtokenProvider
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        TokenProviderInterface $openIDtokenProvider
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->openIDtokenProvider = $openIDtokenProvider;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        if (!$idToken = $this->openIDtokenProvider->get()) {
            return $this->_redirect('noroute');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}
