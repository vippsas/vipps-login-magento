<?php

declare(strict_types=1);

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Vipps\ModuleLogin\Api\MarketingConsentConfigInterface;

class MarketingConsent implements MarketingConsentConfigInterface
{
    public const EMAIL = 'email';
    public const SMS = 'sms';
    public const DIGITAL = 'digital';
    public const PERSONAL = 'personal';

    private ScopeConfigInterface $scopeConfig;

    private const IS_ACTIVE = 'vipps/login/marketing_consent_active';

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::IS_ACTIVE, ScopeInterface::SCOPE_STORE);
    }

    public function isActive(string $code): bool
    {
        return $this
            ->scopeConfig
            ->isSetFlag(
                'vipps/login/marketing_consent_' . $code,
                ScopeInterface::SCOPE_STORE
            );
    }
}
