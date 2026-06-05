<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Plugin\Search\Request\Product\Attribute;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Smile\ElasticsuiteCatalog\Search\Request\Product\Attribute\AggregationResolver as BaseAggregationResolver;
use Amadeco\ElasticsuiteStock\Api\Data\StockInterface;
use Amadeco\ElasticsuiteStock\Search\Request\Product\Attribute\Aggregation\Stock as StockAggregation;

/**
 * Plugin to set aggregation builder for stock.
 */
class AggregationResolver
{
    /**
     * @var StockAggregation
     */
    private StockAggregation $stockAggregation;

    /**
     * AggregationResolver constructor.
     *
     * @param StockAggregation $stockAggregation Stock aggregation builder.
     */
    public function __construct(
        StockAggregation $stockAggregation
    ) {
        $this->stockAggregation = $stockAggregation;
    }

    /**
     * Set aggregation for stock filter.
     *
     * @param BaseAggregationResolver $subject   Aggregation resolver.
     * @param array                   $result    Aggregation config.
     * @param Attribute               $attribute Attribute.
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAggregationData(
        BaseAggregationResolver $subject,
        array $result,
        Attribute $attribute
    ): array {
        if ($attribute->getAttributeCode() === StockInterface::ATTRIBUTE_CODE) {
            $result = $this->stockAggregation->getAggregationData($attribute);
        }

        return $result;
    }
}
