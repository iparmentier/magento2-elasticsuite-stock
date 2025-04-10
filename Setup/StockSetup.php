<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Setup;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Amadeco\ElasticsuiteStock\Plugin\Search\Request\Product\Attribute\AggregationResolver;

/**
 * ElasticsuiteStock Setup
 */
class StockSetup
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private \Magento\Eav\Model\Config $eavConfig;

    /**
     * StockSetup constructor.
     *
     * @param \Magento\Eav\Model\Config $eavConfig EAV Config.
     */
    public function __construct(\Magento\Eav\Model\Config $eavConfig)
    {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Create product stock status attribute.
     *
     * @param \Magento\Eav\Setup\EavSetup $eavSetup EAV module Setup
     */
    public function createStockStatusAttribute($eavSetup): void
    {
        $entity = ProductAttributeInterface::ENTITY_TYPE_CODE;
        $attributeCode = AggregationResolver::STOCK_ATTRIBUTE;

        // Check if attribute already exists
        if ($eavSetup->getAttributeId($entity, $attributeCode) === false) {
            $eavSetup->addAttribute(
                $entity,
                $attributeCode,
                [
                    'group'                      => 'General',
                    'sort_order'                 => 210,
                    'type'                       => 'int',
                    'label'                      => 'Stock Status',
                    'input'                      => 'hidden',
                    'global'                     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'required'                   => false,
                    'default'                    => 0,
                    'visible'                    => true,
                    'visible_on_front'           => false,
                    'searchable'                 => true,
                    'visible_in_advanced_search' => false,
                    'filterable'                 => true,
                    'filterable_in_search'       => true,
                    'is_used_in_grid'            => false,
                    'is_visible_in_grid'         => false,
                    'is_filterable_in_grid'      => false,
                    'used_for_sort_by'           => true
                ]
            );
        }
    }
}