<?php

declare(strict_types=1);

namespace Vipps\Login\Block\Account;

use Magento\Framework\View\Element\Template as MagentoTemplate;
use Magento\Framework\View\Element\Template\Context;
use Vipps\Login\Api\Block\ClassPoolInterface;

class Template extends MagentoTemplate
{
    private ClassPoolInterface $classPool;

    public function __construct(
        ClassPoolInterface $classPool,
        Context            $context,
        array              $data = []
    ) {
        $this->classPool = $classPool;

        parent::__construct($context, $data);
    }

    public function getClassName(): string
    {
        return $this->classPool->get();
    }
}
