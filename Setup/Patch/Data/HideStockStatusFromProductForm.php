<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Setup\Patch\Data;

use Amadeco\ElasticsuiteStock\Api\Data\StockInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Hide the system-managed stock_status attribute from the product edit form.
 *
 * Existing installs created the attribute with is_visible=1 (matching the original
 * CreateStockStatusAttribute patch, which does not re-run). In the UI-component product
 * form, frontend_input "hidden" maps to a visible "input" element, so the field appeared
 * as an editable text box. Setting is_visible=0 removes it from the form without affecting
 * ElasticSuite layered navigation, which filters on is_filterable.
 */
class HideStockStatusFromProductForm implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * Constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup Module data setup.
     * @param EavSetupFactory          $eavSetupFactory EAV setup factory.
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Apply patch.
     *
     * @return void
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entity = ProductAttributeInterface::ENTITY_TYPE_CODE;

        if ($eavSetup->getAttributeId($entity, StockInterface::ATTRIBUTE_CODE) !== false) {
            $eavSetup->updateAttribute($entity, StockInterface::ATTRIBUTE_CODE, 'is_visible', 0);
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * Get aliases.
     *
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Get dependencies.
     *
     * @return array
     */
    public static function getDependencies(): array
    {
        return [
            CreateStockStatusAttribute::class
        ];
    }
}
