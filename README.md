# Amadeco ElasticSuite Stock Filter Module for Magento 2

[![Latest Stable Version](https://img.shields.io/github/v/release/Amadeco/magento2-elasticsuite-stock)](https://github.com/Amadeco/magento2-elasticsuite-stock/releases)
[![Magento 2](https://img.shields.io/badge/Magento-2.4.x-brightgreen.svg)](https://magento.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-blue.svg)](https://www.php.net)
[![License](https://img.shields.io/github/license/Amadeco/magento2-elasticsuite-stock)](https://github.com/Amadeco/magento2-elasticsuite-stock/blob/main/LICENSE.txt)

[SPONSOR: Amadeco](https://www.amadeco.fr)

This module by Amadeco extends Smile ElasticSuite (https://github.com/Smile-SA/elasticsuite) to add an advanced stock filter in the layered navigation.

## Features

<img width="1132" alt="Capture d'écran 2025-04-13 à 21 23 40" src="https://github.com/user-attachments/assets/ecdf90d2-6afa-4524-964a-b2127db1ea5b" />

- Adds a dedicated stock filter in the layered navigation
- Intelligent handling of stock status based on Magento's backorders configuration
- Improves user experience by showing accurate product availability
- Provides clear "In Stock" and "Out of Stock" filter options
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

- PHP 8.3+
- Magento 2.4.x
- Smile ElasticSuite 2.8 or higher
- Smile ElasticSuite Rating 2.3 or higher

## Configuration

Go to Stores > Configuration > ElasticSuite > Stock Filter

Configure the following options:

- **Display Out Of Stock Filter**: When enabled, shows the "Out of Stock" option in the layered navigation. When disabled, only the "In Stock" option is shown.

## Usage

After installation and configuration, the stock filter will automatically appear in the layered navigation on category pages and search results pages (remember to reindex after installation).

## How It Works

### Smart Stock Status Determination

This module creates an indexed attribute `stock_status` that handles stock differently based on your Magento configuration:

#### When Backorders are Disabled
- Uses Magento's native stock status logic
- Products are either in stock or out of stock, as determined by Magento's inventory system

#### When Backorders are Enabled
- **Enhanced behavior**: Determines availability based on actual quantity
- Products with quantity > 0 are marked as "In Stock"
- Products with quantity ≤ 0 are marked as "Out of Stock" (even if Magento would allow them to be ordered)
- This gives customers a clearer view of which products have physical inventory

### Filter Options in Layered Navigation

The filter provides these options:

1. **In Stock**:
   - Shows products with positive inventory when backorders are enabled
   - Follows standard Magento stock status rules when backorders are disabled

2. **Out of Stock** (can be hidden via configuration):
   - Shows products with zero or negative inventory when backorders are enabled
   - Shows products marked as out of stock when backorders are disabled

This approach helps customers find products based on actual availability while respecting your inventory settings.

## Technical Details

### Stock Data Implementation

This module leverages the existing `stock.is_in_stock` and `stock.qty` fields from `quantity_and_stock_status` attribute and creates a derived `stock_status` field that combines this information intelligently.

Instead of relying on Magento's `quantity_and_stock_status` attribute (which has limitations for filtering ; see reported issue: https://github.com/magento/magento2/issues/33453), our module:

- Creates a dedicated filterable attribute for stock status
- Applies specific logic when backorders are enabled to show actual inventory status
- Integrates seamlessly with ElasticSuite's facet filtering system

## Compatibility with Other ElasticSuite Modules

### Dependency on Smile_ElasticsuiteRating: Technical Explanation

Smile_ElasticsuiteRating is a required dependency due to how Magento's preference system works:

Both modules (Rating and our Stock module) attempt to override Smile\ElasticsuiteCatalog\Model\Layer\FilterList using preferences.
In Magento's preference system, when multiple modules declare a preference for the same class, only the last one loaded (based on module loading order) takes effect.
If Smile_ElasticsuiteRating is installed but our module's preference isn't properly applied, our stock filter won't appear in the layered navigation.

To solve this conflict, we must:

```xml
<preference for="Smile\ElasticsuiteCatalog\Model\Layer\FilterList" 
            type="Amadeco\ElasticsuiteStock\Model\Layer\FilterList"/>
            
<preference for="Smile\ElasticsuiteRating\Model\Layer\FilterList" 
            type="Amadeco\ElasticsuiteStock\Model\Layer\FilterList"/>
```

This approach requires Smile_ElasticsuiteRating to be present since we're explicitly overriding its class. Without it, Magento would throw a fatal error when trying to resolve this preference.

**Note:** We recognize that the current dependency on Smile_ElasticsuiteRating is not ideal. In a future release, we plan to implement a more flexible architecture.

## License

This module is licensed under the Open Software License ("OSL") v3.0. See the [LICENSE.txt](LICENSE.txt) file for details.
