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

use Amadeco\ElasticsuiteStock\Helper\Config;
use Magento\CatalogInventory\Model\Stock;
use Psr\Log\LoggerInterface;
use Smile\ElasticsuiteCore\Api\Index\DatasourceInterface;
use Amadeco\ElasticsuiteStock\Plugin\Search\Request\Product\Attribute\AggregationResolver;

/**
 * Stock Status Datasource
 */
class StockStatusData implements DatasourceInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param Config $config Configuration helper
     * @param LoggerInterface $logger Logger
     */
    public function __construct(
        Config $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Add stock status data to the index data
     *
     * Stock Status ID
     * -------------------------------------
     * 0 => Out of Stock
     * 1 => In Stock
     *
     * @param string|int $storeId
     * @param array $indexData
     */
    public function addData($storeId, array $indexData)
    {
        $isBackordersAllowed = $this->config->isBackordersAllowed((int)$storeId);

        $attributeCode = AggregationResolver::STOCK_ATTRIBUTE;

        /**
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
        */

        foreach ($indexData as $productId => &$productData) {
            // Add stock_status to indexed_attributes if not already present
            if (!isset($productData['indexed_attributes'])) {
                $productData['indexed_attributes'] = [$attributeCode];
            } elseif (!in_array($attributeCode, $productData['indexed_attributes'])) {
                $productData['indexed_attributes'][] = $attributeCode;
            }

            // Initialize stock_status with default value
            $productData[$attributeCode] = Stock::STOCK_OUT_OF_STOCK;

            // If stock data is already available in the index, use it
            if (isset($productData['stock']) && isset($productData['stock']['is_in_stock'])) {
                $productData[$attributeCode] = (int)$productData['stock']['is_in_stock'];

                if ($isBackordersAllowed && isset($productData['stock']['qty'])) {
                    $qty = (int)$productData['stock']['qty'];
                    $productData[$attributeCode] = ($qty > 0.01) ? Stock::STOCK_IN_STOCK : Stock::STOCK_OUT_OF_STOCK;
                }
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
        $productData['stock_status'] = Stock::STOCK_OUT_OF_STOCK;
    }
}
