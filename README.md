# Amadeco ElasticSuite Stock Filter Module
[![Latest Stable Version](https://img.shields.io/github/v/release/Amadeco/module-elasticsuite-stock)](https://github.com/Amadeco/module-elasticsuite-stock/releases)
[![License](https://img.shields.io/badge/License-Proprietary-orange.svg)](https://github.com/Amadeco/module-elasticsuite-stock/blob/main/LICENSE)
[![Magento](https://img.shields.io/badge/Magento-2.4.x-brightgreen.svg)](https://magento.com)
[![PHP](https://img.shields.io/badge/PHP-8.1|8.2|8.3-blue.svg)](https://www.php.net)

[SPONSOR: Amadeco](https://www.amadeco.fr)

This module by Amadeco extends the Smile ElasticSuite (https://github.com/Smile-SA/elasticsuite) to add an advanced stock filter in the layered navigation.

## Features
- Adds a dedicated stock filter in the layered navigation
- Configure the filter behavior based on Magento's backorders setting
- Option to consider product quantity for determining stock status
- Intelligently adjust displayed product counts based on actual availability
- Fully compatible with Magento's MSI (Multi-Source Inventory)

## Installation

```bash
composer require amadeco/module-elasticsuite-stock
bin/magento module:enable Amadeco_ElasticsuiteStock
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
bin/magento indexer:reindex catalogsearch_fulltext
```

## Requirements

- PHP 8.1, 8.2 or 8.3
- Magento 2.4.x
- Smile ElasticSuite 2.8 or higher

## Configuration

Go to Stores > Configuration > ElasticSuite Stock Filter

Configure the following options:

- Respect Backorders Configuration: When enabled, the filter respects Magento's backorders settings
- Consider Product Quantity: When enabled and backorders are not allowed, products with qty ≤ 0 will be considered out of stock

## Usage

After installation and configuration, the stock filter will automatically appear in the layered navigation on category pages and search results pages where stock status is set as filterable.

The filter provides two options:

1. In Stock: Shows only available products
2. Out of Stock: Shows only unavailable products

## Technical Details

This module:

- Creates a new filter type for stock status in ElasticSuite
- Enhances the stock filter to consider both stock status and quantity
- Respects Magento's backorders configuration
- Integrates with ElasticSuite's existing facet system
- Adjusts product counts in filter options based on the actual stock availability

### Stock Data in ElasticSearch

This module uses the `stock.is_in_stock` and `stock.qty` fields that are already indexed by ElasticSuite in the product documents rather than creating custom attributes.

**Why not use `quantity_and_stock_status` attribute?**

While Magento has a built-in `quantity_and_stock_status` attribute, it's stored as a complex structure:

```php
[
    'is_in_stock' => $stockItem->getIsInStock(),
    'qty' => $stockItem->getQty()
]
```

This complex structure is difficult to use directly for filtering in ElasticSearch. Instead, our module uses the following approach:

Leverages `stock.is_in_stock` field already indexed by ElasticSuite
Optionally uses `stock.qty` for advanced filtering when respecting backorders configuration
Applies business logic based on the store's backorders configuration

This approach provides several advantages:

- No need to create or modify attributes
- Direct access to already indexed stock data
- Better performance without additional indexing operations
- More accurate stock status representation when considering backorders settings

When the backorders setting is considered and "No Backorders" is configured, products with is_in_stock=1 but qty<=0 are treated as out of stock, providing a more accurate representation of actual product availability.

Note `quantity_and_stock_status` attribute is actually not filterable natively as a raising bug is still pending for a solution from the Core team :
https://github.com/magento/magento2/issues/33453


## License

This module is licensed under the Open Software License ("OSL") v3.0.