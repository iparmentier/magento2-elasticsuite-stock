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

use Amadeco\ElasticsuiteStock\Helper\Config;
use Magento\Catalog\Model\Layer\Filter\DataBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer;
use Magento\Framework\Filter\StripTags;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Search\Model\SearchEngine;
use Magento\Framework\Search\Request\Builder;
use Magento\Framework\App\RequestInterface;
use Smile\ElasticsuiteCatalog\Helper\Attribute as AttributeHelper;

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
     * @param StripTags             $tagFilter           Strip tags filter
     * @param SearchEngine          $searchEngine        Search engine
     * @param Builder               $requestBuilder      Request builder
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
        SearchEngine $searchEngine,
        Builder $requestBuilder,
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
            $searchEngine,
            $requestBuilder,
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

            $storeId = $this->getStoreId();
            $shouldRespectBackorders = $this->config->shouldRespectBackorders($storeId);
            $shouldConsiderQty = $this->config->shouldConsiderQuantity($storeId);
            $isBackordersAllowed = $this->config->isBackordersAllowed($storeId);

            // Filtre standard basé sur stock_status
            if ((int)$value === 1) {
                // Filtre pour les produits en stock
                $productCollection->addFieldToFilter('stock_status', ['eq' => 1]);

                // Si on considère les backorders et la quantité, ajouter un filtre sur qty > 0
                // quand les backorders ne sont pas autorisés
                if ($shouldRespectBackorders && $shouldConsiderQty && !$isBackordersAllowed) {
                    $productCollection->addFieldToFilter('stock.qty', ['gt' => 0]);
                }

                $filterLabel = __('In Stock');
            } else {
                // Filtre pour les produits hors stock
                if ($shouldRespectBackorders && $shouldConsiderQty && !$isBackordersAllowed) {
                    // Si les backorders ne sont pas autorisés et qu'on considère la quantité,
                    // un produit est hors stock si stock_status = 0 OU qty <= 0
                    $filter = [
                        'bool' => [
                            'should' => [
                                ['term' => ['stock_status' => 0]],
                                ['range' => ['stock.qty' => ['lte' => 0]]]
                            ]
                        ]
                    ];

                    $productCollection->addFieldToFilter('', $filter);
                } else {
                    // Sinon, simplement utiliser stock_status
                    $productCollection->addFieldToFilter('stock_status', ['eq' => 0]);
                }

                $filterLabel = __('Out of Stock');
            }

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
        return 'stock_status';
    }

    /**
     * Get current store id
     *
     * @return int
     */
    protected function getStoreId(): int
    {
        return (int) $this->_storeManager->getStore()->getId();
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

        // Récupérer les données de facette pour stock_status
        $optionsFacetedData = $productCollection->getFacetedData('stock_status');

        $items = [];

        // Construire les éléments de filtre
        if (isset($optionsFacetedData[1]) && $optionsFacetedData[1]['count'] > 0) {
            $items[] = [
                'label' => __('In Stock'),
                'value' => 1,
                'count' => $optionsFacetedData[1]['count'],
            ];
        }

        if (isset($optionsFacetedData[0]) && $optionsFacetedData[0]['count'] > 0) {
            $items[] = [
                'label' => __('Out of Stock'),
                'value' => 0,
                'count' => $optionsFacetedData[0]['count'],
            ];
        }

        return $items;
    }
}