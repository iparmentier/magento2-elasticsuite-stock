<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Model\Product\Indexer\Fulltext\Datasource;

use Smile\ElasticsuiteCore\Api\Index\DatasourceInterface;
use Amadeco\ElasticsuiteStock\Model\ResourceModel\Product\Indexer\Fulltext\Datasource\StockStatusData as ResourceModel;

/**
 * Stock Status Datasource
 */
class StockStatusData implements DatasourceInterface
{
    /**
     * @var ResourceModel
     */
    private ResourceModel $resourceModel;

    /**
     * Constructor
     *
     * @param ResourceModel $resourceModel Resource model
     */
    public function __construct(ResourceModel $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    /**
     * Add stock status data to the index data
     *
     * {@inheritdoc}
     */
    public function addData($storeId, array $indexData)
    {
        $stockStatusData = $this->resourceModel->loadStockStatusData((int)$storeId, array_keys($indexData));

        array_walk($indexData, [$this, 'initStockStatusData']);

        foreach ($stockStatusData as $stockDataRow) {
            $productId = (int) $stockDataRow['entity_id'];
            $indexData[$productId]['stock_status'] = (int) $stockDataRow['stock_status'];

            if (!isset($indexData[$productId]['indexed_attributes'])) {
                $indexData[$productId]['indexed_attributes'] = ['stock_status'];
            } elseif (!in_array('stock_status', $indexData[$productId]['indexed_attributes'])) {
                // Add stock_status only one time
                $indexData[$productId]['indexed_attributes'][] = 'stock_status';
            }
        }

        return $indexData;
    }

    /**
     * Initialize stock status field
     *
     * @param array $productData Product index data
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) Used via callback
     */
    private function initStockStatusData(array &$productData): void
    {
        $productData['stock_status'] = 0;
    }
}