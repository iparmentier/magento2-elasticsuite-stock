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


        \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class)
            ->info('test');

        if ($attribute->getAttributeCode() === AggregationResolver::STOCK_ATTRIBUTE) {
            $filterClassName = $this->filterTypes[self::STOCK_FILTER];
        }

        return $filterClassName;
    }
}