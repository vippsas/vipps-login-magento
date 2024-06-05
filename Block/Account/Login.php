<?php

declare(strict_types=1);

namespace Vipps\Login\Block\Account;

use Magento\Customer\Block\Account\SortLink;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template\Context;
use Vipps\Login\Model\Config\Config;


class Login extends SortLink
{
    private Config $config;

    public function __construct(
        Config  $config,
        Context $context, DefaultPathInterface $defaultPath, array $data = [])
    {
        $this->config = $config;

        parent::__construct($context, $defaultPath, $data);
    }

    public function getLabel()
    {
        return __(parent::getLabel(), __($this->config->getTitle()));
    }
}
