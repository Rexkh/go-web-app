# Beauty Shop - E-Commerce Platform

A comprehensive e-commerce website for selling beauty products, built with PHP and SQLite. This platform mimics WooCommerce functionality with a modern, elegant design.

## Features

### Frontend
- **Homepage**: Hero section, featured products, categories, new arrivals, special offers
- **Shop Page**: Product filtering by category, price range, search, sorting options
- **Product Pages**: Detailed product info, image gallery, related products, reviews
- **Shopping Cart**: AJAX cart updates, coupon codes, real-time totals
- **Checkout**: Billing/shipping addresses, order summary, multiple payment methods
- **User Account**: Order history, profile management, password change, addresses
- **Responsive Design**: Mobile-first approach with smooth transitions

### Admin Panel
- **Dashboard**: Sales statistics, recent orders, quick overview
- **Products Management**: CRUD operations, image uploads, stock management
- **Orders Management**: View orders, update status, filter by status
- **Customers**: Customer list with order history and spending
- **Categories**: Category management with hierarchy support
- **Coupons**: Create percentage/fixed discounts with usage limits
- **Settings**: Store information configuration

### Technical Features
- SQLite database (no MySQL required)
- Session-based cart (guest and logged-in users)
- CSRF protection
- Password hashing with bcrypt
- Input sanitization and validation
- AJAX-powered cart operations
- Smooth CSS transitions and animations
- SEO-friendly URLs

## Project Structure

```
beauty-shop/
├── includes/
│   ├── config.php          # Configuration and constants
│   ├── database.php        # Database connection class
│   ├── functions.php       # Helper functions
│   ├── class-product.php   # Product management
│   ├── class-cart.php      # Shopping cart logic
│   ├── class-order.php     # Order processing
│   ├── class-user.php      # User authentication
│   └── class-coupon.php    # Coupon management
├── templates/
│   ├── header.php          # Site header
│   └── footer.php          # Site footer
├── api/
│   ├── cart.php            # Cart API endpoints
│   ├── checkout.php        # Checkout processing
│   └── account.php         # Account management API
├── admin/
│   ├── index.php           # Admin dashboard
│   ├── products.php        # Product management
│   ├── orders.php          # Order management
│   ├── customers.php       # Customer management
│   ├── categories.php      # Category management
│   ├── coupons.php         # Coupon management
│   └── settings.php        # Settings page
├── assets/
│   ├── css/
│   │   └── style.css       # Main stylesheet
│   └── js/
│       └── main.js         # JavaScript functionality
├── uploads/                # Product images
├── index.php               # Homepage
├── shop.php                # Product listing
├── product.php             # Single product page
├── cart.php                # Shopping cart
├── checkout.php            # Checkout page
├── order-received.php      # Order confirmation
├── login.php               # User login
├── register.php            # User registration
├── my-account.php          # User account dashboard
└── logout.php              # User logout
```

## Installation

### Requirements
- PHP 7.4 or higher
- SQLite3 extension enabled
- Web server (Apache/Nginx) or PHP built-in server

### Setup Steps

1. **Clone or copy the project** to your web server directory

2. **Ensure write permissions** for the database and uploads:
   ```bash
   chmod 755 beauty-shop/
   chmod 777 beauty-shop/data/
   chmod 777 beauty-shop/uploads/
   ```

3. **Access the application** in your browser:
   - The database will be created automatically on first access
   - Default admin credentials will be created

4. **Create default admin user** (run once):
   Navigate to `admin/setup-admin.php` or use the included SQL

5. **Start using the platform**:
   - Frontend: `http://your-domain.com/`
   - Admin: `http://your-domain.com/admin/`

### Using PHP Built-in Server

```bash
cd beauty-shop
php -S localhost:8000
```

Then visit: http://localhost:8000

## Default Credentials

After running the setup:
- **Admin Email**: admin@beautyshop.com
- **Admin Password**: admin123

**Important**: Change these credentials immediately after first login!

## Configuration

Edit `includes/config.php` to customize:

- Site name and branding
- Currency settings
- Tax rates
- Shipping options
- Upload limits
- Pagination settings

## Security Features

- CSRF token protection on all forms
- Password hashing with configurable cost
- Input sanitization and validation
- SQL injection prevention via prepared statements
- Session-based authentication
- Admin role verification

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## License

This project is proprietary software. All rights reserved.

## Support

For support and questions, please contact the development team.
