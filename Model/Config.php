<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\CatalogInventory\Model\Configuration as InventoryConfig;
use Magento\CatalogInventory\Model\Stock;

/**
 * ElasticsuiteStock Configuration Model
 */
class Config
{
    /**
     * Configuration paths
     */
    public const string XML_PATH_DISPLAY_OUT_OF_STOCK
        = 'amadeco_elasticsuite_stock/general/display_out_of_stock_filter';

    /**
     * @param ScopeConfigInterface $scopeConfig Application configuration.
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
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
        return $this->getBackordersMode($storeId) !== Stock::BACKORDERS_NO;
    }
}
