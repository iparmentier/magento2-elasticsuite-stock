<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Search\Request\Product\Attribute\Aggregation;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\ElasticsuiteCatalog\Search\Request\Product\Attribute\AggregationInterface;
use Amadeco\ElasticsuiteStock\Helper\Config;

/**
 * Aggregation builder for product stock.
 */
class Stock implements AggregationInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * Stock constructor.
     *
     * @param Config $config Configuration helper
     * @param StoreManagerInterface $storeManager  Store manager
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * Get aggregation data
     *
     * @param Attribute $attribute Attribute
     *
     * @return array
     */
    public function getAggregationData(Attribute $attribute): array
    {
        $bucketConfig = [
            'name'        => 'stock.is_in_stock',
            'type'        => BucketInterface::TYPE_TERM,
            'minDocCount' => 0,
        ];

        // If we need to consider quantity and backorders are not allowed,
        // add an aggregation for qty
        $storeId = $this->storeManager->getStore()->getId();
        $shouldRespectBackorders = $this->config->shouldRespectBackorders($storeId);
        $shouldConsiderQty = $this->config->shouldConsiderQuantity($storeId);
        $isBackordersAllowed = $this->config->isBackordersAllowed($storeId);

        if ($shouldRespectBackorders && $shouldConsiderQty && !$isBackordersAllowed) {
            // Keep track of the original aggregation by adding an additional aggregation for qty
            $bucketConfig['childBuckets'] = [
                [
                    'name'       => 'stock.qty',
                    'type'       => BucketInterface::TYPE_HISTOGRAM,
                    'minDocCount' => 0,
                    'interval'   => 1,
                ],
            ];
        }

        return $bucketConfig;
    }
}