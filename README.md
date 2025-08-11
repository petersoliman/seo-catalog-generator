# SEO Catalog Generator

A comprehensive PHP-based system for managing and displaying product catalogs with SEO optimization, featuring structured data (JSON-LD), multilingual support, and database integration.

## 🚀 Features

- **Product Management**: Import and manage products from JSON-LD structured data
- **Database Integration**: MySQL database with comprehensive product schema
- **Multilingual Support**: English and Arabic product names and descriptions
- **SEO Optimization**: JSON-LD structured data for search engines
- **Product Viewer**: Clean, responsive web interface for browsing products
- **Search & Filtering**: Advanced search and category filtering capabilities
- **Image Management**: Product image URL handling and optimization
- **Duplicate Prevention**: Smart SKU-based duplicate detection and handling

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Data Format**: JSON-LD (Schema.org structured data)
- **Database Access**: PDO (PHP Data Objects)

## 📁 Project Structure

```
scrbrz/
├── config.php                 # Database configuration
├── database_setup.php         # Database initialization script
├── parse_products.php         # Main product viewer interface
├── 21cat.txt                 # Power tools JSON-LD data
├── 11cat.txt                 # Accessories JSON-LD data
├── index.php                 # Main entry point
├── .gitignore               # Git ignore rules
└── README.md                # Project documentation
```

## 🗄️ Database Schema

The system uses a comprehensive `products` table with the following structure:

- `id` - Primary key
- `name` - Product name (English)
- `name_ar` - Product name (Arabic)
- `description` - Product description (English)
- `description_ar` - Product description (Arabic)
- `main_image_url` - Product image URL
- `sku` - Unique product identifier
- `model_number` - Product model number
- `brand` - Product brand
- `category` - Product category
- `url` - Product URL
- `more_details` - Additional product details (JSON)
- `created_at` - Creation timestamp

## 🚀 Quick Start

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/petersoliman/seo-catalog-generator.git
   cd seo-catalog-generator
   ```

2. **Configure database**
   - Edit `config.php` with your database credentials
   - Run `php database_setup.php` to create the database and tables

3. **Import product data**
   - The system includes sample data in `21cat.txt` and `11cat.txt`
   - Use the import scripts to populate your database

4. **Access the application**
   - Navigate to `parse_products.php` in your web browser
   - Browse and search through your product catalog

## 📊 Data Sources

The system is designed to work with Bosch Professional product data, including:

- **Power Tools**: 21 categories of professional power tools
- **Accessories**: 11 categories of tool accessories and consumables
- **Structured Data**: JSON-LD format following Schema.org standards

## 🔧 Configuration

### Database Configuration (`config.php`)

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'products_api');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Web Server Configuration

Ensure your web server is configured to handle PHP files and has access to the MySQL database.

## 📱 Features

### Product Viewer
- Responsive grid layout
- Product cards with images and details
- Search functionality
- Category filtering
- Pagination support

### Data Management
- Automatic duplicate detection
- SKU-based product identification
- Multilingual content generation
- Image URL optimization

### SEO Features
- JSON-LD structured data
- Semantic HTML markup
- Meta information optimization
- Search engine friendly URLs

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions, please open an issue on GitHub or contact the development team.

## 🔄 Version History

- **v1.0.0** - Initial release with basic product management
- **v1.1.0** - Added multilingual support (Arabic)
- **v1.2.0** - Enhanced duplicate detection and data integrity
- **v1.3.0** - Improved product viewer and search functionality

---

**Built with ❤️ for professional product catalog management**
