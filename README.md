# TechElectronics - Full PHP + MySQL Website

A complete e-commerce website for a tech/electronics business built with PHP and MySQL, featuring product management, user authentication, shopping cart, checkout system, and comprehensive admin panel.

## 🚀 Features

### Public Features
- **Homepage** with hero section, services preview, featured products, testimonials
- **Shop** with product filtering, search, and category browsing
- **Product Details** pages with ratings, stock status, and cart functionality
- **Services Page** showcasing 6 business services
- **Student Hub** with project consultation, tutorials, and custom kits
- **Tutorials** section with categorized learning content
- **About Page** with company story, mission, values, and team
- **Contact Page** with form, location, and FAQ accordion
- **User Dashboard** to view orders and account details
- **Shopping Cart** with quantity updates and order summary
- **Secure Checkout** with multiple payment methods

### Admin Features
- **Dashboard** with statistics and recent activity
- **Product Management** - Add, edit, delete products
- **Order Management** - View and update order statuses
- **User Management** - Manage user roles
- **Message Management** - View and respond to contact messages
- Complete CRUD operations for all data

### Design & UX
- Modern, responsive design (mobile, tablet, desktop)
- Vibrant blue (#0077be) and teal (#00a3a3) color scheme
- Smooth hover effects and transitions
- Card-based layouts with subtle shadows
- Star rating system for products
- Testimonials slider/carousel
- Clean, semantic HTML5
- Bootstrap 5 framework with custom CSS

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server with mod_rewrite enabled
- Modern web browser

## 🛠️ Installation

### 1. Database Setup

1. Create a new MySQL database:
```sql
CREATE DATABASE tech_electronics;
```

2. Import the database schema:
```bash
mysql -u your_username -p tech_electronics < database.sql
```

Or use phpMyAdmin to import the `database.sql` file.

### 2. Configuration

1. Open `includes/config.php` and update the database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_database_username');
define('DB_PASS', 'your_database_password');
define('DB_NAME', 'tech_electronics');
define('SITE_URL', 'http://localhost/pk_2026'); // Update to your site URL
```

### 3. File Permissions

Ensure the following directories are writable:
```bash
chmod 755 assets/images/uploads/
```

### 4. Web Server Configuration

For Apache, the `.htaccess` file is already configured. Make sure `mod_rewrite` is enabled:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 5. Access the Website

- **Public Site**: `http://localhost/pk_2026/`
- **Admin Panel**: `http://localhost/pk_2026/admin/`

## 🔐 Default Credentials

### Admin Account
- **Email**: admin@techelectronics.com
- **Password**: admin123

**⚠️ Important**: Change the admin password immediately after first login!

## 📁 Project Structure

```
pk_2026/
├── admin/                  # Admin panel files
│   ├── index.php          # Admin dashboard
│   ├── products.php       # Product management
│   ├── orders.php         # Order management
│   ├── users.php          # User management
│   └── messages.php       # Contact messages
├── assets/
│   ├── css/
│   │   └── style.css      # Custom styles
│   ├── js/
│   │   └── script.js      # Custom JavaScript
│   └── images/            # Image assets
│       ├── products/      # Product images
│       ├── tutorials/     # Tutorial images
│       └── testimonials/  # User avatars
├── includes/
│   ├── config.php         # Site configuration
│   ├── db.php             # Database connection
│   ├── auth.php           # Authentication functions
│   ├── functions.php      # Helper functions
│   ├── header.php         # Site header
│   └── footer.php         # Site footer
├── index.php              # Homepage
├── shop.php               # Products listing
├── product.php            # Product details
├── cart.php               # Shopping cart
├── cart_action.php        # Cart AJAX handler
├── checkout.php           # Checkout page
├── services.php           # Services page
├── student_hub.php        # Student support
├── tutorials.php          # Tutorials listing
├── tutorial.php           # Single tutorial
├── about.php              # About page
├── contact.php            # Contact page
├── login.php              # User login
├── register.php           # User registration
├── dashboard.php          # User dashboard
├── logout.php             # Logout handler
├── database.sql           # Database schema
├── .htaccess              # Apache configuration
└── README.md              # This file
```

## 🎨 Color Scheme

- **Primary Blue**: #0077be - Main brand color
- **Accent Teal**: #00a3a3 - Secondary highlights
- **Background**: #ffffff - Clean white
- **Text**: #333333 - Dark gray for readability

## 🔧 Customization

### Adding Products
1. Log into admin panel
2. Go to Products → Add New Product
3. Fill in product details
4. Add product image to `assets/images/products/`

### Modifying Services
1. Log into admin panel or directly edit database
2. Update `services` table with new service information
3. Service icons use Font Awesome classes

### Changing Site Name
Edit `includes/config.php`:
```php
define('SITE_NAME', 'Your Company Name');
```

### Email Configuration
The site uses PHP's `mail()` function. For production, configure SMTP in `includes/functions.php` using PHPMailer or similar.

## 🛒 Shopping Cart Features

- Add/remove products
- Update quantities
- Real-time total calculation
- Session-based (no login required)
- Persistent across pages

## 💳 Payment Integration

The checkout page includes stubs for:
- M-PESA mobile payment
- Credit/Debit cards
- Cash on delivery

**Note**: Payment processing is simulated. Integrate actual payment gateways (Stripe, PayPal, M-PESA API) for production use.

## 📱 Responsive Design

The website is fully responsive and tested on:
- Desktop (1920px and above)
- Laptop (1366px - 1920px)
- Tablet (768px - 1365px)
- Mobile (320px - 767px)

## 🔒 Security Features

- Password hashing with `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- CSRF protection recommended for forms
- Session security settings
- Admin authentication required

## 📧 Contact Form

Contact messages are saved to database and can be viewed in admin panel. Email notifications are sent using PHP `mail()`.

## 🐛 Troubleshooting

### Database Connection Error
- Verify credentials in `includes/config.php`
- Ensure MySQL service is running
- Check database exists and schema is imported

### Images Not Displaying
- Check file paths in database match actual files
- Verify `assets/images/` permissions
- Use placeholder images for missing files

### Admin Panel Access Denied
- Ensure you're logged in with admin account
- Check `users` table for `role = 'admin'`

### Cart Not Working
- Enable PHP sessions
- Check JavaScript console for errors
- Verify `cart_action.php` is accessible

## 🚀 Deployment

### For Production:

1. **Database**: Use strong passwords, restrict remote access
2. **Files**: Set proper permissions (755 for directories, 644 for files)
3. **Config**: Update `SITE_URL` to your domain
4. **HTTPS**: Enable SSL and update `.htaccess`
5. **Email**: Configure SMTP for reliable email delivery
6. **Backups**: Regular database and file backups
7. **Security**: Keep PHP and MySQL updated

## 📚 Sample Data

The database includes:
- 12 sample products (Arduino, Raspberry Pi, sensors, etc.)
- 6 services (Electrical, IoT, Web Development, etc.)
- 3 tutorials (Arduino, ESP32, Raspberry Pi)
- 6 testimonials from customers and students

## 🤝 Support

For issues or questions about this project:
- Review the code comments for detailed explanations
- Check the database schema in `database.sql`
- Examine error logs in your web server

## 📄 License

This project is provided as-is for educational and commercial use.

## ✨ Credits

- **Framework**: Bootstrap 5
- **Icons**: Font Awesome 6
- **Fonts**: System sans-serif fonts
- **Images**: Placeholder images (replace with actual product photos)

---

**Developed by**: TechElectronics Team
**Version**: 1.0
**Date**: January 2026
