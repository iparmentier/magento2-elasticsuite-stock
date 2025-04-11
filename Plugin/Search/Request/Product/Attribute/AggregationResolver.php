<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Plugin\Search\Request\Product\Attribute;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Smile\ElasticsuiteCatalog\Search\Request\Product\Attribute\AggregationResolver as BaseAggregationResolver;
use Amadeco\ElasticsuiteStock\Helper\Config;

/**
 * Plugin to set aggregation builder for stock.
 */
class AggregationResolver
{
    /**
     * Stock attribute field.
     */
    public const STOCK_FIELD = 'stock.is_in_stock';

    /**
     * Stock qty field.
     */
    public const QTY_FIELD = 'stock.qty';

    /**
     * Stock attribute code.
     */
    public const STOCK_ATTRIBUTE = 'stock_status';

    /**
     * @var \Amadeco\ElasticsuiteStock\Search\Request\Product\Attribute\Aggregation\Stock
     */
    private $stockAggregation;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * AggregationResolver constructor.
     *
     * @param \Amadeco\ElasticsuiteStock\Search\Request\Product\Attribute\Aggregation\Stock $stockAggregation Stock Aggregation
     * @param Config                                                                        $config    Configuration helper
     * @param StoreManagerInterface                                                         $storeManager     Store manager
     */
    public function __construct(
        \Amadeco\ElasticsuiteStock\Search\Request\Product\Attribute\Aggregation\Stock $stockAggregation,
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->stockAggregation = $stockAggregation;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * Set aggregation for stock filter.
     *
     * @param BaseAggregationResolver $subject Aggregation Resolver
     * @param array $result Aggregation Config
     * @param Attribute $attribute Attribute
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAggregationData(
        BaseAggregationResolver $subject,
        array $result,
        Attribute $attribute
    ): array {
        if ($attribute->getAttributeCode() === self::STOCK_ATTRIBUTE) {
            $result = $this->stockAggregation->getAggregationData($attribute);
        }

        return $result;
    }
}