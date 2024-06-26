<?php

declare(strict_types=1);

namespace Vipps\Login\Api;

interface MarketingConsentConfigInterface
{
    public function isEnabled(): bool;
    public function isActive(string $code): bool;
}
