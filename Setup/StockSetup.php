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

use Amadeco\ElasticsuiteStock\Api\Data\StockInterface;
use Amadeco\ElasticsuiteStock\Model\Product\Attribute\Source\Stock as StockSource;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Exception\LocalizedException;

/**
 * ElasticsuiteStock Setup.
 */
class StockSetup
{
    /**
     * Create product stock status attribute.
     *
     * @param EavSetup $eavSetup EAV module setup.
     *
     * @return void
     * @throws LocalizedException
     */
    public function createStockStatusAttribute(EavSetup $eavSetup): void
    {
        $entity = ProductAttributeInterface::ENTITY_TYPE_CODE;
        $attributeCode = StockInterface::ATTRIBUTE_CODE;

        // Idempotent: skip when the attribute already exists.
        if ($eavSetup->getAttributeId($entity, $attributeCode) !== false) {
            return;
        }

        $eavSetup->addAttribute(
            $entity,
            $attributeCode,
            [
                'group'                      => 'General',
                'sort_order'                 => 210,
                'type'                       => 'int',
                'label'                      => 'Stock Status',
                'input'                      => 'hidden',
                // Source model resolves the indexed int (0/1) to a label, so
                // getSource()->getOptionText() works like any option attribute.
                'source'                     => StockSource::class,
                'global'                     => ScopedAttributeInterface::SCOPE_STORE,
                'required'                   => false,
                'default'                    => 0,
                // System-managed value computed by the indexer datasource: keep it out of the
                // product edit form. In the UI-component form, frontend_input "hidden" maps to a
                // visible "input" element, so is_visible=0 is what actually hides the field.
                // ElasticSuite layered navigation filters on is_filterable, never is_visible.
                'visible'                    => false,
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
