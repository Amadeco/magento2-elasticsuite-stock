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

use Amadeco\ElasticsuiteStock\Model\Layer\Filter\Stock as StockFilter;
use Amadeco\ElasticsuiteStock\Plugin\Search\Request\Product\Attribute\AggregationResolver;
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
     * {@inheritDoc}
     */
    public function getFilterClassName(Attribute $attribute, string $originalFilterClassName): string
    {
        if ($attribute->getAttributeCode() === AggregationResolver::STOCK_ATTRIBUTE) {
            return StockFilter::class;
        }

        return $originalFilterClassName;
    }
}
