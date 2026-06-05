<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Search\Request\Product\Attribute\Aggregation;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Smile\ElasticsuiteCore\Search\Request\BucketInterface;
use Smile\ElasticsuiteCatalog\Search\Request\Product\Attribute\AggregationInterface;

/**
 * Aggregation builder for product stock.
 */
class Stock implements AggregationInterface
{
    /**
     * Get aggregation data.
     *
     * @param Attribute $attribute Attribute.
     *
     * @return array
     */
    public function getAggregationData(Attribute $attribute): array
    {
        return [
            'name'        => $attribute->getAttributeCode(),
            'type'        => BucketInterface::TYPE_TERM,
            'minDocCount' => 0,
        ];
    }
}
