<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\CatalogInventory\Model\Configuration as InventoryConfig;

/**
 * ElasticsuiteStock Configuration Helper
 */
class Config extends AbstractHelper
{
    /**
     * Configuration paths
     */
    public const XML_PATH_DISPLAY_OUT_OF_STOCK = 'amadeco_elasticsuite_stock/general/display_out_of_stock_filter';

    /**
     * @var InventoryConfig
     */
    private InventoryConfig $inventoryConfig;

    /**
     * @param Context $context
     * @param InventoryConfig $inventoryConfig
     */
    public function __construct(
        Context $context,
        InventoryConfig $inventoryConfig
    ) {
        parent::__construct($context);
        $this->inventoryConfig = $inventoryConfig;
    }

    /**
     * Check if we need show out of stock filter
     *
     * @param int|null $storeId Store ID
     *
     * @return bool
     */
    public function shouldDisplayOutOfStockFilter(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_OUT_OF_STOCK,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get backorders mode
     *
     * @param int|null $storeId Store ID
     *
     * @return int
     */
    public function getBackordersMode(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            InventoryConfig::XML_PATH_BACKORDERS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if backorders are allowed
     *
     * @param int|null $storeId Store ID
     *
     * @return bool
     */
    public function isBackordersAllowed(?int $storeId = null): bool
    {
        return $this->getBackordersMode($storeId) !== \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO;
    }
}
