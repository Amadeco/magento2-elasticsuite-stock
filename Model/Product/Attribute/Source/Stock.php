<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Model\Product\Attribute\Source;

use Magento\CatalogInventory\Model\Stock as StockStatus;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Source model for the stock_status attribute.
 *
 * Maps the indexed integer stock status to a human-readable label so the
 * attribute behaves like a standard option attribute:
 * $attribute->getSource()->getOptionText($value) returns "In Stock" / "Out of Stock".
 *
 * Stock Status ID
 * -------------------------------------
 * 0 => Out of Stock
 * 1 => In Stock
 */
class Stock extends AbstractSource
{
    /**
     * Get all options of the attribute.
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => StockStatus::STOCK_IN_STOCK, 'label' => __('In Stock')],
                ['value' => StockStatus::STOCK_OUT_OF_STOCK, 'label' => __('Out of Stock')],
            ];
        }

        return $this->_options;
    }
}
