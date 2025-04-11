# Amadeco ElasticSuite Stock Filter Module

[![Latest Stable Version](https://img.shields.io/github/v/release/Amadeco/module-elasticsuite-stock)](https://github.com/Amadeco/module-elasticsuite-stock/releases)
[![License](https://img.shields.io/github/license/Amadeco/magento2-elasticsuite-stock)](https://github.com/Amadeco/magento2-elasticsuite-stock/blob/main/LICENSE)
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

Go to Stores > Configuration > ElasticSuite > Stock Filter

Configure the following options:

- Consider Only Product Quantity: When enabled, products with qty ≤ 0 will be considered out of stock whild qty > 0 will be considered in stock.

## Usage

After installation and configuration, the stock filter will automatically appear in the layered navigation on category pages and search results pages where stock status is set as filterable.

The filter provides two options:

1. In Stock: Shows only available products
2. Out of Stock: Shows only unavailable products

## Technical Details

This module:

- Creates a new product attribute / filter (code stock_status) in ElasticSuite
- Enhances the stock filter to consider both stock status and quantity
- Respects Magento's backorders configuration
- Integrates with ElasticSuite's existing facet system
- Adjusts product counts in filter options based on the actual stock availability

### Stock Data in ElasticSearch

This module directly leverages the `stock.is_in_stock` and `stock.qty` fields that are already indexed by ElasticSuite in the product documents. Instead of creating additional attributes or performing redundant database queries, we use the existing data structure for optimal performance.

**Direct use of ElasticSuite's indexed fields**

The module works with two key fields in the ElasticSearch index:
- `stock.is_in_stock`: Boolean indicator (0/1) showing if a product is marked as in stock
- `stock.qty`: Numerical value representing the actual product quantity

Our implementation offers these advantages:
- Zero additional indexing overhead (uses data already indexed by ElasticSuite)
- No database queries during filter application (everything happens in ElasticSearch)
- Superior performance by avoiding data duplication
- More accurate stock filtering when considering backorders settings

**Why not use `quantity_and_stock_status` attribute?**

While Magento has a built-in `quantity_and_stock_status` attribute, it has two significant limitations:
1. It's stored as a complex structure that's difficult to use directly for filtering in ElasticSearch
2. It's not natively filterable in Magento (see reported issue: https://github.com/magento/magento2/issues/33453)

Our approach provides a cleaner, extensible, more efficient solution by using the properly indexed `stock.is_in_stock` and `stock.qty` fields directly.

**Advanced Logic for Better User Experience**

When the "Consider Only Product Quantity" option is enabled, our module implements this logic:
- For products with `is_in_stock=1` but `qty<=0` are considered out of stock
- This provides a more accurate representation of actual product availability to customers

## License

This module is licensed under the Open Software License ("OSL") v3.0.