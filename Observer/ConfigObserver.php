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

namespace Vipps\Login\Observer;

use Magento\Customer\Model\EmailNotification;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Config\App\Config\Type\System as SystemConfig;
use Vipps\Login\Model\ConfigInterface;

/**
 * Class ConfigObserver
 * @package Vipps\Login\Observer
 */
class ConfigObserver implements ObserverInterface
{
    const VIPPS_TEMPLATE_ID = 'vipps_login_create_account_email_no_password_template';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var SystemConfig
     */
    private $systemConfig;

    /**
     * ConfigObserver constructor.
     *
     * @param SystemConfig $systemConfig
     * @param ConfigInterface $config
     * @param WriterInterface $configWriter
     */
    public function __construct(
        SystemConfig $systemConfig,
        ConfigInterface $config,
        WriterInterface $configWriter
    ) {
        $this->config = $config;
        $this->configWriter = $configWriter;
        $this->systemConfig = $systemConfig;
    }

    /**
     * {@inheritdoc}
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $storeId = $observer->getStore();
        $websiteId = $observer->getWebsite();

        if ($storeId) {
            $scope = 'stores';
            $scopeId = (int) $storeId;
        } elseif ($websiteId) {
            $scope = 'websites';
            $scopeId = (int) $websiteId;
            $storeId = 0;
        } else {
            $scope = 'default';
            $scopeId = 0;
            $storeId = 0;
        }

        if ($this->config->isEnabled($storeId)) {
            $this->configWriter->save(
                EmailNotification::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
                self::VIPPS_TEMPLATE_ID,
                $scope,
                $scopeId
            );
        } else {
            $this->configWriter->delete(
                EmailNotification::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
                $scope,
                $scopeId
            );
        }

        $this->systemConfig->clean();
    }
}
