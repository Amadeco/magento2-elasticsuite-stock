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

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Amadeco\ElasticsuiteStock\Setup\StockSetup;

/**
 * Patch to create stock_status attribute
 */
class CreateStockStatusAttribute implements DataPatchInterface
{
    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup Module data setup.
     * @param EavSetupFactory          $eavSetupFactory EAV setup factory.
     * @param StockSetup               $stockSetup      Stock setup.
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory,
        private readonly StockSetup $stockSetup
    ) {
    }

    /**
     * Apply patch
     *
     * @return void
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $this->stockSetup->createStockStatusAttribute($eavSetup);

        $this->moduleDataSetup->endSetup();
    }

    /**
     * Get aliases
     *
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Get dependencies
     *
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
