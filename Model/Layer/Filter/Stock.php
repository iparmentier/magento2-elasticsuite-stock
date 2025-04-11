<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Model\Layer\Filter;

use Magento\Framework\Search\Request\Builder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Filter\StripTags;
use Magento\Search\Model\SearchEngine;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\CatalogInventory\Model\Stock as MagentoModelStock;
use Smile\ElasticsuiteCatalog\Helper\ProductAttribute as AttributeHelper;
use Amadeco\ElasticsuiteStock\Helper\Config;
use Amadeco\ElasticsuiteStock\Plugin\Search\Request\Product\Attribute\AggregationResolver;

/**
 * Products Stock Filter Model
 */
class Stock extends \Smile\ElasticsuiteCatalog\Model\Layer\Filter\Attribute
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * Constructor.
     *
     * @param ItemFactory           $filterItemFactory   Filter item factory
     * @param StoreManagerInterface $storeManager        Store manager
     * @param Layer                 $layer               Layer
     * @param DataBuilder           $itemDataBuilder     Item data builder
     * @param StripTags             $tagFilter           String HTML tags filter.
     * @param Escaper               $escaper             Html Escaper.
     * @param AttributeHelper       $attributeHelper     Attribute helper
     * @param Config                $config              Stock configuration helper
     * @param array                 $data                Custom data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        StripTags $tagFilter,
        Escaper $escaper,
        AttributeHelper $attributeHelper,
        Config $config,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $tagFilter,
            $escaper,
            $attributeHelper,
            $data
        );

        $this->config = $config;
    }

    /**
     * Apply filter to collection
     *
     * @param RequestInterface $request Request
     *
     * @return $this
     */
    public function apply(RequestInterface $request)
    {
        $value = $request->getParam($this->_requestVar);

        if (null !== $value) {
            $this->currentFilterValue = $value;

            /** @var \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection $productCollection */
            $productCollection = $this->getLayer()->getProductCollection();

            // Use a consistent filter structure for both cases
            if ((int)$value === MagentoModelStock::STOCK_IN_STOCK) {
                // Filter for in-stock products
                $productCollection->addFieldToFilter(AggregationResolver::STOCK_ATTRIBUTE, ['eq' => MagentoModelStock::STOCK_IN_STOCK]);

                $filterLabel = __('In Stock');
            }

            /**
             *
            if ((int)$value === MagentoModelStock::STOCK_OUT_OF_STOCK) {
                // Filter for out-of-stock products
                $productCollection->addFieldToFilter(AggregationResolver::STOCK_ATTRIBUTE, ['eq' => MagentoModelStock::STOCK_OUT_OF_STOCK]);

                $filterLabel = __('Out of Stock');
            }
             *
             */

            $layerState = $this->getLayer()->getState();
            $filter = $this->_createItem($filterLabel, $this->currentFilterValue);
            $layerState->addFilter($filter);
        }

        return $this;
    }

    /**
     * Retrieve ES filter field
     *
     * @return string
     */
    protected function getFilterField(): string
    {
        return AggregationResolver::STOCK_ATTRIBUTE;
    }

    /**
     * Initialize filter items
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _initItems()
    {
        $data  = $this->_getItemsData();
        $items = [];
        foreach ($data as $itemData) {
            $items[] = $this->_createItem($itemData['label'], $itemData['value'], $itemData['count']);
        }
        $this->_items = $items;

        return $this;
    }

    /**
     * Get data array for building filter items
     *
     * @return array
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _getItemsData(): array
    {
        /** @var \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        // Retrieve facet data for stock_status
        $optionsFacetedData = $productCollection->getFacetedData(AggregationResolver::STOCK_ATTRIBUTE);

        $items = [];

        // Build filter items
        if (isset($optionsFacetedData[1]) && $optionsFacetedData[1]['count'] > 0) {
            $items[] = [
                'label' => __('In Stock'),
                'value' => MagentoModelStock::STOCK_IN_STOCK,
                'count' => $optionsFacetedData[1]['count'],
            ];
        }

        /**
         *
         * What is the point to display out of stock products in term of UX ?
         *
        if (isset($optionsFacetedData[0]) && $optionsFacetedData[0]['count'] > 0) {
            $items[] = [
                'label' => __('Out of Stock'),
                'value' => MagentoModelStock::STOCK_OUT_OF_STOCK,
                'count' => $optionsFacetedData[0]['count'],
            ];
        }
         */

        return $items;
    }
}