<?php

namespace Vipps\Login\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class BillingTelephoneFilter implements CustomFilterInterface
{
    /**
     * Apply category_id Filter to Product Collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool Whether the filter is applied
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        $phone = preg_replace('/[^\d]/', '', $filter->getValue());

        $value = '';
        $length = strlen($phone);
        for ($i = 0; $i < $length; $i++) {
            $value .= $phone[$i] . '[^0-9]*';
        }

        $collection->addFilterToMap(
            'main_table.billing_telephone_search',
            new \Zend_Db_Expr("`billing_telephone` REGEXP '{$value}'")
        );
        $collection->addFieldToFilter(
            'billing_telephone_search',
            [
                'eq' => 1,
            ]
        );

        return true;
    }
}
