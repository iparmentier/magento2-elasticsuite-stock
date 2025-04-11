<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Model\Layer;

use Amadeco\ElasticsuiteStock\Plugin\Search\Request\Product\Attribute\AggregationResolver;

/**
 * Override of FilterList to add custom renderer for Stock Filter.
 *
 * /!\ Please note a virtual type conflict happens with third parties modules (in particular, if you use Smile_ElasticsuiteRating)
 */
class FilterList extends \Smile\ElasticsuiteCatalog\Model\Layer\FilterList
{
    /**
     * Stock filter name
     */
    public const STOCK_FILTER = 'stock';

    /**
     * Get filter class by attribute
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     *
     * @return string
     */
    protected function getAttributeFilterClass(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute): string
    {
        $filterClassName = parent::getAttributeFilterClass($attribute);

        if ($attribute->getAttributeCode() === AggregationResolver::STOCK_ATTRIBUTE) {
            $filterClassName = $this->filterTypes[self::STOCK_FILTER];
        }

        if (class_exists(\Smile\ElasticsuiteRating\Model\Layer\FilterList::class)) {
            $filterName = \Smile\ElasticsuiteRating\Model\Layer\FilterList::RATING_FILTER;
            $attributeCode = \Smile\ElasticsuiteRating\Plugin\Search\Request\Product\Attribute\AggregationResolver::RATING_SUMMARY_ATTRIBUTE;

            if ($attribute->getAttributeCode() === $attributeCode) {
                $filterClassName = $this->filterTypes[$filterName];
            }
        }

        return $filterClassName;
    }
}