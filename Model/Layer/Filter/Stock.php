<?php
/**
 * Amadeco ElasticsuiteStock Module
 *
 * @category   Amadeco
 * @package    Amadeco_ElasticsuiteStock
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Amadeco\ElasticsuiteStock\Model\Layer\Filter;

use Magento\Framework\Escaper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\StripTags;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\CatalogInventory\Model\Stock as MagentoModelStock;
use Smile\ElasticsuiteCatalog\Api\LayeredNavAttributeInterface;
use Smile\ElasticsuiteCatalog\Helper\ProductAttribute;
use Smile\ElasticsuiteCatalog\Model\Attribute\LayeredNavAttributesProvider;
use Smile\ElasticsuiteCatalog\Model\Attribute\Source\FilterDisplayMode;
use Amadeco\ElasticsuiteStock\Model\Config;

/**
 * Products Stock Filter Model
 */
class Stock extends \Smile\ElasticsuiteCatalog\Model\Layer\Filter\Boolean
{
    /**
     * @var StripTags
     */
    private StripTags $tagFilter;

    /**
     * Constructor.
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param ItemFactory                  $filterItemFactory            Factory for item of the facets.
     * @param StoreManagerInterface        $storeManager                 Store manager.
     * @param Layer                        $layer                        Catalog product layer.
     * @param DataBuilder                  $itemDataBuilder              Item data builder.
     * @param StripTags                    $tagFilter                    String HTML tags filter.
     * @param Escaper                      $escaper                      Html Escaper.
     * @param ProductAttribute             $mappingHelper                Mapping helper.
     * @param LayeredNavAttributesProvider $layeredNavAttributesProvider Layered navigation attributes Provider.
     * @param Config                       $config                       Stock configuration model.
     * @param array                        $hideNoValueAttributes        Attributes hiding the no-value option.
     * @param array                        $data                         Custom data.
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        StripTags $tagFilter,
        Escaper $escaper,
        ProductAttribute $mappingHelper,
        LayeredNavAttributesProvider $layeredNavAttributesProvider,
        private readonly Config $config,
        array $hideNoValueAttributes = [],
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $tagFilter,
            $escaper,
            $mappingHelper,
            $layeredNavAttributesProvider,
            $hideNoValueAttributes,
            $data
        );

        $this->tagFilter = $tagFilter;
    }

    /**
     * Apply filter to collection
     *
     * @param RequestInterface $request Request
     *
     * @return $this
     */
    public function apply(RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);

        if (null !== $attributeValue) {
            if (!is_array($attributeValue)) {
                $attributeValue = [$attributeValue];
            }
            $this->currentFilterValue = $attributeValue;

            /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
            $productCollection = $this->getLayer()->getProductCollection();

            $filterField = $this->getFilterField();

            $productCollection->addFieldToFilter($filterField, $this->getFilterValue($attributeValue));
            $layerState = $this->getLayer()->getState();

            foreach ($this->currentFilterValue as $currentFilter) {
                $filter = $this->_createItem(
                    $this->getLabel((int) $currentFilter),
                    $this->currentFilterValue
                );
                $filter->setRawValue($currentFilter);
                $layerState->addFilter($filter);
            }
        }

        return $this;
    }

    /**
     * Get data array for building filter items.
     *
     * Each item keeps the raw faceted value (0/1) as its label on purpose: the inherited
     * Smile Boolean::_initItems() then resolves that numeric label to the source-model text
     * ("In Stock" / "Out of Stock") and matches the active selection on it. Because the stock
     * values equal Magento's Boolean source values (VALUE_YES = STOCK_IN_STOCK = 1,
     * VALUE_NO = STOCK_OUT_OF_STOCK = 0), the parent handles labelling, selection and manual
     * sort with no _initItems() override on this class.
     *
     * @return array
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _getItemsData(): array
    {
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        $optionsFacetedData = $productCollection->getFacetedData($this->getFilterField());

        $minCount = !empty($optionsFacetedData) ? min(array_column($optionsFacetedData, 'count')) : 0;
        $attribute = $this->getAttributeModel();
        $forceDisplay = $attribute->getFacetDisplayMode() == FilterDisplayMode::ALWAYS_DISPLAYED;

        $items = [];
        if (!empty($this->currentFilterValue) || $minCount < $productCollection->getSize() || $forceDisplay) {
            foreach ($optionsFacetedData as $value => $data) {
                $items[$value] = [
                    'label' => $this->tagFilter->filter((string) $value),
                    'value' => $value,
                    'count' => $data['count'],
                ];
            }
        }

        if (!$this->config->shouldDisplayOutOfStockFilter()) {
            unset($items[MagentoModelStock::STOCK_OUT_OF_STOCK]);
        }

        return $items;
    }

    /**
     * Get filter value.
     *
     * @param array $value Filter value.
     *
     * @return mixed
     */
    private function getFilterValue(array $value)
    {
        $field = $this->getAttributeModel()->getAttributeCode();

        $layeredNavAttribute = $this->layeredNavAttributesProvider->getLayeredNavAttribute($field);
        if ($layeredNavAttribute instanceof LayeredNavAttributeInterface) {
            return $layeredNavAttribute->getFilterQuery($value);
        }

        return $value;
    }

    /**
     * Get filter label from the given stock status value.
     *
     * Delegates to the attribute source model so the labels live in a single
     * place (Model\Product\Attribute\Source\Stock) instead of being hardcoded.
     *
     * @param int $value Stock status value.
     *
     * @return string
     */
    private function getLabel(int $value): string
    {
        $label = (string) $this->getAttributeModel()->getSource()->getOptionText($value);

        return $this->tagFilter->filter($label);
    }
}
