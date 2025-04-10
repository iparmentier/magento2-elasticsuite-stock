<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Model\ResourceModel\Product\Indexer\Fulltext\Datasource;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Eav\Indexer\Indexer;

/**
 * Stock Status Data source resource model
 */
class StockStatusData extends Indexer
{
    /**
     * Constructor
     *
     * @param ResourceConnection    $resource     Resource connection
     * @param StoreManagerInterface $storeManager Store manager
     * @param MetadataPool          $metadataPool Metadata pool
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool
    ) {
        parent::__construct($resource, $storeManager, $metadataPool);
    }

    /**
     * Load stock status data for a list of product ids and a given store
     *
     * @param int   $storeId    Store id
     * @param array $productIds Product ids list
     *
     * @return array
     */
    public function loadStockStatusData(int $storeId, array $productIds): array
    {
        $websiteId = $this->getWebsiteId($storeId);

        $select = $this->getConnection()->select()
            ->from(
                ['e' => $this->getTable('catalog_product_entity')],
                ['entity_id']
            )
            ->join(
                ['stock' => $this->getTable('cataloginventory_stock_status')],
                'e.entity_id = stock.product_id',
                ['stock_status']
            )
            ->where('stock.website_id = ?', $websiteId)
            ->where('e.entity_id IN (?)', $productIds);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Get website id from store id
     *
     * @param int $storeId Store ID
     *
     * @return int
     */
    private function getWebsiteId(int $storeId): int
    {
        return (int)$this->storeManager->getStore($storeId)->getWebsiteId();
    }
}