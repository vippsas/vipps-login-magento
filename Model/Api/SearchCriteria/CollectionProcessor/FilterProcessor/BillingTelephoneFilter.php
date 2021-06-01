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
        $collection->addFilterToMap(
            'main_table.billing_telephone_search',
            new \Zend_Db_Expr("REGEXP_REPLACE(`billing_telephone`, '[^0-9]+','')")
        );
        $collection->addFieldToFilter(
            'billing_telephone_search',
            [
                $filter->getConditionType() => $filter->getValue(),
            ]
        );

        return true;
    }
}
