<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Model\Layer\Filter\TypeProvider;

use Amadeco\ElasticsuiteStock\Api\Data\StockInterface;
use Amadeco\ElasticsuiteStock\Model\Layer\Filter\Stock as StockFilter;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Smile\ElasticsuiteCatalog\Api\Layer\Filter\TypeProviderInterface;

/**
 * Filter type provider for the Stock layer filter.
 *
 * Registers the custom Stock renderer for the stock status attribute through the
 * core Smile\ElasticsuiteCatalog\Model\Layer\FilterList filterTypeProviders pool,
 * so no FilterList override (preference / virtualType) is required.
 */
class Stock implements TypeProviderInterface
{
    /**
     * Return the stock filter class for the stock_status attribute, default otherwise.
     *
     * @param Attribute $attribute               The attribute model.
     * @param string    $originalFilterClassName The original/default filter class name.
     *
     * @return string
     */
    public function getFilterClassName(Attribute $attribute, string $originalFilterClassName): string
    {
        if ($attribute->getAttributeCode() === StockInterface::ATTRIBUTE_CODE) {
            return StockFilter::class;
        }

        return $originalFilterClassName;
    }
}
