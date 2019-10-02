<?php

namespace Vipps\Login\Controller\Login;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Action;
use Vipps\Login\Model\ConfigInterface;
use Vipps\Login\Model\UrlResolver;

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
     * @var UrlResolver
     */
    private $urlResolver;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param UrlResolver $urlResolver
     * @param ConfigInterface $config
     */
    public function __construct(
        Context $context,
        UrlResolver $urlResolver,
        ConfigInterface $config
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->urlResolver = $urlResolver;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}
