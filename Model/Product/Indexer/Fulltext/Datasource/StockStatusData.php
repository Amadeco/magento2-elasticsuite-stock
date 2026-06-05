<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Model\Product\Indexer\Fulltext\Datasource;

use Amadeco\ElasticsuiteStock\Api\Data\StockInterface;
use Amadeco\ElasticsuiteStock\Helper\Config;
use Magento\CatalogInventory\Model\Stock;
use Smile\ElasticsuiteCore\Api\Index\DatasourceInterface;

/**
 * Stock Status Datasource.
 *
 * Derives an indexed integer stock_status field from the stock data already
 * present in the fulltext index payload.
 *
 * Stock Status ID
 * -------------------------------------
 * 0 => Out of Stock
 * 1 => In Stock
 */
class StockStatusData implements DatasourceInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * Constructor.
     *
     * @param Config $config Configuration helper.
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Add stock status data to the index data.
     *
     * @param string|int $storeId   Store id.
     * @param array      $indexData Index data.
     *
     * @return array
     */
    public function addData($storeId, array $indexData): array
    {
        $isBackordersAllowed = $this->config->isBackordersAllowed((int) $storeId);
        $attributeCode = StockInterface::ATTRIBUTE_CODE;

        foreach ($indexData as &$productData) {
            // Register stock_status as an indexed attribute (once).
            if (!isset($productData['indexed_attributes'])) {
                $productData['indexed_attributes'] = [$attributeCode];
            } elseif (!in_array($attributeCode, $productData['indexed_attributes'], true)) {
                $productData['indexed_attributes'][] = $attributeCode;
            }

            // Default to out of stock, then refine from the stock payload when present.
            $productData[$attributeCode] = Stock::STOCK_OUT_OF_STOCK;

            if (isset($productData['stock']['is_in_stock'])) {
                $productData[$attributeCode] = (int) $productData['stock']['is_in_stock'];

                if ($isBackordersAllowed && isset($productData['stock']['qty'])) {
                    $qty = (float) $productData['stock']['qty'];
                    $productData[$attributeCode] = ($qty > 0) ? Stock::STOCK_IN_STOCK : Stock::STOCK_OUT_OF_STOCK;
                }
            }
        }
        unset($productData);

        return $indexData;
    }
}
