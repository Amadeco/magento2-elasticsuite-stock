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
use Amadeco\ElasticsuiteStock\Model\Product\Attribute\Source\Stock as StockSource;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Attach the source model to the existing stock_status attribute.
 *
 * Existing installs created the attribute without a source_model. Setting it
 * lets getSource()->getOptionText() resolve the indexed int (0/1) to a label,
 * so the layered-navigation filter no longer needs to hardcode the strings.
 */
class AddStockStatusSource implements DataPatchInterface
{
    /**
     * Constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup Module data setup.
     * @param EavSetupFactory          $eavSetupFactory EAV setup factory.
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
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
            $eavSetup->updateAttribute(
                $entity,
                StockInterface::ATTRIBUTE_CODE,
                'source_model',
                StockSource::class
            );
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
