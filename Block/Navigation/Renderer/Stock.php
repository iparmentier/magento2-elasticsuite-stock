<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Block\Navigation\Renderer;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use \Magento\Catalog\Helper\Data as CatalogHelper;

/**
 * Stock Filter renderer block
 */
class Stock extends \Smile\ElasticsuiteCatalog\Block\Navigation\Renderer\Attribute
{
    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * Constructor
     *
     * @param Template\Context $context Block context.
     * @param CatalogHelper $catalogHelper Catalog helper.
     * @param Json $serializer JSON Serializer
     * @param array $data Block data
     */
    public function __construct(
        Template\Context $context,
        CatalogHelper $catalogHelper,
        Json $serializer,
        array $data = []
    ) {
        parent::__construct($context, $catalogHelper, $data);
        $this->serializer = $serializer;
    }

    /**
     * Returns JS layout configuration for the stock filter
     *
     * @return string
     */
    public function getJsLayout(): string
    {
        $filterItems = $this->getFilter()->getItems();

        $jsLayoutConfig = [
            'component'    => self::JS_COMPONENT,
            'hasMoreItems' => false,
            'template'     => 'Amadeco_ElasticsuiteStock/stock-filter',
            'maxSize'      => count($filterItems),
        ];

        foreach ($filterItems as $item) {
            $jsLayoutConfig['items'][] = $item->toArray(['label', 'count', 'url', 'is_selected']);
        }

        return $this->serializer->serialize($jsLayoutConfig);
    }

    /**
     * Check if the current filter can be rendered
     *
     * @return bool
     */
    protected function canRenderFilter(): bool
    {
        return $this->getFilter() instanceof \Amadeco\ElasticsuiteStock\Model\Layer\Filter\Stock;
    }
}