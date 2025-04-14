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

use Magento\Framework\Escaper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\StripTags;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\CatalogInventory\Model\Stock as MagentoModelStock;
use Smile\ElasticsuiteCatalog\Api\LayeredNavAttributeInterface;
use Smile\ElasticsuiteCatalog\Helper\ProductAttribute;
use Smile\ElasticsuiteCatalog\Model\Attribute\LayeredNavAttributesProvider;
use Smile\ElasticsuiteCatalog\Model\Attribute\Source\FilterDisplayMode;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Amadeco\ElasticsuiteStock\Helper\Config;

/**
 * Products Stock Filter Model
 */
class Stock extends \Smile\ElasticsuiteCatalog\Model\Layer\Filter\Boolean
{
    /**
     * @var StripTags
     */
    private StripTags $tagFilter;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * Constructor.
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param ItemFactory                  $filterItemFactory            Factory for item of the facets.
     * @param StoreManagerInterface        $storeManager                 Store manager.
     * @param Layer                        $layer                        Catalog product layer.
     * @param DataBuilder                  $itemDataBuilder              Item data builder.
     * @param StripTags                    $tagFilter                    String HTML tags filter.
     * @param Escaper                      $escaper                      Html Escaper.
     * @param ProductAttribute             $mappingHelper                Mapping helper.
     * @param LayeredNavAttributesProvider $layeredNavAttributesProvider Layered navigation attributes Provider.
     * @param Config                       $config                       Stock configuration helper
     * @param array                        $data                         Custom data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        StripTags $tagFilter,
        Escaper $escaper,
        ProductAttribute $mappingHelper,
        LayeredNavAttributesProvider $layeredNavAttributesProvider,
        Config $config,
        array $hideNoValueAttributes = [],
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $tagFilter,
            $escaper,
            $mappingHelper,
            $layeredNavAttributesProvider,
            $hideNoValueAttributes,
            $data
        );

        $this->tagFilter = $tagFilter;
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
        $attributeValue = $request->getParam($this->_requestVar);

        if (null !== $attributeValue) {
            if (!is_array($attributeValue)) {
                $attributeValue = [$attributeValue];
            }
            $this->currentFilterValue = $attributeValue;

            /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
            $productCollection = $this->getLayer()->getProductCollection();

            $filterField = $this->getFilterField();

            $productCollection->addFieldToFilter($filterField, $this->getFilterValue($attributeValue));
            $layerState = $this->getLayer()->getState();

            foreach ($this->currentFilterValue as $currentFilter) {
                $filter = $this->_createItem(
                    $this->_getLabel((int) $currentFilter),
                    $this->currentFilterValue
                );
                $filter->setRawValue($currentFilter);
                $layerState->addFilter($filter);
            }
        }

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
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        $optionsFacetedData = $productCollection->getFacetedData($this->getFilterField());

        $minCount = !empty($optionsFacetedData) ? min(array_column($optionsFacetedData, 'count')) : 0;
        $attribute = $this->getAttributeModel();
        $forceDisplay = $attribute->getFacetDisplayMode() == FilterDisplayMode::ALWAYS_DISPLAYED;

        $items = [];
        if (!empty($this->currentFilterValue) || $minCount < $productCollection->getSize() || $forceDisplay) {
            foreach ($optionsFacetedData as $value => $data) {
                $items[$value] = [
                    'label' => $this->_getLabel($value),
                    'value' => $value,
                    'count' => $data['count'],
                ];
            }
        }

        if ($this->config->shouldDisplayOutOfStockFilter()) {
            unset($items[MagentoModelStock::STOCK_OUT_OF_STOCK]);
        }

        return $items;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * {@inheritDoc}
     */
    protected function _initItems()
    {
        parent::_initItems();

        foreach ($this->_items as $key => $item) {
            $applyValue = $item->getValue();

            if ($item->getValue() == MagentoModelStock::STOCK_IN_STOCK
                || $item->getValue() == MagentoModelStock::STOCK_OUT_OF_STOCK
            ) {
                if (is_numeric($item->getLabel())) {
                    $label = $this->_getLabel((int) $item->getLabel());
                    $item->setLabel((string) $label);
                }
            }

            if (($valuePos = array_search($applyValue, $this->currentFilterValue)) !== false) {
                $item->setIsSelected(true);
                $applyValue = $this->currentFilterValue;
                unset($applyValue[$valuePos]);
            } else {
                $applyValue = array_merge($this->currentFilterValue, [$applyValue]);
            }

            $item->setApplyFilterValue(array_values($applyValue));
        }

        if (($this->getAttributeModel()->getFacetSortOrder() == BucketInterface::SORT_ORDER_MANUAL)
            && (count($this->_items) > 1)
        ) {
            krsort($this->_items, SORT_NUMERIC);
        }

        return $this;
    }

    /**
     * Get filter value.
     *
     * @param mixed $value Filter value.
     *
     * @return mixed
     */
    private function getFilterValue(array $value)
    {
        $field = $this->getAttributeModel()->getAttributeCode();

        $layeredNavAttribute = $this->layeredNavAttributesProvider->getLayeredNavAttribute($field);
        if ($layeredNavAttribute instanceof LayeredNavAttributeInterface) {
            return $layeredNavAttribute->getFilterQuery($value);
        }

        return $value;
    }

    /**
     * Get filter label from the current value
     *
     * @return string
     */
    private function _getLabel(int $value): string
    {
        $label = $value === (int)MagentoModelStock::STOCK_IN_STOCK ?
            __('In Stock') :
            __('Out of Stock');

        return $this->tagFilter->filter($label);
    }
}
