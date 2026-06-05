<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Api\Data;

/**
 * Stock filter contract.
 *
 * Neutral holder for the stock attribute code shared by the indexer datasource,
 * the setup patch, the aggregation plugin and the layered-navigation filter type
 * provider, so none of them has to depend on a sibling (plugin) class for it.
 */
interface StockInterface
{
    /**
     * Indexed stock status attribute code.
     */
    public const ATTRIBUTE_CODE = 'stock_status';
}
