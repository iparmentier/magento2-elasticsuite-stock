# Comwrap ElasticSuite Blog Search Module for Magento 2

[![Latest Stable Version](https://img.shields.io/github/v/release/iparmentier/magento2-elasticsuite-stock)](https://github.com/iparmentier/magento2-elasticsuite-stock/releases)
[![Magento 2](https://img.shields.io/badge/Magento-2.3.x|2.4.x-brightgreen.svg)](https://magento.com)
[![PHP](https://img.shields.io/badge/PHP-7.3+-blue.svg)](https://www.php.net)
[![License](https://img.shields.io/badge/License-OSL--3.0-green.svg)](https://opensource.org/licenses/OSL-3.0)

This module extends [Smile ElasticSuite](https://github.com/Smile-SA/elasticsuite) to integrate [Magefan Blog](https://github.com/magefan/module-blog) posts into the search engine, providing a unified search experience across products and blog content.

## Features

- **Blog Post Search Integration**: Seamlessly indexes blog posts into ElasticSearch
- **Autocomplete Support**: Shows blog posts in search autocomplete suggestions
- **Dedicated Search Results**: Displays blog posts on the search results page
- **Full-Text Search**: Searches through blog titles and content
- **Smart Filtering**: Only shows active and searchable blog posts
- **Multi-Store Support**: Respects store-specific blog post visibility
- **SEO Optimized**: Helps users discover relevant blog content alongside products

## Screenshots

### Autocomplete Integration
Blog posts appear in the search autocomplete dropdown with a dedicated "News" section:

```
🔍 Search: "installation guide"
├── Products (3)
├── Categories (1)
└── News (2)
    ├── How to Install Your New Kitchen
    └── Installation Tips & Tricks
```

### Search Results Page
Blog posts are displayed in a dedicated section on the search results page:

```
Search results for: "installation"

Results in blog posts:
• How to Install Your New Kitchen
• Installation Tips & Tricks
• Professional Installation Services
[Show more...]

Products:
[Product results...]
```

## Installation

```bash
composer require iparmentier/magento2-elasticsuite-stock
bin/magento module:enable Comwrap_ElasticsuiteBlog
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
bin/magento indexer:reindex elasticsuite_blog_fulltext
```

## Requirements

- PHP 7.3+ or PHP 8.x
- Magento 2.3.x or 2.4.x
- Smile ElasticSuite 2.1 or higher
- Magefan Blog extension

## Configuration

### Admin Configuration

Navigate to **Stores > Configuration > ElasticSuite > Blog Post settings**

#### Blog Post Settings
- **Max result**: Maximum number of blog posts to display in the search results block (default: 5)

#### Autocomplete Settings
Navigate to **Stores > Configuration > ElasticSuite > Autocomplete > Blog Post Autocomplete**
- **Max size**: Maximum number of blog posts to display in autocomplete results (default: 5)

### Making Blog Posts Searchable

1. Go to **Content > Blog > Posts**
2. Edit a blog post
3. In the **Search Engine** section, enable **"Is searchable"**
4. Save the post

The "Is searchable" flag determines whether a blog post will be indexed and appear in search results.

## Usage

After installation and configuration:

1. **Enable Search for Blog Posts**: Mark blog posts as searchable in the admin panel
2. **Reindex**: Run the blog fulltext reindex to populate the search engine
3. **Test Search**: Try searching for blog content from the frontend

The module automatically:
- Indexes searchable blog posts into ElasticSearch
- Shows relevant posts in autocomplete suggestions
- Displays blog results on the search page
- Filters out inactive or non-searchable posts

## How It Works

### Indexing Process

The module creates a dedicated ElasticSearch index for blog posts with the following fields:

```
blog_post index:
├── post_id (integer)
├── title (text, searchable, spellcheck)
├── content (text, searchable, spellcheck)
└── is_active (integer)
```

### Content Processing

1. **HTML Filtering**: Blog content is processed to remove HTML tags and formatting
2. **Text Normalization**: Multiple spaces are collapsed for better search relevance
3. **Store Filtering**: Only posts visible in the current store are indexed

### Search Integration

The module integrates at multiple levels:

1. **Autocomplete**: Adds a blog post data provider to search suggestions
2. **Search Results**: Injects a blog results block above product results
3. **Query Processing**: Uses ElasticSuite's query builder for relevant results

## Technical Details

### Architecture

```
Comwrap_ElasticsuiteBlog/
├── Model/
│   ├── Autocomplete/
│   │   └── Post/DataProvider.php      # Autocomplete data provider
│   ├── Post/Indexer/
│   │   └── Fulltext.php               # Main indexer implementation
│   └── ResourceModel/
│       └── Post/Fulltext/
│           └── Collection.php         # ElasticSearch-aware collection
├── Block/
│   └── Post/
│       ├── Result.php                 # Search results block
│       └── Suggest.php                # Suggestion block
└── etc/
    ├── elasticsuite_indices.xml       # Index configuration
    └── elasticsuite_search_request.xml # Search container config
```

### Key Components

1. **Indexer**: `elasticsuite_blog_fulltext` - Processes and indexes blog posts
2. **Search Container**: `blog_search_container` - Defines search request structure
3. **Collection**: Custom collection that queries ElasticSearch instead of MySQL

### Database Schema

The module adds an `is_searchable` field to the `magefan_blog_post` table:

```sql
ALTER TABLE `magefan_blog_post` 
ADD COLUMN `is_searchable` tinyint(1) NOT NULL DEFAULT 0 
COMMENT 'If post is searchable';
```

## Troubleshooting

### Blog posts not appearing in search

1. Ensure posts are marked as "Is searchable" in admin
2. Verify posts are active
3. Run reindex: `bin/magento indexer:reindex elasticsuite_blog_fulltext`
4. Clear cache: `bin/magento cache:clean`
## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues and feature requests, please use the [GitHub issue tracker](https://github.com/iparmentier/magento2-elasticsuite-stock/issues).

## License

This module is licensed under the Open Software License ("OSL") v3.0. See the [LICENSE.txt](LICENSE.txt) file for details.

## Credits

- Developed by Comwrap
- Based on [Smile ElasticSuite](https://github.com/Smile-SA/elasticsuite)
- Integrates with [Magefan Blog](https://github.com/magefan/module-blog)
