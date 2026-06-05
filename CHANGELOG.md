# Changelog

All notable changes to the `Amadeco_ElasticsuiteStock` module are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.2.0] - 2026-06-05

### Changed (Breaking)
- Minimum PHP raised to **8.3** (`composer.json` now requires `~8.3.0 || ~8.4.0`). The module
  uses typed class constants (`public const string ATTRIBUTE_CODE`), which are an 8.3-only
  syntax. Installs on PHP 8.1 / 8.2 must stay on `2.1.x`.

### Fixed
- Corrected the `smile/elasticsuite` version constraint from `>=2.8.0` to `>=2.12.0`. The
  filter is registered through the `TypeProviderInterface` / `filterTypeProviders` pool, which
  ElasticSuite only introduced in **2.12.0**. The old constraint allowed installing 2.8–2.11,
  where that API does not exist, leading to a fatal error on the storefront. README requirement
  updated to match.

### Changed
- Modernized to PHP 8.3 idioms with no behavioral change:
  - Constructor property promotion across the datasource, aggregation plugin, configuration
    helper, navigation block, layered-navigation filter and the three setup data patches.
  - Injected dependencies are now `readonly` (immutable after construction).
  - Typed class constants on `Api\Data\StockInterface` and `Helper\Config`.

### Performance
- Removed the `Model\Layer\Filter\Stock::_initItems()` override. It called
  `parent::_initItems()` and then re-ran the same per-item loop a third time (relabel,
  apply-value, `krsort`), discarding and recomputing the work the parent had just done. The
  override only existed to compensate for `_getItemsData()` storing the display text as the
  item label; that now stores the raw faceted value (0/1) like core ElasticSuite, so the
  inherited `Smile\…\Boolean::_initItems()` resolves the label via the source model and matches
  the selection on the value with no extra pass. Net effect: one fewer full item loop and one
  fewer `krsort` per filter render, plus ~40 lines and three `SuppressWarnings` removed.

## [2.1.0] - 2026-06-05

### Added
- `Model\Product\Attribute\Source\Stock` — source model for the `stock_status` attribute.
  It maps the indexed integer (0/1) to a label so the attribute behaves like a standard
  option attribute: `$attribute->getSource()->getOptionText($value)` returns
  "In Stock" / "Out of Stock". This is the Magento-idiomatic place for the labels.
- `Setup\Patch\Data\AddStockStatusSource` — idempotent data patch that attaches the source
  model to the `stock_status` attribute on existing installs (the original create patch does
  not re-run).

### Changed
- `StockSetup` now creates the `stock_status` attribute with `source => Stock::class`.
- `Model\Layer\Filter\Stock::getLabel()` delegates to the attribute source model instead of
  hardcoding the "In Stock" / "Out of Stock" strings (DRY: the labels now live in one place).
  The attribute stays hidden from the product form (`input = hidden`, `is_visible = 0`); adding
  a source model only affects label resolution, not form visibility or ElasticSuite filtering.

## [2.0.0] - 2026-06-05

### Changed (Breaking)
- Register the stock layered-navigation filter through the core ElasticSuite
  `filterTypeProviders` pool (`Smile\ElasticsuiteCatalog\Api\Layer\Filter\TypeProviderInterface`)
  instead of overriding `Smile\ElasticsuiteCatalog\Model\Layer\FilterList`. This removes the
  last-wins virtual-type / preference conflict with other filter modules. No `preference`,
  no virtual-type override, and no module load-order constraint are required anymore.
- Removed the dependency on `Smile_ElasticsuiteRating` (composer `require` and `module.xml`
  `<sequence>`). The conflict it worked around no longer exists once both modules use the
  TypeProvider pattern.
- The system-managed `stock_status` attribute is now created with `is_visible = 0` so it no
  longer appears in the product edit form. In the UI-component form, `frontend_input = "hidden"`
  maps to a visible `input` element, so `is_visible = 0` is what actually hides the field.
  ElasticSuite layered navigation filters on `is_filterable`, never `is_visible`, so the stock
  filter is unaffected.

### Added
- `Api\Data\StockInterface` — neutral holder for the `stock_status` attribute code, shared by
  the datasource, setup, aggregation plugin and filter type provider (removes the backwards
  dependency on the aggregation plugin class for a constant).
- `Setup\Patch\Data\HideStockStatusFromProductForm` — idempotent data patch that sets
  `is_visible = 0` on existing installs (the original create patch does not re-run).

### Fixed
- Decimal stock quantities were mis-indexed as out of stock. With backorders enabled, the qty
  check cast the value to int before comparing (`(int) $qty > 0.01`), so any product with a
  fractional quantity below 1 (e.g. `0.5` for items sold by weight/length with
  `is_qty_decimal = 1`) was floored to `0` and indexed as Out of Stock. Now compared as a
  float (`(float) $qty > 0`).
- "Display Out Of Stock Filter" admin setting was inverted: enabling it hid the Out-of-Stock
  option instead of showing it. The condition is now correct.
- `StockSetup` no longer swallows exceptions while creating the attribute; a failed
  `addAttribute` now throws so the patch is not silently recorded as applied on a half-install.

### Removed
- Dead `Model\Layer\FilterList` override.
- Dead `Model\ResourceModel\...\Datasource\StockStatusData` resource model (unreachable).
- Dead commented block, unused `initStockStatusData()` method and unused `LoggerInterface`
  dependency in the indexer datasource.
- Unused `STOCK_FIELD` / `QTY_FIELD` constants and unused `Config` / `StoreManagerInterface`
  dependencies in the aggregation plugin.

### Internal
- Strict type declarations and return types added across datasource, aggregation and setup.
- PSR-12 / Magento2 coding-standard cleanups: private method renamed off the `_` prefix,
  leading backslashes removed from `di.xml` type name and `use` statements, docblocks aligned.
- README requirements corrected (the `Smile ElasticSuite Rating` requirement was stale).
